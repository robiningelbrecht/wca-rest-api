<?php

namespace App\Domain\Result\BuildResultApi;

use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\CQRS\ProgressAwareDomainCommand;

class BuildResultApi extends DomainCommand
{
    use ProgressAwareDomainCommand;
}
