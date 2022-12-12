<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutgoingMessage extends Model
{
    use HasFactory;

    public $fillable= [
        'message'
    ];
}
