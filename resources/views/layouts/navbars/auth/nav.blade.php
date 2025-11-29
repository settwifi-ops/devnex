<!-- Navbar -->
<nav class="relative flex flex-wrap items-center justify-between px-0 py-2 mx-6 transition-all shadow-none duration-250 ease-soft-in rounded-2xl lg:flex-nowrap lg:justify-start" navbar-main navbar-scroll="true">
  <div class="flex items-center justify-between w-full px-4 py-1 mx-auto flex-wrap-inherit">
    
    <!-- Running Text Section - AI Decisions -->
    <div class="flex items-center flex-1 min-w-0 mr-4" style="max-width: calc(100% - 320px);">
      <div class="relative overflow-hidden w-full bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl px-6 py-3 border border-blue-200 shadow-sm">
        <div class="flex items-center">
          <div class="flex-shrink-0 mr-4">
            <div class="relative w-8 h-8 bg-gradient-to-br from-purple-500 to-blue-600 rounded-full flex items-center justify-center shadow-lg">
              <i class="fas fa-exchange-alt text-white text-sm"></i>
              <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-400 rounded-full border-2 border-white animate-pulse"></div>
            </div>
          </div>
          
          <div class="relative overflow-hidden flex-1">
            <div id="ai-running-text" class="text-sm font-medium text-gray-800 whitespace-nowrap">
              @php
                $navbarController = new \App\Http\Controllers\NavbarController();
                $navbarData = $navbarController->getNavbarData();
                $aiDecisions = $navbarData['aiDecisions'] ?? [];
              @endphp
              
              @if(count($aiDecisions) > 0)
                <div class="flex space-x-8 animate-marquee">
                  @foreach($aiDecisions as $decision)
                    <div class="flex items-center space-x-3 bg-white/80 backdrop-blur-sm rounded-lg px-4 py-2 shadow-sm border border-gray-200">
                      <span class="font-bold text-gray-900 text-xs">{{ $decision->symbol }}</span>
                      <div class="flex items-center space-x-1">
                        <span class="w-2 h-2 rounded-full {{ $decision->action === 'BUY' ? 'bg-green-500' : ($decision->action === 'SELL' ? 'bg-red-500' : 'bg-yellow-500') }}"></span>
                        <span class="text-xs font-semibold {{ $decision->getActionColor() }}">{{ $decision->action }}</span>
                      </div>
                      <div class="flex items-center space-x-1">
                        <i class="fas fa-bullseye text-blue-500 text-xs"></i>
                        <span class="text-xs font-mono font-bold text-green-600">{{ $decision->getFormattedConfidence() }}</span>
                      </div>
                      <div class="flex items-center space-x-1">
                        <i class="fas fa-dollar-sign text-gray-500 text-xs"></i>
                        <span class="text-xs font-mono font-bold text-blue-600">{{ $decision->getFormattedPrice() }}</span>
                      </div>
                    </div>
                  @endforeach
                </div>
              @else
                <div class="flex items-center space-x-4 text-gray-600">
                  <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-sm font-semibold">AI Trading System Active</span>
                  </div>
                  <span class="text-gray-400">•</span>
                  <span class="text-sm">Monitoring markets in real-time</span>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Notifications & User Actions -->
    <div class="flex items-center justify-end" style="min-width: 320px;">
      <ul class="flex flex-row justify-end pl-0 mb-0 list-none items-center space-x-2">
        
        <!-- 🔔 BELL ICON - AI SIGNALS -->
        <li class="relative flex items-center">
          <a href="javascript:;" class="block p-0 transition-all text-size-sm ease-nav-brand text-slate-600 relative group" onclick="toggleAISignalNotifications()">
            <div class="relative p-3 rounded-xl transition-all duration-300 group-hover:bg-gradient-to-r group-hover:from-purple-50 group-hover:to-pink-50 group-hover:shadow-lg">
              <div class="relative">
                <i class="cursor-pointer fa-solid fa-bell text-xl text-purple-600 transition-transform duration-300 group-hover:scale-110"></i>
                <span id="ai-signal-badge" class="absolute -top-2 -right-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center border-2 border-white shadow-lg transform scale-0 transition-transform duration-300">0</span>
              </div>
            </div>
          </a>

          <!-- AI Signals Dropdown -->
          <div id="ai-signal-dropdown" class="hidden absolute right-0 top-full mt-2 w-96 bg-white rounded-2xl shadow-2xl border border-gray-200/80 backdrop-blur-lg z-50 transform origin-top-right transition-all duration-300 scale-95 opacity-0">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-purple-50 via-pink-50 to-red-50 rounded-t-2xl">
              <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fa-solid fa-money-bill-trend-up text-white text-sm"></i>
                  </div>
                  <div>
                    <h3 class="font-bold text-gray-800 text-sm">AI Signals</h3>
                    <p class="text-xs text-gray-600">Real-time trading signals</p>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <span id="ai-signal-count" class="text-xs bg-purple-500 text-white px-2 py-1 rounded-full">0</span>
                  <button onclick="clearAISignals()" class="text-xs text-purple-600 hover:text-purple-800 font-medium transition-colors duration-200">Clear</button>
                </div>
              </div>
            </div>
            
            <div class="max-h-80 overflow-y-auto custom-scrollbar">
              <div id="ai-signal-list">
                <div class="px-6 py-8 text-center">
                  <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-pink-200 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-inner">
                    <i class="fa-solid fa-bell text-purple-300 text-xl"></i>
                  </div>
                  <p class="text-gray-500 text-sm font-medium">No AI signals yet</p>
                  <p class="text-gray-400 text-xs mt-1">AI trading alerts will appear here</p>
                </div>
              </div>
            </div>

            <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
              <div class="flex justify-between items-center text-xs text-gray-500">
                <span>AI Trading Signals</span>
                <span id="ai-last-update">Just now</span>
              </div>
            </div>
          </div>
        </li>

        <!-- ⚙️ SETTINGS ICON - TRADE EXECUTIONS -->
        <li class="relative flex items-center">
          <a href="javascript:;" class="block p-0 transition-all text-size-sm ease-nav-brand text-slate-600 relative group" onclick="toggleTradeNotifications()">
            <div class="relative p-3 rounded-xl transition-all duration-300 group-hover:bg-gradient-to-r group-hover:from-blue-50 group-hover:to-green-50 group-hover:shadow-lg">
              <div class="relative">
                <i class="cursor-pointer fa-solid fa-wave-square text-xl text-blue-600 transition-transform duration-300 group-hover:scale-110"></i>
                <span id="trade-badge" class="absolute -top-2 -right-2 bg-gradient-to-r from-blue-500 to-green-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center border-2 border-white shadow-lg transform scale-0 transition-transform duration-300">0</span>
              </div>
            </div>
          </a>

          <!-- Trade Executions Dropdown -->
          <div id="trade-dropdown" class="hidden absolute right-0 top-full mt-2 w-96 bg-white rounded-2xl shadow-2xl border border-gray-200/80 backdrop-blur-lg z-50 transform origin-top-right transition-all duration-300 scale-95 opacity-0">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 via-green-50 to-emerald-50 rounded-t-2xl">
              <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-exchange-alt text-white text-sm"></i>
                  </div>
                  <div>
                    <h3 class="font-bold text-gray-800 text-sm">Trade Executions</h3>
                    <p class="text-xs text-gray-600">Position opens & closes</p>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <span id="trade-count" class="text-xs bg-blue-500 text-white px-2 py-1 rounded-full">0</span>
                  <button onclick="clearTradeNotifications()" class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200">Clear</button>
                </div>
              </div>
            </div>
            
            <div class="max-h-80 overflow-y-auto custom-scrollbar">
              <div id="trade-list">
                <div class="px-6 py-8 text-center">
                  <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-green-200 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-inner">
                    <i class="fas fa-solid fa-chart-column text-blue-300 text-xl"></i>
                  </div>
                  <p class="text-gray-500 text-sm font-medium">No trade executions yet</p>
                  <p class="text-gray-400 text-xs mt-1">Trade activities will appear here</p>
                </div>
              </div>
            </div>

            <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
              <div class="flex justify-between items-center text-xs text-gray-500">
                <span>Virtual Trading Activities</span>
                <span id="trade-last-update">Just now</span>
              </div>
            </div>
          </div>
        </li>

        <!-- User Status Badge & Dropdown -->
        <li class="relative flex items-center">
          <div class="relative group">
            <!-- Badge Status - Compact Version -->
            <button class="flex items-center space-x-2 px-3 py-2 rounded-xl bg-gradient-to-r from-gray-50 to-blue-50 border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300">
              <div class="flex items-center space-x-2">
                @php
                  $user = Auth::user();
                  $status = $user->getAccessStatus();
                  $statusConfig = [
                    'premium_active' => [
                      'icon' => '⭐',
                      'color' => 'from-yellow-400 to-orange-500',
                      'text' => 'Premium',
                      'textColor' => 'text-yellow-700'
                    ],
                    'trial_active' => [
                      'icon' => '🆓',
                      'color' => 'from-green-400 to-blue-500',
                      'text' => 'Trial',
                      'textColor' => 'text-green-700'
                    ],
                    'trial_expired' => [
                      'icon' => '⏰',
                      'color' => 'from-red-400 to-pink-500',
                      'text' => 'Expired',
                      'textColor' => 'text-red-700'
                    ],
                    'premium_expired' => [
                      'icon' => '💸',
                      'color' => 'from-red-400 to-pink-500',
                      'text' => 'Expired',
                      'textColor' => 'text-red-700'
                    ],
                    'free' => [
                      'icon' => '🔒',
                      'color' => 'from-gray-400 to-gray-600',
                      'text' => 'Free',
                      'textColor' => 'text-gray-700'
                    ]
                  ];
                  $config = $statusConfig[$status] ?? $statusConfig['free'];
                @endphp
                
                <!-- Badge Icon -->
                <div class="w-8 h-8 bg-gradient-to-r {{ $config['color'] }} rounded-full flex items-center justify-center shadow-lg">
                  <span class="text-white text-sm font-bold">{{ $config['icon'] }}</span>
                </div>
                
                <!-- Status Text -->
                <span class="text-sm font-semibold {{ $config['textColor'] }} hidden sm:block">
                  {{ $config['text'] }}
                </span>
                
                <!-- Chevron Icon -->
                <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-300 group-hover:rotate-180"></i>
              </div>
            </button>

            <!-- Dropdown Menu -->
            <div class="absolute right-0 top-full mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-gray-200/80 backdrop-blur-lg z-50 transform origin-top-right transition-all duration-300 scale-95 opacity-0 invisible group-hover:scale-100 group-hover:opacity-100 group-hover:visible">
              
              <!-- Header -->
              <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-blue-50 rounded-t-2xl">
                <div class="flex items-center space-x-3">
                  <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-user text-white text-lg"></i>
                  </div>
                  <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-gray-800 text-sm truncate">{{ Auth::user()->name }}</h3>
                    <p class="text-xs text-gray-600 truncate">{{ Auth::user()->email }}</p>
                  </div>
                </div>
              </div>

              <!-- Plan Info -->
              <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex justify-between items-center mb-2">
                  <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Current Plan</span>
                  <span class="text-xs font-bold {{ $config['textColor'] }} bg-{{ explode(' ', $config['textColor'])[0] }}-50 px-2 py-1 rounded-full">
                    {{ $config['text'] }}
                  </span>
                </div>
                
                @if($user->hasActiveTrial())
                  <div class="flex items-center space-x-2 text-xs text-gray-600">
                    <i class="fas fa-clock text-green-500"></i>
                    <span>Trial ends: <strong>{{ $user->trial_ends_at->format('M d, Y H:i') }}</strong></span>
                  </div>
                  <div class="mt-2 w-full bg-gray-200 rounded-full h-1.5">
                    <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $user->getTrialProgressPercent() }}%"></div>
                  </div>
                  <div class="flex justify-between text-xs text-gray-500 mt-1">
                    <span>Started</span>
                    <span>{{ $user->getTrialProgressPercent() }}%</span>
                    <span>Ends</span>
                  </div>
                @elseif($user->hasActivePremium())
                  <div class="flex items-center space-x-2 text-xs text-gray-600">
                    <i class="fas fa-crown text-yellow-500"></i>
                    <span>Premium until: <strong>{{ $user->premium_ends_at->format('M d, Y H:i') }}</strong></span>
                  </div>
                @elseif($user->hasExpiredTrial())
                  <div class="flex items-center space-x-2 text-xs text-red-600 bg-red-50 px-3 py-2 rounded-lg">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Your trial expired on {{ $user->trial_ends_at->format('M d, Y') }}</span>
                  </div>
                @else
                  <div class="flex items-center space-x-2 text-xs text-gray-600">
                    <i class="fas fa-info-circle text-blue-500"></i>
                    <span>Free plan - upgrade for full access</span>
                  </div>
                @endif
              </div>

              <!-- Menu Items -->
              <div class="py-2">
                @if($user->shouldRedirectToPricing())
                  <a href="{{ route('subscription') }}" class="flex items-center space-x-3 px-6 py-3 text-sm text-white bg-gradient-to-r from-purple-500 to-blue-600 hover:from-purple-600 hover:to-blue-700 transition-all duration-300 mx-4 rounded-xl shadow-lg">
                    <i class="fas fa-rocket w-5 text-center"></i>
                    <span class="font-semibold">Upgrade to Premium</span>
                    <i class="fas fa-arrow-right ml-auto text-xs"></i>
                  </a>
                @endif

                <a href="{{ route('subscription') }}" class="flex items-center space-x-3 px-6 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-all duration-200">
                  <i class="fas fa-credit-card w-5 text-center text-purple-500"></i>
                  <span>Billing & Subscription</span>
                </a>

                <a href="{{ route('profile') }}" class="flex items-center space-x-3 px-6 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-all duration-200">
                  <i class="fas fa-user-cog w-5 text-center text-blue-500"></i>
                  <span>Account Settings</span>
                </a>

                <div class="border-t border-gray-100 mt-2 pt-2">
                  <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center space-x-3 px-6 py-3 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 w-full text-left transition-all duration-200">
                      <i class="fas fa-sign-out-alt w-5 text-center text-red-500"></i>
                      <span>Logout</span>
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Audio Elements -->
<audio id="ai-notification-sound" preload="auto">
  <source src="{{ asset('sounds/notification.mp3') }}" type="audio/mpeg">
