<?php

namespace App\Domain\Version\UpdateApiVersion;

use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\Serialization\Json;
use App\Infrastructure\ValueObject\Time\SerializableDateTime;

class UpdateApiVersion extends DomainCommand
{
    public function __construct(
        private readonly string $versionInfo
    ) {
    }

    public function getVersionInfo(): string
    {
        return $this->versionInfo;
    }

    public function getExportDate(): SerializableDateTime
    {
        return SerializableDateTime::fromString(Json::decode($this->versionInfo)['export_date']);
    }
}
