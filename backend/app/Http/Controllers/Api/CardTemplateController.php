<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CardTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CardTemplateController extends Controller
{
    /**
     * Get templates for users based on their plan
     * GET /api/card-templates
     */
    public function index(Request $request)
    {
        $query = CardTemplate::active()->completed()->orderBy('sort_order');

        // Filter by plan type
        if ($request->has('plan')) {
            $plan = $request->plan;
            $query->where(function($q) use ($plan) {
                $q->whereJsonContains('plan_types', $plan)
                  ->orWhereNull('plan_types');
            });
        }

        // Filter by category
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        $templates = $query->get();

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    /**
     * Get single template
     * GET /api/card-templates/{id}
     */
    public function show($id)
    {
        $template = CardTemplate::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $template,
        ]);
    }

    /**
     * Admin: Get all templates (including hidden/inactive)
     * GET /api/admin/card-templates
     */
    public function adminIndex(Request $request)
    {
        $query = CardTemplate::with('creator')->orderBy('sort_order')->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('processing_status', $request->status);
        }

        // Filter by active/hidden
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('is_hidden')) {
            $query->where('is_hidden', $request->boolean('is_hidden'));
        }

        $templates = $query->get();

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    /**
     * Admin: Upload template and send to n8n for processing
     * POST /api/admin/card-templates
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|in:business,personal,creative',
            'plan_types' => 'required|array',
            'plan_types.*' => 'string|in:basic,premium,business', // Free plan doesn't have NFC cards
            'front_image' => 'required|image|max:10240', // 10MB
            'back_image' => 'nullable|image|max:10240',
        ]);

        try {
            // Store original images
            $frontPath = null;
            $backPath = null;

            if ($request->hasFile('front_image')) {
                $frontPath = $request->file('front_image')->store('templates/original', 'public');
            }

            if ($request->hasFile('back_image')) {
                $backPath = $request->file('back_image')->store('templates/original', 'public');
            }

            // Create template record
            $template = CardTemplate::create([
                'name' => $request->name,
                'description' => $request->description,
                'category' => $request->category ?? 'business',
                'plan_types' => $request->plan_types,
                'original_front_url' => $frontPath ? Storage::url($frontPath) : null,
                'original_back_url' => $backPath ? Storage::url($backPath) : null,
                'front_image_url' => $frontPath ? Storage::url($frontPath) : null, // Initially same as original
                'back_image_url' => $backPath ? Storage::url($backPath) : null,
                'processing_status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            // Trigger n8n workflow
            $this->triggerN8nWorkflow($template);

            return response()->json([
                'success' => true,
                'message' => 'Template created and sent for processing',
                'data' => $template,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create template: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create template: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trigger n8n workflow for image processing
     */
    private function triggerN8nWorkflow(CardTemplate $template)
    {
        $n8nWebhookUrl = config('services.n8n.webhook_url');

        if (!$n8nWebhookUrl) {
            Log::warning('n8n webhook URL not configured, skipping processing');
            // Mark as completed immediately if no n8n configured
            $template->update(['processing_status' => 'completed']);
            return;
        }

        try {
            $template->update(['processing_status' => 'processing']);

            // Get full URLs
            $frontUrl = $template->original_front_url ? url($template->original_front_url) : null;
            $backUrl = $template->original_back_url ? url($template->original_back_url) : null;

            /**
             * Send to n8n - 发送到 n8n 的数据
             * 
             * Required fields (必须):
             * - template_id: int - 模板ID，n8n回调时需要
             * - callback_url: string - n8n处理完成后回调的URL
             * 
             * Template info (模板信息):
             * - template_name: string - 模板名称
             * - category: string - 分类 (business/personal/creative)
             * - plan_types: array - 可用的plan类型 ['basic','premium','business']
             * 
             * Images (图片):
             * - front_image_url: string - 前面图片URL
             * - back_image_url: string|null - 背面图片URL (可选)
             */
            $response = Http::timeout(30)->post($n8nWebhookUrl, [
                // Required - n8n回调时需要
                'template_id' => $template->id,
                'callback_url' => url('/api/webhooks/n8n/template-processed'),
                
                // Template info - 模板信息
                'template_name' => $template->name,
                'description' => $template->description,
                'category' => $template->category,
                'plan_types' => $template->plan_types, // ['basic', 'premium', 'business'] 等
                
                // Images - 图片URL
                'front_image_url' => $frontUrl,
                'back_image_url' => $backUrl,
            ]);

            if (!$response->successful()) {
                Log::error("n8n webhook failed: " . $response->body());
                $template->update([
                    'processing_status' => 'failed',
                    'processing_error' => 'Failed to send to n8n: ' . $response->status(),
                ]);
            } else {
                Log::info("n8n workflow triggered for template {$template->id}");
            }

        } catch (\Exception $e) {
            Log::error("n8n webhook error: " . $e->getMessage());
            $template->update([
                'processing_status' => 'failed',
                'processing_error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Webhook: Receive processed images from n8n
     * POST /api/webhooks/n8n/template-processed
     * 
     * ============================================
     * n8n 回调时需要发送的 JSON 数据:
     * ============================================
     * 
     * Required (必须):
     * {
     *   "template_id": 1,                    // 模板ID (从发送时获取)
     *   "status": "completed"                // "completed" 或 "failed"
     * }
     * 
     * On Success (成功时 - 至少需要 front 图片):
     * {
     *   "template_id": 1,
     *   "status": "completed",
     *   
     *   // 方式1: Base64 PNG (推荐)
     *   "processed_front_base64": "data:image/png;base64,iVBORw0KGgo...",
     *   "processed_back_base64": "data:image/png;base64,iVBORw0KGgo...",  // 可选
     *   "thumbnail_base64": "data:image/png;base64,iVBORw0KGgo...",        // 可选-缩略图
     *   
     *   // 方式2: URL (如果图片已经上传到某处)
     *   "processed_front_url": "https://example.com/image.png",
     *   "processed_back_url": "https://example.com/back.png",              // 可选
     *   "thumbnail_url": "https://example.com/thumb.png"                   // 可选
     * }
     * 
     * On Failure (失败时):
     * {
     *   "template_id": 1,
     *   "status": "failed",
     *   "error_message": "处理失败的原因"
     * }
     * 
     * ============================================
     */
    public function n8nWebhook(Request $request)
    {
        Log::info('n8n webhook received', [
            'template_id' => $request->template_id,
            'status' => $request->status,
            'has_front_base64' => $request->has('processed_front_base64'),
            'has_front_url' => $request->has('processed_front_url'),
        ]);

        // Optional: Verify webhook secret
        $webhookSecret = config('services.n8n.webhook_secret');
        if ($webhookSecret && $request->header('X-Webhook-Secret') !== $webhookSecret) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'template_id' => 'required|exists:card_templates,id',
            'status' => 'required|in:completed,failed',
        ]);

        try {
            $template = CardTemplate::findOrFail($request->template_id);

            if ($request->status === 'completed') {
                $updateData = [
                    'processing_status' => 'completed',
                    'processing_error' => null,
                ];

                // Handle processed images (can be URL or base64)
                if ($request->has('thumbnail_url')) {
                    $updateData['thumbnail_url'] = $this->saveImageFromUrl(
                        $request->thumbnail_url,
                        "templates/thumbnails/{$template->id}"
                    );
                }

                if ($request->has('processed_front_url')) {
                    $updateData['front_image_url'] = $this->saveImageFromUrl(
                        $request->processed_front_url,
                        "templates/processed/{$template->id}_front"
                    );
                }

                if ($request->has('processed_back_url')) {
                    $updateData['back_image_url'] = $this->saveImageFromUrl(
                        $request->processed_back_url,
                        "templates/processed/{$template->id}_back"
                    );
                }

                // Handle base64 images
                if ($request->has('thumbnail_base64')) {
                    $updateData['thumbnail_url'] = $this->saveBase64Image(
                        $request->thumbnail_base64,
                        "templates/thumbnails/{$template->id}"
                    );
                }

                if ($request->has('processed_front_base64')) {
                    $updateData['front_image_url'] = $this->saveBase64Image(
                        $request->processed_front_base64,
                        "templates/processed/{$template->id}_front"
                    );
                }

                if ($request->has('processed_back_base64')) {
                    $updateData['back_image_url'] = $this->saveBase64Image(
                        $request->processed_back_base64,
                        "templates/processed/{$template->id}_back"
                    );
                }

                $template->update($updateData);
                Log::info("Template {$template->id} processing completed");

            } else {
                $template->update([
                    'processing_status' => 'failed',
                    'processing_error' => $request->error_message ?? 'Processing failed',
                ]);
                Log::error("Template {$template->id} processing failed");
            }

            return response()->json(['success' => true, 'message' => 'Webhook processed']);

        } catch (\Exception $e) {
            Log::error('Webhook processing error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Save image from URL
     */
    private function saveImageFromUrl($url, $filename)
    {
        try {
            // If it's already a local URL, return as is
            if (Str::startsWith($url, '/storage/')) {
                return $url;
            }

            $response = Http::timeout(60)->get($url);
            if ($response->successful()) {
                $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'png';
                $path = "{$filename}.{$extension}";
                Storage::disk('public')->put($path, $response->body());
                return Storage::url($path);
            }
        } catch (\Exception $e) {
            Log::error("Failed to download image: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Save base64 image
     */
    private function saveBase64Image($base64, $filename)
    {
        try {
            // Remove data URL prefix if present
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
                $extension = $matches[1];
                $base64 = substr($base64, strpos($base64, ',') + 1);
            } else {
                $extension = 'png';
            }

            $imageData = base64_decode($base64);
            $path = "{$filename}.{$extension}";
            Storage::disk('public')->put($path, $imageData);
            return Storage::url($path);

        } catch (\Exception $e) {
            Log::error("Failed to save base64 image: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Admin: Update template
     * PUT /api/admin/card-templates/{id}
     */
    public function update(Request $request, $id)
    {
        $template = CardTemplate::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|in:business,personal,creative',
            'plan_types' => 'nullable|array',
            'plan_types.*' => 'string|in:basic,premium,business', // Free plan doesn't have NFC cards
            'is_active' => 'nullable|boolean',
            'is_hidden' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $template->update($request->only([
            'name', 'description', 'category', 'plan_types', 
            'is_active', 'is_hidden', 'sort_order'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Template updated',
            'data' => $template->fresh(),
        ]);
    }

    /**
     * Admin: Toggle template visibility
     * POST /api/admin/card-templates/{id}/toggle-visibility
     */
    public function toggleVisibility($id)
    {
        $template = CardTemplate::findOrFail($id);
        $template->update(['is_hidden' => !$template->is_hidden]);

        return response()->json([
            'success' => true,
            'message' => $template->is_hidden ? 'Template hidden' : 'Template visible',
            'data' => $template,
        ]);
    }

    /**
     * Admin: Delete template
     * DELETE /api/admin/card-templates/{id}
     */
    public function destroy($id)
    {
        $template = CardTemplate::findOrFail($id);

        // Delete associated files
        $paths = [
            $template->front_image_url,
            $template->back_image_url,
            $template->thumbnail_url,
            $template->original_front_url,
            $template->original_back_url,
        ];

        foreach ($paths as $path) {
            if ($path) {
                $storagePath = str_replace('/storage/', '', $path);
                Storage::disk('public')->delete($storagePath);
            }
        }

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template deleted',
        ]);
    }

    /**
     * Admin: Retry n8n processing
     * POST /api/admin/card-templates/{id}/retry
     */
    public function retryProcessing($id)
    {
        $template = CardTemplate::findOrFail($id);

        if ($template->processing_status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Template already processed',
            ], 400);
        }

        $template->update([
            'processing_status' => 'pending',
            'processing_error' => null,
        ]);

        $this->triggerN8nWorkflow($template);

        return response()->json([
            'success' => true,
            'message' => 'Processing retry initiated',
            'data' => $template->fresh(),
        ]);
    }

    /**
     * Admin: Manually mark as completed (skip n8n)
     * POST /api/admin/card-templates/{id}/mark-completed
     */
    public function markCompleted($id)
    {
        $template = CardTemplate::findOrFail($id);

        $template->update([
            'processing_status' => 'completed',
            'processing_error' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template marked as completed',
            'data' => $template,
        ]);
    }
}
