<?php

namespace App\Events;

use App\Models\Signal;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewSignalActive
{
    use Dispatchable, SerializesModels;

    public $signal;

    public function __construct(Signal $signal)
    {
        $this->signal = $signal;
    }
}
