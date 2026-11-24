<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatbotFeedback;
use App\Models\ChatbotQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminChatbotController extends Controller
{
    /**
     * Get all feedback with filtering and search
     * 
     * GET /api/admin/chatbot/feedback
     * Query params: is_read, type, status, category, start_date, end_date, search, per_page
     */
    public function getFeedback(Request $request)
    {
        try {
            $query = ChatbotFeedback::with(['question', 'resolver'])->orderBy('created_at', 'desc');

            // Filter by read status
            if ($request->has('is_read')) {
                $query->where('is_read', $request->boolean('is_read'));
            }

            // Filter by type
            if ($request->has('type') && $request->type !== 'all') {
                $query->where('feedback_type', $request->type);
            }

            // Filter by status
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by category
            if ($request->has('category') && $request->category !== 'all') {
                $query->where('category', $request->category);
            }

            // Filter by date range
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            }

            // Search by user name, email, or message
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('user_name', 'like', "%{$search}%")
                        ->orWhere('user_email', 'like', "%{$search}%")
                        ->orWhere('user_message', 'like', "%{$search}%")
                        ->orWhere('user_question', 'like', "%{$search}%");
                });
            }

            $perPage = $request->get('per_page', 20);
            $feedback = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $feedback->getCollection()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'question_id' => $item->question_id,
                        'question_text' => $item->question ? $item->question->question : null,
                        'user_question' => $item->user_question,
                        'user_message' => $item->user_message,
                        'user_name' => $item->user_name ?? 'Anonymous',
                        'user_email' => $item->user_email ?? 'N/A',
                        'rating' => $item->rating,
                        'feedback_type' => $item->feedback_type,
                        'status' => $item->status ?? 'pending',
                        'category' => $item->category,
                        'bot_response' => $item->bot_response,
                        'admin_notes' => $item->admin_notes,
                        'resolved_by' => $item->resolved_by,
                        'resolver_name' => $item->resolver ? $item->resolver->name : null,
                        'resolved_at' => $item->resolved_at,
                        'is_read' => (bool) $item->is_read,
                        'ip_address' => $item->ip_address,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];
                }),
                'pagination' => [
                    'total' => $feedback->total(),
                    'per_page' => $feedback->perPage(),
                    'current_page' => $feedback->currentPage(),
                    'last_page' => $feedback->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('AdminChatbotController::getFeedback error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch feedback.'
            ], 500);
        }
    }

    /**
     * Get unread feedback count
     * 
     * GET /api/admin/chatbot/feedback/unread-count
     * Returns the count of unread feedback items (is_read = false)
     * This matches the "Unread" statistic shown in Feedback Management
     */
    public function getUnreadCount()
    {
        try {
            // Count feedback that is unread - matches the Feedback Management unread count
            $unreadCount = ChatbotFeedback::where('is_read', false)->count();

            return response()->json([
                'success' => true,
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get unread count.'
            ], 500);
        }
    }

    /**
     * Mark feedback as read
     * 
     * PUT /api/admin/chatbot/feedback/{id}/read
     */
    public function markFeedbackAsRead($id)
    {
        try {
            $feedback = ChatbotFeedback::findOrFail($id);
            $feedback->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Feedback marked as read.',
                'data' => $feedback
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark feedback as read.'
            ], 404);
        }
    }

    /**
     * Update feedback status
     * 
     * PUT /api/admin/chatbot/feedback/{id}/status
     * Body: { "status": "pending|in_progress|resolved" }
     */
    public function updateFeedbackStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,resolved',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $feedback = ChatbotFeedback::findOrFail($id);

            $updateData = [
                'status' => $request->status,
                'is_read' => true
            ];

            if ($request->admin_notes) {
                $updateData['admin_notes'] = $request->admin_notes;
            }

            if ($request->status === 'resolved') {
                $updateData['resolved_by'] = auth()->id();
                $updateData['resolved_at'] = now();
            }

            $feedback->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Feedback status updated.',
                'data' => $feedback
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update feedback status.'
            ], 404);
        }
    }

    /**
     * Delete feedback
     * 
     * DELETE /api/admin/chatbot/feedback/{id}
     */
    public function deleteFeedback($id)
    {
        try {
            $feedback = ChatbotFeedback::findOrFail($id);
            $feedback->delete();

            return response()->json([
                'success' => true,
                'message' => 'Feedback deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete feedback.'
            ], 404);
        }
    }

    /**
     * Get feedback statistics
     * 
     * GET /api/admin/chatbot/feedback/statistics
     */
    public function getStatistics()
    {
        try {
            $totalFeedback = ChatbotFeedback::count();
            $unreadFeedback = ChatbotFeedback::where('is_read', false)->count();
            $pendingFeedback = ChatbotFeedback::where('status', 'pending')->count();

            $categoryBreakdown = ChatbotFeedback::selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray();

            $averageRating = ChatbotFeedback::whereNotNull('rating')->avg('rating');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_feedback' => $totalFeedback,
                    'unread_feedback' => $unreadFeedback,
                    'pending_feedback' => $pendingFeedback,
                    'category_breakdown' => $categoryBreakdown,
                    'average_rating' => round($averageRating, 2),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics.'
            ], 500);
        }
    }

    /**
     * Get all FAQ questions with filtering and search
     *
     * GET /api/admin/chatbot/questions
     * Query params: is_active, search, per_page
     */
    public function index(Request $request)
    {
        try {
            $query = ChatbotQuestion::query();

            // Filter by active status
            if ($request->has('is_active') && $request->is_active !== '' && $request->is_active !== 'all') {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Search by question or answer
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('question', 'like', "%{$search}%")
                        ->orWhere('answer', 'like', "%{$search}%");
                });
            }

            $perPage = $request->get('per_page', 20);
            $questions = $query->orderBy('priority', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $questions->getCollection()->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'question' => $item->question,
                        'answer' => $item->answer,
                        'keywords' => $item->keywords ?? [],
                        'priority' => (int) $item->priority,
                        'is_active' => (bool) $item->is_active,
                        'view_count' => (int) $item->view_count,
                        'helpful_count' => (int) $item->helpful_count,
                        'not_helpful_count' => (int) $item->not_helpful_count,
                        'helpfulness_percentage' => $item->getHelpfulnessPercentage(),
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];
                }),
                'pagination' => [
                    'total' => $questions->total(),
                    'per_page' => $questions->perPage(),
                    'current_page' => $questions->currentPage(),
                    'last_page' => $questions->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('AdminChatbotController::index error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch FAQ questions.'
            ], 500);
        }
    }

    /**
     * Create a new FAQ question
     *
     * POST /api/admin/chatbot/questions
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:5000',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:100',
            'priority' => 'nullable|integer|min:0|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $question = ChatbotQuestion::create([
                'question' => $request->question,
                'answer' => $request->answer,
                'keywords' => $this->normalizeKeywords($request->input('keywords', [])),
                'priority' => $request->input('priority', 0),
                'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FAQ question created successfully.',
                'data' => $question
            ], 201);
        } catch (\Exception $e) {
            \Log::error('AdminChatbotController::store error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create FAQ question.'
            ], 500);
        }
    }

    /**
     * Update an existing FAQ question
     *
     * PUT /api/admin/chatbot/questions/{id}
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:5000',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:100',
            'priority' => 'nullable|integer|min:0|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $question = ChatbotQuestion::findOrFail($id);

            $updateData = [
                'question' => $request->question,
                'answer' => $request->answer,
                'keywords' => $this->normalizeKeywords($request->input('keywords', [])),
                'priority' => $request->input('priority', 0),
            ];

            if ($request->has('is_active')) {
                $updateData['is_active'] = $request->boolean('is_active');
            }

            $question->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'FAQ question updated successfully.',
                'data' => $question
            ]);
        } catch (\Exception $e) {
            \Log::error('AdminChatbotController::update error', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update FAQ question.'
            ], 404);
        }
    }

    /**
     * Delete a FAQ question
     *
     * DELETE /api/admin/chatbot/questions/{id}
     */
    public function destroy($id)
    {
        try {
            $question = ChatbotQuestion::findOrFail($id);
            $question->delete();

            return response()->json([
                'success' => true,
                'message' => 'FAQ question deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete FAQ question.'
            ], 404);
        }
    }

    /**
     * Toggle a FAQ question's active state
     *
     * POST /api/admin/chatbot/questions/{id}/toggle-active
     */
    public function toggleActive($id)
    {
        try {
            $question = ChatbotQuestion::findOrFail($id);
            $question->update(['is_active' => !$question->is_active]);

            return response()->json([
                'success' => true,
                'message' => $question->is_active
                    ? 'FAQ question activated.'
                    : 'FAQ question deactivated.',
                'data' => $question
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle FAQ question state.'
            ], 404);
        }
    }

    /**
     * Normalize submitted keywords to a clean, unique, non-empty list of strings.
     *
     * @param  mixed  $keywords
     * @return array<int, string>
     */
    private function normalizeKeywords($keywords): array
    {
        if (!is_array($keywords)) {
            return [];
        }

        $normalized = [];
        foreach ($keywords as $keyword) {
            if (!is_string($keyword)) {
                continue;
            }
            $keyword = trim($keyword);
            if ($keyword !== '') {
                $normalized[] = $keyword;
            }
        }

        return array_values(array_unique($normalized));
    }
}
