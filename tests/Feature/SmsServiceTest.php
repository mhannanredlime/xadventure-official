<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\SmsTemplateService;
use App\Services\MimSmsService;
use App\Models\SmsLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SmsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sms_template_service_can_render_template()
    {
        $templateService = new SmsTemplateService();
        
        $variables = [
            'booking_code' => 'BK123456',
            'date' => '2025-01-20',
            'time' => '10:00 AM',
            'amount' => '5000',
        ];
        
        $rendered = $templateService->renderTemplate('booking_confirmation', $variables);
        
        $this->assertStringContainsString('BK123456', $rendered);
        $this->assertStringContainsString('2025-01-20', $rendered);
        $this->assertStringContainsString('10:00 AM', $rendered);
        $this->assertStringContainsString('5000', $rendered);
    }

    public function test_sms_template_service_can_preview_template()
    {
        $templateService = new SmsTemplateService();
        
        $preview = $templateService->previewTemplate('booking_confirmation');
        
        $this->assertNotEmpty($preview);
        $this->assertStringContainsString('BK', $preview);
        $this->assertStringContainsString('confirmed', $preview);
    }

    public function test_sms_template_service_validation()
    {
        $templateService = new SmsTemplateService();
        
        // Valid template
        $this->assertTrue($templateService->validateTemplate('booking_confirmation', 
            'Your booking #{booking_code} is confirmed for {date} at {time}. Total: {amount} BDT'));
        
        // Invalid template name
        $this->expectException(\Exception::class);
        $templateService->validateTemplate('invalid_template', 'Some content');
    }

    public function test_sms_log_model_can_be_created()
    {
        $smsLog = SmsLog::create([
            'phone_number' => '8801712345678',
            'message' => 'Test SMS message',
            'template_name' => 'booking_confirmation',
            'provider' => 'mim',
            'status' => SmsLog::STATUS_PENDING,
        ]);
        
        $this->assertDatabaseHas('sms_logs', [
            'id' => $smsLog->id,
            'phone_number' => '8801712345678',
            'template_name' => 'booking_confirmation',
            'provider' => 'mim',
            'status' => SmsLog::STATUS_PENDING,
        ]);
    }

    public function test_sms_log_model_status_methods()
    {
        $smsLog = SmsLog::create([
            'phone_number' => '8801712345678',
            'message' => 'Test SMS message',
            'provider' => 'mim',
            'status' => SmsLog::STATUS_PENDING,
        ]);
        
        $this->assertTrue($smsLog->isPending());
        $this->assertFalse($smsLog->isSent());
        $this->assertFalse($smsLog->isDelivered());
        $this->assertFalse($smsLog->isFailed());
    }

    public function test_sms_log_model_can_mark_as_sent()
    {
        $smsLog = SmsLog::create([
            'phone_number' => '8801712345678',
            'message' => 'Test SMS message',
            'provider' => 'mim',
            'status' => SmsLog::STATUS_PENDING,
        ]);
        
        $smsLog->markAsSent('MSG123456');
        
        $this->assertTrue($smsLog->isSent());
        $this->assertEquals('MSG123456', $smsLog->message_id);
        $this->assertNotNull($smsLog->sent_at);
    }

    public function test_sms_log_model_can_mark_as_delivered()
    {
        $smsLog = SmsLog::create([
            'phone_number' => '8801712345678',
            'message' => 'Test SMS message',
            'provider' => 'mim',
            'status' => SmsLog::STATUS_SENT,
            'sent_at' => now(),
        ]);
        
        $smsLog->markAsDelivered();
        
        $this->assertTrue($smsLog->isDelivered());
        $this->assertNotNull($smsLog->delivered_at);
    }

    public function test_sms_log_model_can_mark_as_failed()
    {
        $smsLog = SmsLog::create([
            'phone_number' => '8801712345678',
            'message' => 'Test SMS message',
            'provider' => 'mim',
            'status' => SmsLog::STATUS_PENDING,
        ]);
        
        $smsLog->markAsFailed('Network error');
        
        $this->assertTrue($smsLog->isFailed());
        $this->assertEquals('Network error', $smsLog->error_message);
    }

    public function test_sms_log_model_scopes()
    {
        // Create SMS logs with different statuses
        SmsLog::create([
            'phone_number' => '8801712345678',
            'message' => 'Test SMS 1',
            'provider' => 'mim',
            'status' => SmsLog::STATUS_SENT,
        ]);
        
        SmsLog::create([
            'phone_number' => '8801798765432',
            'message' => 'Test SMS 2',
            'provider' => 'mim',
            'status' => SmsLog::STATUS_FAILED,
        ]);
        
        $this->assertEquals(1, SmsLog::successful()->count());
        $this->assertEquals(1, SmsLog::failed()->count());
        $this->assertEquals(2, SmsLog::byProvider('mim')->count());
    }

    public function test_sms_template_service_get_all_templates()
    {
        $templateService = new SmsTemplateService();
        
        $templates = $templateService->getAllTemplateNames();
        
        $this->assertContains('booking_confirmation', $templates);
        $this->assertContains('payment_confirmation', $templates);
        $this->assertContains('booking_reminder', $templates);
        $this->assertContains('booking_cancelled', $templates);
        $this->assertContains('admin_new_booking', $templates);
    }

    public function test_sms_template_service_get_available_variables()
    {
        $templateService = new SmsTemplateService();
        
        $variables = $templateService->getAvailableVariables('booking_confirmation');
        
        $this->assertContains('booking_code', $variables);
        $this->assertContains('date', $variables);
        $this->assertContains('time', $variables);
        $this->assertContains('amount', $variables);
    }
}
