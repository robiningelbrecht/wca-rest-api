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
            ->from(RankType::SINGLE === $rankType ? 'RanksSingle' : 'RanksAverage', 'r')
            ->innerJoin('r', 'Persons', 'p', 'r.personId = p.id')
            ->innerJoin('p', 'Countries', 'c', 'p.countryId = c.id')
            ->andWhere('r.eventId = :event')
            ->setParameter('event', $eventId)
            ->setFirstResult($pagination->getOffset())
            ->setMaxResults($pagination->getLimit());

        if (RegionType::WORLD === $regionType) {
            $queryBuilder->addOrderBy('r.worldRank');
            $queryBuilder->andWhere('r.worldRank != 0');
        } elseif (RegionType::CONTINENT === $regionType) {
            $queryBuilder->addOrderBy('r.continentRank');
            $queryBuilder->andWhere('r.continentRank != 0');
            $queryBuilder->andWhere('c.continentId = :region');
            $queryBuilder->setParameter('region', $region);
        } elseif (RegionType::COUNTRY === $regionType) {
            $queryBuilder->addOrderBy('r.countryRank');
            $queryBuilder->andWhere('r.countryRank != 0');
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
                personId: $result['personId'],
                eventId: $result['eventId'],
                best: $result['best'],
                worldRank: $result['worldRank'],
                continentRank: $result['continentRank'],
                countryRank: $result['countryRank'],
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
            FROM RanksAverage
            WHERE personId = :personId
            UNION
            SELECT *, "single" as rankType
            FROM RanksSingle
            WHERE personId = :personId
        ';

        $results = $this->connection->executeQuery($query, [
            'personId' => $personId,
        ])->fetchAllAssociative();

        return array_map(fn (array $result) => Rank::fromState(
            rankType: RankType::from($result['rankType']),
            personId: $result['personId'],
            eventId: $result['eventId'],
            best: $result['best'],
            worldRank: $result['worldRank'],
            continentRank: $result['continentRank'],
            countryRank: $result['countryRank'],
        ), $results);
    }
}
