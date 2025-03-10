<?php

namespace App\Domain\Rank\BuildRankApi;

use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\CQRS\ProgressAwareDomainCommand;

class BuildRankApi extends DomainCommand
{
    use ProgressAwareDomainCommand;
}
