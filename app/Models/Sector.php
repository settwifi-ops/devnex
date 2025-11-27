<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    use HasFactory;

    protected $table = 'sectors';

    protected $fillable = [
        'sector_id',
        'name',
        'market_cap',
        'market_cap_change_24h',
        'volume_24h',
        'top_3_coins',
        'top_3_logos',
        'updated_at_api'
    ];

    protected $casts = [
        'top_3_coins' => 'array',
        'top_3_logos' => 'array',
        'updated_at_api' => 'datetime',
    ];
}
