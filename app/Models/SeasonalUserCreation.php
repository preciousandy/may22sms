<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalUserCreation extends Model
{
    protected $fillable = [
        'user_id',
        'tree_config',
        'snowman_config',
        'greeting_card_config',
        'village_Visited_spots',
        'cards_generated_count',
    ];

    protected $casts = [
        'tree_config' => 'array',
        'snowman_config' => 'array',
        'greeting_card_config' => 'array',
        'village_Visited_spots' => 'array',
        'cards_generated_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
