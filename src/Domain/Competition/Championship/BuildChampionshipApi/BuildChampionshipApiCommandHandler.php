<?php

namespace App\Domain\Competition\Championship\BuildChampionshipApi;

use App\Domain\Competition\Championship\ChampionshipRepository;
use App\Domain\Continent\ContinentRepository;
use App\Domain\Continent\Country\CountryRepository;
use App\Domain\FileWriter;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Overview\Pagination;
use App\Infrastructure\Serialization\Json;

#[AsCommandHandler]
readonly class BuildChampionshipApiCommandHandler implements CommandHandler
{
    public function __construct(
        private ChampionshipRepository $championshipRepository,
        private CountryRepository $countryRepository,
        private ContinentRepository $continentRepository,
        private FileWriter $apiFileWriter
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof BuildChampionshipApi);

        $progressBar = $command->getProgressBar();
        $progressBar->start();

        $countries = $this->countryRepository->findAll();
        $continents = $this->continentRepository->findAll();

        $overview = $this->championshipRepository->findOneBy(
            Pagination::default(),
        );
        $progressBar->setMaxSteps($overview->getTotal() + 4);

        $this->apiFileWriter->write('championships', Json::encode($overview));
        $progressBar->advance();

        $pagination = Pagination::default();
        do {
            $overview = $this->championshipRepository->findOneBy(
                $pagination,
            );

            $this->apiFileWriter->writeWithPagination(
                'championships',
                $pagination,
                Json::encode($overview)
            );

            /** @var \App\Domain\Competition\Championship\Championship $item */
            foreach ($overview->getItems() as $item) {
                $this->apiFileWriter->write('championships/'.$item->getId(), Json::encode($item));
                $progressBar->advance();
            }

            $pagination = $pagination->next();
        } while (($pagination->getPageNumber() - 1) * $pagination->getPageSize() < $overview->getTotal());

        // World championships.
        $overview = $this->championshipRepository->findOneBy(
            Pagination::all(),
            'world'
        );

        $this->apiFileWriter->write(
            'championships/world',
            Json::encode($overview)
        );
        $progressBar->advance();

        // National championships.
        /** @var \App\Domain\Continent\Country\Country $country */
        foreach ($countries->getItems() as $country) {
            $overview = $this->championshipRepository->findOneBy(
                Pagination::all(),
                $country->getIso2Code(),
            );

            if ($overview->isEmpty()) {
                continue;
            }
            $this->apiFileWriter->write(
                'championships/'.$country->getIso2Code(),
                Json::encode($overview)
            );
        }
        $progressBar->advance();

        // Continent championships.
        /** @var \App\Domain\Continent\Continent $continent */
        foreach ($continents->getItems() as $continent) {
            $overview = $this->championshipRepository->findOneBy(
                Pagination::all(),
                $continent->getId()
            );

            if ($overview->isEmpty()) {
                continue;
            }
            $this->apiFileWriter->write(
                'championships/'.$continent->getSlug(),
                Json::encode($overview)
            );
        }
        $progressBar->finish();
    }
}