</audio>

<audio id="trade-notification-sound" preload="auto">
  <source src="{{ asset('sounds/trade.mp3') }}" type="audio/mpeg">
</audio>

<!-- Pusher -->
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>

<script>
window.pusherInstance = null;
window.aiSignalManager = null;
window.tradeNotificationManager = null;

// 🔔 AI SIGNAL MANAGER
class AISignalManager {
    constructor() {
        if (window.aiSignalManager) {
            return window.aiSignalManager;
        }

        console.log('🚀 INIT: Starting AI Signal Manager...');
        this.signalCount = 0;
        this.aiSignals = new Map();
        this.userId = {{ Auth::id() ?? 'null' }};
        
        this.init();
    }

    init() {
        this.connectPusher();
        this.loadAISignals();
    }

    connectPusher() {
        if (!window.pusherInstance) {
            console.error('❌ Pusher not initialized');
            return;
        }

        this.pusher = window.pusherInstance;
        this.subscribeToAISignals();
    }

    subscribeToAISignals() {
        const channelName = `private-user-${this.userId}`;
        console.log('📡 AI: Subscribing to channel:', channelName);
        
        try {
            const channel = this.pusher.subscribe(channelName);
            
            channel.bind('pusher:subscription_succeeded', () => {
                console.log('✅ AI: Channel subscribed!');
            });

            // ✅ HANDLE AI SIGNALS & REMOVAL SIGNALS
            channel.bind('new.signal', (data) => {
                // AI Signals biasa (dengan confidence & score)
                if (data.confidence && data.score) {
                    console.log('💎 AI SIGNAL RECEIVED:', data);
                    this.handleAISignal(data);
                }
                // Removal signals (score & confidence rendah + risk HIGH)
                else if (data.score <= 10 && data.confidence <= 15 && data.risk === 'HIGH') {
                    console.log('🗑️ REMOVAL SIGNAL RECEIVED:', data);
                    this.handleAISignal(data); // Tetap proses sebagai AI signal
                }
            });

        } catch (error) {
            console.error('❌ AI: Subscription failed:', error);
        }
    }

