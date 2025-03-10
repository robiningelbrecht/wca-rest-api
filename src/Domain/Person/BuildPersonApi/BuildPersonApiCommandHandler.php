<?php

namespace App\Domain\Person\BuildPersonApi;

use App\Domain\FileWriter;
use App\Domain\Person\PersonRepository;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Overview\Pagination;
use App\Infrastructure\Serialization\Json;

#[AsCommandHandler]
readonly class BuildPersonApiCommandHandler implements CommandHandler
{
    public function __construct(
        private PersonRepository $personRepository,
        private FileWriter $apiFileWriter
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof BuildPersonApi);

        $progressBar = $command->getProgressBar();
        $progressBar->start();

        $overview = $this->personRepository->findOneBy(
            Pagination::default(),
        );

        $progressBar->setMaxSteps($overview->getTotal());

        $this->apiFileWriter->write('persons', Json::encode($overview));

        $pagination = Pagination::default();
        do {
            $overview = $this->personRepository->findOneBy(
                $pagination,
            );

            $this->apiFileWriter->writeWithPagination(
                'persons',
                $pagination,
                Json::encode($overview)
            );

            /** @var \App\Domain\Person\Person $item */
            foreach ($overview->getItems() as $item) {
                $this->apiFileWriter->write('persons/'.$item->getId(), Json::encode($item));
                $progressBar->advance();
            }

            $pagination = $pagination->next();
        } while (($pagination->getPageNumber() - 1) * $pagination->getPageSize() < $overview->getTotal());
        $progressBar->finish();
    }
}
