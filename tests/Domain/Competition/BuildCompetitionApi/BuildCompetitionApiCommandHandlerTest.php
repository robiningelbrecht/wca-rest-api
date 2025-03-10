<?php

namespace App\Tests\Domain\Competition\BuildCompetitionApi;

use App\Console\Progress;
use App\Domain\Competition\BuildCompetitionApi\BuildCompetitionApi;
use App\Domain\Competition\BuildCompetitionApi\BuildCompetitionApiCommandHandler;
use App\Domain\FileWriter;
use App\Tests\DatabaseTestCase;
use App\Tests\SpyApiFileWriter;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCompetitionApiCommandHandlerTest extends DatabaseTestCase
{
    use MatchesSnapshots;

    private BuildCompetitionApiCommandHandler $buildCompetitionApiCommandHandler;
    private FileWriter $apiFileWriter;
    private string $snapshotName;

    public function testHandle(): void
    {
        $this->buildCompetitionApiCommandHandler->handle(new BuildCompetitionApi(
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

        $this->buildCompetitionApiCommandHandler = $this->getContainer()->get(BuildCompetitionApiCommandHandler::class);
    }
}
