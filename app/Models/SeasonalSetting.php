<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function getVal(string $key, $default = null)
    {
        $row = static::where('key', $key)->first();
        return $row ? $row->value : $default;
    }

    public static function setVal(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
    }
}

