<?php

require_once 'vendor/autoload.php';

use App\Services\SmsTemplateService;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SMS Template Debug ===\n\n";

try {
    $templateService = new SmsTemplateService();
    
    // Check the booking_confirmation template
    echo "📱 Booking confirmation template:\n";
    $template = $templateService->getTemplate('booking_confirmation');
    echo "   Template: " . $template . "\n\n";
    
    // Test with sample data
    echo "🧪 Testing with sample data:\n";
    $sampleData = $templateService->generateSampleData('booking_confirmation');
    foreach ($sampleData as $key => $value) {
        echo "   {$key}: {$value}\n";
    }
    echo "\n";
    
    // Test rendering
    echo "🔄 Testing template rendering:\n";
    $message = $templateService->renderTemplate('booking_confirmation', $sampleData);
    echo "   Result: " . $message . "\n\n";
    
    // Check if there are any errors
    echo "🔍 Checking for errors:\n";
    try {
        $templateService->validateTemplate('booking_confirmation', $message);
        echo "   ✅ No validation errors\n";
    } catch (Exception $e) {
        echo "   ❌ Validation error: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
