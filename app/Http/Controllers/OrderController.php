<?php

namespace App\Http\Controllers;

use App\Models\Guarantor;
use App\Models\Insurance;
use App\Models\Order;
use Aranyasen\HL7\Message;
use Aranyasen\HL7\Segments\MSH;
use Aranyasen\HL7\Segments\PID;
use Config;
use App\Models\OutgoingMessage;
use App\Models\Patient;
use Aranyasen\HL7;
use Aranyasen\HL7\Segments\NK1;
use Aranyasen\HL7\Segments\PV1;
use Aranyasen\HL7\Segments\IN1;
use Aranyasen\HL7\Segments\GT1;
use Aranyasen\HL7\Segments\DG1;
use Aranyasen\HL7\Segments\ORC;
use Aranyasen\HL7\Segments\OBR;
use Database\Factories\PatientFactory;
use Faker\Generator;
use Illuminate\Container\Container;


class OrderController extends Controller
{

/*

Segments (*: mandatory):

MSH => Message Header *
PID => Patient Identification *
NK1 => Next of Kin
PV1 => Patient Visit *
IN1 => Insurance
GT1 => Guarantor *
DG1 => Diagnosis
ORC => Common Order *
OBR => Observation Request *
NTE => Notes and Comments (use is not recommended)
OBX => Observation/Result (only for AOEs)
SPM => Specimen

*/

    private function generateMSH($data) {
        $msh= new MSH();

        // Sending Application (LCS Vendor Mnemonic)
        $msh->setField(3,strtoupper($data["sender"]["app"]));

        // Sending Facility (Client Site ID)
        $msh->setField(4,strtoupper($data["sender"]["facility"]));

        // Receiving Application ('1100' - Labcorp Lab System)
        $msh->setField(5,strtoupper(Config::get("app.RECEIVING_APP")));

        // Receiving Facility (Responsible Lab Code)
        $msh->setField(6,strtoupper($data["receiving"]["facility"]));

        // Date/Time of Message - format: YYYYMMDDHHMM
        $msh->setField(7,date("YmdHi"));

        // Message Type - fixed value: ORM (Order)
        $msh->setField(9, Config::get("app.MSH_MESSAGE_TYPE"));

        // Processing ID - "P" for production or empty in other cases
        $msh->setField(11,strtoupper(Config::get("app.PRODUCTION_MODE")));

        return $msh;
    }

    private function generatePID($patientData,$order) {
        $pid= new PID();

        // Sequence Number, hardcoded
        $pid->setField(1,Config::get("app.PID_SEQUENCE_NUMBER"));

        // External Patient ID (WRS patient ID)
        $pid->setField(2,$patientData["id"]);

        // Patient name
        $pid->setField(5,implode("^",[strtoupper($patientData["surname"]),strtoupper($patientData["name"]),(isset($patientData["middleName"])?strtoupper($patientData["middleName"]):null)]));

        // Patient birthday
        $pid->setField(7,date("Ymd",strtotime($patientData["birthday"])));

        // Patient gender
        $pid->setField(8,$patientData["gender"]);

        // Patient race
        $pid->setField(10,$patientData["race"]);

        // Patient address
        $pid->setField(11, implode("^",[
            $patientData["address"],
            null,
            $patientData["city"],
            $patientData["state"],
            $patientData["zip"],
        ]));

        // Patient phone number
        $pid->setField(13, implode("^",[
            $patientData["phoneNumber"],
            null,
            "PH",
            null
        ]));

        // Account Number (Use: For client identification purposes and for electronic data retrieval.)
        // LabCorp Client ID
        // Also defines the payer

        $pid->setField(18, implode("^", [
            $order["accountNumber"],
            null,
            null,
            $order["payer"]

        ]));

        // Patient ethnic
        $pid->setField(22, $patientData["ethnic"]);

        return $pid;
    }

