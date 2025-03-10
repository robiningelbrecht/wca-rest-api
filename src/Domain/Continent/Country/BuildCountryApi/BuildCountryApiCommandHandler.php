<?php

namespace App\Domain\Continent\Country\BuildCountryApi;

use App\Domain\Continent\Country\CountryRepository;
use App\Domain\FileWriter;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Serialization\Json;

#[AsCommandHandler]
readonly class BuildCountryApiCommandHandler implements CommandHandler
{
    public function __construct(
        private CountryRepository $countryRepository,
        private FileWriter $apiFileWriter
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof BuildCountryApi);

        $progressBar = $command->getProgressBar();
        $progressBar->setMaxSteps(2);
        $progressBar->start();

        $overview = $this->countryRepository->findAll();
        $progressBar->advance();

        $this->apiFileWriter->write('countries', Json::encode($overview));
        $progressBar->finish();
    }
}
