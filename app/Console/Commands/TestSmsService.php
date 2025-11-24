<?php

namespace App\Console\Commands;

use App\Services\MimSmsService;
use App\Services\SmsTemplateService;
use Illuminate\Console\Command;

class TestSmsService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:test {--phone=} {--template=} {--message=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test SMS service functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing SMS Service...');
        
        // Test SMS Template Service
        $this->testTemplateService();
        
        // Test SMS Provider Service
        $this->testSmsProviderService();
        
        $this->info('✅ SMS Service testing completed!');
    }

    /**
     * Test SMS Template Service
     */
    private function testTemplateService(): void
    {
        $this->info('📝 Testing SMS Template Service...');
        
        $templateService = new SmsTemplateService();
        
        // Test template retrieval
        $template = $templateService->getTemplate('booking_confirmation');
        $this->line("📋 Booking confirmation template: {$template}");
        
        // Test template rendering with all variables
        $variables = [
            'booking_code' => 'BK123456',
            'date' => '2025-01-20',
            'time' => '10:00 AM',
            'amount' => '5000',
            'location' => 'Adventure Zone, Dhaka',
            'contact_number' => '+880 1712 345678',
        ];
        
        $renderedMessage = $templateService->renderTemplate('booking_confirmation', $variables);
        $this->line("📱 Rendered message: {$renderedMessage}");
        
        // Test multiple template previews
        $templates = [
            'payment_confirmation',
            'booking_reminder',
            'booking_cancelled',
            'admin_new_booking',
            'welcome_message',
            'password_reset',
            'verification_code',
            'booking_modified',
            'special_offer',
        ];
        
        $this->line('👀 Template previews:');
        foreach ($templates as $templateName) {
            $preview = $templateService->previewTemplate($templateName);
            $this->line("   • {$templateName}: {$preview}");
        }
        
        // Test template validation
        try {
            $templateService->validateTemplate('booking_confirmation', $template);
            $this->line('✅ Template validation passed');
        } catch (\Exception $e) {
            $this->error('❌ Template validation failed: ' . $e->getMessage());
        }
        
        // Test template statistics
        $stats = $templateService->getTemplateStats();
        $this->line('📊 Template statistics:');
        foreach ($stats as $templateName => $stat) {
            $this->line("   • {$templateName}: {$stat['length']} chars, {$stat['variables']} variables");
        }
        
        $this->line('');
    }

    /**
     * Test SMS Provider Service
     */
    private function testSmsProviderService(): void
    {
        $this->info('📱 Testing SMS Provider Service...');
        
        $smsService = new MimSmsService();
        
        // Test configuration
        $isConfigured = $smsService->isConfigured();
        $this->line("⚙️  SMS Service configured: " . ($isConfigured ? 'Yes' : 'No'));
        
        if (!$isConfigured) {
            $this->warn('⚠️  SMS service is not configured. Please set up environment variables.');
            return;
        }
        
        // Test phone number validation
        $testPhones = ['01712345678', '+8801712345678', '1234567890'];
        foreach ($testPhones as $phone) {
            $isValid = $smsService->validatePhoneNumber($phone);
            $this->line("📞 Phone {$phone} valid: " . ($isValid ? 'Yes' : 'No'));
        }
        
                            // Test connection (if configured)
                    try {
                        $isConnected = $smsService->testConnection();
                        $this->line("🔗 SMS API connection: " . ($isConnected ? 'Success' : 'Failed'));
                    } catch (\Exception $e) {
                        $this->warn("⚠️  Connection test failed: " . $e->getMessage());
                    }

                    // Test rate limiting
                    $rateLimitStatus = $smsService->getRateLimitStatus();
                    $this->line("📊 Rate limit status:");
                    $this->line("   • Per minute: {$rateLimitStatus['per_minute']['current']}/{$rateLimitStatus['per_minute']['limit']} (remaining: {$rateLimitStatus['per_minute']['remaining']})");
                    $this->line("   • Per hour: {$rateLimitStatus['per_hour']['current']}/{$rateLimitStatus['per_hour']['limit']} (remaining: {$rateLimitStatus['per_hour']['remaining']})");
                    $this->line("   • Per day: {$rateLimitStatus['per_day']['current']}/{$rateLimitStatus['per_day']['limit']} (remaining: {$rateLimitStatus['per_day']['remaining']})");
        
        // Test sending SMS (if phone provided)
        $phone = $this->option('phone');
        $message = $this->option('message') ?? 'Test SMS from ATV/UTV Adventure Booking System';
        
        if ($phone) {
            $this->info("📤 Testing SMS sending to {$phone}...");
            
            try {
                $response = $smsService->sendWithLogging($phone, $message, [
                    'template_name' => 'test',
                    'priority' => 'normal'
                ]);
                
                if ($response->isSuccess()) {
                    $this->info("✅ SMS sent successfully! Message ID: {$response->messageId}");
                } else {
                    $this->error("❌ SMS sending failed: {$response->errorMessage}");
                }
            } catch (\Exception $e) {
                $this->error("❌ SMS sending exception: " . $e->getMessage());
            }
        } else {
            $this->line('💡 Use --phone option to test actual SMS sending');
        }
        
        $this->line('');
    }
}