    handleAISignal(signalData) {
        const signalKey = `ai_${signalData.id}_${Date.now()}`;
        
        const signal = {
            id: signalKey,
            title: `Alert`,
            message: this.formatAIMessage(signalData),
            type: signalData.action.toLowerCase(),
            data: {
                symbol: signalData.symbol,
                name: signalData.name,
                action: signalData.action,
                confidence: signalData.confidence,
                price: signalData.price,
                score: signalData.score,
                risk: signalData.risk,
                health: signalData.health,
                volume_spike: signalData.volume_spike,
                momentum_regime: signalData.momentum_regime,
                rsi_delta: signalData.rsi_delta,
                source: 'ai_signal'
            },
            timestamp: signalData.timestamp,
            received_at: new Date().toISOString()
        };
        
        this.aiSignals.set(signalKey, signal);
        this.signalCount = this.aiSignals.size;
        this.updateAIBadge();
        this.addAISignalToUI(signal, signalKey);
        this.saveAISignals();
        this.playAISound();
        this.showAIBrowserNotification(signal);
        this.updateAILastUpdate();
    }

    formatAIMessage(signal) {
        return `
${signal.name} (${signal.symbol})
💲 Price: $${parseFloat(signal.price).toFixed(4)}
📊 Score: ${signal.score}/100 | Confidence: ${signal.confidence}%
⚡ Volume: ${signal.volume_spike}x | Momentum: ${signal.momentum_regime}
🎯 Action: ${signal.action} | Risk: ${signal.risk} | Health: ${signal.health}/100
        `.trim();
    }

