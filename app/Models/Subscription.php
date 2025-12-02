<?php
// app/Models/Subscription.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'provider', 'subscription_id', 'status',
        'plan', 'amount_idr', 'start_date', 'end_date'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'amount_idr' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->end_date > now();
    }

    public function getPriceDisplay($currency = 'USD')
    {
        if ($currency === 'IDR') {
            return 'Rp ' . number_format($this->amount_idr, 0, ',', '.');
        }
        
        // Convert to USD (rate: 1 USD = 16,600 IDR)
        $usdAmount = $this->amount_idr / 16600;
        return '$' . number_format($usdAmount, 2);
    }
}