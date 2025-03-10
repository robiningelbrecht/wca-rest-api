<?php

namespace App\Tests\Domain\Person\BuildPersonApi;

use App\Console\Progress;
use App\Domain\FileWriter;
use App\Domain\Person\BuildPersonApi\BuildPersonApi;
use App\Domain\Person\BuildPersonApi\BuildPersonApiCommandHandler;
use App\Tests\DatabaseTestCase;
use App\Tests\SpyApiFileWriter;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Output\OutputInterface;

class BuildPersonApiCommandHandlerTest extends DatabaseTestCase
{
    use MatchesSnapshots;

    private BuildPersonApiCommandHandler $buildPersonApiCommandHandler;
    private FileWriter $apiFileWriter;

    public function testHandle(): void
    {
        $this->buildPersonApiCommandHandler->handle(new BuildPersonApi(
            new Progress($this->createMock(OutputInterface::class))
        ));
        $this->assertMatchesJsonSnapshot($this->apiFileWriter->getWrites());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiFileWriter = new SpyApiFileWriter();
        $this->getContainer()->set(FileWriter::class, $this->apiFileWriter);

        $this->buildPersonApiCommandHandler = $this->getContainer()->get(BuildPersonApiCommandHandler::class);
    }
}
