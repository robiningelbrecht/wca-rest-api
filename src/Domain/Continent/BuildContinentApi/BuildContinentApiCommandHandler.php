<?php

namespace App\Domain\Continent\BuildContinentApi;

use App\Domain\Continent\ContinentRepository;
use App\Domain\FileWriter;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Serialization\Json;

#[AsCommandHandler]
readonly class BuildContinentApiCommandHandler implements CommandHandler
{
    public function __construct(
        private ContinentRepository $continentRepository,
        private FileWriter $apiFileWriter
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof BuildContinentApi);

        $progressBar = $command->getProgressBar();
        $progressBar->setMaxSteps(2);
        $progressBar->start();

        $overview = $this->continentRepository->findAll();
        $progressBar->advance();

        $this->apiFileWriter->write('continents', Json::encode($overview));
        $progressBar->finish();
    }
}
