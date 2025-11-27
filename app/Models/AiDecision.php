<?php
// app/Models/AiDecision.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiDecision extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol',
        'action', 
        'confidence',
        'price',
        'explanation',
        'market_data',
        'executed',
        'decision_time'
    ];

    protected $casts = [
        'confidence' => 'decimal:2',
        'price' => 'decimal:8',
        'market_data' => 'array',
        'executed' => 'boolean',
        'decision_time' => 'datetime'
    ];

    // ✅ TAMBAHKAN RELATIONSHIP KE SIGNAL
    public function signal()
    {
        return $this->belongsTo(Signal::class, 'symbol', 'symbol');
    }

    // Relasi ke positions
    public function positions()
    {
        return $this->hasMany(UserPosition::class);
    }

    // Relasi ke trade histories
    public function tradeHistories()
    {
        return $this->hasMany(TradeHistory::class);
    }

    // Scope untuk keputusan yang belum dieksekusi
    public function scopePending($query)
    {
        return $query->where('executed', false);
    }

    // Scope untuk BUY decisions
    public function scopeBuyActions($query)
    {
        return $query->where('action', 'BUY');
    }

    // Scope untuk SELL decisions  
    public function scopeSellActions($query)
    {
        return $query->where('action', 'SELL');
    }

    // ✅ FUNGSI BARU: Untuk running text
    public function scopeLatestForRunningText($query, $limit = 5)
    {
        return $query->latest('decision_time')->limit($limit);
    }

    // ✅ FUNGSI BARU: Untuk dashboard top signals
    public function scopeForDashboard($query, $limit = 5)
    {
        return $query->where('confidence', '>=', 70)
                    ->whereIn('action', ['BUY', 'SELL'])
                    ->where('decision_time', '>=', now()->subDays(3))
                    ->orderBy('confidence', 'desc')
                    ->orderBy('decision_time', 'desc')
                    ->limit($limit);
    }

    // ✅ FUNGSI BARU: Format untuk display
    public function getFormattedConfidence()
    {
        return $this->confidence . '%';
    }

    public function getFormattedPrice()
    {
        return number_format($this->price, 4);
    }

    public function getActionColor()
    {
        return match($this->action) {
            'BUY' => 'text-green-600',
            'SELL' => 'text-red-600',
            'HOLD' => 'text-yellow-600',
            default => 'text-gray-600'
        };
    }

    public function getActionBgColor()
    {
        return match($this->action) {
            'BUY' => 'bg-green-100',
            'SELL' => 'bg-red-100',
            'HOLD' => 'bg-yellow-100',
            default => 'bg-gray-100'
        };
    }

    // ✅ FUNGSI BARU: Untuk summary
    public function getSummaryAttribute()
    {
        $marketData = $this->market_data ?: [];
        $rsi = $marketData['rsi'] ?? null;
        $volume = $marketData['volume_change_24h'] ?? null;
        
        $additionalInfo = '';
        if ($rsi) {
            $additionalInfo .= "RSI: {$rsi}. ";
        }
        if ($volume) {
            $volumeChange = $volume > 0 ? "+{$volume}%" : "{$volume}%";
            $additionalInfo .= "Volume: {$volumeChange}. ";
        }
        
        return "AI recommends {$this->action} {$this->symbol} at \${$this->getFormattedPrice()} with {$this->confidence}% confidence. {$additionalInfo}{$this->explanation}";
    }

    // ✅ FUNGSI BARU: Untuk trend power
    public function getTrendPowerAttribute()
    {
        return match($this->action) {
            'BUY' => 'Bullish Momentum',
            'SELL' => 'Bearish Pressure',
            default => 'Market Analysis'
        };
    }

    // ✅ FUNGSI BARU: Untuk time ago
    public function getTimeAgoAttribute()
    {
        return $this->decision_time->diffForHumans();
    }
}