    // Next of kin
    private function generateNK1($nextOfKinData) {
        $nk1= new NK1();

        // Sequence Number, hardcoded
        $nk1->setField(1,Config::get("app.NK1_SEQUENCE_NUMBER"));

        // Next of kin name
        $nk1->setField(2,implode("^",[strtoupper($nextOfKinData["surname"]),strtoupper($nextOfKinData["name"]),(isset($nextOfKinData["middleName"]))?strtoupper($nextOfKinData["middleName"]):null]));

        // Type of relationship, hardcoded
        $nk1->setField(3,Config::get("app.NK1_RELATIONSHIP"));

        // Next of kin  address
        $nk1->setField(4, implode("^",[
            $nextOfKinData["address"],
            null,
            $nextOfKinData["city"],
            $nextOfKinData["state"],
            $nextOfKinData["zip"],
        ]));

        // Next of kin phone number
        $nk1->setField(5, $nextOfKinData["phoneNumber"]);

        return $nk1;
    }

    // Patient visit
    private function generatePV1($visit) {
        $pv1= new PV1();

        // Sequence Number, hardcoded
        $pv1->setField(1,Config::get("app.PV1_SEQUENCE_NUMBER"));

        // Patient class
        $pv1->setField(2,$visit["patientClass"]);

        return $pv1;
    }

    // Insurance
    private function generateIN1($insurance) {

        $in1= new IN1();

        // Insurance Company Identification Number & Insurance Payer Code
        $in1->setField(3,implode("^",[
            (isset($insurance["identificationNumber"])?strtoupper($insurance["identificationNumber"]):null),
            (isset($insurance["payerCode"])?strtoupper($insurance["payerCode"]):null),
        ]));

        // Insurance Company Name
        $in1->setField(4,$insurance["insuranceCompanyName"]);

        // Insurance Company Address
        $in1->setField(5,implode("^",[
            strtoupper($insurance["insuranceCompanyAddress"]),
            null,
            strtoupper($insurance["insuranceCompanyCity"]),
            strtoupper($insurance["insuranceCompanyState"]),
            strtoupper($insurance["insuranceCompanyZip"]),
        ]));

        // Group Number of Insured Patient
        $in1->setField(8,(isset($insurance["insuredPatientGroupNumber"])?strtoupper($insurance["insuredPatientGroupNumber"]):null));

        // Insured’s Group Employer Name
        $in1->setField(11,(isset($insurance["insuredGroupEmployerName"])?strtoupper($insurance["insuredGroupEmployerName"]):null));

        // Plan Type - Should be deprecated and replaced with the new billing methods
        $in1->setField(15,(isset($insurance["planType"])?strtoupper($insurance["planType"]):null));

        // Insured name
        $in1->setField(16,implode("^",[
            (isset($insurance["insuredSurname"])?strtoupper($insurance["insuredSurname"]):null),
            (isset($insurance["insuredName"])?strtoupper($insurance["insuredName"]):null),
            (isset($insurance["insuredMiddleName"])?strtoupper($insurance["insuredMiddleName"]):null),
        ]));

        // Insured’s Relationship to Patient
        $in1->setField(17,$insurance["insuredRelationshipWithPatient"]);

        // Insured address
        $in1->setField(19,implode("^",[
            (isset($insurance["insuredAddress"])?strtoupper($insurance["insuredAddress"]):null),
            null,
            (isset($insurance["insuredCity"])?strtoupper($insurance["insuredCity"]):null),
            (isset($insurance["insuredState"])?strtoupper($insurance["insuredState"]):null),
            (isset($insurance["insuredZip"])?strtoupper($insurance["insuredZip"]):null),
        ]));

        // Type of Agreement (Worker’s Compensation Flag)
        $in1->setField(31,strtoupper($insurance["workerCompensation"]));

        // Policy Number (Insurance Number/Subscriber Number/Member ID)
        $in1->setField(36,strtoupper($insurance["policyNumber"]));

        return $in1;
    }

