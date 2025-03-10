<?php

namespace App\Tests\Domain\Continent\Country\BuildCountryApi;

use App\Console\Progress;
use App\Domain\Continent\Country\BuildCountryApi\BuildCountryApi;
use App\Domain\Continent\Country\BuildCountryApi\BuildCountryApiCommandHandler;
use App\Domain\FileWriter;
use App\Tests\DatabaseTestCase;
use App\Tests\SpyApiFileWriter;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCountryApiCommandHandlerTest extends DatabaseTestCase
{
    use MatchesSnapshots;

    private BuildCountryApiCommandHandler $buildCountryApiCommandHandler;
    private FileWriter $apiFileWriter;

    public function testHandle(): void
    {
        $this->buildCountryApiCommandHandler->handle(new BuildCountryApi(
            new Progress($this->createMock(OutputInterface::class))
        ));
        $this->assertMatchesJsonSnapshot($this->apiFileWriter->getWrites());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiFileWriter = new SpyApiFileWriter();
        $this->getContainer()->set(FileWriter::class, $this->apiFileWriter);

        $this->buildCountryApiCommandHandler = $this->getContainer()->get(BuildCountryApiCommandHandler::class);
    }
}
