<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ShortlinkService
{
    /**
     * Generate a shortlink for a booking receipt
     */
    public function generateBookingReceiptLink($reservation): string
    {
        try {
            $baseUrl = $this->getSmsBaseUrl(); // Use the same logic as SMS to prevent localhost URLs
            
            // Option 1: Use booking code (more user-friendly)
            $bookingCode = $reservation->booking_code;
            $link = $baseUrl . '/receipt/' . $bookingCode;
            
            // Option 2: Use shortlink ID (shorter URL)
            // $shortlinkId = $reservation->id;
            // $link = $baseUrl . '/r/' . $shortlinkId;
            
            Log::info('Generated booking receipt link', [
                'reservation_id' => $reservation->id,
                'booking_code' => $bookingCode,
                'link' => $link,
                'base_url_source' => $this->getBaseUrlSource(),
            ]);
            
            return $link;
            
        } catch (\Exception $e) {
            Log::error('Error generating booking receipt link', [
                'reservation_id' => $reservation->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            
            // Fallback to base URL
            return config('app.url');
        }
    }
    
    /**
     * Generate a shortlink using external service (optional)
     */
    public function generateExternalShortlink($longUrl): string
    {
        try {
            // You can integrate with external shortlink services like:
            // - Bitly API
            // - TinyURL API
            // - Google URL Shortener API
            
            // For now, we'll return the original URL
            // In production, you can implement actual shortlink service integration
            
            Log::info('External shortlink generation requested', [
                'original_url' => $longUrl,
            ]);
            
            return $longUrl;
            
        } catch (\Exception $e) {
            Log::error('Error generating external shortlink', [
                'original_url' => $longUrl,
                'error' => $e->getMessage(),
            ]);
            
            return $longUrl;
        }
    }
    
    /**
     * Get the best shortlink for SMS (prioritizes shorter URLs)
     */
    public function getSmsShortlink($reservation): string
    {
        $baseUrl = $this->getSmsBaseUrl();
        
        // For SMS, use the shortest possible link
        $shortlinkId = $reservation->id;
        $link = $baseUrl . '/r/' . $shortlinkId;
        
        Log::info('Generated SMS shortlink', [
            'reservation_id' => $reservation->id,
            'booking_code' => $reservation->booking_code,
            'shortlink' => $link,
            'base_url_source' => $this->getBaseUrlSource(),
        ]);
        
        return $link;
    }

    /**
     * Generate a checkout-level shortlink that shows all reservations from the same checkout
     */
    public function getCheckoutShortlink($customer, $reservations): string
    {
        $baseUrl = $this->getSmsBaseUrl();
        
        // Create a unique checkout ID based on customer and first reservation
        $firstReservation = $reservations[0];
        $checkoutId = 'checkout_' . $customer->id . '_' . $firstReservation->id;
        
        // For SMS, use the shortest possible link
        $link = $baseUrl . '/checkout/' . $checkoutId;
        
        Log::info('Generated checkout shortlink', [
            'customer_id' => $customer->id,
            'reservation_count' => count($reservations),
            'first_reservation_id' => $firstReservation->id,
            'checkout_id' => $checkoutId,
            'shortlink' => $link,
            'base_url_source' => $this->getBaseUrlSource(),
        ]);
        
        return $link;
    }

    /**
     * Get the base URL for SMS links (prevents localhost URLs)
     */
    protected function getSmsBaseUrl(): string
    {
        // Check for SMS-specific URL configuration
        $smsUrl = config('app.sms_url') ?? env('SMS_BASE_URL');
        
        if ($smsUrl && !$this->isLocalhostUrl($smsUrl)) {
            return rtrim($smsUrl, '/');
        }
        
        // Check if current app URL is localhost
        $appUrl = config('app.url');
        if (!$this->isLocalhostUrl($appUrl)) {
            return rtrim($appUrl, '/');
        }
        
        // Fallback to production URL or throw exception
        $productionUrl = config('app.production_url') ?? env('PRODUCTION_URL');
        if ($productionUrl && !$this->isLocalhostUrl($productionUrl)) {
            Log::warning('Using production URL fallback for SMS due to localhost app URL', [
                'app_url' => $appUrl,
                'production_url' => $productionUrl,
            ]);
            return rtrim($productionUrl, '/');
        }
        
        // If no valid URL found, throw exception
        throw new \Exception('Cannot generate SMS links: No valid production URL configured. Please set SMS_BASE_URL or PRODUCTION_URL environment variable.');
    }

    /**
     * Check if URL is localhost
     */
    protected function isLocalhostUrl(string $url): bool
    {
        $parsedUrl = parse_url($url);
        $host = $parsedUrl['host'] ?? '';
        
        return in_array($host, ['localhost', '127.0.0.1', '0.0.0.0']) || 
               str_contains($host, 'localhost') ||
               str_contains($host, '127.0.0.1');
    }

    /**
     * Get the source of the base URL for logging
     */
    protected function getBaseUrlSource(): string
    {
        if (config('app.sms_url') ?? env('SMS_BASE_URL')) {
            return 'sms_url_config';
        }
        
        if (!$this->isLocalhostUrl(config('app.url'))) {
            return 'app_url_config';
        }
        
        if (config('app.production_url') ?? env('PRODUCTION_URL')) {
            return 'production_url_fallback';
        }
        
        return 'unknown';
    }
}
