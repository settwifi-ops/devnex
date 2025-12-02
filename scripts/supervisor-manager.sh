#!/bin/bash

PROJECT_PATH="/var/www/devnex"
SUPERVISOR_CONF_DIR="/etc/supervisor/conf.d"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}DevNex Supervisor Manager${NC}"
echo "================================"

case "$1" in
    start)
        echo "Starting all DevNex services..."
        sudo supervisorctl start all
        ;;
    stop)
        echo "Stopping all DevNex services..."
        sudo supervisorctl stop all
        ;;
    restart)
        echo "Restarting all DevNex services..."
        sudo supervisorctl restart all
        ;;
    status)
        echo "Checking status..."
        sudo supervisorctl status | grep devnex
        ;;
    logs)
        echo "Showing logs..."
        case "$2" in
            scheduler)
                tail -f $PROJECT_PATH/storage/logs/supervisor/scheduler.log
                ;;
            worker)
                tail -f $PROJECT_PATH/storage/logs/supervisor/worker-default.log
                ;;
            all)
                tail -f $PROJECT_PATH/storage/logs/supervisor/*.log
                ;;
            *)
                echo "Usage: $0 logs [scheduler|worker|all]"
                ;;
        esac
        ;;
    reload)
        echo "Reloading supervisor configuration..."
        sudo supervisorctl reread
        sudo supervisorctl update
        ;;
    install)
        echo "Installing supervisor configurations..."
        
        # Scheduler config
        sudo tee $SUPERVISOR_CONF_DIR/devnex-scheduler.conf > /dev/null << 'EOF'
[program:devnex-scheduler]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/devnex/artisan schedule:work
directory=/var/www/devnex
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=devnex
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/devnex/storage/logs/supervisor/scheduler.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=60
environment=APP_ENV="production"
EOF
        
        # Worker config
        sudo tee $SUPERVISOR_CONF_DIR/devnex-worker.conf > /dev/null << 'EOF'
[program:devnex-worker-default]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/devnex/artisan queue:work redis --queue=default --sleep=3 --tries=3 --max-time=3600
directory=/var/www/devnex
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=devnex
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/devnex/storage/logs/supervisor/worker-default.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=60
environment=APP_ENV="production"

[program:devnex-worker-high]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/devnex/artisan queue:work redis --queue=high --sleep=1 --tries=1 --max-time=1800
directory=/var/www/devnex
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=devnex
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/devnex/storage/logs/supervisor/worker-high.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=60
environment=APP_ENV="production"
EOF
        
        echo "Configuration installed. Run '$0 reload' to apply."
        ;;
    *)
        echo "Usage: $0 {start|stop|restart|status|logs|reload|install}"
        echo ""
        echo "Commands:"
        echo "  start     - Start all services"
        echo "  stop      - Stop all services"
        echo "  restart   - Restart all services"
        echo "  status    - Check service status"
        echo "  logs      - View logs (scheduler|worker|all)"
        echo "  reload    - Reload configuration"
        echo "  install   - Install configuration files"
        exit 1
        ;;
esac
