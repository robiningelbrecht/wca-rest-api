<?php

namespace App\Tests\Domain\Event\BuildEventApi;

use App\Console\Progress;
use App\Domain\Event\BuildEventApi\BuildEventApi;
use App\Domain\Event\BuildEventApi\BuildEventApiCommandHandler;
use App\Domain\FileWriter;
use App\Tests\DatabaseTestCase;
use App\Tests\SpyApiFileWriter;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Component\Console\Output\OutputInterface;

class BuildEventApiCommandHandlerTest extends DatabaseTestCase
{
    use MatchesSnapshots;

    private BuildEventApiCommandHandler $buildEventApiCommandHandler;
    private FileWriter $apiFileWriter;

    public function testHandle(): void
    {
        $this->buildEventApiCommandHandler->handle(new BuildEventApi(
            new Progress($this->createMock(OutputInterface::class))
        ));
        $this->assertMatchesJsonSnapshot($this->apiFileWriter->getWrites());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiFileWriter = new SpyApiFileWriter();
        $this->getContainer()->set(FileWriter::class, $this->apiFileWriter);

        $this->buildEventApiCommandHandler = $this->getContainer()->get(BuildEventApiCommandHandler::class);
    }
}
