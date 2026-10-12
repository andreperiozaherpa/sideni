<?php

namespace App\Http\Controllers;

use App\Models\TemporaryUpload;
use App\Support\UploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TemporaryUploadController extends Controller
{
    public function __construct(private readonly UploadService $uploads) {}

    /**
     * Store an asynchronous upload from Dropzone and return its token.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:20480'],
        ]);

        $stored = $this->uploads->store($request->file('file'), 'tmp', 'local', true);

        $upload = $request->user()->temporaryUploads()->create([
            'token' => (string) Str::uuid(),
            'disk' => $stored['disk'],
            'path' => $stored['path'],
            'original_name' => $stored['original_name'],
            'mime_type' => $stored['mime_type'],
            'size' => $stored['size'],
        ]);

        return response()->json([
            'token' => $upload->token,
            'name' => $upload->original_name,
            'size' => $upload->size,
        ]);
    }

    /**
     * Discard a temporary upload the user removed before submitting.
     */
    public function destroy(TemporaryUpload $temporaryUpload): JsonResponse
    {
        abort_unless($temporaryUpload->user_id === auth()->id(), 403);

        $this->uploads->delete($temporaryUpload->disk, $temporaryUpload->path);
        $temporaryUpload->delete();

        return response()->json(['deleted' => true]);
    }
}
