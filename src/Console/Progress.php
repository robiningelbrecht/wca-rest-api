<?php

namespace App\Console;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

readonly class Progress
{
    private ProgressBar $progressBar;

    public function __construct(
        OutputInterface $output
    ) {
        $this->progressBar = new ProgressBar($output, 0);
        $this->progressBar->setFormat('  %percent:3s%% [%bar%] %current%/%max% [<comment>%elapsed:6s%</comment>]');
    }

    public function getProgressBar(): ProgressBar
    {
        return $this->progressBar;
    }
}
