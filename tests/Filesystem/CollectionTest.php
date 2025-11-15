<?php

use Tarsana\Filesystem\Adapters\Local;
use Tarsana\Filesystem\Collection;
use Tarsana\Filesystem\Directory;
use Tarsana\Filesystem\File;

class CollectionTest extends PHPUnit\Framework\TestCase
{
    protected $collection;

    protected $tempPath;

    public function setUp(): void
    {
        $this->tempPath = DEMO_DIR . '/temp/';
        $this->collection = new Collection([
            new File($this->tempPath . 'file1.txt'),
            new File($this->tempPath . 'file2.txt'),
            new Directory($this->tempPath . 'dir1'),
            new Directory($this->tempPath . 'dir2'),
            new Directory($this->tempPath . 'dir1/dir11'),
            new File($this->tempPath . 'dir1/file11.txt')
        ]);
    }

    public function test_adding_and_counting_elements(): void
    {
        $this->assertEquals(6, $this->collection->count());

        $this->collection->add(new File($this->tempPath . 'file3.txt'));
        $this->assertEquals(7, $this->collection->count());

        // Does not add the same file twice
        $this->collection->add(new File($this->tempPath . 'file1.txt'));
        $this->assertEquals(7, $this->collection->count());
    }

    public function test_contains(): void
    {
        $this->assertTrue($this->collection->contains($this->tempPath . 'file1.txt'));
        $this->assertFalse($this->collection->contains($this->tempPath . 'file.txt'));
    }

    public function test_gets_file_or_directory_by_path(): void
    {
        $file = $this->collection->get($this->tempPath . 'file1.txt');
        $this->assertTrue($file instanceof File);
        $this->assertEquals('file1.txt', $file->name());
    }

    public function test_updates_paths_automatically(): void
    {
        $file = $this->collection->get($this->tempPath . 'file1.txt');
        $this->assertFalse($this->collection->contains($this->tempPath . 'other.txt'));
        $file->name('other.txt', true);
        $this->assertTrue($this->collection->contains($this->tempPath . 'other.txt'));
    }

    public function test_gets_all(): void
    {
        $array = $this->collection->asArray();

        $this->assertTrue(is_array($array));
        $this->assertEquals(6, count($array));
    }

    public function test_gets_files(): void
    {
        $files = $this->collection->files();

        $this->assertTrue($files instanceof Collection);
        $this->assertEquals(3, $files->count());
    }

    public function test_gets_directories(): void
    {
        $dirs = $this->collection->dirs();

        $this->assertTrue($dirs instanceof Collection);
        $this->assertEquals(3, $dirs->count());
    }

    public function test_gets_first_element(): void
    {
        $this->assertEquals(
            $this->tempPath . 'file1.txt',
            $this->collection->first()->path()
        );
    }

    public function test_gets_last_element(): void
    {
        $this->assertEquals(
            $this->tempPath . 'dir1/file11.txt',
            $this->collection->last()->path()
        );
    }

    public function test_gets_array_of_names(): void
    {
        $this->assertEquals(
            ['file1.txt', 'file2.txt', 'dir1', 'dir2', 'dir11', 'file11.txt'],
            $this->collection->names()
        );
    }

    public function test_gets_array_of_paths(): void
    {
        $this->assertEquals(
            [
                $this->tempPath . 'file1.txt',
                $this->tempPath . 'file2.txt',
                $this->tempPath . 'dir1',
                $this->tempPath . 'dir2',
                $this->tempPath . 'dir1/dir11',
                $this->tempPath . 'dir1/file11.txt'
            ],
            $this->collection->paths()
        );
    }

    public function test_removes_element_by_path(): void
    {
        $this->assertTrue($this->collection->contains($this->tempPath . 'dir1'));

        $this->collection->remove($this->tempPath . 'dir1');

        $this->assertEquals(5, $this->collection->count());
        $this->assertFalse($this->collection->contains($this->tempPath . 'dir1'));
    }

    public function tearDown(): void
    {
        remove(DEMO_DIR . '/temp');
    }
}
