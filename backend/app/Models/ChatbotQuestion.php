<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatbotQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'keywords',
        'priority',
        'is_active',
        'view_count',
        'helpful_count',
        'not_helpful_count',
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
        'view_count' => 'integer',
        'helpful_count' => 'integer',
        'not_helpful_count' => 'integer',
    ];

    /**
     * Get all feedback for this question
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(ChatbotFeedback::class, 'question_id');
    }

    /**
     * Increment view count
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Mark as helpful
     */
    public function markAsHelpful(): void
    {
        $this->increment('helpful_count');
    }

    /**
     * Mark as not helpful
     */
    public function markAsNotHelpful(): void
    {
        $this->increment('not_helpful_count');
    }

    /**
     * Get helpfulness percentage
     */
    public function getHelpfulnessPercentage(): float
    {
        $total = $this->helpful_count + $this->not_helpful_count;
        
        if ($total === 0) {
            return 0;
        }
        
        return round(($this->helpful_count / $total) * 100, 2);
    }

    /**
     * Scope to only active questions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by priority
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc')->orderBy('created_at', 'desc');
    }

    /**
     * Search questions by keywords
     */
    public static function searchByKeywords(string $searchQuery): ?self
    {
        $searchQuery = strtolower(trim($searchQuery));
        $searchWords = explode(' ', $searchQuery);
        
        $questions = self::active()->byPriority()->get();
        
        $bestMatch = null;
        $highestScore = 0;
        
        foreach ($questions as $question) {
            $score = 0;
            $keywords = array_map('strtolower', $question->keywords);
            
            // Check for exact match in question
            if (str_contains(strtolower($question->question), $searchQuery)) {
                $score += 10;
            }
            
            // Check for keyword matches
            foreach ($keywords as $keyword) {
                if (str_contains($searchQuery, $keyword)) {
                    $score += 5;
                }
                
                foreach ($searchWords as $word) {
                    if (strlen($word) > 2 && str_contains($keyword, $word)) {
                        $score += 2;
                    }
                }
            }
            
            if ($score > $highestScore) {
                $highestScore = $score;
                $bestMatch = $question;
            }
        }
        
        // Only return if we have a reasonable match (score > 2)
        return $highestScore > 2 ? $bestMatch : null;
    }
}
