<?php

namespace App\Domain\Rank;

enum RankType: string
{
    case SINGLE = 'single';
    case AVERAGE = 'average';
}
