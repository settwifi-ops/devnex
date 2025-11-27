<?php

namespace App\Console\Commands;

use App\Services\PerformanceService;
use Illuminate\Console\Command;

class FetchPerformanceData extends Command
{
    protected $signature = 'performance:fetch';
    protected $description = 'Fetch performance data from bot API';

    protected $performanceService;

    public function __construct(PerformanceService $performanceService)
    {
        parent::__construct();
        $this->performanceService = $performanceService;
    }

    public function handle()
    {
        $this->info('Fetching performance data from API...');
        
        $result = $this->performanceService->fetchAndStorePerformanceData();
        
        if ($result['success']) {
            $this->info('Success! ' . $result['count'] . ' records updated.');
        } else {
            $this->error('Failed: ' . $result['error']);
        }
        
        return 0;
    }
}