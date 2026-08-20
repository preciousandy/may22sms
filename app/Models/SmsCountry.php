<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCountry extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'iso',
        'is_active',
    ];
}
