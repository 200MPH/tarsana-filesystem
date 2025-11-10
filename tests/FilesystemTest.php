<?php

use Tarsana\Filesystem\Filesystem;
use Tarsana\Filesystem\Adapters\Local;
use Tarsana\Filesystem\Collection;
use Tarsana\Filesystem\Directory;
use Tarsana\Filesystem\File;

/**
 * This uses the directory tests/demo as testing filesystem.
 * The tree of this directory is the following:
 *
 * folder1/
 *     folder11/
 *         some-doc.pdf
 *     some-doc.txt
 *     track.mp3
 * folder2/
 *     track1.mp3
 * folder3/
 * folder4/
 *     folder41/
 *         other.pdf
 *     folder42/
 *         other.mp3
 *     folder43/
 *         picture.jpg
 * files.txt
 *
 */
class FilesystemTest extends PHPUnit\Framework\TestCase
{
    protected $fs;

    public function setUp(): void
    {
        $this->fs = new Filesystem(DEMO_DIR);
    }

    public function test_throws_exception_if_root_directory_not_found(): void
    {
        $this->expectException(\Tarsana\Filesystem\Exceptions\FilesystemException::class);
        $fs = new Filesystem(DEMO_DIR . '/none-present-folder');
    }

    public function test_gets_root_path(): void
    {
        $this->assertEquals(DEMO_DIR . '/', $this->fs->path());
    }

    public function test_gets_the_type_of_path_or_pattern(): void
    {
        $this->assertEquals('file', $this->fs->whatIs('folder1/some-doc.txt'));
        $this->assertEquals('file', $this->fs->whatIs('folder1/*-doc.txt'));
        $this->assertEquals('dir', $this->fs->whatIs('folder1'));
        $this->assertEquals('dir', $this->fs->whatIs('*1'));
        $this->assertEquals('nothing', $this->fs->whatIs('folder1/missing-doc.txt'));
        $this->assertEquals('nothing', $this->fs->whatIs('folder1/*.jpg'));
        $this->assertEquals('collection', $this->fs->whatIs('folder*/*.mp3'));
    }

    public function test_checks_if_file_exists(): void
    {
        $this->assertTrue($this->fs->isFile('folder1/track.mp3'));
        $this->assertTrue($this->fs->isFile(DEMO_DIR . '/folder1/track.mp3'));
        $this->assertFalse($this->fs->isFile('folder1/track.txt'));
    }

    public function test_checks_if_directory_exists(): void
    {
        $this->assertTrue($this->fs->isDir('folder4/folder42'));
        $this->assertFalse($this->fs->isDir('folder5'));
    }

    public function test_checks_if_file_or_directory_exists(): void
    {
        $this->assertTrue($this->fs->isAny('folder1/track.mp3'));
        $this->assertFalse($this->fs->isAny('folder1/track.txt'));
        $this->assertTrue($this->fs->isAny('folder4/folder42'));
        $this->assertFalse($this->fs->isAny('folder5'));
    }

    public function test_checks_if_multiple_files_exist(): void
    {
        $this->assertTrue($this->fs->areFiles([
            'folder1/track.mp3',
            'files.txt',
            'folder4/folder41/other.pdf'
        ]));
        $this->assertFalse($this->fs->areFiles([
            'folder1',
            'files.txt'
        ]));
        $this->assertFalse($this->fs->areFiles([
            'some-missing-file.txt',
            'files.txt'
        ]));
    }

    public function test_checks_if_multiple_directories_exist(): void
    {
        $this->assertTrue($this->fs->areDirs([
            'folder1',
            'folder4/folder41'
        ]));
        $this->assertFalse($this->fs->areDirs([
            'folder1',
            'files.txt'
        ]));
        $this->assertFalse($this->fs->areDirs([
            'folder1',
            'folder5'
        ]));
    }

    public function test_checks_if_multiple_files_or_directories_exist(): void
    {
        $this->assertTrue($this->fs->areAny([
            'folder1',
            'folder4/folder41'
        ]));
        $this->assertTrue($this->fs->areAny([
            'folder1',
            'files.txt'
        ]));
        $this->assertFalse($this->fs->areAny([
            'folder1',
            'folder5'
        ]));
    }

    public function test_gets_or_creates_files_by_name(): void
    {
        $file = $this->fs->file('files.txt');
        $this->assertTrue($file instanceof File);
        $this->assertEquals('files.txt', $file->name());

        $file = $this->fs->file('tmp/file-to-be-created.txt', true);
        $this->assertTrue($file instanceof File);
        $this->assertEquals('file-to-be-created.txt', $file->name());

        $files = $this->fs->files(['folder4/folder43/picture.jpg', 'files.txt']);
        $this->assertTrue($files instanceof Collection);
        $this->assertEquals(2, $files->count());

        $files = $this->fs->files(); // all files under root directory
        $this->assertTrue($files instanceof Collection);
        $this->assertEquals(1, $files->count());

        $this->fs->dir('tmp')->remove();
    }

    public function test_gets_or_creates_directories_by_name(): void
    {
        $dir = $this->fs->dir('folder1');
        $this->assertTrue($dir instanceof Directory);
        $this->assertEquals('folder1', $dir->name());

        $dir = $this->fs->dir('tmp/folder-to-be-created/sub-folder', true);
        $this->assertTrue($dir instanceof Directory);
        $this->assertEquals('sub-folder', $dir->name());
        $this->tearDown(); // removes the tmp folder

        $dirs = $this->fs->dirs(['folder4/folder43', 'folder2']);
        $this->assertTrue($dirs instanceof Collection);
        $this->assertEquals(2, $dirs->count());

        $dirs = $this->fs->dirs(); // all directories under root directory
        $this->assertTrue($dirs instanceof Collection);
        $this->assertEquals(4, $dirs->count());
    }

    public function test_throws_exception_if_file_not_found(): void
    {
        $this->expectException(\Tarsana\Filesystem\Exceptions\FilesystemException::class);
        $this->fs->file('none-present-file.txt');
    }

    public function test_throws_exception_if_directory_not_found(): void
    {
        $this->expectException(\Tarsana\Filesystem\Exceptions\FilesystemException::class);
        $this->fs->dir('none-present-folder');
    }

    public function test_gets_files_or_directories_matching_pattern(): void
    {
        $found = $this->fs->find('f*');
        $this->assertTrue($found instanceof Collection);
        $this->assertEquals(5, $found->count());
        $this->assertEquals(1, $found->files()->count());
        $this->assertEquals(4, $found->dirs()->count());
    }

    public function test_removes_files_and_directories(): void
    {
        $file = $this->fs->file('tmp/file.php', true);
        $this->assertTrue($this->fs->isFile('tmp/file.php'));
        $this->fs->remove('tmp/file.php');
        $this->assertFalse($this->fs->isFile('tmp/file.php'));

        $dir = $this->fs->dir('tmp/dir', true);
        $this->assertTrue($this->fs->isDir('tmp/dir'));
        $this->fs->remove('tmp/dir');
        $this->assertFalse($this->fs->isDir('tmp/file.php'));

        $file = $this->fs->file('tmp/file.php', true);
        $dir = $this->fs->dir('tmp/dir', true);
        $this->fs->removeAll(['tmp/file.php', 'tmp/dir']);
        $this->assertFalse($this->fs->isFile('tmp/file.php'));
        $this->assertFalse($this->fs->isDir('tmp/file.php'));
    }

    public function tearDown(): void
    {
        remove(DEMO_DIR . '/tmp');
    }
}