    updateAIBadge() {
        const badge = document.getElementById('ai-signal-badge');
        const countBadge = document.getElementById('ai-signal-count');
        
        if (badge) {
            badge.textContent = this.signalCount > 99 ? '99+' : this.signalCount;
            if (this.signalCount > 0) {
                badge.classList.remove('scale-0');
                badge.classList.add('scale-100');
            } else {
                badge.classList.add('scale-0');
                badge.classList.remove('scale-100');
            }
        }
        
        if (countBadge) {
            countBadge.textContent = this.signalCount > 99 ? '99+' : this.signalCount;
        }
    }

    addAISignalToUI(signal, signalKey) {
        const list = document.getElementById('ai-signal-list');
        if (!list) return;

        const emptyState = list.querySelector('.text-center');
        if (emptyState) emptyState.style.display = 'none';

        const signalElement = this.createAISignalElement(signal, signalKey);
        list.insertBefore(signalElement, list.firstChild);

        // Auto cleanup
        if (list.children.length > 15) {
            const children = Array.from(list.children);
            for (let i = children.length - 1; i >= 0; i--) {
                if (children[i].classList.contains('ai-signal-item') && children[i] !== signalElement) {
                    const key = children[i].dataset.signalKey;
                    if (key) this.aiSignals.delete(key);
                    list.removeChild(children[i]);
                    break;
                }
            }
            this.saveAISignals();
        }
    }

    createAISignalElement(signal, signalKey = null) {
        const config = {
            'buy': { 
                color: 'text-green-600', 
                bg: 'bg-green-100', 
                border: 'border-l-green-400',
                gradient: 'from-green-50 to-emerald-50',
                titleIcon: '🚀'
            },
            'sell': { 
                color: 'text-red-600', 
                bg: 'bg-red-100', 
                border: 'border-l-red-400',
                gradient: 'from-red-50 to-pink-50',
                titleIcon: '❌'
            },
            'monitor': { 
                color: 'text-yellow-600', 
                bg: 'bg-yellow-100', 
                border: 'border-l-yellow-400',
                gradient: 'from-yellow-50 to-amber-50',
                titleIcon: '❌'
            }
        };
        
        const typeConfig = config[signal.type] || config.buy;
        const timeAgo = this.getTimeAgo(signal.received_at || signal.timestamp);

        const div = document.createElement('div');
        div.className = `ai-signal-item px-4 py-3 border-b border-gray-100/50 hover:bg-gradient-to-r ${typeConfig.gradient} cursor-pointer transform transition-all duration-500 opacity-0 -translate-x-4 border-l-4 ${typeConfig.border}`;
        
        if (signalKey) {
            div.dataset.signalKey = signalKey;
        }
        
        div.innerHTML = `
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 ${typeConfig.bg} rounded-xl flex items-center justify-center shadow-lg">
                        <span class="text-lg">${typeConfig.titleIcon}</span>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center space-x-2">
                            <h4 class="text-sm font-bold text-gray-900 truncate">${signal.title}</h4>
                            <span class="text-xs font-mono font-semibold bg-gray-100 text-gray-700 px-2 py-1 rounded-full">${signal.data.symbol}</span>
                            <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full">AI</span>
                        </div>
                        <span class="text-xs text-gray-400 whitespace-nowrap">${timeAgo}</span>
                    </div>
                    
                    <p class="text-sm text-gray-700 leading-relaxed mb-2 whitespace-pre-line">${signal.message}</p>
                    
                    <div class="flex items-center space-x-3 text-xs text-gray-600 flex-wrap gap-2">
                        <span class="px-2 py-1 rounded-full ${signal.data.action === 'buy' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'} font-medium">${signal.data.action}</span>
                        <span class="flex items-center space-x-1 bg-blue-50 text-blue-700 px-2 py-1 rounded-full">
                            <i class="fas fa-bullseye text-xs"></i>
                            <span>${signal.data.confidence}%</span>
                        </span>
                        <span class="flex items-center space-x-1 bg-purple-50 text-purple-700 px-2 py-1 rounded-full">
                            <i class="fas fa-star text-xs"></i>
                            <span>${signal.data.score}</span>
                        </span>
                        <span class="flex items-center space-x-1 ${signal.data.risk === 'LOW' ? 'bg-green-50 text-green-700' : (signal.data.risk === 'HIGH' ? 'bg-red-50 text-red-700' : 'bg-yellow-50 text-yellow-700')} px-2 py-1 rounded-full">
                            <i class="fas fa-shield-alt text-xs"></i>
                            <span>${signal.data.risk}</span>
                        </span>
                    </div>
                </div>
            </div>
        `;
        
        setTimeout(() => {
            div.classList.add('opacity-100', 'translate-x-0');
        }, 10);
        
        div.addEventListener('click', () => {
            if (signalKey) {
                this.aiSignals.delete(signalKey);
                this.signalCount = this.aiSignals.size;
                this.updateAIBadge();
                this.saveAISignals();
            }
            div.style.opacity = '0';
            div.style.transform = 'translateX(-20px)';
            setTimeout(() => {
                if (div.parentNode) {
                    div.parentNode.removeChild(div);
                }
                if (this.signalCount === 0) {
                    const emptyState = document.querySelector('#ai-signal-list .text-center');
                    if (emptyState) emptyState.style.display = 'block';
                }
            }, 300);
        });
        
        return div;
    }

