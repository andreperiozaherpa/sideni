<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadService
{
    public function __construct(private readonly ImageCompressor $compressor) {}

    /**
     * Store an uploaded file under a module folder with a standardized name.
     *
     * @return array{disk: string, path: string, original_name: string, mime_type: string|null, size: int}
     */
    public function store(UploadedFile $file, string $module, string $disk = 'public', bool $compressImages = false): array
    {
        $directory = 'uploads/'.trim($module, '/');
        $compressed = $compressImages ? $this->compressor->compress($file) : null;

        $extension = $compressed['extension'] ?? ($file->extension() ?: $file->getClientOriginalExtension());
        $filename = Str::uuid()->toString().($extension !== '' ? '.'.$extension : '');
        $path = $directory.'/'.$filename;

        if ($compressed !== null) {
            Storage::disk($disk)->put($path, $compressed['bytes']);
            $size = strlen($compressed['bytes']);
            $mime = $compressed['mime'];
        } else {
            $file->storeAs($directory, $filename, $disk);
            $size = $file->getSize();
            $mime = $file->getMimeType();
        }

        return [
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'size' => $size,
        ];
    }

    /**
     * Remove a previously stored file, ignoring empty references.
     */
    public function delete(?string $disk, ?string $path): void
    {
        if ($disk !== null && $path !== null) {
            Storage::disk($disk)->delete($path);
        }
    }

    /**
     * Move a previously stored file into a module folder with a standardized name.
     *
     * @return array{disk: string, path: string, original_name: string, mime_type: string|null, size: int}
     */
    public function move(string $disk, string $fromPath, string $module, ?string $originalName = null, ?string $mimeType = null, ?int $size = null): array
    {
        $directory = 'uploads/'.trim($module, '/');
        $extension = pathinfo($fromPath, PATHINFO_EXTENSION);
        $toPath = $directory.'/'.Str::uuid()->toString().($extension !== '' ? '.'.$extension : '');

        Storage::disk($disk)->move($fromPath, $toPath);

        return [
            'disk' => $disk,
            'path' => $toPath,
            'original_name' => $originalName ?? basename($fromPath),
            'mime_type' => $mimeType ?? (Storage::disk($disk)->mimeType($toPath) ?: null),
            'size' => $size ?? (Storage::disk($disk)->size($toPath) ?: 0),
        ];
    }
}
