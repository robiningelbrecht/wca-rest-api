<?php

namespace App\Domain\Version;

use App\Domain\FileWriter;

readonly class VersionRepository
{
    public function __construct(
        private FileWriter $apiFileWriter
    ) {
    }

    public function save(string $versionInfo): void
    {
        $this->apiFileWriter->write('version', $versionInfo);
    }
}
