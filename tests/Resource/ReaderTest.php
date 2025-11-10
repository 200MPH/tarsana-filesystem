<?php

use Tarsana\Filesystem\Adapters\Local;
use Tarsana\Filesystem\Resource\Reader;

class ReaderTest extends PHPUnit\Framework\TestCase
{
    protected $reader;

    public function setUp(): void
    {
        $path = DEMO_DIR . '/temp.txt';
        file_put_contents($path, "Hello World !" . PHP_EOL . "How are you ?");
        $this->reader = new Reader($path);
    }

    public function test_fails_if_not_readable(): void
    {
        $this->expectException(\Tarsana\Filesystem\Exceptions\ResourceException::class);
        $writer = new Reader(fopen(DEMO_DIR . '/temp.txt', 'w'));
    }

    public function test_reads_whole_content(): void
    {
        $this->assertEquals(
            "Hello World !" . PHP_EOL . "How are you ?",
            $this->reader->read()
        );
    }

    public function test_reads_one_line(): void
    {
        $this->assertEquals(
            "Hello World !",
            $this->reader->readLine()
        );
    }

    public function test_reads_until_a_character(): void
    {
        $this->assertEquals(
            "Hello World !" . PHP_EOL . "How are ",
            $this->reader->readUntil('y')
        );
    }

    public function test_reads_until_a_word(): void
    {
        $this->assertEquals(
            "Hello World !" . PHP_EOL . "How",
            $this->reader->readUntil(' are')
        );
    }

    public function test_reads_all_if_ending_word_not_found(): void
    {
        $this->assertEquals(
            "Hello World !" . PHP_EOL . "How are you ?",
            $this->reader->readUntil('foo')
        );
    }

    public function test_throws_exception_if_empty_ending_word_given(): void
    {
        $this->expectException(\Tarsana\Filesystem\Exceptions\ResourceException::class);
        $this->reader->readUntil('');
    }

    public function test_reads_part_of_content(): void
    {
        $this->assertEquals(
            "Hello",
            $this->reader->read(5)
        );

        $this->assertEquals(
            " World",
            $this->reader->read(6)
        );

        $this->assertEquals(
            " !" . PHP_EOL . "How are you ?",
            $this->reader->read()
        );
    }

    public function test_non_blocking(): void
    {
        $in = new Reader();
        $in->blocking(false);
        $this->assertEquals("", $in->read());
    }

    public function test_close(): void
    {
        $resource = fopen('php://memory', 'r');
        $in = new Reader($resource);
        $this->assertTrue(is_resource($resource));
        $in->close();
        $this->assertFalse(is_resource($resource));
    }

    public function tearDown(): void
    {
        remove(DEMO_DIR . '/temp.txt');
    }
}
