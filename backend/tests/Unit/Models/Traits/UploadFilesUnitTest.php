<?php

namespace Tests\Unit\Models\Traits;

use Illuminate\Http\UploadedFile;
use Tests\Stubs\Models\UploadFilesStub;
use Tests\TestCase;

class UploadFilesUnitTest extends TestCase {

    protected $obj;

    protected function setUp(): void {
        parent::setUp();
        $this->obj = new UploadFilesStub();
    }

    public function testRelativeFilePath() {
        $this->assertEquals("1/video.mp4", $this->obj->relativeFilePath("video.mp4"));
    }

    public function testUploadFile() {
        \Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);

        \Storage::assertExists("1/{$file->hashName()}");
    }

    public function testUploadFiles() {
        \Storage::fake();
        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->obj->uploadFiles([$file1, $file2]);

        \Storage::assertExists("1/{$file1->hashName()}");
        \Storage::assertExists("1/{$file2->hashName()}");
    }

    public function testDeleteOldFiles() {
        \Storage::fake();
        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->obj->uploadFiles([$file1, $file2]);
        $this->obj->deleteOldFiles();
        $this->assertCount(2, \Storage::allFiles());

        $this->obj->oldFiles = [$file1->hashName()];
        $this->obj->deleteOldFiles();
        \Storage::assertMissing("1/{$file1->hashName()}");
        \Storage::assertExists("1/{$file2->hashName()}");
    }

    public function testDeleteFile() {
        \Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);
        $fileName = $file->hashName();
        $this->obj->deleteFile($fileName);

        \Storage::assertMissing("1/{$fileName}");

        $file = UploadedFile::fake()->create('video.mp4');
        $this->obj->uploadFile($file);
        $this->obj->deleteFile($file);

        \Storage::assertMissing("1/{$file->hashName()}");
    }

    public function testDeleteFiles() {
        \Storage::fake();
        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->obj->uploadFiles([$file1, $file2]);

        $this->obj->deleteFiles([$file1->hashName(), $file2]);
        \Storage::assertMissing("1/{$file1->hashName()}");
        \Storage::assertMissing("1/{$file2->hashName()}");
    }

    public function testExtractFiles() {
        $attributes = [];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(0, $attributes);
        $this->assertCount(0, $files);

        $attributes = ['file1' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(1, $attributes);
        $this->assertCount(0, $files);

        $attributes = ['file1' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(1, $attributes);
        $this->assertCount(0, $files);
        $this->assertEquals(['file1' => 'test'], $attributes);

        $attributes = ['file1' => 'test', 'file2' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(2, $attributes);
        $this->assertCount(0, $files);
        $this->assertEquals(['file1' => 'test', 'file2' => 'test'], $attributes);

        $file = UploadedFile::fake()->create('video1.mp4');

        $attributes = ['file1' => 'test', 'file2' => $file, 'other' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(3, $attributes);
        $this->assertCount(1, $files);
        $this->assertEquals(['file1' => 'test', 'file2' => $file->hashName(), 'other' => 'test'], $attributes);
        $this->assertEquals([$file], $files);


        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');

        $attributes = ['file1' => $file1, 'file2' => $file2, 'other' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(3, $attributes);
        $this->assertCount(2, $files);
        $this->assertEquals(['file1' => $file1->hashName(), 'file2' => $file2->hashName(), 'other' => 'test'], $attributes);
        $this->assertEquals([$file1, $file2], $files);

    }
}