    playAISound() {
        const audio = document.getElementById('ai-notification-sound');
        if (audio) {
            audio.currentTime = 0;
            audio.play().catch(error => {
                console.log('🔇 AI SOUND: Auto-play blocked', error);
            });
        }
    }

    showAIBrowserNotification(signal) {
        if (!("Notification" in window) || Notification.permission !== "granted") {
            return;
        }

        try {
            const notif = new Notification(`🤖 ${signal.title}`, {
                body: signal.message,
                icon: '/favicon.ico',
                tag: 'ai-signal',
                requireInteraction: true,
                silent: false
            });

            notif.onclick = () => {
                window.focus();
                toggleAISignalNotifications();
                notif.close();
            };

            setTimeout(() => notif.close(), 8000);

        } catch (error) {
            console.error('🔔 AI: Browser notification failed:', error);
        }
    }

    updateAILastUpdate() {
        const lastUpdate = document.getElementById('ai-last-update');
        if (lastUpdate) {
            const now = new Date();
            lastUpdate.textContent = now.toLocaleTimeString();
        }
    }

    async loadAISignals() {
        try {
            const storageKey = `user_${this.userId}_ai_signals`;
            const stored = localStorage.getItem(storageKey);
            
            if (stored) {
                const signals = JSON.parse(stored);
                this.aiSignals = new Map(signals);
                this.signalCount = this.aiSignals.size;
                this.updateAIBadge();
                
                if (this.aiSignals.size > 0) {
                    console.log(`📥 AI: Loaded ${this.aiSignals.size} signals`);
                    this.displayAISignals();
                }
            }
        } catch (error) {
            console.error('❌ AI: Load failed:', error);
        }
    }

    displayAISignals() {
        const list = document.getElementById('ai-signal-list');
        if (!list) return;

        const emptyState = list.querySelector('.text-center');
        if (emptyState) emptyState.style.display = 'none';

        list.querySelectorAll('.ai-signal-item').forEach(el => el.remove());

        Array.from(this.aiSignals.values())
            .reverse()
            .forEach(signal => {
                const element = this.createAISignalElement(signal);
                list.appendChild(element);
            });
    }

    saveAISignals() {
        try {
            const storageKey = `user_${this.userId}_ai_signals`;
            const signalsArray = Array.from(this.aiSignals.entries());
            localStorage.setItem(storageKey, JSON.stringify(signalsArray));
        } catch (error) {
            console.error('❌ AI: Save failed:', error);
        }
    }

    clearAISignals() {
        this.aiSignals.clear();
        this.signalCount = 0;
        this.updateAIBadge();
        this.saveAISignals();
        
        const list = document.getElementById('ai-signal-list');
        if (list) {
            const emptyState = list.querySelector('.text-center');
            if (emptyState) emptyState.style.display = 'block';
            list.querySelectorAll('.ai-signal-item').forEach(el => el.remove());
        }
        
        console.log('🗑️ AI: All signals cleared');
    }

    getTimeAgo(timestamp) {
        if (!timestamp) return 'just now';
        
        const now = new Date();
        const signalTime = new Date(timestamp);
        const diffMs = now - signalTime;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        
        if (diffMins < 1) return 'just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        return `${Math.floor(diffHours / 24)}d ago`;
    }
}

// ⚙️ TRADE NOTIFICATION MANAGER
class TradeNotificationManager {
    constructor() {
        if (window.tradeNotificationManager) {
            return window.tradeNotificationManager;
        }

        console.log('🚀 INIT: Starting Trade Notification Manager...');
        this.tradeCount = 0;
        this.tradeNotifications = new Map();
        this.userId = {{ Auth::id() ?? 'null' }};
        
        this.init();
    }

    init() {
        this.connectPusher();
        this.loadTradeNotifications();
    }

    connectPusher() {
        if (!window.pusherInstance) {
            console.error('❌ Pusher not initialized');
            return;
        }

        this.pusher = window.pusherInstance;
        this.subscribeToTradeNotifications();
    }

    subscribeToTradeNotifications() {
        const channelName = `private-user-${this.userId}`;
        console.log('📡 TRADE: Subscribing to channel:', channelName);
        
        try {
            const channel = this.pusher.subscribe(channelName);
            
            channel.bind('pusher:subscription_succeeded', () => {
                console.log('✅ TRADE: Channel subscribed!');
            });

            // ✅ PERBAIKI FILTER: Handle trade execution berdasarkan data.type
            channel.bind('new.signal', (data) => {
                console.log('📦 RAW TRADE DATA:', data); // Debug semua data
                
                // Filter untuk trade execution
                if (data.data && data.data.type === 'trade_execution') {
                    console.log('⚡ TRADE EXECUTION RECEIVED:', data);
                    this.handleTradeNotification(data);
                }
                // Filter untuk position closed
                else if (data.data && data.data.type === 'position_closed') {
                    console.log('🔒 POSITION CLOSED RECEIVED:', data);
                    this.handleTradeNotification(data);
                }
                // Fallback: jika ada action tapi tanpa confidence (kemungkinan trade)
                else if (data.data && data.data.action && !data.confidence) {
                    console.log('⚡ POTENTIAL TRADE RECEIVED:', data);
                    this.handleTradeNotification(data);
                }
            });

        } catch (error) {
            console.error('❌ TRADE: Subscription failed:', error);
        }
    }

