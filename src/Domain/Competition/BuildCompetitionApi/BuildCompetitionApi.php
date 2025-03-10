<?php

namespace App\Domain\Competition\BuildCompetitionApi;

use App\Infrastructure\CQRS\DomainCommand;
use App\Infrastructure\CQRS\ProgressAwareDomainCommand;

class BuildCompetitionApi extends DomainCommand
{
    use ProgressAwareDomainCommand;
}
