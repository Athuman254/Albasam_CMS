#!/bin/bash

# Laravel Queue Worker - Supervisor Configuration Generator
# This script creates the supervisor configuration for automatic SMS sending

PROJECT_PATH="/home/ecobiz/Documents/Software/School-Management-System-CMS"
USER="ecobiz"

echo "Creating Supervisor configuration for Laravel Queue Worker..."

# Create the supervisor config file
sudo tee /etc/supervisor/conf.d/laravel-worker.conf > /dev/null <<EOF
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php ${PROJECT_PATH}/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=${USER}
numprocs=1
redirect_stderr=true
stdout_logfile=${PROJECT_PATH}/storage/logs/worker.log
stopwaitsecs=3600
EOF

echo "✓ Configuration file created at /etc/supervisor/conf.d/laravel-worker.conf"

# Reload supervisor
echo "Reloading Supervisor configuration..."
sudo supervisorctl reread

echo "Updating Supervisor..."
sudo supervisorctl update

echo "Starting Laravel worker..."
sudo supervisorctl start laravel-worker:*

echo ""
echo "✓ Setup complete! Checking status..."
sudo supervisorctl status laravel-worker:*

echo ""
echo "========================================="
echo "Automatic SMS Sending is now ACTIVE!"
echo "========================================="
echo ""
echo "Messages will be sent automatically within seconds of being queued."
echo ""
echo "Useful commands:"
echo "  - Check status:  sudo supervisorctl status laravel-worker:*"
echo "  - View logs:     tail -f ${PROJECT_PATH}/storage/logs/worker.log"
echo "  - Restart:       sudo supervisorctl restart laravel-worker:*"
echo ""
