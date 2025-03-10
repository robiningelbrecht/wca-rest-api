<?php

namespace App\Tests\Domain\Result\BuildResultApi;

use App\Console\Progress;
use App\Domain\FileWriter;
use App\Domain\Result\BuildResultApi\BuildResultApi;
use App\Domain\Result\BuildResultApi\BuildResultApiCommandHandler;
use App\Tests\DatabaseTestCase;
use App\Tests\SpyApiFileWriter;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Output\OutputInterface;

class BuildResultApiCommandHandlerTest extends DatabaseTestCase
{
    use MatchesSnapshots;

    private BuildResultApiCommandHandler $buildResultApiCommandHandler;
    private FileWriter $apiFileWriter;

    public function testHandle(): void
    {
        $this->buildResultApiCommandHandler->handle(new BuildResultApi(
            new Progress($this->createMock(OutputInterface::class))
        ));
        $this->assertMatchesJsonSnapshot($this->apiFileWriter->getWrites());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiFileWriter = new SpyApiFileWriter();
        $this->getContainer()->set(FileWriter::class, $this->apiFileWriter);

        $this->buildResultApiCommandHandler = $this->getContainer()->get(BuildResultApiCommandHandler::class);
    }
}
