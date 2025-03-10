<?php

namespace App\Infrastructure\CQRS;

use App\Console\Progress;
use Symfony\Component\Console\Helper\ProgressBar;

trait ProgressAwareDomainCommand
{
    public function __construct(
        private readonly Progress $progress
    ) {
    }

    public function getProgressBar(): ProgressBar
    {
        return $this->progress->getProgressBar();
    }
}
