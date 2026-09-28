<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class ProductMediaUploader
{
    /**
     * Media files written during this request. They are retained so physical files can be
     * explicitly removed if the surrounding database transaction rolls back.
     *
     * @var array<int, Media>
     */
    private array $storedMedia = [];

    /**
     * @param  array<int, UploadedFile>  $galleryImages
     */
    public function store(Product $product, UploadedFile $mainImage, array $galleryImages): void
    {
        $this->begin();

        $this->replaceMain($product, $mainImage);
        $this->addGallery($product, $galleryImages);
    }

    public function begin(): void
    {
        $this->storedMedia = [];
    }

    public function replaceMain(Product $product, UploadedFile $mainImage): void
    {
        $this->storedMedia[] = $this->add($product, $mainImage, 'main_image');
    }

    /**
     * @param  array<int, UploadedFile>  $galleryImages
     */
    public function addGallery(Product $product, array $galleryImages): void
    {
        foreach ($galleryImages as $galleryImage) {
            $this->storedMedia[] = $this->add($product, $galleryImage, 'gallery');
        }
    }

    /**
     * Best-effort cleanup for media whose files may outlive a rolled-back database transaction.
     */
    public function cleanup(): void
    {
        foreach (array_reverse($this->storedMedia) as $media) {
            try {
                $media->delete();
            } catch (Throwable $cleanupException) {
                report($cleanupException);
            }
        }

        $this->storedMedia = [];
    }

    protected function add(Product $product, UploadedFile $image, string $collection): Media
    {
        return $product
            ->addMedia($image)
            ->toMediaCollection($collection, 'public');
    }
}
