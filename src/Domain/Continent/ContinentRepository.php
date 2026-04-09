<?php

namespace App\Domain\Continent;

use App\Infrastructure\Overview\Overview;
use App\Infrastructure\Overview\Pagination;
use Doctrine\DBAL\Connection;

readonly class ContinentRepository
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public function findAll(): Overview
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->select('SQL_CALC_FOUND_ROWS *')
            ->from('continents', 'c')
            ->orderBy('id', 'ASC');

        $results = $queryBuilder->executeQuery()->fetchAllAssociative();
        $total = $this->connection->executeQuery('SELECT FOUND_ROWS() as total;')->fetchOne();

        $overview = Overview::empty(Pagination::default(), $total);
        foreach ($results as $result) {
            $overview->addItem(Continent::fromState(
                $result['id'],
                $result['name'],
            ));
        }

        return $overview;
    }
}
