<?php

namespace App\Domain\Continent\BuildContinentApi;

use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\CQRS\ProgressAwareDomainCommand;

class BuildContinentApi extends DomainCommand
{
    use ProgressAwareDomainCommand;
}