    // Guarantor
    private function generateGT1($guarantor) {

        $gt1= new GT1();

        // Sequence Number, hardcoded
        $gt1->setField(1,Config::get("app.GT1_SEQUENCE_NUMBER"));

        // Guarantor name
        $gt1->setField(3,implode("^",[
            strtoupper($guarantor["surname"]),
            strtoupper($guarantor["name"]),
            (isset($guarantor["middleName"])?strtoupper($guarantor["middleName"]):null),
        ]));

        // Guarantor address
        $gt1->setField(5,implode("^",[
            strtoupper($guarantor["address"]),
            null,
            strtoupper($guarantor["city"]),
            strtoupper($guarantor["state"]),
            strtoupper($guarantor["zip"]),
        ]));

        // Guarantor Phone Number
        $gt1->setField(6,strtoupper($guarantor["phone"]));

        // Guarantor Relationship to Patient
        $gt1->setField(11,strtoupper($guarantor["guarantorRelationshipWithPatient"]));

        // Guarantor's Employer Name
        $gt1->setField(16,isset($guarantor["employerName"])?strtoupper($guarantor["employerName"]):null);

        return $gt1;
    }

    // Diagnoses
    private function generateDG1($diagnose) {

        $dg1= new DG1();

        $coding= explode('^',$diagnose);

        if (isset($coding[2]) && (trim($coding[2])!=""))
            // Diagnosis Coding Method - Only for backward compatibility
            $dg1->setField(2,$coding[2]);

        // Diagnose
        $dg1->setField(3,$diagnose);

        return $dg1;
    }

    private function generateOBR($observation,$accessionNumber)
    {
        $obr= new OBR();

        $obr->setField(2,$accessionNumber);
        $obr->setField(2,$observation);

        return $obr;

    }

    private function generateORCandOBR($msg,$order)
    {
        $orc= new ORC();

        // New order, hardcoded
        $orc->setField(1,Config::get("app.ORC_ORDER_CONTROL"));

        // Unique Foreign Accession or Specimen ID & Application / Institution ID
        $orc->setField(2, implode("^",[
            $order["uniqueAccession"],
            (isset($order["applicationID"])?strtoupper($order["applicationID"]):null)
        ]));

        // Ordering Provider ID Number &
        // Ordering Provider Last Name &
        // Ordering Provider First Initial or Name &
        // Source Table

        $orc->setField(12, implode("^",[
            (isset($order["orderingProviderID"])?strtoupper($order["orderingProviderID"]):null),
            (isset($order["orderingProviderSurname"])?strtoupper($order["orderingProviderSurname"]):null),
            (isset($order["orderingProviderName"])?strtoupper($order["orderingProviderName"]):null),
            null,
            null,
            null,
            null,
            (isset($order["sourceTable"])?strtoupper($order["sourceTable"]):null),
        ]));

        $msg->addSegment($orc);

        foreach ($order["observations"] as $observation)
            $msg->addSegment($this->generateOBR($observation,$order["uniqueAccession"]));

        return $msg;

    }

    private function saveMessage(&$message) {
        $messageModel= new OutgoingMessage();

        // We get the last used Message ID, if not found we use 1
        $messageID= 1;
        if (count($messageModel::latest()->get())>0)
            $messageID= $messageModel::latest()->get()->toArray()[0]['id']+1;

        // We overwrite the MSH message ID
        $msh= $message->getSegmentByIndex(0);
        $msh->setField(11,$messageID);
        $message->setSegment($msh,0);

        return $messageModel->create(["message"=>$message->toString()]);
    }

    private function validateInput($data) {
        // Validating patient

        print_r($data);
        die();

        return true;
    }