    handleTradeNotification(tradeData) {
        const tradeKey = `trade_${tradeData.id}_${Date.now()}`;
        
        // Normalize data berdasarkan type
        let normalizedData = this.normalizeTradeData(tradeData);
        
        const notification = {
            id: tradeKey,
            title: normalizedData.title,
            message: normalizedData.message,
            type: normalizedData.type,
            data: {
                symbol: normalizedData.symbol,
                action: normalizedData.action,
                type: normalizedData.notificationType,
                icon: normalizedData.icon,
                color: normalizedData.color,
                timestamp: normalizedData.timestamp,
                source: 'trade_execution'
            },
            timestamp: normalizedData.timestamp,
            received_at: new Date().toISOString()
        };
        
        this.tradeNotifications.set(tradeKey, notification);
        this.tradeCount = this.tradeNotifications.size;
        this.updateTradeBadge();
        this.addTradeToUI(notification, tradeKey);
        this.saveTradeNotifications();
        this.playTradeSound();
        this.showTradeBrowserNotification(notification);
        this.updateTradeLastUpdate();
    }

    normalizeTradeData(rawData) {
        // Jika data dari backend trade execution
        if (rawData.data && rawData.data.type === 'trade_execution') {
            return {
                id: rawData.id || uniqid(),
                title: rawData.title || `🎯 Trade - ${rawData.data.symbol}`,
                message: rawData.message || `Your ${rawData.data.action} position for ${rawData.data.symbol} has been opened`,
                type: rawData.data.action.toLowerCase(),
                symbol: rawData.data.symbol,
                action: rawData.data.action,
                notificationType: 'trade_execution',
                icon: '💰',
                color: rawData.data.action === 'BUY' ? 'green' : 'red',
                timestamp: rawData.data.timestamp || new Date().toISOString()
            };
        }
        // Jika data dari backend position closed
        else if (rawData.data && rawData.data.type === 'position_closed') {
            const pnl = rawData.data.pnl || 0;
            const pnlFormatted = rawData.data.pnl_formatted || '0.00';
            const icon = pnl >= 0 ? '💰' : '🔴';
            const type = rawData.data.action ? rawData.data.action.toLowerCase() : 'close';
            
            return {
                id: rawData.id || uniqid(),
                title: rawData.title || `⚡ Position Closed - ${rawData.data.symbol}`,
                message: rawData.message || `${rawData.data.position_type} position closed. PnL: $${pnlFormatted} - ${rawData.data.reason}`,
                type: type,
                symbol: rawData.data.symbol,
                action: rawData.data.action || 'CLOSE',
                notificationType: 'position_closed',
                icon: icon,
                color: pnl >= 0 ? 'green' : 'red',
                timestamp: rawData.data.timestamp || new Date().toISOString()
            };
        }
        // Fallback untuk data generic
        else {
            return {
                id: rawData.id || uniqid(),
                title: rawData.title || `${rawData.icon || '⚡'} ${rawData.data?.action || 'Trade'} - ${rawData.data?.symbol || 'Unknown'}`,
                message: rawData.message || rawData.original_message || rawData.explanation || 'Trade executed',
                type: rawData.type || rawData.data?.action?.toLowerCase() || 'info',
                symbol: rawData.data?.symbol || 'Unknown',
                action: rawData.data?.action || 'TRADE',
                notificationType: 'generic_trade',
                icon: rawData.icon || '⚡',
                color: 'blue',
                timestamp: rawData.timestamp || new Date().toISOString()
            };
        }
    }

    updateTradeBadge() {
        const badge = document.getElementById('trade-badge');
        const countBadge = document.getElementById('trade-count');
        
        if (badge) {
            badge.textContent = this.tradeCount > 99 ? '99+' : this.tradeCount;
            if (this.tradeCount > 0) {
                badge.classList.remove('scale-0');
                badge.classList.add('scale-100');
            } else {
                badge.classList.add('scale-0');
                badge.classList.remove('scale-100');
            }
        }
        
        if (countBadge) {
            countBadge.textContent = this.tradeCount > 99 ? '99+' : this.tradeCount;
        }
    }

    addTradeToUI(notification, tradeKey) {
        const list = document.getElementById('trade-list');
        if (!list) return;

        const emptyState = list.querySelector('.text-center');
        if (emptyState) emptyState.style.display = 'none';

        const tradeElement = this.createTradeElement(notification, tradeKey);
        list.insertBefore(tradeElement, list.firstChild);

        // Auto cleanup
        if (list.children.length > 15) {
            const children = Array.from(list.children);
            for (let i = children.length - 1; i >= 0; i--) {
                if (children[i].classList.contains('trade-item') && children[i] !== tradeElement) {
                    const key = children[i].dataset.tradeKey;
                    if (key) this.tradeNotifications.delete(key);
                    list.removeChild(children[i]);
                    break;
                }
            }
            this.saveTradeNotifications();
        }
    }

