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
        'status',
        'category',
        'admin_notes',
        'resolved_by',
        'resolved_at',
        'is_read',
        'ip_address',
        'bot_response',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'rating' => 'integer',
        'resolved_at' => 'datetime',
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
     * Get the admin who resolved this feedback
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Mark feedback as read
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Mark feedback as resolved
     */
    public function markAsResolved(int $userId): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_by' => $userId,
            'resolved_at' => now(),
            'is_read' => true,
        ]);
    }

    /**
     * Update status
     */
    public function updateStatus(string $status, ?int $userId = null): void
    {
        $data = ['status' => $status];

        if ($status === 'resolved' && $userId) {
            $data['resolved_by'] = $userId;
            $data['resolved_at'] = now();
        }

        $this->update($data);
    }

    /**
     * Scope to only unread feedback
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to filter by feedback type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('feedback_type', $type);
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('created_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59'
        ]);
    }
}
