<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CardDesign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Admin: business card artwork designer.
 *
 * A design stores the background images plus a JSON layout of elements
 * (text placeholders, images, QR, shapes). Coordinates/sizes are stored in
 * millimetres so that the frontend can render print-accurate PDFs.
 */
class CardDesignController extends Controller
{
    /**
     * GET /api/admin/card-designs
     */
    public function index(Request $request)
    {
        $designs = CardDesign::with('creator:id,first_name,last_name,email')
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $designs,
        ]);
    }

    /**
     * POST /api/admin/card-designs
     */
    public function store(Request $request)
    {
        $data = $this->validatedPayload($request);

        $design = CardDesign::create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Card design saved successfully',
            'data' => $design,
        ], 201);
    }

    /**
     * GET /api/admin/card-designs/{id}
     */
    public function show($id)
    {
        $design = CardDesign::with('creator:id,first_name,last_name,email')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $design,
        ]);
    }

    /**
     * PUT /api/admin/card-designs/{id}
     */
    public function update(Request $request, $id)
    {
        $design = CardDesign::findOrFail($id);
        $data = $this->validatedPayload($request);

        $design->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Card design updated successfully',
            'data' => $design->fresh(),
        ]);
    }

    /**
     * DELETE /api/admin/card-designs/{id}
     */
    public function destroy($id)
    {
        $design = CardDesign::findOrFail($id);

        foreach ([$design->background_front_url, $design->background_back_url] as $url) {
            if ($url && str_contains($url, '/storage/')) {
                $path = substr($url, strpos($url, '/storage/') + strlen('/storage/'));
                Storage::disk('public')->delete($path);
            }
        }

        $design->delete();

        return response()->json([
            'success' => true,
            'message' => 'Card design deleted',
        ]);
    }

    /**
     * POST /api/admin/card-designs/upload-background
     * Upload (or replace) a design background image and return its public URL.
     */
    public function uploadBackground(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
            'side' => 'nullable|in:front,back',
        ]);

        $side = $request->input('side', 'front');
        $file = $request->file('file');

        $filename = 'card-bg-' . $side . '-' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = 'card-designs/' . $filename;

        Storage::disk('public')->makeDirectory('card-designs');
        Storage::disk('public')->put($path, file_get_contents($file));

        return response()->json([
            'success' => true,
            'message' => 'Background uploaded',
            'url' => asset('storage/' . $path),
            'path' => $path,
        ]);
    }

    /**
     * Validate and normalise the create/update payload.
     */
    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'card_size' => 'nullable|string|in:90x54,88.9x50.8',
            'bleed' => 'nullable|boolean',
            'background_front_url' => 'nullable|string|max:2048',
            'background_back_url' => 'nullable|string|max:2048',
            'front_elements' => 'nullable|array',
            'back_elements' => 'nullable|array',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        return [
            'name' => $validated['name'],
            'card_size' => $validated['card_size'] ?? '90x54',
            'bleed' => $request->boolean('bleed'),
            'background_front_url' => $validated['background_front_url'] ?? null,
            'background_back_url' => $validated['background_back_url'] ?? null,
            'front_elements' => $validated['front_elements'] ?? [],
            'back_elements' => $validated['back_elements'] ?? [],
            'notes' => $validated['notes'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
