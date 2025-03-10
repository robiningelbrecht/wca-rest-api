<?php

namespace App\Domain\Competition\Championship\BuildChampionshipApi;

use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\CQRS\ProgressAwareDomainCommand;

class BuildChampionshipApi extends DomainCommand
{
    use ProgressAwareDomainCommand;
}