    createTradeElement(notification, tradeKey = null) {
        const config = {
            'buy': { 
                color: 'text-green-600', 
                bg: 'bg-green-100', 
                border: 'border-l-green-400',
                gradient: 'from-green-50 to-emerald-50',
                titleIcon: '🟢'
            },
            'sell': { 
                color: 'text-red-600', 
                bg: 'bg-red-100', 
                border: 'border-l-red-400',
                gradient: 'from-red-50 to-pink-50',
                titleIcon: '🔴'
            },
            'close': { 
                color: 'text-blue-600', 
                bg: 'bg-blue-100', 
                border: 'border-l-blue-400',
                gradient: 'from-blue-50 to-cyan-50',
                titleIcon: '⚡'
            },
            'stop_loss': { 
                color: 'text-red-600', 
                bg: 'bg-red-100', 
                border: 'border-l-red-400',
                gradient: 'from-red-50 to-pink-50',
                titleIcon: '🛑'
            },
            'take_profit': { 
                color: 'text-green-600', 
                bg: 'bg-green-100', 
                border: 'border-l-green-400',
                gradient: 'from-green-50 to-emerald-50',
                titleIcon: '💰'
            },
            'error': { 
                color: 'text-red-600', 
                bg: 'bg-red-100', 
                border: 'border-l-red-400',
                gradient: 'from-red-50 to-pink-50',
                titleIcon: '❌'
            }
        };
        
        const typeConfig = config[notification.type] || config.close;
        const timeAgo = this.getTimeAgo(notification.received_at || notification.timestamp);

        const div = document.createElement('div');
        div.className = `trade-item px-4 py-3 border-b border-gray-100/50 hover:bg-gradient-to-r ${typeConfig.gradient} cursor-pointer transform transition-all duration-500 opacity-0 -translate-x-4 border-l-4 ${typeConfig.border}`;
        
        if (tradeKey) {
            div.dataset.tradeKey = tradeKey;
        }
        
        div.innerHTML = `
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 ${typeConfig.bg} rounded-xl flex items-center justify-center shadow-lg">
                        <span class="text-lg">${typeConfig.titleIcon}</span>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center space-x-2">
                            <h4 class="text-sm font-bold text-gray-900 truncate">${notification.title}</h4>
                            <span class="text-xs font-mono font-semibold bg-gray-100 text-gray-700 px-2 py-1 rounded-full">${notification.data.symbol}</span>
                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">Trade</span>
                        </div>
                        <span class="text-xs text-gray-400 whitespace-nowrap">${timeAgo}</span>
                    </div>
                    
                    <p class="text-sm text-gray-700 leading-relaxed mb-2 whitespace-pre-line">${notification.message}</p>
                    
                    <div class="flex items-center space-x-3 text-xs text-gray-600 flex-wrap gap-2">
                        <span class="px-2 py-1 rounded-full ${notification.data.action === 'BUY' ? 'bg-green-100 text-green-700' : (notification.data.action === 'SELL' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700')} font-medium">${notification.data.action}</span>
                    </div>
                </div>
            </div>
        `;
        
        setTimeout(() => {
            div.classList.add('opacity-100', 'translate-x-0');
        }, 10);
        
        div.addEventListener('click', () => {
            if (tradeKey) {
                this.tradeNotifications.delete(tradeKey);
                this.tradeCount = this.tradeNotifications.size;
                this.updateTradeBadge();
                this.saveTradeNotifications();
            }
            div.style.opacity = '0';
            div.style.transform = 'translateX(-20px)';
            setTimeout(() => {
                if (div.parentNode) {
                    div.parentNode.removeChild(div);
                }
                if (this.tradeCount === 0) {
                    const emptyState = document.querySelector('#trade-list .text-center');
                    if (emptyState) emptyState.style.display = 'block';
                }
            }, 300);
        });
        
        return div;
    }

    playTradeSound() {
        const audio = document.getElementById('trade-notification-sound');
        if (audio) {
            audio.currentTime = 0;
            audio.play().catch(error => {
                console.log('🔇 TRADE SOUND: Auto-play blocked', error);
            });
        }
    }

    showTradeBrowserNotification(notification) {
        if (!("Notification" in window) || Notification.permission !== "granted") {
            return;
        }

        try {
            const notif = new Notification(`⚡ ${notification.title}`, {
                body: notification.message,
                icon: '/favicon.ico',
                tag: 'trade-execution',
                requireInteraction: true,
                silent: false
            });

            notif.onclick = () => {
                window.focus();
                toggleTradeNotifications();
                notif.close();
            };

            setTimeout(() => notif.close(), 8000);

        } catch (error) {
            console.error('🔔 TRADE: Browser notification failed:', error);
        }
    }

    updateTradeLastUpdate() {
        const lastUpdate = document.getElementById('trade-last-update');
        if (lastUpdate) {
            const now = new Date();
            lastUpdate.textContent = now.toLocaleTimeString();
        }
    }

    async loadTradeNotifications() {
        try {
            const storageKey = `user_${this.userId}_trade_notifications`;
            const stored = localStorage.getItem(storageKey);
            
            if (stored) {
                const notifications = JSON.parse(stored);
                this.tradeNotifications = new Map(notifications);
                this.tradeCount = this.tradeNotifications.size;
                this.updateTradeBadge();
                
                if (this.tradeNotifications.size > 0) {
                    console.log(`📥 TRADE: Loaded ${this.tradeNotifications.size} notifications`);
                    this.displayTradeNotifications();
                }
            }
        } catch (error) {
            console.error('❌ TRADE: Load failed:', error);
        }
    }

    displayTradeNotifications() {
        const list = document.getElementById('trade-list');
        if (!list) return;

        const emptyState = list.querySelector('.text-center');
        if (emptyState) emptyState.style.display = 'none';

        list.querySelectorAll('.trade-item').forEach(el => el.remove());

        Array.from(this.tradeNotifications.values())
            .reverse()
            .forEach(notification => {
                const element = this.createTradeElement(notification);
                list.appendChild(element);
            });
    }

