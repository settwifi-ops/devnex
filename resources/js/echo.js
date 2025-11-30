import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    withCredentials: true, // PENTING!
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }
});


// Listen untuk notifikasi user
const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');

if (userId) {
    // Notifikasi khusus user
    window.Echo.private(`notifications.user.${userId}`)
        .listen('.user.notification', (e) => {
            console.log('User notification received:', e);
            showBrowserNotification(e.title, e.message, e.icon);
            showNavbarNotification(e);
        });

    // Trading events untuk user
    window.Echo.private(`trading.user.${userId}`)
        .listen('.trading.executed', (e) => {
            console.log('Trading event received:', e);
            showTradingNotification(e);
        });
}

// Notifikasi global (opsional)
window.Echo.channel('notifications.global')
    .listen('.user.notification', (e) => {
        console.log('Global notification:', e);
        showBrowserNotification(e.title, e.message, e.icon);
    });

// Fungsi untuk show browser notification
function showBrowserNotification(title, message, icon = 'ℹ️') {
    // Check jika browser support notifications
    if (!("Notification" in window)) {
        console.log("Browser tidak support notifications");
        return;
    }

    // Check jika sudah diizinkan
    if (Notification.permission === "granted") {
        createNotification(title, message, icon);
    } else if (Notification.permission !== "denied") {
        Notification.requestPermission().then(permission => {
            if (permission === "granted") {
                createNotification(title, message, icon);
            }
        });
    }
}

function createNotification(title, message, icon) {
    const notification = new Notification(`${icon} ${title}`, {
        body: message,
        icon: '/favicon.ico', // Ganti dengan icon aplikasi Anda
        badge: '/favicon.ico'
    });

    // Tutup notification setelah 5 detik
    setTimeout(() => {
        notification.close();
    }, 5000);

    // Click notification untuk focus window
    notification.onclick = function() {
        window.focus();
        notification.close();
    };
}

// Fungsi untuk show notification di navbar
function showNavbarNotification(data) {
    // Cari atau buat container notifikasi
    let notificationContainer = document.getElementById('notification-container');
    
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'notification-container';
        notificationContainer.className = 'notification-container';
        document.body.appendChild(notificationContainer);
    }

    // Buat element notifikasi
    const notification = document.createElement('div');
    notification.className = `alert alert-${getAlertType(data.type)} alert-dismissible fade show`;
    notification.innerHTML = `
        <strong>${data.icon} ${data.title}</strong>
        <br>${data.message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        <small class="d-block text-muted">${new Date().toLocaleTimeString()}</small>
    `;

    // Tambahkan ke container
    notificationContainer.appendChild(notification);

    // Auto remove setelah 5 detik
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function showTradingNotification(data) {
    // Notifikasi khusus trading dengan style berbeda
    let tradingContainer = document.getElementById('trading-notifications');
    
    if (!tradingContainer) {
        tradingContainer = document.createElement('div');
        tradingContainer.id = 'trading-notifications';
        tradingContainer.className = 'trading-notifications';
        document.body.appendChild(tradingContainer);
    }

    const notification = document.createElement('div');
    notification.className = `trading-alert trading-${data.color}`;
    notification.innerHTML = `
        <div class="trading-icon">${data.icon}</div>
        <div class="trading-content">
            <strong>${data.symbol} ${data.action}</strong>
            <div>${data.message}</div>
            <small>${new Date().toLocaleTimeString()}</small>
        </div>
        <button class="trading-close">&times;</button>
    `;

    tradingContainer.appendChild(notification);

    // Close button
    notification.querySelector('.trading-close').onclick = () => notification.remove();

    // Auto remove
    setTimeout(() => notification.remove(), 5000);
}

function getAlertType(type) {
    const typeMap = {
        'buy': 'success',
        'sell': 'danger', 
        'close': 'warning',
        'stop_loss': 'danger',
        'take_profit': 'success',
        'error': 'danger',
        'info': 'info'
    };
    return typeMap[type] || 'info';
}