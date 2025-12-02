<?php
// app/Models/MarketEvent.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 'symbol', 'event_type', 'title', 'description',
        'previous_state', 'current_state', 'severity', 'is_active', 'triggered_at'
    ];

    protected $casts = [
        'date' => 'date',
        'previous_state' => 'array',
        'current_state' => 'array',
        'triggered_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('date', '>=', now()->subDays($days));
    }

    public function scopeRegimeChanges($query)
    {
        return $query->where('event_type', 'regime_change');
    }

    public function scopeDominanceShifts($query)
    {
        return $query->where('event_type', 'dominance_shift');
    }

    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }
}