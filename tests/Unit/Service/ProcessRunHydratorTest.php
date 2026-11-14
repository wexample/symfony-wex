<?php

declare(strict_types=1);

namespace Wexample\SymfonyWex\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Wexample\SymfonyWex\Entity\ProcessRun;
use Wexample\SymfonyWex\Repository\ProcessRepository;
use Wexample\SymfonyWex\Service\ProcessRunHydrator;

class ProcessRunHydratorTest extends TestCase
{
    private function newRun(): ProcessRun
    {
        return new ProcessRun('/app/.wex/data/process_run/'.Uuid::v4()->toRfc4122().'.json');
    }

    public function testAnEmptyRecordIsAPendingRunOfNothing(): void
    {
        $processes = $this->createMock(ProcessRepository::class);
        $processes->expects($this->never())->method('find');

        $run = (new ProcessRunHydrator($processes))->hydrate($this->newRun(), []);

        $this->assertSame(ProcessRun::STATE_PENDING, $run->getState());
        $this->assertNull($run->getProcess());
        $this->assertNull($run->getItemsTotal());
        $this->assertSame(0, $run->getItemsDone());
        $this->assertSame('', $run->getData());
        $this->assertNull($run->getDateStarted());
    }

    public function testAnInvalidProcessIdentityIsNotLookedUp(): void
    {
        $processes = $this->createMock(ProcessRepository::class);
        $processes->expects($this->never())->method('find');

        $run = (new ProcessRunHydrator($processes))->hydrate($this->newRun(), [
            ProcessRunHydrator::KEY_PROCESS_ID => 'not-a-uuid',
        ]);

        $this->assertNull($run->getProcess());
    }

    public function testValuesComeBackAsTheyWereWritten(): void
    {
        $hydrator = new ProcessRunHydrator($this->createStub(ProcessRepository::class));

        $values = [
            ProcessRunHydrator::KEY_STATE => 'running',
            ProcessRunHydrator::KEY_ITEMS_TOTAL => '3',
            ProcessRunHydrator::KEY_ITEMS_DONE => 1,
            ProcessRunHydrator::KEY_CURRENT => 'src/a.php',
            ProcessRunHydrator::KEY_DATA => ['findings' => 2],
            ProcessRunHydrator::KEY_DATE_STARTED => '2026-11-14T10:00:00+00:00',
        ];

        $run = $hydrator->hydrate($this->newRun(), $values);

        $this->assertSame(3, $run->getItemsTotal());
        $this->assertSame(['findings' => 2], $run->getDataValues());

        $dump = $hydrator->dump($run);

        $this->assertNull($dump[ProcessRunHydrator::KEY_PROCESS_ID]);
        $this->assertSame('running', $dump[ProcessRunHydrator::KEY_STATE]);
        $this->assertSame(3, $dump[ProcessRunHydrator::KEY_ITEMS_TOTAL]);
        $this->assertSame(1, $dump[ProcessRunHydrator::KEY_ITEMS_DONE]);
        $this->assertSame('src/a.php', $dump[ProcessRunHydrator::KEY_CURRENT]);
        $this->assertSame(['findings' => 2], $dump[ProcessRunHydrator::KEY_DATA]);
        $this->assertSame('2026-11-14T10:00:00+00:00', $dump[ProcessRunHydrator::KEY_DATE_STARTED]);
        $this->assertNull($dump[ProcessRunHydrator::KEY_DATE_ENDED]);
    }

    public function testDataWrittenAsTextIsKeptAsIs(): void
    {
        $run = (new ProcessRunHydrator($this->createStub(ProcessRepository::class)))->hydrate($this->newRun(), [
            ProcessRunHydrator::KEY_DATA => '{"a":1}',
        ]);

        $this->assertSame('{"a":1}', $run->getData());
    }
}
