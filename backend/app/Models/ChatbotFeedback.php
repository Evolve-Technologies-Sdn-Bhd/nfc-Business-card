<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotFeedback extends Model
{
    use HasFactory;

    protected $table = 'chatbot_feedback';

    protected $fillable = [
        'question_id',
        'user_question',
        'user_message',
        'user_name',
        'user_email',
        'rating',
        'feedback_type',
        'is_read',
        'ip_address',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the question this feedback belongs to
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(ChatbotQuestion::class, 'question_id');
    }

    /**
     * Mark feedback as read
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Scope to only unread feedback
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to only read feedback
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('feedback_type', $type);
    }

    /**
     * Get count of unread feedback
     */
    public static function getUnreadCount(): int
    {
        return self::unread()->count();
    }

    /**
     * Get average rating
     */
    public static function getAverageRating(): float
    {
        return round(self::whereNotNull('rating')->avg('rating') ?? 0, 2);
    }
}
