<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ResetUserPortfolio extends Command
{
    protected $signature = 'portfolio:reset 
                            {user_id : The ID of the user}
                            {--initial-balance=1000 : Initial balance after reset}';
    
    protected $description = 'Reset user portfolio to initial state';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $initialBalance = $this->option('initial-balance');

        $user = User::find($userId);
        
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found");
            return Command::FAILURE;
        }

        $portfolio = $user->getPortfolio();
        $portfolio->reset();
        $portfolio->update([
            'initial_balance' => $initialBalance,
            'balance' => $initialBalance,
            'equity' => $initialBalance,
        ]);

        $this->info("✅ Portfolio for user {$user->name} reset successfully!");
        $this->line("   Initial Balance: \${$initialBalance}");
        
        return Command::SUCCESS;
    }
}