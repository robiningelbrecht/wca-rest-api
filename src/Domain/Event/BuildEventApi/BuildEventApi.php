<?php

namespace App\Domain\Event\BuildEventApi;

use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\CQRS\ProgressAwareDomainCommand;

class BuildEventApi extends DomainCommand
{
    use ProgressAwareDomainCommand;
}
