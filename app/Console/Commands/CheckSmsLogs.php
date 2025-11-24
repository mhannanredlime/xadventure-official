<?php

namespace App\Console\Commands;

use App\Models\SmsLog;
use Illuminate\Console\Command;

class CheckSmsLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:logs {--limit=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check recent SMS logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        
        $this->info("📊 Recent SMS Logs (Last {$limit}):");
        $this->line('=====================================');
        
        $logs = SmsLog::latest()->limit($limit)->get();
        
        if ($logs->isEmpty()) {
            $this->warn('No SMS logs found.');
            return 0;
        }
        
        foreach ($logs as $log) {
            $status = match($log->status) {
                'pending' => '⏳',
                'sent' => '✅',
                'delivered' => '📱',
                'failed' => '❌',
                default => '❓'
            };
            
            $this->line("{$status} {$log->phone_number} - {$log->template_name} - {$log->status} - {$log->created_at->format('Y-m-d H:i:s')}");
            
            if ($log->error_message) {
                $this->line("   Error: {$log->error_message}");
            }
        }
        
        $this->line('');
        $this->info('📈 SMS Statistics:');
        $this->line('Total SMS: ' . SmsLog::count());
        $this->line('Sent: ' . SmsLog::where('status', 'sent')->count());
        $this->line('Delivered: ' . SmsLog::where('status', 'delivered')->count());
        $this->line('Failed: ' . SmsLog::where('status', 'failed')->count());
        
        return 0;
    }
}

