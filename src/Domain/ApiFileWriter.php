<?php

namespace App\Domain;

use App\Infrastructure\Environment\Settings;
use App\Infrastructure\Overview\Pagination;
use League\Flysystem\Filesystem;

readonly class ApiFileWriter implements FileWriter
{
    public function __construct(
        private Filesystem $filesystem
    ) {
    }

    public function write(
        string $fileName,
        string $contents,
    ): void {
        $this->filesystem->write(
            sprintf('/api/%s.json', trim($fileName, '/')),
            $contents
        );
    }

    public function writeWithPagination(
        string $fileName,
        Pagination $pagination,
        string $contents,
    ): void {
        $this->filesystem->write(
            sprintf('/api/%s-page-%s.json', trim($fileName, '/'), $pagination->getPageNumber()),
            $contents
        );
    }

    public function fileExists(string $path): bool
    {
        return file_exists(sprintf('%s/api/%s', Settings::getAppRoot(), $path));
    }
}
