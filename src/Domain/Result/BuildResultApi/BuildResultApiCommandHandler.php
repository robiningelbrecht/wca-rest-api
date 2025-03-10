<?php

namespace App\Domain\Result\BuildResultApi;

use App\Domain\Competition\CompetitionRepository;
use App\Domain\FileWriter;
use App\Domain\Result\ResultRepository;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Overview\Pagination;
use App\Infrastructure\Serialization\Json;

#[AsCommandHandler]
readonly class BuildResultApiCommandHandler implements CommandHandler
{
    public function __construct(
        private ResultRepository $resultRepository,
        private CompetitionRepository $competitionRepository,
        private FileWriter $apiFileWriter
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof BuildResultApi);

        $progressBar = $command->getProgressBar();
        $progressBar->start();
        $progressBar->setMaxSteps(
            $this->competitionRepository->findOneBy(Pagination::fromOffsetAndLimit(0, 1))->getTotal()
        );

        $pagination = Pagination::default();
        do {
            $competitions = $this->competitionRepository->findOneBy($pagination);

            /** @var \App\Domain\Competition\Competition $competition */
            foreach ($competitions->getItems() as $competition) {
                if ($this->apiFileWriter->fileExists(sprintf('results/%s.json', $competition->getId()))) {
                    // Results for a comp should never change, they are final
                    // So we only need to fetch and write the ones that are not in the API yet.
                    // We can do this by checking if the file exists.
                    $progressBar->advance();
                    continue;
                }
                $overview = $this->resultRepository->findOneBy(
                    $competition->getId()
                );

                if ($overview->isEmpty()) {
                    continue;
                }

                $this->apiFileWriter->write(
                    sprintf('results/%s', $competition->getId()),
                    Json::encode($overview)
                );

                foreach ($competition->getEvents() as $eventId) {
                    $overview = $this->resultRepository->findOneBy(
                        $competition->getId(),
                        $eventId
                    );

                    if ($overview->isEmpty()) {
                        continue;
                    }

                    $this->apiFileWriter->write(
                        sprintf('results/%s/%s', $competition->getId(), $eventId),
                        Json::encode($overview)
                    );
                }
                $progressBar->advance();
            }

            $pagination = $pagination->next();
        } while (($pagination->getPageNumber() - 1) * $pagination->getPageSize() < $competitions->getTotal());
        $progressBar->finish();
    }
}
