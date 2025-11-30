<?php
// app/Console/Commands/FetchSignalsCommand.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SignalService;

class FetchSignalsCommand extends Command
{
    protected $signature = 'signals:fetch';
    protected $description = 'Fetch signals from bot API';

    public function handle(SignalService $signalService)
    {
        $this->info('Fetching signals from API...');
        
        $success = $signalService->fetchAndStoreSignals();
        
        if ($success) {
            $this->info('Signals fetched successfully!');
        } else {
            $this->error('Failed to fetch signals.');
        }
    }
}