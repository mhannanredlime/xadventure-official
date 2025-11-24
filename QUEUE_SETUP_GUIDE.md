# Laravel Queue Setup Guide for SMS Notifications

This guide will help you set up Laravel queues on your server to ensure SMS notifications are sent properly after checkout.

## 1. Verify Queue Configuration

Your Laravel application is already configured to use the `database` queue driver. Check your `.env` file:

```env
QUEUE_CONNECTION=database
```

## 2. Create Queue Tables (if not already done)

Run these commands to create the necessary database tables:

```bash
php artisan queue:table
php artisan queue:failed-table
php artisan migrate
```

## 3. Queue Worker Setup Options

### Option A: Using Supervisor (Recommended for Production)

Supervisor is a process manager that will keep your queue worker running and restart it if it crashes.

#### Install Supervisor (Ubuntu/Debian):
```bash
sudo apt-get update
sudo apt-get install supervisor
```

#### Create Supervisor Configuration:
Create a file `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/atvutv/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/atvutv/storage/logs/worker.log
stopwaitsecs=3600
```

**Important:** Replace `/path/to/your/atvutv` with your actual project path.

#### Start Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

#### Check Status:
```bash
sudo supervisorctl status
```

### Option B: Using Systemd (Alternative for Production)

Create a systemd service file `/etc/systemd/system/laravel-queue.service`:

```ini
[Unit]
Description=Laravel Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /path/to/your/atvutv/artisan queue:work --sleep=3 --tries=3
StandardOutput=append:/path/to/your/atvutv/storage/logs/queue.log
StandardError=append:/path/to/your/atvutv/storage/logs/queue.log

[Install]
WantedBy=multi-user.target
```

**Important:** Replace `/path/to/your/atvutv` with your actual project path.

#### Enable and Start:
```bash
sudo systemctl enable laravel-queue
sudo systemctl start laravel-queue
sudo systemctl status laravel-queue
```

### Option C: Manual Queue Worker (For Testing/Development)

For testing purposes, you can run the queue worker manually:

```bash
php artisan queue:work --sleep=3 --tries=3
```

**Note:** This will run in the foreground. Use Ctrl+C to stop it.

## 4. Queue Worker Options Explained

- `--sleep=3`: Wait 3 seconds when no jobs are available
- `--tries=3`: Retry failed jobs up to 3 times
- `--max-time=3600`: Restart worker after 1 hour (prevents memory leaks)
- `--memory=128`: Restart when memory usage exceeds 128MB

## 5. Monitoring Queue Status

### Check Queue Status:
```bash
php artisan queue:work --once
```

### View Failed Jobs:
```bash
php artisan queue:failed
```

### Retry Failed Jobs:
```bash
php artisan queue:retry all
```

### Clear Failed Jobs:
```bash
php artisan queue:flush
```

## 6. Testing SMS Functionality

### Test Queue Processing:
```bash
# Start queue worker
php artisan queue:work --once

# Check logs
tail -f storage/logs/laravel.log
```

### Monitor SMS Logs:
Use the provided monitoring script:
```bash
php monitor_sms_logs.php
```

## 7. Troubleshooting

### Queue Worker Not Processing Jobs:
1. Check if queue worker is running:
   ```bash
   ps aux | grep "queue:work"
   ```

2. Check queue table for pending jobs:
   ```bash
   php artisan tinker
   >>> DB::table('jobs')->count();
   ```

3. Check failed jobs:
   ```bash
   php artisan queue:failed
   ```

### Permission Issues:
Ensure the web server user has write permissions:
```bash
sudo chown -R www-data:www-data /path/to/your/atvutv/storage
sudo chmod -R 775 /path/to/your/atvutv/storage
```

### Memory Issues:
If you experience memory leaks, add memory limits to your queue worker:
```bash
php artisan queue:work --memory=128 --max-time=3600
```

## 8. Production Recommendations

1. **Use Supervisor or Systemd**: Never rely on manual queue workers in production
2. **Monitor Logs**: Set up log rotation and monitoring
3. **Multiple Workers**: Run multiple queue workers for better performance
4. **Database Optimization**: Consider using Redis for better queue performance
5. **Backup Strategy**: Ensure failed jobs are properly handled

## 9. Quick Setup Commands

For a quick setup, run these commands in order:

```bash
# 1. Create queue tables
php artisan queue:table
php artisan queue:failed-table
php artisan migrate

# 2. Test queue worker
php artisan queue:work --once

# 3. Check for any failed jobs
php artisan queue:failed

# 4. If using Supervisor, set it up as described above
```

## 10. Verification

After setup, test the SMS functionality:

1. Complete a test booking on your website
2. Check the logs: `tail -f storage/logs/laravel.log`
3. Verify SMS is sent to the customer
4. Check admin SMS if configured

The SMS functionality should now work properly after checkout!
