<?php

namespace App\Tests\Domain\Competition\Championship\BuildChampionshipApi;

use App\Console\Progress;
use App\Domain\Competition\Championship\BuildChampionshipApi\BuildChampionshipApi;
use App\Domain\Competition\Championship\BuildChampionshipApi\BuildChampionshipApiCommandHandler;
use App\Domain\FileWriter;
use App\Tests\DatabaseTestCase;
use App\Tests\SpyApiFileWriter;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Output\OutputInterface;

class BuildChampionshipApiCommandHandlerTest extends DatabaseTestCase
{
    use MatchesSnapshots;

    private BuildChampionshipApiCommandHandler $buildChampionshipApiCommandHandler;
    private FileWriter $apiFileWriter;
    private string $snapshotName;

    public function testHandle(): void
    {
        $this->buildChampionshipApiCommandHandler->handle(new BuildChampionshipApi(
            new Progress($this->createMock(OutputInterface::class))
        ));
        foreach ($this->apiFileWriter->getWrites() as $name => $write) {
            $this->snapshotName = $name;
            $this->assertMatchesJsonSnapshot($write);
        }
    }

    protected function getSnapshotId(): string
    {
        return (new \ReflectionClass($this))->getShortName().'--'.
            $this->name().'--'.
            $this->snapshotName;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiFileWriter = new SpyApiFileWriter();
        $this->getContainer()->set(FileWriter::class, $this->apiFileWriter);

        $this->buildChampionshipApiCommandHandler = $this->getContainer()->get(BuildChampionshipApiCommandHandler::class);
    }
}
