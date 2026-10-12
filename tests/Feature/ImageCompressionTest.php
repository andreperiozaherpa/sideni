<?php

use App\Models\TemporaryUpload;
use App\Models\User;
use App\Support\ImageCompressor;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

/**
 * Build a noisy JPEG on disk that is large enough to require compression.
 */
function makeSideniNoisyJpeg(int $width, int $height): string
{
    $image = imagecreatetruecolor($width, $height);

    for ($x = 0; $x < $width; $x += 4) {
        for ($y = 0; $y < $height; $y += 4) {
            $color = imagecolorallocate($image, random_int(0, 255), random_int(0, 255), random_int(0, 255));
            imagefilledrectangle($image, $x, $y, $x + 3, $y + 3, $color);
        }
    }

    $path = tempnam(sys_get_temp_dir(), 'sideni').'.jpg';
    imagejpeg($image, $path, 100);
    imagedestroy($image);

    return $path;
}

test('the image compressor brings a large photo under 500 KB', function () {
    $path = makeSideniNoisyJpeg(2600, 1800);
    expect(filesize($path))->toBeGreaterThan(ImageCompressor::MAX_BYTES);

    $result = app(ImageCompressor::class)->compress(new UploadedFile($path, 'besar.jpg', 'image/jpeg', null, true));

    expect($result)->not->toBeNull();
    expect($result['mime'])->toBe('image/jpeg');
    expect(strlen($result['bytes']))->toBeLessThanOrEqual(ImageCompressor::MAX_BYTES);

    @unlink($path);
});

test('the temporary upload endpoint stores a compressed image', function () {
    Storage::fake('local');
    $user = User::factory()->create();

    $path = makeSideniNoisyJpeg(2600, 1800);
    $file = new UploadedFile($path, 'bukti.jpg', 'image/jpeg', null, true);

    $response = $this->actingAs($user)->post(
        route('uploads.temporary.store'),
        ['file' => $file],
        ['Accept' => 'application/json'],
    );

    $response->assertOk()->assertJsonStructure(['token', 'name', 'size']);
    expect($response->json('size'))->toBeLessThanOrEqual(ImageCompressor::MAX_BYTES);

    $upload = TemporaryUpload::firstOrFail();
    Storage::disk('local')->assertExists($upload->path);
    expect($upload->size)->toBeLessThanOrEqual(ImageCompressor::MAX_BYTES);
    expect(Storage::disk('local')->size($upload->path))->toBeLessThanOrEqual(ImageCompressor::MAX_BYTES);

    @unlink($path);
});

test('non-image files are stored without compression', function () {
    Storage::fake('local');
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(
        route('uploads.temporary.store'),
        ['file' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf')],
        ['Accept' => 'application/json'],
    );

    $response->assertOk();
    $upload = TemporaryUpload::firstOrFail();
    expect($upload->mime_type)->toBe('application/pdf');
});
