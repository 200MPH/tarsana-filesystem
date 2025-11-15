<?php

use Tarsana\Filesystem\Adapters\Local;
use Tarsana\Filesystem\Resource\Writer;

class WriterTest extends PHPUnit\Framework\TestCase
{
    protected $writer;

    protected $path;

    public function setUp(): void
    {
        $this->path = DEMO_DIR . '/temp.txt';
        file_put_contents($this->path, "");
        $this->writer = new Writer($this->path);
    }

    public function test_fails_if_not_writable(): void
    {
        $this->expectException(\Tarsana\Filesystem\Exceptions\ResourceException::class);
        $writer = new Writer(fopen('php://memory', 'r'));
    }

    public function test_constructor(): void
    {
        $out = new Writer();
        $this->assertTrue($out instanceof Writer);
    }

    public function test_close(): void
    {
        $resource = fopen('php://memory', 'w');
        $out = new Writer($resource);
        $this->assertTrue(is_resource($resource));
        $out->close();
        $this->assertFalse(is_resource($resource));
    }

    public function test_writes_content(): void
    {
        $this->writer->write("Hello");
        $this->assertEquals("Hello", file_get_contents($this->path));

        $this->writer->writeLine(" World");
        $this->assertEquals("Hello World" . PHP_EOL, file_get_contents($this->path));
    }

    public function tearDown(): void
    {
        remove(DEMO_DIR . '/temp.txt');
    }
}
