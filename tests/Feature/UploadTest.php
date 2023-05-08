<?php

namespace W360\ImageStorage\Tests\Feature;


use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use W360\ImageStorage\Facades\ImageST;
use W360\ImageStorage\Models\ImageStorage;
use W360\ImageStorage\Models\User;
use W360\ImageStorage\Tests\TestCase;

class UploadTest extends TestCase
{

    use RefreshDatabase;

    /**
     * get storage disk
     *
     * @param string $storage
     * @return Filesystem
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
     * @test
     */
    public function save_image_in_database(){
        $image = factory(ImageStorage::class)->create();
        $this->assertCount(1, ImageStorage::all(), 'Database Images Is Empty');
    }

    /**
     * @test
     */
    public function upload_and_create_new_image_to_storage(){

       $storage = 'avatars';
       Storage::fake($storage);
       $disk = $this->getDisk($storage);

       $upload = UploadedFile::fake()->image('avatar.jpg');
       $userTest = factory(User::class)->create();
       $image = ImageST::create($upload, $storage, $userTest);


       $photo = $userTest->photo;

       $this->assertEquals($userTest->id, $image->model_id,'No save model id' );
       $this->assertEquals(get_class($userTest), $image->model_type,'No save model type' );
       $this->assertEquals($photo->name, $image->name,'No save image name' );

       $disk->assertExists($photo->storage."/".$photo->name);
       $assetUrl = image($photo->name, $photo->storage);
       $expectedUrl = URL::to('/').Storage::disk($photo->storage)->url($photo->storage."/".$photo->name);
       $this->assertEquals($expectedUrl, $assetUrl,'Url get image not found' );
       $this->assertEquals($expectedUrl, $assetUrl,'Url get image not found' );
       $this->assertTrue(true);

    }

    /**
     * @test
     */
    public function upload_and_update_or_create_image_to_storage(){

        $storage = 'avatars';
        Storage::fake($storage);
        $disk = $this->getDisk($storage);

        $upload = UploadedFile::fake()->image('avatar.jpg');
        $userTest = factory(User::class)->create();
        $image = ImageST::updateOrCreate($upload, $storage, $userTest);

        $photo = $userTest->photo;

        $this->assertEquals($userTest->id, $image->model_id,'No save model id' );
        $this->assertEquals(get_class($userTest), $image->model_type,'No save model type' );
        $this->assertEquals($photo->name, $image->name,'No save image name' );

        $disk->assertExists($photo->storage."/".$photo->name);
        $assetUrl = image($photo->name, $photo->storage);
        $expectedUrl = URL::to('/').Storage::disk($photo->storage)->url($photo->storage."/".$photo->name);
        $this->assertEquals($expectedUrl, $assetUrl,'Url get image not found' );
        $this->assertEquals($expectedUrl, $assetUrl,'Url get image not found' );
        $this->assertTrue(true);

    }


}