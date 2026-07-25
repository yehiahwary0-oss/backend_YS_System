<?php

namespace App\Domains\System\Services;

use App\Domains\System\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MediaUploadService
{
    private array $allowedMimeTypes;
    private array $blockedExtensions;
    private int   $maxFileSizeKb;

    public function __construct()
    {
        $this->allowedMimeTypes  = config('security.uploads.allowed_mime_types');
        $this->blockedExtensions = config('security.uploads.blocked_extensions');
        $this->maxFileSizeKb     = config('security.uploads.max_file_size', 10240);
    }

    /**
     * Validate, store, and record a file upload.
     *
     * @throws ValidationException
     */
    public function upload(
        UploadedFile $file,
        string       $directory = 'media',
        ?string      $altTextEn = null,
        ?string      $altTextAr = null,
    ): Media {
        $this->validateFile($file);

        $disk     = config('filesystems.default', 'local');
        $filename = $this->generateFilename($file);
        $path     = $directory . '/' . $filename;

        // Store the file
        Storage::disk($disk)->putFileAs($directory, $file, $filename);

        return Media::create([
            'disk'          => $disk,
            'path'          => $path,
            'filename'      => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'size'          => $file->getSize(),
            'alt_text_en'   => $altTextEn,
            'alt_text_ar'   => $altTextAr,
            'uploaded_by'   => Auth::id(),
        ]);
    }

    /**
     * Delete a media file from storage and database.
     */
    public function delete(Media $media): void
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();
    }

    // ── Validation ───────────────────────────────────────────────────

    /**
     * @throws ValidationException
     */
    private function validateFile(UploadedFile $file): void
    {
        // Size check
        $fileSizeKb = $file->getSize() / 1024;
        if ($fileSizeKb > $this->maxFileSizeKb) {
            throw ValidationException::withMessages([
                'file' => ["File size exceeds the maximum allowed size of {$this->maxFileSizeKb} KB."],
            ]);
        }

        // MIME type validation — use server-detected MIME, never trust client
        $detectedMime = $file->getMimeType();
        if (! in_array($detectedMime, $this->allowedMimeTypes, true)) {
            throw ValidationException::withMessages([
                'file' => ["File type '{$detectedMime}' is not allowed."],
            ]);
        }

        // Extension validation — double-check against blocked list
        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, $this->blockedExtensions, true)) {
            throw ValidationException::withMessages([
                'file' => ["Files with '.{$extension}' extension are not allowed."],
            ]);
        }

        // Prevent double extensions: image.php.jpg
        $originalName = $file->getClientOriginalName();
        if (substr_count($originalName, '.') > 1) {
            $parts = explode('.', $originalName);
            // Remove the last extension and check remaining
            array_pop($parts);
            foreach ($parts as $part) {
                if (in_array(strtolower($part), $this->blockedExtensions, true)) {
                    throw ValidationException::withMessages([
                        'file' => ['File name contains a potentially dangerous extension.'],
                    ]);
                }
            }
        }
    }

    /**
     * Generate a randomized filename to prevent path traversal and guessing.
     */
    private function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $random    = Str::uuid()->toString();

        return $random . '.' . strtolower($extension);
    }
}
