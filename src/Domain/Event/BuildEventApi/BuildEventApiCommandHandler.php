<?php

namespace App\Domain\Event\BuildEventApi;

use App\Domain\Event\EventRepository;
use App\Domain\FileWriter;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Serialization\Json;

#[AsCommandHandler]
readonly class BuildEventApiCommandHandler implements CommandHandler
{
    public function __construct(
        private EventRepository $eventRepository,
        private FileWriter $apiFileWriter
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof BuildEventApi);

        $progressBar = $command->getProgressBar();
        $progressBar->setMaxSteps(2);
        $progressBar->start();

        $overview = $this->eventRepository->findAll();
        $progressBar->advance();

        $this->apiFileWriter->write('events', Json::encode($overview));
        $progressBar->finish();
    }
}