    saveTradeNotifications() {
        try {
            const storageKey = `user_${this.userId}_trade_notifications`;
            const notificationsArray = Array.from(this.tradeNotifications.entries());
            localStorage.setItem(storageKey, JSON.stringify(notificationsArray));
        } catch (error) {
            console.error('❌ TRADE: Save failed:', error);
        }
    }

    clearTradeNotifications() {
        this.tradeNotifications.clear();
        this.tradeCount = 0;
        this.updateTradeBadge();
        this.saveTradeNotifications();
        
        const list = document.getElementById('trade-list');
        if (list) {
            const emptyState = list.querySelector('.text-center');
            if (emptyState) emptyState.style.display = 'block';
            list.querySelectorAll('.trade-item').forEach(el => el.remove());
        }
        
        console.log('🗑️ TRADE: All notifications cleared');
    }

    getTimeAgo(timestamp) {
        if (!timestamp) return 'just now';
        
        const now = new Date();
        const signalTime = new Date(timestamp);
        const diffMs = now - signalTime;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        
        if (diffMins < 1) return 'just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        return `${Math.floor(diffHours / 24)}d ago`;
    }
}

// PUSHER CONNECTION MANAGER
class PusherConnectionManager {
    constructor() {
        console.log('🚀 INIT: Starting Pusher Connection Manager...');
        this.pusher = null;
        this.isConnected = false;
        this.userId = {{ Auth::id() ?? 'null' }};
        
        this.init();
    }

    init() {
        this.connectPusher();
        this.setupNotificationPermission();
    }

    setupNotificationPermission() {
        if (!("Notification" in window)) {
            console.log('❌ Browser tidak support notifications');
            return;
        }

        if (Notification.permission === "default") {
            console.log('🔔 Requesting notification permission...');
            Notification.requestPermission();
        }
    }

    connectPusher() {
        if (window.pusherInstance) {
            console.log('🔧 INIT: Reusing existing Pusher instance');
            this.pusher = window.pusherInstance;
            this.setupManagers();
            return;
        }

        try {
            console.log('🔌 CONNECT: Creating new Pusher connection...');
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            this.pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
                cluster: '{{ env('PUSHER_APP_CLUSTER', 'ap1') }}',
                forceTLS: true,
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            });

            window.pusherInstance = this.pusher;

            this.pusher.connection.bind('connected', () => {
                console.log('✅ CONNECT: Pusher Connected!');
                this.isConnected = true;
                this.setupManagers();
            });

            this.pusher.connection.bind('disconnected', () => {
                console.log('🔴 CONNECT: Pusher Disconnected');
                this.isConnected = false;
            });

            this.pusher.connection.bind('error', (error) => {
                console.error('❌ CONNECT: Pusher Error:', error);
            });

        } catch (error) {
            console.error('❌ CONNECT: Pusher init failed:', error);
        }
    }

    setupManagers() {
        // Initialize both managers
        window.aiSignalManager = new AISignalManager();
        window.tradeNotificationManager = new TradeNotificationManager();
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 DOM Ready - Initializing Pusher...');
    window.pusherConnectionManager = new PusherConnectionManager();
});


// Utility function untuk generate unique ID
function uniqid() {
    return 'id_' + Math.random().toString(36).substr(2, 9);
}
// GLOBAL FUNCTIONS
function toggleAISignalNotifications() {
    const dropdown = document.getElementById('ai-signal-dropdown');
    if (dropdown.classList.contains('hidden')) {
        dropdown.classList.remove('hidden', 'scale-95', 'opacity-0');
        dropdown.classList.add('scale-100', 'opacity-100');
    } else {
        dropdown.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            dropdown.classList.add('hidden');
        }, 300);
    }
}

function toggleTradeNotifications() {
    const dropdown = document.getElementById('trade-dropdown');
    if (dropdown.classList.contains('hidden')) {
        dropdown.classList.remove('hidden', 'scale-95', 'opacity-0');
        dropdown.classList.add('scale-100', 'opacity-100');
    } else {
        dropdown.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            dropdown.classList.add('hidden');
        }, 300);
    }
}

function clearAISignals() {
    if (window.aiSignalManager) {
        window.aiSignalManager.clearAISignals();
    }
}

function clearTradeNotifications() {
    if (window.tradeNotificationManager) {
        window.tradeNotificationManager.clearTradeNotifications();
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const aiDropdown = document.getElementById('ai-signal-dropdown');
    const tradeDropdown = document.getElementById('trade-dropdown');
    const aiBell = event.target.closest('a[onclick="toggleAISignalNotifications()"]');
    const tradeCog = event.target.closest('a[onclick="toggleTradeNotifications()"]');
    
    if (!aiBell && aiDropdown && !aiDropdown.contains(event.target)) {
        aiDropdown.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            aiDropdown.classList.add('hidden');
        }, 300);
    }
    
    if (!tradeCog && tradeDropdown && !tradeDropdown.contains(event.target)) {
        tradeDropdown.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            tradeDropdown.classList.add('hidden');
        }, 300);
    }
});

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 DOM: Content loaded, initializing notification system...');
    
    // Initialize pusher connection
    new PusherConnectionManager();
});

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    @keyframes marquee {
        0% { transform: translateX(100%); }
        100% { transform: translateX(-100%); }
    }
    .animate-marquee {
        animation: marquee 30s linear infinite;
    }
    
    .ai-signal-item, .trade-item {
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .ai-signal-item:hover, .trade-item:hover {
        transform: translateX(4px);
    }
`;
document.head.appendChild(style);
</script>