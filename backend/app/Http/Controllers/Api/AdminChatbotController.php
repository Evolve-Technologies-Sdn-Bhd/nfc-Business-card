<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatbotFeedback;
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
}
