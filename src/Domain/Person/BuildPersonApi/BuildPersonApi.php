<?php

namespace App\Domain\Person\BuildPersonApi;

use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\CQRS\ProgressAwareDomainCommand;

class BuildPersonApi extends DomainCommand
{
    use ProgressAwareDomainCommand;
}
