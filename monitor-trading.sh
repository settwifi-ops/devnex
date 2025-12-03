#!/bin/bash
# monitor-trading.sh

echo "📊 TRADING SYSTEM MONITOR"
echo "=========================="
echo ""

# 1. Supervisor status
echo "1. Supervisor Workers Status:"
sudo supervisorctl status | grep trading
echo ""

# 2. Queue sizes
echo "2. Redis Queue Sizes:"
redis-cli -n 2 llen queues:trading
redis-cli -n 2 llen queues:trading_batch
redis-cli -n 2 llen queues:sync
echo ""

# 3. Process counts
echo "3. Process Counts:"
ps aux | grep "queue:work" | grep -v grep | wc -l
echo ""

# 4. Memory usage
echo "4. Memory Usage:"
free -h
echo ""

# 5. Recent errors
echo "5. Recent Errors (last 10 lines):"
tail -10 /var/www/devnex/storage/logs/trading-worker-error.log 2>/dev/null || echo "No error log found"
