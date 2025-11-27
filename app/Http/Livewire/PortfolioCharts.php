<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\TradeHistory;
use App\Models\UserPortfolio;
use Illuminate\Support\Facades\Auth;

class PortfolioCharts extends Component
{
    public $portfolio;
    public $equityHistory = [];
    public $pnlHistory = [];
    public $performanceData = [];

    protected $listeners = ['portfolioUpdated' => 'refreshCharts'];

    public function mount()
    {
        $this->loadChartData();
    }

    public function loadChartData()
    {
        $user = Auth::user();
        $this->portfolio = $user->getPortfolio();

        // Get last 30 days of trade history for equity curve
        $trades = TradeHistory::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at')
            ->get();

        $this->generateEquityCurve($trades);
        $this->generatePnlData($trades);
        $this->generatePerformanceData();
    }

    private function generateEquityCurve($trades)
    {
        $equity = $this->portfolio->initial_balance;
        $this->equityHistory = [
            'labels' => [],
            'data' => []
        ];

        foreach ($trades as $trade) {
            if ($trade->action === 'BUY') {
                $equity -= $trade->amount;
            } elseif ($trade->action === 'SELL' && $trade->pnl !== null) {
                $equity += $trade->amount + $trade->pnl;
            }
            
            $this->equityHistory['labels'][] = $trade->created_at->format('M j, H:i');
            $this->equityHistory['data'][] = $equity;
        }

        // If no trades, show initial balance
        if (empty($this->equityHistory['data'])) {
            $this->equityHistory['labels'] = [now()->format('M j, H:i')];
            $this->equityHistory['data'] = [$this->portfolio->initial_balance];
        }
    }

    private function generatePnlData($trades)
    {
        $this->pnlHistory = [
            'labels' => [],
            'data' => []
        ];

        $cumulativePnl = 0;

        foreach ($trades as $trade) {
            if ($trade->pnl !== null) {
                $cumulativePnl += $trade->pnl;
                $this->pnlHistory['labels'][] = $trade->created_at->format('M j, H:i');
                $this->pnlHistory['data'][] = $cumulativePnl;
            }
        }
    }

    private function generatePerformanceData()
    {
        $this->performanceData = [
            'total_trades' => TradeHistory::where('user_id', Auth::id())->count(),
            'winning_trades' => TradeHistory::where('user_id', Auth::id())->where('pnl', '>', 0)->count(),
            'losing_trades' => TradeHistory::where('user_id', Auth::id())->where('pnl', '<', 0)->count(),
            'total_pnl' => $this->portfolio->realized_pnl,
            'win_rate' => 0
        ];

        if ($this->performanceData['total_trades'] > 0) {
            $this->performanceData['win_rate'] = 
                ($this->performanceData['winning_trades'] / $this->performanceData['total_trades']) * 100;
        }
    }

    public function refreshCharts()
    {
        $this->loadChartData();
    }

    public function render()
    {
        return view('livewire.portfolio-charts');
    }
}