    public function generate()
    {
        //$this->validateInput(request()->post());
        $hl7= new HL7();
        $msg= $hl7->createMessage();
//        $msg->setEscapeCharacter('=');

        $postData= request()->post();

        // Generate the MSH segment
        $msg->addSegment($this->generateMSH($postData));
        // Generate the PID segment
        $msg->addSegment($this->generatePID($postData["patient"],$postData["orders"]));
        // Generate the PV1 segment
        $msg->addSegment($this->generatePV1($postData["visit"]));

        // Generate the NK1 segment if data is provided
        if (isset($postData["nextOfKin"]))
            $msg->addSegment($this->generateNK1($postData["nextOfKin"]));

        // Generate the IN1 segments if provided (up to a maximum defined)
        if (isset($postData["insurance"]))
        {
            $sequence= 1;

            foreach ($postData["insurance"] as $insurance)
            {
                $msg->addSegment($this->generateIN1($insurance));
                $sequence++;

                // Check if maximum amount of IN1 segments is reached
                if ($sequence>Config::get("app.IN1_MAX_SEQUENCES"))
                    break;
            }
        }

        // Generate the GT1 segment
        $msg->addSegment($this->generateGT1($postData["guarantor"]));

        // Generate the DG1 segments if provided
        if (isset($postData["diagnoses"]))
        {
            foreach ($postData["diagnoses"] as $diagnose)
                $msg->addSegment($this->generateDG1($diagnose));

        }

        // Generate the ORC segments
        foreach ($postData["orders"]["elements"] as $order)
            $msg= $this->generateORCandOBR($msg,$order);

        // Save the message in the DB with the Message ID dynamically created
        $this->saveMessage($msg);

        echo $msg->toString(true);
        die();
    }

    public function randomData() {
        $patient= Patient::inRandomOrder()->limit(1)->get()->toArray()[0];
        $guarantor= Guarantor::inRandomOrder()->limit(1)->get()->toArray()[0];

        $faker= Container::getInstance()->make(Generator::class);
        $orders= null;
        $item= null;

        // Randomly generating orders (at least 1)

        for ($i=0;$i<$faker->numberBetween(1,4);$i++)
        {
            $item= Order::inRandomOrder()->limit(1)->get()->toArray()[0];

            // Randomly generating observations for each order
            for ($j=0;$j<$faker->numberBetween(1,4);$j++)
                $item["observations"][]= "capo";

            $orders["elements"][]= $item;
        }

        // We add extra info for the order
        $orders["accountNumber"]=$faker->randomNumber(9,true);
        $orders["payer"]=$faker->randomElement(['P','T','C']);


        $insurance= null;
        $nextOfKin= null;
        $diagnoses= null;


        // Randomizing if there is 1 insurance
        if ($faker->boolean())
        {

            $insurance= array(Insurance::inRandomOrder()->limit(1)->get()->toArray()[0]);

            // Randomizing if there are 2 insurances
            if ($faker->boolean())
                $insurance[]= Insurance::inRandomOrder()->limit(1)->get()->toArray()[0];
        }

        // Randomizing if there's a next of kin
        if ($faker->boolean())
            $nextOfKin= Patient::inRandomOrder()->limit(1)->get()->toArray()[0];

        // Randomizing diagnosis
        $diagnosesList= [
            "784.3^APHASIA^I9C",
            "781.6^MENINGISMUS^I9C",
            "S22.089D^^I10",
            "S24.114A^^I10",
            "Z3A.22^^I10",
            "S23.163A^^I10"
        ];

        for ($i=0;$i<$faker->numberBetween(0,count($diagnosesList));$i++)
            $diagnoses[]= $diagnosesList[$i];

        $return= [
            "patient"=>$patient,
            "nextOfKin"=>$nextOfKin,
            "sender"=>["app"=>"WRS Health","facility"=>"TEST SENDER FACILITY (VERIFY)"],
            "receiving"=>["facility"=> "TEST RECEIVING FACILITY (VERIFY)"],
            "orders"=>$orders,
            "visit"=>["patientClass"=>$faker->randomElement(['I','O','N'])],
            "insurance"=>$insurance,
            "guarantor"=>$guarantor,
            "diagnoses"=>$diagnoses,
        ];

        die(preg_replace('/,\s*"[^"]+":null|"[^"]+":null,?/', '', json_encode($return)));
    }
}
