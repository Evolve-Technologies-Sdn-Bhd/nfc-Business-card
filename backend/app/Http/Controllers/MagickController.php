<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MagickController extends Controller
{
    public function replace(Request $request)
    {
        // Validate input
        $request->validate([
            'image'  => 'required|file|image',
            'fields' => 'required|array',
        ]);

        // Store original file temporarily
        $uploadedPath = $request->file('image')->store('temp');
        $inputPath = Storage::path($uploadedPath);
        $outputPath = Storage::path('temp/output_' . time() . '.png');

        // Start with original image
        $currentImage = $inputPath;

        foreach ($request->fields as $field) {

            // Each field input
            $text = $field['text'] ?? '';
            $x = $field['x'] ?? 0;
            $y = $field['y'] ?? 0;
            $size = $field['size'] ?? 40;
            $color = $field['color'] ?? 'black';
            $fontPath = $field['font'] ?? null;

            // If custom font not provided → use system default
            $fontCmd = $fontPath ? "-font '$fontPath'" : "";

            // Prepare ImageMagick command
            $cmd = "magick \"$currentImage\" -fill \"$color\" -pointsize $size $fontCmd -annotate +$x+$y \"$text\" \"$outputPath\"";

            shell_exec($cmd);

            // Update current image for next field
            $currentImage = $outputPath;
        }

        // Return final output
        return response()->file($outputPath, [
            'Content-Type' => 'image/png'
        ]);
    }
}
