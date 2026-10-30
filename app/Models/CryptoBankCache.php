<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CryptoBankCache extends Model
{
    protected $table = 'crypto_banks_cache';

    protected $fillable = [
        'code',
        'name',
        'public_id',
        'country',
    ];
}
