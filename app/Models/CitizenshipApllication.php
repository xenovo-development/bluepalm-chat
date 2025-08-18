<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CitizenshipApllication extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'nationality',
        'more_nationality',
        'date_of_birth',
        'passport_number',
        'email',
        'phone',
        'investment',
        'bank_type',
        'bank_name',
        'iban',
        'account',
        'routing',
        'swift',
        'additional_notes',
        'passport_file',
    ];
}
