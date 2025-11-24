<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class SmsLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'phone_number',
        'message',
        'template_name',
        'provider',
        'status',
        'message_id',
        'error_message',
        'sent_at',
        'delivered_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'metadata',
    ];

    /**
     * SMS Status Constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';

    /**
     * Get all available SMS statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_SENT,
            self::STATUS_DELIVERED,
            self::STATUS_FAILED,
        ];
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by provider
     */
    public function scopeByProvider(Builder $query, string $provider): Builder
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope to filter by template
     */
    public function scopeByTemplate(Builder $query, string $template): Builder
    {
        return $query->where('template_name', $template);
    }

    /**
     * Scope to filter by phone number
     */
    public function scopeByPhoneNumber(Builder $query, string $phoneNumber): Builder
    {
        return $query->where('phone_number', $phoneNumber);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange(Builder $query, Carbon $startDate, Carbon $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to get failed SMS
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope to get successful SMS
     */
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_SENT, self::STATUS_DELIVERED]);
    }

    /**
     * Check if SMS is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if SMS is sent
     */
    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    /**
     * Check if SMS is delivered
     */
    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    /**
     * Check if SMS failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Mark SMS as sent
     */
    public function markAsSent(?string $messageId = null): bool
    {
        return $this->update([
            'status' => self::STATUS_SENT,
            'message_id' => $messageId,
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark SMS as delivered
     */
    public function markAsDelivered(): bool
    {
        return $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivered_at' => now(),
        ]);
    }

    /**
     * Mark SMS as failed
     */
    public function markAsFailed(?string $errorMessage = null): bool
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Get delivery time in seconds
     */
    public function getDeliveryTimeInSeconds(): ?int
    {
        if (!$this->sent_at || !$this->delivered_at) {
            return null;
        }

        return $this->sent_at->diffInSeconds($this->delivered_at);
    }

    /**
     * Get metadata value
     */
    public function getMetadata(string $key, $default = null)
    {
        return data_get($this->metadata, $key, $default);
    }

    /**
     * Set metadata value
     */
    public function setMetadata(string $key, $value): bool
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        
        return $this->update(['metadata' => $metadata]);
    }
}
