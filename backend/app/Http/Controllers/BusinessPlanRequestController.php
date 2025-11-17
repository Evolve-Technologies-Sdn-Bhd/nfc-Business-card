<?php

namespace App\Http\Controllers;

use App\Mail\BusinessPlanRequestMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BusinessPlanRequestController extends Controller
{
    /**
     * Submit a business plan request
     */
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string|max:1000',
            'quota' => 'required|integer|min:1',
            'contact_person' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();
            $user = auth()->user();

            // Add user information
            $data['user_id'] = $user->id;
            $data['user_name'] = $user->first_name . ' ' . $user->last_name;
            $data['user_email'] = $user->email;
            $data['submitted_at'] = now()->toDateTimeString();

            // Send email to sales team
            Mail::to('genn.chong@clbgroups.com')->send(new BusinessPlanRequestMail($data));

            // Log the request
            Log::info('Business Plan Request Submitted', [
                'user_id' => $user->id,
                'company_name' => $data['company_name'],
                'quota' => $data['quota'],
                'email' => $data['email'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Business plan request submitted successfully. Our team will contact you within 24 hours.',
                'data' => [
                    'company_name' => $data['company_name'],
                    'submitted_at' => $data['submitted_at'],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Business Plan Request Error: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit business plan request. Please try again later.',
            ], 500);
        }
    }
}
