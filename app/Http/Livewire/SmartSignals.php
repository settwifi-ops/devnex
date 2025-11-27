<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\AiSignal;
use Livewire\WithPagination;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class SmartSignals extends Component
{
    use WithPagination;

    public $symbolFilter = '';
    public $actionFilter = '';
    public $riskFilter = '';
    public $sortBy = 'signal_time';
    public $sortDirection = 'desc';
    public $perPage = 12;

    protected $queryString = [
        'symbolFilter' => ['except' => ''],
        'actionFilter' => ['except' => ''],
        'riskFilter' => ['except' => ''],
    ];

    public function render()
    {
        $signals = AiSignal::query()
            ->when($this->symbolFilter, function ($query) {
                $query->where('symbol', $this->symbolFilter);
            })
            ->when($this->actionFilter, function ($query) {
                $query->where('action', $this->actionFilter);
            })
            ->when($this->riskFilter, function ($query) {
                $query->where('risk_level', $this->riskFilter);
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        // Mark as read when viewed
        if ($signals->isNotEmpty()) {
            AiSignal::whereIn('id', $signals->pluck('id'))
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return view('livewire.smart-signals', [
            'signals' => $signals,
            'symbols' => AiSignal::distinct()->pluck('symbol'),
        ]);
    }

    public function applyFilters()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->symbolFilter = '';
        $this->actionFilter = '';
        $this->riskFilter = '';
        $this->sortBy = 'signal_time';
        $this->sortDirection = 'desc';
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortBy = $field;
    }

    // ========== GENERATE PNG METHOD ==========
    public function generatePNG($signalId)
    {
        $signal = AiSignal::findOrFail($signalId);
        
        try {
            // Create image using Intervention Image
            $manager = new ImageManager(new Driver());
            $width = 400;
            $height = 500;
            
            // Create base image with gradient background
            $image = $manager->create($width, $height);
            
            // Draw gradient background
            $this->drawGradientBackground($image, $width, $height, $signal->action);
            
            // Add content to the image
            $this->addSignalContent($image, $signal, $width, $height);
            
            // Generate filename
            $filename = 'signal-' . $signal->symbol . '-' . $signal->id . '-' . now()->format('Y-m-d-H-i-s') . '.png';
            $filePath = storage_path('app/public/signals/' . $filename);
            
            // Ensure directory exists
            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }
            
            // Save image
            $image->save($filePath);
            
            // Return download response
            return response()->download($filePath)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Failed to generate PNG: ' . $e->getMessage());
        }
    }

    private function drawGradientBackground($image, $width, $height, $action)
    {
        // Define colors based on action
        $colors = match($action) {
            'BUY' => ['#10b981', '#059669', '#047857'],
            'SELL' => ['#ef4444', '#dc2626', '#b91c1c'],
            'HOLD' => ['#f59e0b', '#d97706', '#b45309'],
            default => ['#6b7280', '#4b5563', '#374151']
        };
        
        // Draw gradient (simplified version)
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $r = $this->interpolateColor($colors[0], $colors[2], $ratio);
            $color = $this->hexToRgb($r);
            
            for ($x = 0; $x < $width; $x++) {
                $image->drawPixel($color, $x, $y);
            }
        }
    }

    private function addSignalContent($image, $signal, $width, $height)
    {
        // Add symbol and action
        $this->addText($image, $signal->symbol, $width / 2, 50, 24, '#ffffff', 'center');
        $this->addText($image, $signal->action, $width / 2, 80, 18, '#ffffff', 'center');
        
        // Add confidence
        $this->addText($image, 'Confidence: ' . $signal->confidence . '%', $width / 2, 120, 16, '#ffffff', 'center');
        
        // Add prices
        $this->addText($image, 'Current: $' . number_format($signal->current_price, 2), 20, 160, 14, '#ffffff', 'left');
        if ($signal->target_price) {
            $this->addText($image, 'Target: $' . number_format($signal->target_price, 2), 20, 185, 14, '#ffffff', 'left');
        }
        
        // Add metrics
        $this->addText($image, 'Score: ' . $signal->signal_score, 20, 220, 12, '#ffffff', 'left');
        $this->addText($image, 'Health: ' . $signal->health_score, 20, 240, 12, '#ffffff', 'left');
        $this->addText($image, 'Volume: ' . $signal->volume_spike . 'x', 20, 260, 12, '#ffffff', 'left');
        $this->addText($image, 'Trend: ' . $signal->momentum_regime, 20, 280, 12, '#ffffff', 'left');
        
        // Add risk level
        $this->addText($image, 'Risk: ' . $signal->risk_level, $width / 2, 320, 14, '#ffffff', 'center');
        
        // Add timestamp
        $this->addText($image, 'Generated: ' . now()->format('M j, Y H:i'), $width / 2, 350, 10, '#ffffff', 'center');
    }

    private function addText($image, $text, $x, $y, $size, $color, $align = 'left')
    {
        // Simplified text addition - in real implementation you'd use font rendering
        $color = $this->hexToRgb($color);
        // Note: Actual text rendering would require TrueType fonts
        // This is a simplified version
    }

    private function interpolateColor($color1, $color2, $ratio)
    {
        $r1 = hexdec(substr($color1, 1, 2));
        $g1 = hexdec(substr($color1, 3, 2));
        $b1 = hexdec(substr($color1, 5, 2));
        
        $r2 = hexdec(substr($color2, 1, 2));
        $g2 = hexdec(substr($color2, 3, 2));
        $b2 = hexdec(substr($color2, 5, 2));
        
        $r = $r1 + ($r2 - $r1) * $ratio;
        $g = $g1 + ($g2 - $g1) * $ratio;
        $b = $b1 + ($b2 - $b1) * $ratio;
        
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }

    private function hexToRgb($hex)
    {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) {
            $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
            $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
            $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return [$r, $g, $b];
    }

    // ========== HELPER METHODS UNTUK WARNA ==========

    public function getConfidenceColor($confidence)
    {
        if ($confidence >= 80) return 'text-green-600';
        if ($confidence >= 60) return 'text-yellow-600';
        return 'text-red-600';
    }

    public function getActionColor($action)
    {
        return match($action) {
            'BUY' => 'bg-gradient-to-r from-green-500 to-emerald-600',
            'SELL' => 'bg-gradient-to-r from-red-500 to-rose-600', 
            'HOLD' => 'bg-gradient-to-r from-yellow-500 to-amber-600',
            'MONITOR' => 'bg-gradient-to-r from-gray-500 to-slate-600',
            default => 'bg-gradient-to-r from-blue-500 to-cyan-600'
        };
    }

    public function getRiskColor($risk)
    {
        return match($risk) {
            'VERY_LOW' => 'text-green-600',
            'LOW' => 'text-emerald-600',
            'MEDIUM' => 'text-yellow-600',
            'HIGH' => 'text-red-600',
            default => 'text-gray-600'
        };
    }

    public function getScoreColor($score)
    {
        if ($score >= 8) return 'text-green-600';
        if ($score >= 6) return 'text-yellow-600';
        return 'text-red-600';
    }

    public function getHealthColor($health)
    {
        if ($health >= 8) return 'text-green-600';
        if ($health >= 6) return 'text-yellow-600';
        return 'text-red-600';
    }

    public function getMomentumColor($momentum)
    {
        return match(strtoupper($momentum)) {
            'BULLISH' => 'text-green-600',
            'BEARISH' => 'text-red-600',
            'SIDEWAYS' => 'text-yellow-600',
            default => 'text-gray-600'
        };
    }

    public function getConfidenceGradient($confidence)
    {
        if ($confidence >= 80) return 'bg-gradient-to-r from-green-500 to-emerald-500';
        if ($confidence >= 60) return 'bg-gradient-to-r from-yellow-500 to-amber-500';
        return 'bg-gradient-to-r from-red-500 to-rose-500';
    }

    public function getRiskDotColor($risk)
    {
        return match($risk) {
            'VERY_LOW' => 'bg-green-500',
            'LOW' => 'bg-emerald-500',
            'MEDIUM' => 'bg-yellow-500',
            'HIGH' => 'bg-red-500',
            default => 'bg-gray-500'
        };
    }

    public function getMomentumBgColor($momentum)
    {
        return match($momentum) {
            'BULLISH' => 'bg-green-100 text-green-800',
            'BEARISH' => 'bg-red-100 text-red-800',
            'SIDEWAYS' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}