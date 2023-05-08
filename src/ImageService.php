<?php

namespace W360\ImageStorage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use W360\ImageStorage\Models\ImageStorage;

/**
 * @class ImageService
 * @author Elbert Tous <elbertjose@hotmail.com>
 * @version 2.2.0
 */
class ImageService
{
    /**
     * get storage disk
     *
     * @param string $storage
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function getDisk(string $storage)
    {
        $driver = config('filesystem.default');
        if (config()->has('filesystem.disks.' . $storage)) {
            $driver = $storage;
        }
        return Storage::disk($driver);
    }

    /**
     * @param UploadedFile $image
     * @param $storage
     * @param \Closure $function
     * @return mixed
     */
    private function upload(UploadedFile $image, $storage, \Closure $function)
    {
        $manager = new ImageManager(['gd']);
        $fileName = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        $img = $manager->make($image->getRealPath());
        $disk = $this->getDisk($storage);
        $disk->put($storage . "/" . $fileName, $img->encode('webp'));
        $this->sizesImages($fileName, $disk, $storage, function ($sizePath, $width, $height, $quality) use ($img, $disk) {
            $image = $img->fit($width, $height)->encode('webp', $quality);
            $disk->put($sizePath, $image);
        });
        return $function($fileName, $storage);
    }

    /**
     * @param UploadedFile $image
     * @param $storage
     * @param $model
     * @return mixed
     */
    public function create(UploadedFile $image, $storage, &$model)
    {
        return $this->upload($image, $storage, function ($fileName, $storage) use ($model) {
            return ImageStorage::firstOrCreate([
                'name' => $fileName,
                'storage' => $storage,
                'author' => (Auth::check() ? Auth::user()->username : null),
                'model_type' => get_class($model),
                'model_id' => $model->id
            ]);
        });
    }

    /**
     * @param UploadedFile $image
     * @param $storage
     * @param $model
     * @return mixed
     */
    public function updateOrCreate(UploadedFile $image, $storage, &$model)
    {
        return $this->upload($image, $storage, function ($fileName, $storage) use ($model) {
            $delete = $this->delete($model);
            if ($delete) {
                return ImageStorage::updateOrCreate([
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'storage' => $storage
                ], [
                    'name' => $fileName,
                    'storage' => $storage,
                    'author' => (Auth::check() ? Auth::user()->username : null),
                    'model_type' => get_class($model),
                    'model_id' => $model->id
                ]);
            }
        });
    }

    /**
     * @param $model
     * @return bool
     */
    public function delete($model)
    {
        if (isset($model->storage) && isset($model->name)) {
            $image = ImageStorage::where('model_id', $model->id)->where('model_type', get_class($model))->first();
            if ($image) {
                $disk = $this->getDisk($image->storage);
                $paths = [];
                $this->sizesImages($image->name, $disk, $image->storage, function ($sizePath) use ($paths) {
                    $paths[] = $sizePath;
                });
                if (!empty($paths))
                    return $disk->delete($paths);
            }
        }
        return true;
    }

    /**
     * @param $imageName
     * @param $disk
     * @param $storage
     * @param \Closure $function
     */
    private function sizesImages($imageName, $disk, $storage, \Closure $function)
    {
        $sizes = config('image-storage.sizes');
        if ($disk->exists($storage . "/" . $imageName)) {
            foreach ($sizes as $sizeName => $widthHeight) {
                list($width, $height) = $widthHeight;
                $quality = config('image-storage.quality');
                $sizePath = $storage . "/" . $sizeName . "/" . $imageName;
                if (isset($quality[$sizeName]) && is_numeric($quality[$sizeName])) {
                    $function($sizePath, $width, $height, $quality[$sizeName]);
                }
            }
        }
    }
}
