<?php

namespace App\Domain\Continent\Country\BuildCountryApi;

use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\CQRS\ProgressAwareDomainCommand;

class BuildCountryApi extends DomainCommand
{
    use ProgressAwareDomainCommand;
}
