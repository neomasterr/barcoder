<?php

namespace Neomasterr\Barcoder\Tests\Unit;

use InvalidArgumentException;
use Neomasterr\Barcoder\Reader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ReaderTest extends TestCase
{
    private string $tempCli;
    private string $tempImage;

    protected function setUp(): void
    {
        $this->tempCli = sys_get_temp_dir() . '/mock_cli.exe';
        $this->tempImage = sys_get_temp_dir() . '/mock_image.jpg';
        
        file_put_contents($this->tempCli, '');
        file_put_contents($this->tempImage, '');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempCli)) {
            unlink($this->tempCli);
        }

        if (file_exists($this->tempImage)) {
            unlink($this->tempImage);
        }
    }

    public function testConstructorThrowsExceptionIfCliNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Reader('/invalid/path/to/cli.exe');
    }

    public function testReadThrowsExceptionIfImageNotFound(): void
    {
        $reader = new Reader($this->tempCli);
        
        $this->expectException(InvalidArgumentException::class);
        $reader->readAsArray('/invalid/path/to/image.jpg');
    }

    public function testReadAsArrayParsesJsonCorrectly(): void
    {
        $mockJsonResponse = '{
          "sessions": [
            {
              "barcodes": [
                {
                  "data": "MjEwMDAwMDAyNDY3NA==",
                  "length": 13,
                  "text": "2100000024674",
                  "type": "code128"
                },
                {
                  "data": "MQ==",
                  "length": 1,
                  "text": "1",
                  "type": "patch"
                }
              ]
            }
          ]
        }';

        $mockProcess = $this->createStub(Process::class);
        $mockProcess->method('isSuccessful')->willReturn(true);
        $mockProcess->method('getOutput')->willReturn($mockJsonResponse);

        $reader = new class($this->tempCli, $mockProcess) extends Reader {
            private Process $mockProcess;

            public function __construct($cli, $mockProcess) {
                parent::__construct($cli);
                $this->mockProcess = $mockProcess;
            }

            protected function createProcess(array $command): Process {
                return $this->mockProcess;
            }
        };

        $result = $reader->readAsArray($this->tempImage, ['code128', 'patch']);

        $this->assertCount(2, $result);
        
        $this->assertEquals('code128', $result[0]['type']);
        $this->assertEquals('2100000024674', $result[0]['text']);
        $this->assertEquals(13, $result[0]['length']);
        
        $this->assertEquals('patch', $result[1]['type']);
        $this->assertEquals('1', $result[1]['text']);
    }
}
