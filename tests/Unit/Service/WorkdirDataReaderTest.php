<?php

declare(strict_types=1);

namespace Wexample\SymfonyWex\Tests\Unit\Service;

use JsonException;
use PHPUnit\Framework\TestCase;
use Wexample\SymfonyWex\Service\WorkdirDataReader;

class WorkdirDataReaderTest extends TestCase
{
    public function testAnEmptyRecordHoldsNoValues(): void
    {
        $this->assertSame([], WorkdirDataReader::decode(''));
        $this->assertSame([], WorkdirDataReader::decode("  \n"));
        $this->assertSame([], WorkdirDataReader::decode('null'));
    }

    public function testAnInvalidRecordIsRefused(): void
    {
        $this->expectException(JsonException::class);

        WorkdirDataReader::decode('{"title":');
    }

    public function testEncodeKeepsSlashesAndAccentsAndIndentsByFour(): void
    {
        $text = WorkdirDataReader::encode(['path' => 'src/a.php', 'title' => 'Étape']);

        $this->assertSame("{\n    \"path\": \"src/a.php\",\n    \"title\": \"Étape\"\n}\n", $text);
    }

    public function testEncodeAndDecodeRoundTrip(): void
    {
        $values = ['name' => 'run', 'items' => ['a', 'b'], 'nested' => ['done' => 2]];

        $this->assertSame($values, WorkdirDataReader::decode(WorkdirDataReader::encode($values)));
    }

    public function testTheAppIsFourLevelsAboveARecord(): void
    {
        $this->assertSame('/srv/app', (new WorkdirDataReader())->appPath('/srv/app/.wex/data/selection/x.json'));
    }
}
