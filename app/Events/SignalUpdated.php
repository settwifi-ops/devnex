<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use App\Models\Signal;

class SignalUpdated
{
    use SerializesModels;

    public Signal $signal;

    public function __construct(Signal $signal)
    {
        $this->signal = $signal;
    }
}
