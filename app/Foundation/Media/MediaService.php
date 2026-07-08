<?php

declare(strict_types=1);

namespace App\Foundation\Media;

use App\Foundation\Contracts\ServiceInterface;
use Illuminate\Support\Facades\Storage;

class MediaService implements ServiceInterface
{
    private string $disk;

    public function __construct()
    {
        $this->disk = config('filesystems.default', 'public');
    }

    /**
     * Get the configured disk name.
     */
    public function getDiskName(): string
    {
        return $this->disk;
    }

    /**
     * Get public URL for a media path.
     */
    public function getUrl(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Store a file on the storage disk.
     */
    public function storeFile(string $path, string $contents): bool
    {
        return Storage::disk($this->disk)->put($path, $contents);
    }

    /**
     * Get path variant for different image sizes.
     */
    public function getVariantPath(string $originalPath, string $size = 'thumb'): string
    {
        $info = pathinfo($originalPath);
        $dirname = $info['dirname'] === '.' ? '' : $info['dirname'].'/';

        return $dirname.$info['filename'].'_'.$size.'.'.($info['extension'] ?? 'jpg');
    }

    /**
     * Get public URL for a specific image variant.
     */
    public function getVariantUrl(string $originalPath, string $size = 'thumb'): string
    {
        $variantPath = $this->getVariantPath($originalPath, $size);

        return $this->getUrl($variantPath);
    }
}
