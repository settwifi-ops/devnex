<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50/30 py-8 font-['Sofia_Sans']">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Enhanced Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
            <div class="flex items-center gap-4 mb-4 md:mb-0">
                <div class="relative">
                    <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-purple-500/20">
                        <i class="fas fa-brain text-white text-xl"></i>
                    </div>
                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-green-400 rounded-full border-2 border-white animate-pulse"></div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">Smart AI Signals</h1>
                    <p class="text-gray-600 text-sm mt-1">Real-time crypto signals • Powered by Aerolink AI</p>
                </div>
            </div>

            <!-- Enhanced Filters -->
            <div class="flex flex-wrap gap-3">
                <div class="relative group">
                    <select wire:model="symbolFilter" wire:change="applyFilters" 
                            class="bg-white/80 backdrop-blur-sm border border-gray-200 rounded-xl py-2.5 pl-10 pr-4 text-gray-700 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 shadow-sm transition-all duration-200 appearance-none hover:bg-white">
                        <option value="">All Symbols</option>
                        @foreach($symbols as $symbol)
                            <option value="{{ $symbol }}">{{ $symbol }}</option>
                        @endforeach
                    </select>
                    <i class="fas fa-filter absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 group-hover:text-purple-500 transition-colors"></i>
                </div>

                <div class="relative group">
                    <select wire:model="actionFilter" wire:change="applyFilters" 
                            class="bg-white/80 backdrop-blur-sm border border-gray-200 rounded-xl py-2.5 pl-10 pr-4 text-gray-700 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 shadow-sm transition-all duration-200 appearance-none hover:bg-white">
                        <option value="">All Actions</option>
                        <option value="BUY">BUY</option>
                        <option value="SELL">SELL</option>
                        <option value="HOLD">HOLD</option>
                        <option value="MONITOR">MONITOR</option>
                    </select>
                    <i class="fas fa-chart-line absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 group-hover:text-purple-500 transition-colors"></i>
                </div>

                <div class="relative group">
                    <select wire:model="riskFilter" wire:change="applyFilters" 
                            class="bg-white/80 backdrop-blur-sm border border-gray-200 rounded-xl py-2.5 pl-10 pr-4 text-gray-700 focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 shadow-sm transition-all duration-200 appearance-none hover:bg-white">
                        <option value="">All Risk Levels</option>
                        <option value="VERY_LOW">Very Low</option>
                        <option value="LOW">Low</option>
                        <option value="MEDIUM">Medium</option>
                        <option value="HIGH">High</option>
                    </select>
                    <i class="fas fa-shield-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 group-hover:text-purple-500 transition-colors"></i>
                </div>
            </div>
        </div>

        <!-- Enhanced Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 shadow-sm border border-gray-200/50 hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-medium uppercase tracking-wide">Total Signals</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $signals->total() }}</h3>
                    </div>
                    <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-signal text-blue-500 text-lg"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 shadow-sm border border-gray-200/50 hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-medium uppercase tracking-wide">Active Now</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $signals->where('created_at', '>=', now()->subHours(24))->count() }}</h3>
                    </div>
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-play-circle text-green-500 text-lg"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 shadow-sm border border-gray-200/50 hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-medium uppercase tracking-wide">Avg Confidence</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($signals->avg('confidence') ?? 0, 1) }}%</h3>
                    </div>
                    <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-brain text-purple-500 text-lg"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-4 shadow-sm border border-gray-200/50 hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-medium uppercase tracking-wide">Success Rate</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-1">92%</h3>
                    </div>
                    <div class="w-10 h-10 bg-cyan-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-trophy text-cyan-500 text-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Signals Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
            @forelse($signals as $signal)
                @php
                    // Determine gradient based on confidence
                    $confidenceGradient = match(true) {
                        $signal->confidence >= 80 => 'from-green-50/80 to-emerald-100/60 border-green-200/30',
                        $signal->confidence >= 60 => 'from-yellow-50/80 to-amber-100/60 border-yellow-200/30',
                        $signal->confidence >= 40 => 'from-orange-50/80 to-red-100/60 border-orange-200/30',
                        default => 'from-red-50/80 to-rose-100/60 border-red-200/30'
                    };
                    
                    // Determine header gradient based on confidence
                    $headerGradient = match(true) {
                        $signal->confidence >= 80 => 'from-green-500/10 to-emerald-600/10',
                        $signal->confidence >= 60 => 'from-yellow-500/10 to-amber-600/10',
                        $signal->confidence >= 40 => 'from-orange-500/10 to-red-600/10',
                        default => 'from-red-500/10 to-rose-600/10'
                    };
                    
                    // Format timestamp
                    $timeDisplay = $signal->signal_time 
                        ? $signal->signal_time->format('M j, Y • H:i') 
                        : ($signal->created_at 
                            ? $signal->created_at->format('M j, Y • H:i') 
                            : now()->format('M j, Y • H:i'));
                @endphp

                <div class="rounded-2xl shadow-sm border overflow-hidden transition-all duration-500 hover:shadow-xl hover:scale-[1.02] group bg-gradient-to-br {{ $confidenceGradient }}" 
                     id="signal-card-{{ $signal->id }}">
                    
                    <!-- Enhanced Card Header with Confidence-based Background -->
                    <div class="px-6 py-4 border-b border-white/30 flex justify-between items-start bg-gradient-to-r {{ $headerGradient }}">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="font-bold text-xl text-gray-900">{{ $signal->symbol }}</h3>
                                @if(!$signal->is_read)
                                    <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600">{{ $signal->name ?? 'Crypto Asset' }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-medium text-gray-600 uppercase tracking-wide">Current</div>
                            <div class="text-xl font-bold text-gray-900">${{ number_format($signal->current_price, 2) }}</div>
                        </div>
                    </div>
                    
                    <!-- Enhanced Card Body -->
                    <div class="p-6">
                        <!-- Action & Confidence Row -->
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold shadow-sm
                                    @if($signal->action === 'BUY') 
                                        bg-gradient-to-r from-green-500 to-emerald-600 text-white
                                    @elseif($signal->action === 'SELL') 
                                        bg-gradient-to-r from-red-500 to-rose-600 text-white
                                    @elseif($signal->action === 'HOLD') 
                                        bg-gradient-to-r from-yellow-500 to-amber-600 text-white
                                    @else 
                                        bg-gradient-to-r from-blue-500 to-cyan-600 text-white
                                    @endif">
                                    <i class="fas 
                                        @if($signal->action === 'BUY') fa-arrow-up 
                                        @elseif($signal->action === 'SELL') fa-arrow-down 
                                        @elseif($signal->action === 'HOLD') fa-pause 
                                        @else fa-eye 
                                        @endif mr-2 text-xs">
                                    </i>
                                    {{ $signal->action }}
                                </span>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-600 uppercase tracking-wide font-medium">Confidence</div>
                                <div class="flex items-center gap-2">
                                    <div class="text-xl font-bold 
                                        @if($signal->confidence >= 80) text-green-600
                                        @elseif($signal->confidence >= 60) text-yellow-600
                                        @else text-red-600
                                        @endif">
                                        {{ $signal->confidence }}%
                                    </div>
                                    <div class="w-12 h-2 bg-gray-300 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-1000
                                            @if($signal->confidence >= 80) bg-gradient-to-r from-green-500 to-emerald-500
                                            @elseif($signal->confidence >= 60) bg-gradient-to-r from-yellow-500 to-amber-500
                                            @else bg-gradient-to-r from-red-500 to-rose-500
                                            @endif" 
                                            style="width: {{ $signal->confidence }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Enhanced Signal Details -->
                        <div class="space-y-4">
                            <!-- Target Price -->
                            @if($signal->target_price)
                            <div class="flex justify-between items-center p-3 bg-white/50 backdrop-blur-sm rounded-xl border border-white/50 shadow-sm">
                                <span class="text-sm font-medium text-gray-700 flex items-center gap-2">
                                    <i class="fas fa-bullseye text-green-500 text-xs"></i>
                                    Target Price
                                </span>
                                <span class="text-lg font-bold text-green-600">${{ number_format($signal->target_price, 2) }}</span>
                            </div>
                            @endif
                            
                            <!-- Metrics Grid -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="text-center p-3 bg-white/50 backdrop-blur-sm rounded-xl border border-white/50 shadow-sm">
                                    <div class="text-lg font-bold 
                                        @if($signal->signal_score >= 8) text-green-600
                                        @elseif($signal->signal_score >= 6) text-yellow-600
                                        @else text-red-600
                                        @endif">
                                        {{ $signal->signal_score }}/10
                                    </div>
                                    <div class="text-xs text-gray-600 mt-1 font-medium">Signal Score</div>
                                </div>
                                <div class="text-center p-3 bg-white/50 backdrop-blur-sm rounded-xl border border-white/50 shadow-sm">
                                    <div class="text-lg font-bold 
                                        @if($signal->health_score >= 8) text-green-600
                                        @elseif($signal->health_score >= 6) text-yellow-600
                                        @else text-red-600
                                        @endif">
                                        {{ $signal->health_score }}/10
                                    </div>
                                    <div class="text-xs text-gray-600 mt-1 font-medium">Health Score</div>
                                </div>
                            </div>

                            <!-- Additional Info -->
                            <div class="space-y-3">
                                @if($signal->volume_spike)
                                <div class="flex justify-between items-center p-2">
                                    <span class="text-sm text-gray-700 flex items-center gap-2">
                                        <i class="fas fa-chart-bar text-blue-500 text-xs"></i>
                                        Volume Spike
                                    </span>
                                    <span class="text-sm font-semibold text-gray-800">{{ $signal->volume_spike }}x</span>
                                </div>
                                @endif
                                
                                <div class="flex justify-between items-center p-2">
                                    <span class="text-sm text-gray-700 flex items-center gap-2">
                                        <i class="fas fa-trending-up text-purple-500 text-xs"></i>
                                        Momentum
                                    </span>
                                    <span class="text-sm font-semibold 
                                        @if(strtoupper($signal->momentum_regime) === 'BULLISH') text-green-600
                                        @elseif(strtoupper($signal->momentum_regime) === 'BEARISH') text-red-600
                                        @else text-yellow-600
                                        @endif">
                                        {{ $signal->momentum_regime ?? 'SIDEWAYS' }}
                                    </span>
                                </div>
                                
                                <div class="flex justify-between items-center p-2">
                                    <span class="text-sm text-gray-700 flex items-center gap-2">
                                        <i class="fas fa-shield-alt text-orange-500 text-xs"></i>
                                        Risk Level
                                    </span>
                                    <span class="text-sm font-semibold 
                                        @if($signal->risk_level === 'LOW' || $signal->risk_level === 'VERY_LOW') text-green-600
                                        @elseif($signal->risk_level === 'MEDIUM') text-yellow-600
                                        @elseif($signal->risk_level === 'HIGH') text-red-600
                                        @else text-gray-600
                                        @endif">
                                        {{ $signal->risk_level }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Enhanced Card Footer -->
                    <div class="px-6 py-4 bg-white/30 backdrop-blur-sm border-t border-white/30 flex justify-between items-center">
                        <div class="flex items-center gap-2 text-xs text-gray-600">
                            <i class="fas fa-clock text-gray-500"></i>
                            <span class="font-medium">{{ $timeDisplay }}</span>
                        </div>
                        <button onclick="generatePremiumPNG({{ $signal->id }})" 
                                class="inline-flex items-center gap-2 px-4 py-2.5 border border-transparent text-sm font-medium rounded-xl shadow-sm text-white bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 group-hover:scale-105 backdrop-blur-sm">
                            <i class="fas fa-download text-xs"></i>
                            <span class="export-text">Export PNG</span>
                            <span class="loading-text hidden">
                                <i class="fas fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    </div>
                </div>
            @empty
                <!-- Enhanced Empty State -->
                <div class="col-span-full text-center py-16">
                    <div class="w-32 h-32 mx-auto mb-6 text-gray-300">
                        <i class="fas fa-chart-line text-8xl opacity-50"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-700 mb-3">No Signals Available</h3>
                    <p class="text-gray-500 text-sm mb-6 max-w-md mx-auto">Adjust your filters or check back later for new trading opportunities</p>
                    <button wire:click="clearFilters" 
                            class="bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white px-8 py-3 rounded-xl transition-all duration-300 font-bold text-sm shadow-lg hover:shadow-xl transform hover:scale-105">
                        Clear All Filters
                    </button>
                </div>
            @endforelse
        </div>

        <!-- Enhanced Pagination -->
        @if($signals->hasPages())
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-sm p-6 border border-gray-200/50 mt-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-sm text-gray-700 font-medium">
                        Showing <span class="text-gray-900 font-bold">{{ $signals->firstItem() }}</span> to 
                        <span class="text-gray-900 font-bold">{{ $signals->lastItem() }}</span> of 
                        <span class="text-gray-900 font-bold">{{ $signals->total() }}</span> results
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- Previous Button -->
                        @if($signals->onFirstPage())
                            <span class="px-4 py-2 text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed text-sm font-medium">
                                <i class="fas fa-chevron-left mr-2"></i>Previous
                            </span>
                        @else
                            <button wire:click="previousPage" 
                                    class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200 text-sm font-medium flex items-center gap-2 hover:shadow-sm">
                                <i class="fas fa-chevron-left text-xs"></i>Previous
                            </button>
                        @endif

                        <!-- Page Numbers -->
                        <div class="flex items-center gap-1">
                            @foreach($signals->getUrlRange(max(1, $signals->currentPage() - 2), min($signals->lastPage(), $signals->currentPage() + 2)) as $page => $url)
                                @if($page == $signals->currentPage())
                                    <span class="w-10 h-10 flex items-center justify-center text-white bg-gradient-to-r from-purple-500 to-indigo-600 border border-purple-600 rounded-lg text-sm font-bold shadow-sm">
                                        {{ $page }}
                                    </span>
                                @else
                                    <button wire:click="gotoPage({{ $page }})" 
                                            class="w-10 h-10 flex items-center justify-center text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200 text-sm font-medium hover:shadow-sm">
                                        {{ $page }}
                                    </button>
                                @endif
                            @endforeach
                        </div>

                        <!-- Next Button -->
                        @if($signals->hasMorePages())
                            <button wire:click="nextPage" 
                                    class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200 text-sm font-medium flex items-center gap-2 hover:shadow-sm">
                                Next<i class="fas fa-chevron-right text-xs"></i>
                            </button>
                        @else
                            <span class="px-4 py-2 text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed text-sm font-medium flex items-center gap-2">
                                Next<i class="fas fa-chevron-right text-xs"></i>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Enhanced Success Toast Notification -->
    <div id="toast-success" class="fixed top-6 right-6 bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-4 rounded-xl shadow-2xl transform translate-x-full transition-all duration-500 z-50 hidden">
        <div class="flex items-center gap-3">
            <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center">
                <i class="fas fa-check text-xs"></i>
            </div>
            <div>
                <div class="font-semibold">Success!</div>
                <div class="text-sm text-white/90">Signal exported successfully</div>
            </div>
        </div>
    </div>

    <style>
    @import url('https://fonts.googleapis.com/css2?family=Sofia+Sans:wght@300;400;500;600;700;800&display=swap');
    
    .hidden {
        display: none !important;
    }

    /* Enhanced Animations */
    .grid > div {
        opacity: 0;
        visibility: hidden;
        animation: cardSlideIn 0.6s ease forwards;
    }

    @keyframes cardSlideIn {
        0% {
            opacity: 0;
            visibility: hidden;
            transform: translateY(30px) scale(0.95);
        }
        100% {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }
    }

    /* Stagger animation delay untuk card */
    .grid > div:nth-child(1) { animation-delay: 0.1s; }
    .grid > div:nth-child(2) { animation-delay: 0.2s; }
    .grid > div:nth-child(3) { animation-delay: 0.3s; }
    .grid > div:nth-child(4) { animation-delay: 0.4s; }
    .grid > div:nth-child(5) { animation-delay: 0.5s; }
    .grid > div:nth-child(6) { animation-delay: 0.6s; }

    /* Smooth scroll behavior */
    html {
        scroll-behavior: smooth;
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
    }
    ::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Confidence-based background animations */
    @keyframes confidencePulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.8; }
    }
    
    .bg-gradient-confidence-high {
        animation: confidencePulse 3s ease-in-out infinite;
    }
    </style>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
