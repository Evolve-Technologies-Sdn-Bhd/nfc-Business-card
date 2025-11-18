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
        \Log::info('AdminChatbotController::store called', [
            'request_data' => $request->all(),
            'user_id' => auth()->id(),
        ]);

        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'keywords' => 'required|array|min:1',
            'keywords.*' => 'required|string|max:100',
            'priority' => 'nullable|integer|min:0|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            \Log::warning('AdminChatbotController::store validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $question = ChatbotQuestion::create([
                'question' => $request->question,
                'answer' => $request->answer,
                'keywords' => $request->keywords,
                'priority' => $request->priority ?? 0,
                'is_active' => $request->is_active ?? true,
            ]);

            \Log::info('AdminChatbotController::store question created', [
                'question_id' => $question->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Q&A created successfully',
                'data' => $question
            ], 201);
        } catch (\Exception $e) {
            \Log::error('AdminChatbotController::store error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create question: ' . $e->getMessage()
            ], 500);
        }
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
        $query = ChatbotFeedback::with(['question', 'resolver'])->orderBy('created_at', 'desc');

        // Filter by read status
        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->ofType($request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->byStatus($request->status);
        }

        // Filter by category
        if ($request->has('category') && $request->category !== 'all') {
            $query->byCategory($request->category);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Search by user name or email or message
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%")
                  ->orWhere('user_message', 'like', "%{$search}%")
                  ->orWhere('user_question', 'like', "%{$search}%");
            });
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
                'status' => $item->status ?? 'pending',
                'category' => $item->category,
                'admin_notes' => $item->admin_notes,
                'resolved_by' => $item->resolved_by,
                'resolver_name' => $item->resolver ? $item->resolver->name : null,
                'resolved_at' => $item->resolved_at,
                'is_read' => $item->is_read,
                'ip_address' => $item->ip_address,
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
     * Update feedback status
     * 
     * PUT /api/admin/chatbot/feedback/{id}/status
     */
    public function updateFeedbackStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,resolved',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $feedback = ChatbotFeedback::findOrFail($id);
        
        $data = ['status' => $request->status];
        
        if ($request->status === 'resolved') {
            $data['resolved_by'] = auth()->id();
            $data['resolved_at'] = now();
            $data['is_read'] = true;
        }
        
        if ($request->has('admin_notes')) {
            $data['admin_notes'] = $request->admin_notes;
        }
        
        $feedback->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Feedback status updated successfully',
            'data' => $feedback
        ]);
    }

    /**
     * Export feedback to CSV
     * 
     * GET /api/admin/chatbot/feedback/export
     */
    public function exportFeedback(Request $request)
    {
        $query = ChatbotFeedback::with(['question', 'resolver'])->orderBy('created_at', 'desc');

        // Apply same filters as getFeedback
        if ($request->has('status') && $request->status !== 'all') {
            $query->byStatus($request->status);
        }

        if ($request->has('category') && $request->category !== 'all') {
            $query->byCategory($request->category);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        $feedback = $query->get();

        $csvData = [];
        $csvData[] = [
            'ID',
            'Date',
            'User Name',
            'User Email',
            'User Question',
            'User Message',
            'Category',
            'Type',
            'Rating',
            'Status',
            'Resolved By',
            'Resolved At',
            'Admin Notes',
            'IP Address'
        ];

        foreach ($feedback as $item) {
            $csvData[] = [
                $item->id,
                $item->created_at->format('Y-m-d H:i:s'),
                $item->user_name ?? 'Anonymous',
                $item->user_email ?? 'N/A',
                $item->user_question,
                $item->user_message ?? '',
                $item->category ?? 'N/A',
                $item->feedback_type,
                $item->rating ?? 'N/A',
                $item->status ?? 'pending',
                $item->resolver ? $item->resolver->name : 'N/A',
                $item->resolved_at ? $item->resolved_at->format('Y-m-d H:i:s') : 'N/A',
                $item->admin_notes ?? '',
                $item->ip_address ?? 'N/A'
            ];
        }

        $filename = 'chatbot_feedback_' . now()->format('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'r+');
        
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
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
