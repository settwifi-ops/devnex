<?php
// app/Models/MarketAlert.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'alert_type', 'symbol', 'title', 'message', 'trigger_conditions',
        'market_data', 'severity', 'is_read', 'triggered_at'
    ];

    protected $casts = [
        'trigger_conditions' => 'array',
        'market_data' => 'array',
        'triggered_at' => 'datetime',
        'is_read' => 'boolean'
    ];

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('triggered_at', '>=', now()->subHours($hours));
    }

    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    public function isCritical()
    {
        return $this->severity === 'critical';
    }
}