<?php

namespace App\Domain;

use App\Infrastructure\Overview\Pagination;

interface FileWriter
{
    public function write(
        string $fileName,
        string $contents,
    ): void;

    public function writeWithPagination(
        string $fileName,
        Pagination $pagination,
        string $contents,
    ): void;

    public function fileExists(string $path): bool;
}
