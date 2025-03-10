<?php

namespace App\Domain\Rank;

enum RegionType: string
{
    case WORLD = 'world';
    case CONTINENT = 'continent';
    case COUNTRY = 'country';
}
