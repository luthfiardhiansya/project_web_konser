<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * Upload gambar poster event.
     * POST /api/upload-image
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:5120', // max 5MB
        ]);

        $file      = $request->file('image');
        $filename  = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path      = $file->storeAs('posters', $filename, 'public');

        $url = Storage::disk('public')->url($path);

        return response()->json([
            'status'  => true,
            'message' => 'Gambar berhasil diunggah.',
            'url'     => $url,
            'path'    => $path,
        ]);
    }
}
