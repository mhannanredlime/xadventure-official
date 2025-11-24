<?php
/**
 * Queue Setup Script for SMS Notifications
 * 
 * This script helps you set up the Laravel queue system for SMS notifications.
 * Run this script to check your current setup and get guidance.
 */

echo "=== Laravel Queue Setup for SMS Notifications ===\n\n";

// Check if we're in a Laravel project
if (!file_exists('artisan')) {
    echo "❌ Error: This script must be run from the Laravel project root directory.\n";
    echo "Please navigate to your atvutv directory and run this script again.\n";
    exit(1);
}

echo "✅ Laravel project detected.\n\n";

// Check if queue tables exist
echo "Checking queue tables...\n";
try {
    $pdo = new PDO('mysql:host=' . env('DB_HOST', 'localhost') . ';dbname=' . env('DB_DATABASE'), env('DB_USERNAME'), env('DB_PASSWORD'));
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'jobs'");
    $jobsTableExists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'failed_jobs'");
    $failedJobsTableExists = $stmt->rowCount() > 0;
    
    if ($jobsTableExists) {
        echo "✅ Jobs table exists.\n";
    } else {
        echo "❌ Jobs table missing. Run: php artisan queue:table\n";
    }
    
    if ($failedJobsTableExists) {
        echo "✅ Failed jobs table exists.\n";
    } else {
        echo "❌ Failed jobs table missing. Run: php artisan queue:failed-table\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration.\n";
}

echo "\n";

// Check queue configuration
echo "Checking queue configuration...\n";
$queueConnection = env('QUEUE_CONNECTION', 'sync');
echo "Queue connection: " . $queueConnection . "\n";

if ($queueConnection === 'database') {
    echo "✅ Using database queue driver (recommended for production).\n";
} elseif ($queueConnection === 'sync') {
    echo "⚠️  Using sync queue driver (not recommended for production).\n";
    echo "   Change QUEUE_CONNECTION=database in your .env file.\n";
} else {
    echo "ℹ️  Using " . $queueConnection . " queue driver.\n";
}

echo "\n";

// Check if queue worker is running
echo "Checking if queue worker is running...\n";
$output = shell_exec('ps aux | grep "queue:work" | grep -v grep');
if ($output) {
    echo "✅ Queue worker is running.\n";
    echo "Processes found:\n" . $output . "\n";
} else {
    echo "❌ No queue worker is running.\n";
    echo "You need to start a queue worker to process SMS jobs.\n";
}

echo "\n";

// Check for pending jobs
echo "Checking for pending jobs...\n";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM jobs");
    $pendingJobs = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM failed_jobs");
    $failedJobs = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "Pending jobs: " . $pendingJobs . "\n";
    echo "Failed jobs: " . $failedJobs . "\n";
    
    if ($failedJobs > 0) {
        echo "⚠️  You have failed jobs. Run: php artisan queue:failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Could not check job counts: " . $e->getMessage() . "\n";
}

echo "\n";

// Check SMS configuration
echo "Checking SMS configuration...\n";
$smsConfig = config('services.sms');
if ($smsConfig) {
    echo "✅ SMS configuration found.\n";
    
    if (!empty($smsConfig['provider_url'])) {
        echo "✅ SMS provider URL configured.\n";
    } else {
        echo "❌ SMS provider URL not configured.\n";
    }
    
    if (!empty($smsConfig['username'])) {
        echo "✅ SMS username configured.\n";
    } else {
        echo "❌ SMS username not configured.\n";
    }
    
    if (!empty($smsConfig['password'])) {
        echo "✅ SMS password configured.\n";
    } else {
        echo "❌ SMS password not configured.\n";
    }
    
    if (!empty($smsConfig['admin_phone_numbers'])) {
        echo "✅ Admin phone numbers configured.\n";
    } else {
        echo "⚠️  Admin phone numbers not configured (optional).\n";
    }
} else {
    echo "❌ SMS configuration not found.\n";
}

echo "\n";

// Provide setup instructions
echo "=== Setup Instructions ===\n\n";

echo "1. If queue tables are missing, run:\n";
echo "   php artisan queue:table\n";
echo "   php artisan queue:failed-table\n";
echo "   php artisan migrate\n\n";

echo "2. If queue connection is not 'database', update your .env file:\n";
echo "   QUEUE_CONNECTION=database\n\n";

echo "3. Start a queue worker:\n";
echo "   For testing: php artisan queue:work --sleep=3 --tries=3\n";
echo "   For production: Use Supervisor or Systemd (see QUEUE_SETUP_GUIDE.md)\n\n";

echo "4. Test SMS functionality:\n";
echo "   - Complete a test booking on your website\n";
echo "   - Check logs: tail -f storage/logs/laravel.log\n";
echo "   - Monitor SMS: php monitor_sms_logs.php\n\n";

echo "5. For production setup, read QUEUE_SETUP_GUIDE.md for detailed instructions.\n\n";

echo "=== End of Setup Check ===\n";
