<?php

namespace App\Domain\Event;

use App\Infrastructure\Overview\Overview;
use App\Infrastructure\Overview\Pagination;
use Doctrine\DBAL\Connection;

readonly class EventRepository
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public function findAll(): Overview
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->select('SQL_CALC_FOUND_ROWS *')
            ->from('events', 'c')
            ->orderBy('name', 'ASC');

        $results = $queryBuilder->executeQuery()->fetchAllAssociative();
        $total = $this->connection->executeQuery('SELECT FOUND_ROWS() as total;')->fetchOne();

        $overview = Overview::empty(Pagination::default(), $total);
        foreach ($results as $result) {
            $overview->addItem(Event::fromState(
                id: $result['id'],
                name: $result['name'],
                format: $result['format'],
            ));
        }

        return $overview;
    }
}
