<?php

namespace App\Domain\Competition\BuildCompetitionApi;

use App\Domain\Competition\CompetitionRepository;
use App\Domain\Continent\Country\CountryRepository;
use App\Domain\Event\EventRepository;
use App\Domain\FileWriter;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Overview\Pagination;
use App\Infrastructure\Serialization\Json;
use Symfony\Component\Console\Helper\ProgressBar;

#[AsCommandHandler]
readonly class BuildCompetitionApiCommandHandler implements CommandHandler
{
    public function __construct(
        private CompetitionRepository $competitionRepository,
        private CountryRepository $countryRepository,
        private EventRepository $eventRepository,
        private FileWriter $apiFileWriter
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof BuildCompetitionApi);

        $progressBar = $command->getProgressBar();
        $progressBar->start();

        $allCompetitions = $this->competitionRepository->findOneBy(
            Pagination::fromOffsetAndLimit(0, 1),
        );
        $allCountries = $this->countryRepository->findAll();
        $allEvents = $this->eventRepository->findAll();
        $progressBar->setMaxSteps(
            $allCompetitions->getTotal() + $allCountries->getTotal() + $allEvents->getTotal() + $this->competitionRepository->countUniqueCompetitionDays()
        );

        $this->buildAllCompetitions($progressBar);
        $this->buildCompetitionsPerCountry($progressBar);
        $this->buildCompetitionsPerDate($progressBar);
        $this->buildCompetitionsPerEvent($progressBar);

        $progressBar->finish();
    }

    private function buildAllCompetitions(ProgressBar $progressBar): void
    {
        $overview = $this->competitionRepository->findOneBy(
            Pagination::default(),
        );

        $this->apiFileWriter->write('competitions', Json::encode($overview));

        $pagination = Pagination::default();
        do {
            $overview = $this->competitionRepository->findOneBy(
                $pagination,
            );

            $this->apiFileWriter->writeWithPagination(
                'competitions',
                $pagination,
                Json::encode($overview)
            );

            /** @var \App\Domain\Competition\Competition $item */
            foreach ($overview->getItems() as $item) {
                $this->apiFileWriter->write('competitions/'.$item->getId(), Json::encode($item));
                $progressBar->advance();
            }

            $pagination = $pagination->next();
        } while (($pagination->getPageNumber() - 1) * $pagination->getPageSize() < $overview->getTotal());
    }

    private function buildCompetitionsPerCountry(ProgressBar $progressBar): void
    {
        $countries = $this->countryRepository->findAll();

        /** @var \App\Domain\Continent\Country\Country $country */
        foreach ($countries->getItems() as $country) {
            $overview = $this->competitionRepository->findOneBy(
                Pagination::all(),
                country: $country
            );
            $this->apiFileWriter->write(
                'competitions/'.$country->getIso2Code(),
                Json::encode($overview)
            );
            $progressBar->advance();
        }
    }

    private function buildCompetitionsPerDate(ProgressBar $progressBar): void
    {
        foreach (range(1980, (int) date('Y') + 1) as $year) {
            $overview = $this->competitionRepository->findOneBy(
                Pagination::all(),
                year: $year
            );
            if ($overview->isEmpty()) {
                continue;
            }
            $this->apiFileWriter->write(
                'competitions/'.$year,
                Json::encode($overview)
            );

            foreach (range(1, 12) as $month) {
                $monthWithLeadingZero = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
                $overview = $this->competitionRepository->findOneBy(
                    Pagination::all(),
                    year: $year,
                    month: $month
                );
                if ($overview->isEmpty()) {
                    continue;
                }
                $this->apiFileWriter->write(
                    'competitions/'.$year.'/'.$monthWithLeadingZero,
                    Json::encode($overview)
                );

                foreach (range(1, 31) as $day) {
                    $dayWithLeadingZero = str_pad((string) $day, 2, '0', STR_PAD_LEFT);
                    $overview = $this->competitionRepository->findOneBy(
                        Pagination::all(),
                        year: $year,
                        month: $month,
                        day: $day
                    );
                    if ($overview->isEmpty()) {
                        continue;
                    }
                    $this->apiFileWriter->write(
                        'competitions/'.$year.'/'.$monthWithLeadingZero.'/'.$dayWithLeadingZero,
                        Json::encode($overview)
                    );
                    $progressBar->advance();
                }
            }
        }
    }

    private function buildCompetitionsPerEvent(ProgressBar $progressBar): void
    {
        $events = $this->eventRepository->findAll();
        /** @var \App\Domain\Event\Event $event */
        foreach ($events->getItems() as $event) {
            $overview = $this->competitionRepository->findOneBy(
                Pagination::default(),
                eventId: $event->getId()
            );
            if ($overview->isEmpty()) {
                continue;
            }
            $this->apiFileWriter->write(
                'competitions/'.$event->getId(),
                Json::encode($overview)
            );

            $pagination = Pagination::default();
            do {
                $overview = $this->competitionRepository->findOneBy(
                    $pagination,
                    eventId: $event->getId()
                );

                $this->apiFileWriter->writeWithPagination(
                    'competitions/'.$event->getId(),
                    $pagination,
                    Json::encode($overview)
                );

                $pagination = $pagination->next();
            } while (($pagination->getPageNumber() - 1) * $pagination->getPageSize() < $overview->getTotal());

            $progressBar->advance();
        }
    }
}
