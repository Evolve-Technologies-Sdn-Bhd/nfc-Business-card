<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatbotQuestion;
use App\Models\ChatbotFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminChatbotController extends Controller
{
    /**
     * Get all Q&A pairs
     * 
     * GET /api/admin/chatbot/questions
     */
    public function index(Request $request)
    {
        $query = ChatbotQuestion::query();

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        $questions = $query->byPriority()->get()->map(function ($question) {
            return [
                'id' => $question->id,
                'question' => $question->question,
                'answer' => $question->answer,
                'keywords' => $question->keywords,
                'priority' => $question->priority,
                'is_active' => $question->is_active,
                'view_count' => $question->view_count,
                'helpful_count' => $question->helpful_count,
                'not_helpful_count' => $question->not_helpful_count,
                'helpfulness' => $question->getHelpfulnessPercentage(),
                'created_at' => $question->created_at,
                'updated_at' => $question->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $questions
        ]);
    }

    /**
     * Store new Q&A
     * 
     * POST /api/admin/chatbot/questions
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'keywords' => 'required|array|min:1',
            'keywords.*' => 'required|string|max:100',
            'priority' => 'nullable|integer|min:0|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $question = ChatbotQuestion::create([
            'question' => $request->question,
            'answer' => $request->answer,
            'keywords' => $request->keywords,
            'priority' => $request->priority ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Q&A created successfully',
            'data' => $question
        ], 201);
    }

    /**
     * Update existing Q&A
     * 
     * PUT /api/admin/chatbot/questions/{id}
     */
    public function update(Request $request, $id)
    {
        $question = ChatbotQuestion::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'question' => 'sometimes|required|string|max:255',
            'answer' => 'sometimes|required|string',
            'keywords' => 'sometimes|required|array|min:1',
            'keywords.*' => 'required|string|max:100',
            'priority' => 'nullable|integer|min:0|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $question->update($request->only([
            'question',
            'answer',
            'keywords',
            'priority',
            'is_active'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Q&A updated successfully',
            'data' => $question
        ]);
    }

    /**
     * Delete Q&A
     * 
     * DELETE /api/admin/chatbot/questions/{id}
     */
    public function destroy($id)
    {
        $question = ChatbotQuestion::findOrFail($id);
        $question->delete();

        return response()->json([
            'success' => true,
            'message' => 'Q&A deleted successfully'
        ]);
    }

    /**
     * Get all feedback
     * 
     * GET /api/admin/chatbot/feedback
     */
    public function getFeedback(Request $request)
    {
        $query = ChatbotFeedback::with('question')->orderBy('created_at', 'desc');

        // Filter by read status
        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        // Filter by type
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        $feedback = $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'question_id' => $item->question_id,
                'question_text' => $item->question ? $item->question->question : null,
                'user_question' => $item->user_question,
                'user_message' => $item->user_message,
                'user_name' => $item->user_name,
                'user_email' => $item->user_email,
                'rating' => $item->rating,
                'feedback_type' => $item->feedback_type,
                'is_read' => $item->is_read,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $feedback
        ]);
    }

    /**
     * Mark feedback as read
     * 
     * PUT /api/admin/chatbot/feedback/{id}/read
     */
    public function markFeedbackAsRead($id)
    {
        $feedback = ChatbotFeedback::findOrFail($id);
        $feedback->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Feedback marked as read'
        ]);
    }

    /**
     * Delete feedback
     * 
     * DELETE /api/admin/chatbot/feedback/{id}
     */
    public function deleteFeedback($id)
    {
        $feedback = ChatbotFeedback::findOrFail($id);
        $feedback->delete();

        return response()->json([
            'success' => true,
            'message' => 'Feedback deleted successfully'
        ]);
    }

    /**
     * Get analytics
     * 
     * GET /api/admin/chatbot/analytics
     */
    public function getAnalytics()
    {
        $totalQuestions = ChatbotQuestion::count();
        $activeQuestions = ChatbotQuestion::active()->count();
        $totalFeedback = ChatbotFeedback::count();
        $unreadFeedback = ChatbotFeedback::getUnreadCount();
        $averageRating = ChatbotFeedback::getAverageRating();
        
        // Most viewed questions
        $mostViewed = ChatbotQuestion::active()
            ->orderBy('view_count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($q) {
                return [
                    'question' => $q->question,
                    'view_count' => $q->view_count,
                ];
            });

        // Recent feedback
        $recentFeedback = ChatbotFeedback::with('question')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user_question' => $item->user_question,
                    'user_name' => $item->user_name,
                    'rating' => $item->rating,
                    'is_read' => $item->is_read,
                    'created_at' => $item->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'total_questions' => $totalQuestions,
                'active_questions' => $activeQuestions,
                'total_feedback' => $totalFeedback,
                'unread_feedback' => $unreadFeedback,
                'average_rating' => $averageRating,
                'most_viewed_questions' => $mostViewed,
                'recent_feedback' => $recentFeedback,
            ]
        ]);
    }
}