// Global state untuk mencegah multiple export
let isExporting = false;

function generatePremiumPNG(signalId) {
    if (isExporting) return;
    
    const element = document.getElementById(`signal-card-${signalId}`);
    const button = element.querySelector('button');
    const exportText = button.querySelector('.export-text');
    const loadingText = button.querySelector('.loading-text');
    const toast = document.getElementById('toast-success');
    
    // Show loading state
    exportText.classList.add('hidden');
    loadingText.classList.remove('hidden');
    button.disabled = true;
    isExporting = true;

    // Create a new card element untuk export dengan styling yang persis sama
    const exportCard = element.cloneNode(true);
    exportCard.style.cssText = `
        width: ${element.offsetWidth}px;
        height: ${element.offsetHeight}px;
        transform: none !important;
        position: fixed;
        left: -9999px;
        top: 0;
        z-index: -1;
        opacity: 1 !important;
        visibility: visible !important;
    `;

    // Remove hover effects and animations for export
    exportCard.classList.remove('group', 'hover:scale-[1.02]', 'hover:shadow-xl');
    exportCard.querySelectorAll('*').forEach(el => {
        el.classList.remove('group-hover:scale-105', 'group-hover:scale-110');
    });

    document.body.appendChild(exportCard);

    // Tunggu sebentar untuk memastikan DOM sudah ter-render
    setTimeout(() => {
        html2canvas(exportCard, {
            backgroundColor: null, // Transparent background untuk gradient
            scale: 3, // Higher scale for better quality
            useCORS: true,
            logging: false,
            width: exportCard.offsetWidth,
            height: exportCard.offsetHeight,
            allowTaint: true,
            removeContainer: true,
            onclone: function(clonedDoc) {
                // Ensure all elements are visible in the clone
                const clonedCard = clonedDoc.getElementById(exportCard.id);
                if (clonedCard) {
                    clonedCard.style.opacity = '1';
                    clonedCard.style.visibility = 'visible';
                    clonedCard.style.transform = 'none';
                }
            }
        }).then(canvas => {
            // Create download link
            const link = document.createElement('a');
            const timestamp = new Date().toISOString().slice(0,19).replace(/:/g, '-');
            link.download = `smart-signal-${signalId}-${timestamp}.png`;
            link.href = canvas.toDataURL('image/png', 1.0);
            
            // Trigger download
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Cleanup
            document.body.removeChild(exportCard);
            
            // Show success toast
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 10);
            
            // Hide toast after 3 seconds
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => {
                    toast.classList.add('hidden');
                }, 500);
            }, 3000);
            
            // Restore button
            exportText.classList.remove('hidden');
            loadingText.classList.add('hidden');
            button.disabled = false;
            isExporting = false;
            
        }).catch(error => {
            console.error('Error generating PNG:', error);
            
            // Cleanup on error
            if (document.body.contains(exportCard)) {
                document.body.removeChild(exportCard);
            }
            
            // Restore button
            exportText.classList.remove('hidden');
            loadingText.classList.add('hidden');
            button.disabled = false;
            isExporting = false;
            
            alert('Error generating PNG. Please try again.');
        });
    }, 500);
}
</script>