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
     * Search for answer to user's question
     * 
     * POST /api/chatbot/ask
     * Body: { "question": "What are your business hours?" }
     */
    public function ask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question' => 'required|string|min:3|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userQuestion = $request->question;
        
        // Search for matching Q&A
        $matchedQuestion = ChatbotQuestion::searchByKeywords($userQuestion);

        if ($matchedQuestion) {
            // Increment view count
            $matchedQuestion->incrementViewCount();

            return response()->json([
                'success' => true,
                'found' => true,
                'data' => [
                    'id' => $matchedQuestion->id,
                    'question' => $matchedQuestion->question,
                    'answer' => $matchedQuestion->answer,
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'found' => false,
            'message' => "I couldn't find an answer to your question. Would you like to leave feedback?"
        ]);
    }

    /**
     * Submit feedback
     * 
     * POST /api/chatbot/feedback
     */
    public function submitFeedback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'nullable|exists:chatbot_questions,id',
            'user_question' => 'required|string|max:500',
            'user_message' => 'nullable|string|max:1000',
            'user_name' => 'nullable|string|max:255',
            'user_email' => 'nullable|email|max:255',
            'rating' => 'nullable|integer|min:1|max:5',
            'feedback_type' => 'required|in:not_found,rating,general',
            'category' => 'nullable|string|in:bug,feature,question,complaint,suggestion,other',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $feedback = ChatbotFeedback::create([
            'question_id' => $request->question_id,
            'user_question' => $request->user_question,
            'user_message' => $request->user_message,
            'user_name' => $request->user_name,
            'user_email' => $request->user_email,
            'rating' => $request->rating,
            'feedback_type' => $request->feedback_type,
            'category' => $request->category ?? 'other',
            'status' => 'pending',
            'ip_address' => $request->ip(),
        ]);

        // If it's a rating feedback, update the question stats
        if ($request->question_id && $request->rating) {
            $question = ChatbotQuestion::find($request->question_id);
            if ($question) {
                if ($request->rating >= 4) {
                    $question->markAsHelpful();
                } else {
                    $question->markAsNotHelpful();
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback!',
            'data' => $feedback
        ]);
    }

    /**
     * Get all active questions (for FAQ page)
     * 
     * GET /api/chatbot/questions
     */
    public function getQuestions()
    {
        $questions = ChatbotQuestion::active()
            ->byPriority()
            ->get()
            ->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question' => $question->question,
                    'answer' => $question->answer,
                    'helpfulness' => $question->getHelpfulnessPercentage(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $questions
        ]);
    }
}
