<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatbotQuestion;
use App\Models\ChatbotFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatbotController extends Controller
{
    /**
     * Submit user feedback from the chatbot
     * 
     * POST /api/chatbot/feedback
     * Body: {
     *   "category": "bug|feature|question|complaint|suggestion|other",
     *   "user_name": "John Doe",
     *   "user_email": "john@example.com",
     *   "message": "Feedback message",
     *   "rating": 5,
     *   "question_id": null (optional)
     * }
     */
    public function submitFeedback(Request $request)
    {
        \Log::info('=== CHATBOT FEEDBACK SUBMISSION START ===');
        \Log::info('Request Details', [
            'method' => $request->method(),
            'path' => $request->path(),
            'url' => $request->url(),
            'headers' => $request->headers->all(),
        ]);
        \Log::info('Raw Request Body', $request->all());
        \Log::info('Request Input Keys', array_keys($request->all()));

        $validator = Validator::make($request->all(), [
            'category' => 'required|string',
            'message' => 'required|string|max:1000',
            'user_name' => 'nullable|string|max:255',
            'user_email' => 'nullable|email|max:255',
            'rating' => 'nullable|integer|min:1|max:5',
            'question_id' => 'nullable|exists:chatbot_questions,id',
            'feedback_type' => 'nullable|string',
            'bot_response' => 'nullable|string',
            'user_question' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            \Log::error('=== VALIDATION FAILED ===', [
                'errors' => $validator->errors()->toArray(),
                'error_messages' => $validator->errors()->messages(),
                'failed_fields' => array_keys($validator->errors()->toArray()),
                'input_received' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            \Log::info('Validation passed, creating feedback');

            // Use the message field from frontend
            $feedbackMessage = $request->input('message');

            $feedback = ChatbotFeedback::create([
                'question_id' => $request->question_id,
                'user_question' => $request->input('user_question') ?? 'User Feedback Submission',
                'user_message' => $feedbackMessage,
                'user_name' => $request->user_name,
                'user_email' => $request->user_email,
                'rating' => $request->rating,
                'feedback_type' => $request->input('feedback_type') ?? 'general',
                'category' => $request->category ?? 'other',
                'status' => 'pending',
                'ip_address' => $request->ip(),
                'bot_response' => $request->input('bot_response'),
            ]);

            \Log::info('=== FEEDBACK CREATED SUCCESSFULLY ===', [
                'feedback_id' => $feedback->id,
                'category' => $request->category,
                'user_email' => $request->user_email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your feedback!',
                'data' => [
                    'id' => $feedback->id,
                    'created_at' => $feedback->created_at,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Chatbot feedback submission error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit feedback. Please try again later.'
            ], 500);
        }
    }
}
