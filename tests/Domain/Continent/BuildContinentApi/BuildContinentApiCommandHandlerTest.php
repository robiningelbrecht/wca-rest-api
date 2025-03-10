<?php

namespace App\Tests\Domain\Continent\BuildContinentApi;

use App\Console\Progress;
use App\Domain\Continent\BuildContinentApi\BuildContinentApi;
use App\Domain\Continent\BuildContinentApi\BuildContinentApiCommandHandler;
use App\Domain\FileWriter;
use App\Tests\DatabaseTestCase;
use App\Tests\SpyApiFileWriter;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Output\OutputInterface;

class BuildContinentApiCommandHandlerTest extends DatabaseTestCase
{
    use MatchesSnapshots;

    private BuildContinentApiCommandHandler $buildContinentApiCommandHandler;
    private FileWriter $apiFileWriter;

    public function testHandle(): void
    {
        $this->buildContinentApiCommandHandler->handle(new BuildContinentApi(
            new Progress($this->createMock(OutputInterface::class))
        ));
        $this->assertMatchesJsonSnapshot($this->apiFileWriter->getWrites());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiFileWriter = new SpyApiFileWriter();
        $this->getContainer()->set(FileWriter::class, $this->apiFileWriter);

        $this->buildContinentApiCommandHandler = $this->getContainer()->get(BuildContinentApiCommandHandler::class);
    }
}
