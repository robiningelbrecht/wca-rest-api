<?php

namespace App\Domain\Rank;

use App\Infrastructure\Overview\Overview;
use App\Infrastructure\Overview\Pagination;
use Doctrine\DBAL\Connection;

readonly class RankRepository
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public function findOneBy(
        Pagination $pagination,
        RankType $rankType,
        RegionType $regionType,
        string $eventId,
        string $region = null
    ): Overview {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->select('SQL_CALC_FOUND_ROWS r.*, c.iso2')
            ->from(RankType::SINGLE === $rankType ? 'ranks_single' : 'ranks_average', 'r')
            ->innerJoin('r', 'persons', 'p', 'r.person_id = p.wca_id')
            ->innerJoin('p', 'countries', 'c', 'p.country_id = c.id')
            ->andWhere('r.event_id = :event')
            ->setParameter('event', $eventId)
            ->setFirstResult($pagination->getOffset())
            ->setMaxResults($pagination->getLimit());

        if (RegionType::WORLD === $regionType) {
            $queryBuilder->addOrderBy('r.world_rank');
            $queryBuilder->andWhere('r.world_rank != 0');
        } elseif (RegionType::CONTINENT === $regionType) {
            $queryBuilder->addOrderBy('r.continent_rank');
            $queryBuilder->andWhere('r.continent_rank != 0');
            $queryBuilder->andWhere('c.continent_id = :region');
            $queryBuilder->setParameter('region', $region);
        } elseif (RegionType::COUNTRY === $regionType) {
            $queryBuilder->addOrderBy('r.country_rank');
            $queryBuilder->andWhere('r.country_rank != 0');
            $queryBuilder->andWhere('c.iso2 = :region');
            $queryBuilder->setParameter('region', $region);
        }

        $results = $queryBuilder->executeQuery()->fetchAllAssociative();
        $total = $this->connection->executeQuery('SELECT FOUND_ROWS() as total;')->fetchOne();

        if (0 === count($results)) {
            return Overview::empty(Pagination::default());
        }

        $overview = Overview::empty(
            count($results) == $pagination->getPageSize() ? $pagination : $pagination::fromPageNumberAndSize(
                $pagination->getPageNumber(),
                count($results)
            ),
            $total
        );

        foreach ($results as $result) {
            $overview->addItem(Rank::fromState(
                rankType: $rankType,
                personId: $result['person_id'],
                eventId: $result['event_id'],
                best: $result['best'],
                worldRank: $result['world_rank'],
                continentRank: $result['continent_rank'],
                countryRank: $result['country_rank'],
            ));
        }

        return $overview;
    }

    /**
     * @return \App\Domain\Rank\Rank[]
     */
    public function findByPerson(string $personId): array
    {
        $query = '
            SELECT *, "average" as rankType
            FROM ranks_average
            WHERE person_id = :personId
            UNION
            SELECT *, "single" as rankType
            FROM ranks_single
            WHERE person_id = :personId
        ';

        $results = $this->connection->executeQuery($query, [
            'personId' => $personId,
        ])->fetchAllAssociative();

        return array_map(fn (array $result) => Rank::fromState(
            rankType: RankType::from($result['rankType']),
            personId: $result['person_id'],
            eventId: $result['event_id'],
            best: $result['best'],
            worldRank: $result['world_rank'],
            continentRank: $result['continent_rank'],
            countryRank: $result['country_rank'],
        ), $results);
    }
}
