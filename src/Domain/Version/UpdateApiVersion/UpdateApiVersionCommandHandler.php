<?php

namespace App\Domain\Version\UpdateApiVersion;

use App\Domain\Version\VersionRepository;
use App\Infrastructure\Attribute\AsCommandHandler;
use App\Infrastructure\CQRS\CommandHandler\CommandHandler;
use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Environment\Settings;

#[AsCommandHandler]
readonly class UpdateApiVersionCommandHandler implements CommandHandler
{
    public function __construct(
        private VersionRepository $versionRepository
    ) {
    }

    public function handle(DomainCommand $command): void
    {
        assert($command instanceof UpdateApiVersion);
        $exportDate = $command->getExportDate();

        /** @var string $readMe */
        $readMe = file_get_contents(Settings::getAppRoot().'/README.md');
        $readMe = preg_replace(
            '/<!--START_SECTION:version-date-->[\s\S]+<!--END_SECTION:version-date-->/',
            sprintf('<!--START_SECTION:version-date-->%s<!--END_SECTION:version-date-->', $exportDate->format('F d, Y')),
            $readMe
        );
        file_put_contents(Settings::getAppRoot().'/README.md', $readMe);

        // Update API docs as well.
        /** @var string $apiDocs */
        $apiDocs = file_get_contents(Settings::getAppRoot().'/docs/openapi.yml');
        $apiDocs = preg_replace(
            '/<!--START_SECTION:version-date-->[\s\S]+<!--END_SECTION:version-date-->/',
            sprintf('<!--START_SECTION:version-date-->%s<!--END_SECTION:version-date-->', $exportDate->format('F d, Y')),
            $apiDocs
        );
        file_put_contents(Settings::getAppRoot().'/docs/openapi.yml', $apiDocs);

        $this->versionRepository->save($command->getVersionInfo());
    }
}
