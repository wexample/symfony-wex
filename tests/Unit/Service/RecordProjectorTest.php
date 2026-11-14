<?php

declare(strict_types=1);

namespace Wexample\SymfonyWex\Tests\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Wexample\SymfonyWex\Entity\ProcessRun;
use Wexample\SymfonyWex\Repository\ProcessRepository;
use Wexample\SymfonyWex\Service\ProcessRunHydrator;
use Wexample\SymfonyWex\Service\ProcessRunRecordProjector;
use Wexample\SymfonyWex\Service\WorkdirDataReader;

class RecordProjectorTest extends TestCase
{
    public function testARowIsRebuiltFromItsFileAndWritten(): void
    {
        $dir = sys_get_temp_dir().'/'.uniqid('projector', true);
        mkdir($dir);
        $path = $dir.'/'.Uuid::v4()->toRfc4122().'.json';
        file_put_contents($path, WorkdirDataReader::encode([ProcessRunHydrator::KEY_STATE => 'done']));

        $run = new ProcessRun($path);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist')->with($run);
        $entityManager->expects($this->once())->method('flush');

        try {
            (new ProcessRunRecordProjector(
                new WorkdirDataReader(),
                new ProcessRunHydrator($this->createStub(ProcessRepository::class)),
                $entityManager,
            ))->projectOne($run);
        } finally {
            unlink($path);
            rmdir($dir);
        }

        $this->assertSame('done', $run->getState());
    }
}
