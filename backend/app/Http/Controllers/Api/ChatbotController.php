<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatbotQuestion;
use App\Models\ChatbotFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /**
     * Get all public active questions (for FAQ list & chatbot suggestions)
     *
     * GET /api/chatbot/questions
     * Query params: limit (default 30), include_inactive (admin-only fallback)
     */
    public function getPublicQuestions(Request $request)
    {
        try {
            $limit = min((int) $request->get('limit', 30), 100);

            $questions = ChatbotQuestion::active()
                ->byPriority()
                ->select('id', 'question', 'answer', 'keywords', 'priority', 'view_count')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $questions,
                'total' => $questions->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Chatbot getPublicQuestions error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to load questions.',
                'data' => [],
            ], 500);
        }
    }

    /**
     * Ask the chatbot a question.
     * Pipeline:
     *   1. Search knowledge base (chatbot_questions) via keyword fuzzy match
     *   2. If match (score > 2), return the pre-built answer
     *   3. If no match, proxy to n8n AI chatbot webhook (if configured)
     *   4. If everything fails, return a helpful fallback message
     *
     * POST /api/chatbot/ask
     * Body: { "question": "How do I reset my password?" }
     */
    public function askQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|max:1000',
            'session_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'found' => false,
                'message' => 'Question is required and must be under 1000 characters.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $question = trim($request->input('question'));
        $sessionId = $request->input('session_id') ?? substr(md5($request->ip() . '|' . now()->toDateString()), 0, 16);

        try {
            // === Stage 1: Knowledge base fuzzy match ===
            $match = ChatbotQuestion::searchByKeywords($question);

            if ($match) {
                try {
                    $match->incrementViewCount();
                } catch (\Throwable) {
                    // Non-fatal - swallow and continue
                }

                return response()->json([
                    'success' => true,
                    'found' => true,
                    'source' => 'knowledge_base',
                    'data' => [
                        'id' => $match->id,
                        'question' => $match->question,
                        'answer' => $match->answer,
                    ],
                ]);
            }

            // === Stage 2: Proxy to n8n AI webhook ===
            $chatbotWebhook = config('services.n8n.chatbot_webhook_url');

            if (!empty($chatbotWebhook) && filter_var($chatbotWebhook, FILTER_VALIDATE_URL)) {
                try {
                    $httpResponse = Http::timeout(20)
                        ->withHeaders([
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                        ])
                        ->post($chatbotWebhook, [
                            'question' => $question,
                            'session_id' => $sessionId,
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'user_id' => $request->user()?->id,
                        ]);

                    if ($httpResponse->successful()) {
                        $payload = $httpResponse->json();
                        $answer = is_array($payload)
                            ? ($payload['answer'] ?? $payload['message'] ?? $payload['response'] ?? null)
                            : null;

                        if (!empty($answer) && is_string($answer)) {
                            return response()->json([
                                'success' => true,
                                'found' => true,
                                'source' => 'n8n_ai',
                                'data' => [
                                    'id' => null,
                                    'question' => $question,
                                    'answer' => $answer,
                                ],
                            ]);
                        }

                        Log::warning('Chatbot n8n returned success but empty answer', [
                            'raw_payload' => $payload,
                            'question' => $question,
                        ]);
                    } else {
                        Log::warning('Chatbot n8n webhook failed', [
                            'status' => $httpResponse->status(),
                            'body' => $httpResponse->body(),
                            'question' => $question,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Chatbot n8n webhook exception: ' . $e->getMessage(), [
                        'question' => $question,
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            // === Stage 3: Graceful fallback ===
            return response()->json([
                'success' => true,
                'found' => false,
                'source' => 'fallback',
                'message' => 'Sorry, I could not find an answer to your question. You can try rephrasing or contact our support team directly using the Contact button on our site.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Chatbot askQuestion fatal error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'question' => $question,
            ]);

            return response()->json([
                'success' => false,
                'found' => false,
                'message' => 'The chatbot is temporarily unavailable. Please try again in a few minutes.',
            ], 503);
        }
    }

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
