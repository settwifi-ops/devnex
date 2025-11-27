// GANTI file resources/js/websocket-notifications.js dengan ini:

// Pusher Manager
class PusherManager {
    constructor() {
        this.notificationCount = 0;
        this.pusher = null;
        this.channel = null;
        this.init();
    }

    init() {
        this.connectPusher();
        this.setupEventListeners();
        this.initRunningTextAnimation();
        
        // Auto-refresh AI decisions every 30 seconds
        setInterval(() => this.refreshAIDecisions(), 30000);
    }

    connectPusher() {
        try {
            // Initialize Pusher
            this.pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
                cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
                encrypted: true
            });

            // Subscribe to channel
            this.channel = this.pusher.subscribe('ai-signals');

            // Listen for new signals
            this.channel.bind('new.signal', (data) => {
                console.log('New AI Signal received:', data);
                this.handleNewAISignal(data);
            });

            console.log('Pusher connected successfully');
            this.addNotification('System Connected', 'Real-time trading signals active', 'success');

        } catch (error) {
            console.error('Failed to initialize Pusher:', error);
            setTimeout(() => this.connectPusher(), 5000);
        }
    }

    handleNewAISignal(signal) {
        // Format data untuk notifikasi
        const notificationData = {
            symbol: signal.symbol,
            action: signal.action,
            confidence: signal.confidence,
            price: signal.price,
            explanation: signal.explanation,
            score: signal.market_data?.score || 0,
            risk: signal.market_data?.risk || 'MEDIUM',
            health: signal.market_data?.health || 100,
            volume_spike: signal.market_data?.volume_spike || 0,
            momentum_regime: signal.market_data?.momentum_regime || 'NEUTRAL'
        };

        this.addNotification(
            `AI Signal: ${signal.symbol}`,
            this.generateNotificationMessage(notificationData),
            'ai_decision',
            notificationData
        );

        // Refresh data dengan delay untuk smooth transition
        setTimeout(() => this.refreshAIDecisions(), 1000);
    }

    generateNotificationMessage(data) {
        return `${data.action} recommendation • ${data.confidence}% confidence • Score: ${data.score}/100 • Risk: ${data.risk}`;
    }

    addNotification(title, message, type = 'info', data = null) {
        this.notificationCount++;
        this.updateBadge();

        const notificationsList = document.getElementById('notifications-list');
        const emptyState = notificationsList.querySelector('.text-center');
        
        if (emptyState) {
            emptyState.style.display = 'none';
        }

        const notificationElement = this.createNotificationElement(title, message, type, data);
        
        // Smooth add animation
        notificationElement.style.opacity = '0';
        notificationElement.style.transform = 'translateY(-10px)';
        notificationsList.insertBefore(notificationElement, notificationsList.firstChild);
        
        setTimeout(() => {
            notificationElement.style.opacity = '1';
            notificationElement.style.transform = 'translateY(0)';
            notificationElement.style.transition = 'all 0.3s ease-out';
        }, 10);

        // Keep only last 6 notifications
        if (notificationsList.children.length > 6) {
            const lastChild = notificationsList.lastChild;
            if (lastChild.style.display !== 'none') {
                lastChild.style.opacity = '0';
                lastChild.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    if (lastChild.parentNode) {
                        lastChild.parentNode.removeChild(lastChild);
                    }
                }, 300);
            }
        }

        // Show browser notification
        this.showBrowserNotification(title, message);
    }

    createNotificationElement(title, message, type, data) {
        const div = document.createElement('div');
        div.className = 'px-4 py-3 transition-all duration-300 border-b border-gray-100 last:border-b-0 hover:bg-blue-50';
        
        const iconConfig = {
            ai_decision: { icon: 'fa-robot', color: 'from-purple-500 to-blue-500', bg: 'bg-gradient-to-r' },
            success: { icon: 'fa-check-circle', color: 'from-green-500 to-emerald-500', bg: 'bg-gradient-to-r' },
            error: { icon: 'fa-exclamation-triangle', color: 'from-red-500 to-orange-500', bg: 'bg-gradient-to-r' },
            info: { icon: 'fa-info-circle', color: 'from-blue-500 to-cyan-500', bg: 'bg-gradient-to-r' }
        };

        const config = iconConfig[type] || iconConfig.info;
        
        div.innerHTML = `
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 ${config.bg} ${config.color} rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas ${config.icon} text-white text-sm"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between mb-1">
                        <h4 class="text-sm font-semibold text-gray-900 truncate">${title}</h4>
                        <span class="text-xs text-gray-400 ml-2 flex-shrink-0">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                    </div>
                    <p class="text-xs text-gray-600 mb-2 leading-relaxed">${message}</p>
                    ${data ? `
                        <div class="flex items-center space-x-2 text-xs flex-wrap gap-1">
                            <span class="font-semibold ${this.getActionColor(data.action)} bg-opacity-10 ${this.getActionBgColor(data.action)} px-2 py-1 rounded-lg border">
                                ${data.symbol} • ${data.action}
                            </span>
                            <span class="font-mono font-bold ${data.confidence > 80 ? 'text-green-600' : (data.confidence > 60 ? 'text-yellow-600' : 'text-red-600')} bg-opacity-10 px-2 py-1 rounded">
                                ${data.confidence}%
                            </span>
                            <span class="font-semibold ${this.getRiskColor(data.risk)} bg-opacity-10 px-2 py-1 rounded">
                                Risk: ${data.risk}
                            </span>
                            <span class="font-mono text-orange-600 bg-orange-50 px-2 py-1 rounded">
                                Score: ${data.score}
                            </span>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
        
        return div;
    }

    getActionColor(action) {
        return action === 'BUY' ? 'text-green-600' : 
               action === 'SELL' ? 'text-red-600' : 'text-yellow-600';
    }

    getActionBgColor(action) {
        return action === 'BUY' ? 'bg-green-100' : 
               action === 'SELL' ? 'bg-red-100' : 'bg-yellow-100';
    }

    getRiskColor(risk) {
        return risk === 'LOW' ? 'text-green-600 bg-green-100' :
               risk === 'MEDIUM' ? 'text-yellow-600 bg-yellow-100' :
               risk === 'HIGH' ? 'text-red-600 bg-red-100' :
               'text-gray-600 bg-gray-100';
    }

    updateBadge() {
        const badge = document.getElementById('notification-badge');
        if (badge) {
            badge.textContent = this.notificationCount;
            badge.classList.remove('hidden');
            badge.classList.add('badge-pop');
            
            setTimeout(() => {
                badge.classList.remove('badge-pop');
            }, 600);
        }
    }

    async refreshAIDecisions() {
        try {
            const response = await fetch('/navbar/ai-decisions');
            const result = await response.json();
            
            if (result.success) {
                this.updateRunningText(result.data);
                this.updateLatestDecisions(result.data);
            }
        } catch (error) {
            console.error('Failed to refresh AI decisions:', error);
        }
    }

    updateRunningText(decisions) {
        const runningText = document.getElementById('ai-running-text');
        if (!runningText || !decisions.length) return;

        const content = decisions.map(decision => 
            `<span class="running-text-item inline-block mr-12">
                <span class="font-bold text-gray-900">${decision.symbol}</span>
                <span class="mx-2">•</span>
                <span class="font-semibold ${this.getActionColor(decision.action)} px-2 py-1 rounded-lg ${this.getActionBgColor(decision.action)} border border-opacity-20">
                    ${decision.action}
                </span>
                <span class="mx-2">•</span>
                <span class="font-mono text-green-600 bg-green-50 px-2 py-1 rounded">
                    ${decision.confidence}%
                </span>
                <span class="mx-2">•</span>
                <span class="font-mono text-blue-600">$${parseFloat(decision.price).toFixed(4)}</span>
                <span class="mx-2">•</span>
                <span class="text-gray-600 italic">"${decision.explanation.substring(0, 45)}"</span>
            </span>`
        ).join('');

        runningText.innerHTML = content + content;
    }

    updateLatestDecisions(decisions) {
        const decisionsList = document.getElementById('latest-decisions-list');
        if (!decisionsList) return;

        if (decisions.length > 0) {
            decisionsList.innerHTML = decisions.map(decision => `
                <div class="px-4 py-3 transition-colors hover:bg-gray-50 cursor-pointer group">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-lg ${this.getActionBgColor(decision.action)} flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow">
                                    <i class="fas ${decision.action === 'BUY' ? 'fa-arrow-up text-green-600' : (decision.action === 'SELL' ? 'fa-arrow-down text-red-600' : 'fa-pause text-yellow-600')} text-xs"></i>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="font-bold text-gray-900 text-sm">${decision.symbol}</span>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full ${this.getActionColor(decision.action)} ${this.getActionBgColor(decision.action)} border">
                                        ${decision.action}
                                    </span>
                                    <span class="font-mono text-xs font-bold ${decision.confidence > 80 ? 'text-green-600' : (decision.confidence > 60 ? 'text-yellow-600' : 'text-red-600')} bg-opacity-10 px-1 rounded">
                                        ${decision.confidence}%
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 truncate" title="${decision.explanation}">
                                    ${decision.explanation}
                                </p>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="font-mono text-xs text-blue-600 font-bold">
                                        $${parseFloat(decision.price).toFixed(4)}
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        ${new Date(decision.decision_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    }

    showBrowserNotification(title, message) {
        if (!('Notification' in window)) return;

        if (Notification.permission === 'granted') {
            new Notification(title, {
                body: message,
                icon: '/favicon.ico',
                silent: true
            });
        } else if (Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    setupEventListeners() {
        // Request notification permission on first interaction
        document.addEventListener('click', () => {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        });
    }

    disconnect() {
        if (this.pusher) {
            this.pusher.disconnect();
        }
    }
}