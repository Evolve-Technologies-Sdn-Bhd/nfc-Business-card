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
     *
     * Paginated design library. Supports ?search, ?status, ?sort_by and
     * ?sort_order so the designer's library table can filter and page through
     * saved designs without loading every row at once.
     */
    public function index(Request $request)
    {
        $query = CardDesign::with('creator:id,first_name,last_name,email');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->query('status') === 'active');
        }

        $sortBy = $request->query('sort_by', 'updated_at');
        if (! in_array($sortBy, ['updated_at', 'created_at', 'name'], true)) {
            $sortBy = 'updated_at';
        }
        $sortOrder = strtolower((string) $request->query('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min(100, $perPage));

        $designs = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

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
     * GET /api/admin/card-designs/background-media?url=...
     *
     * Streams a stored design image back to the browser. Storage files are
     * served directly by the web server and therefore bypass Laravel's CORS
     * middleware, so the designer cannot read their pixels into a canvas from
     * a different origin (e.g. backend :8000 vs frontend :3001). Routing the
     * bytes through this CORS-enabled API endpoint keeps PNG/PDF export working
     * in every environment.
     */
    public function backgroundMedia(Request $request)
    {
        $request->validate([
            'url' => 'required|string|max:2048',
        ]);

        $url = $request->query('url');

        // Only serve files that live on this application's own host.
        $targetHost = parse_url($url, PHP_URL_HOST);
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        if ($targetHost && $appHost && $targetHost !== $appHost) {
            abort(403, 'Invalid media URL');
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $marker = '/storage/';
        $pos = strpos($path, $marker);
        if ($pos === false) {
            abort(404);
        }

        $relative = ltrim(substr($path, $pos + strlen($marker)), '/');
        if ($relative === '' || str_contains($relative, '..')) {
            abort(404);
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($relative)) {
            abort(404);
        }

        return response()->file($disk->path($relative));
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
