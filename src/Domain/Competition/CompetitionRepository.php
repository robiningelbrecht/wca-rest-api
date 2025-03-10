<?php

namespace App\Domain\Competition;

use App\Domain\Continent\Country\Country;
use App\Domain\Continent\Country\Iso2Code;
use App\Infrastructure\Exception\EntityNotFound;
use App\Infrastructure\Overview\Overview;
use App\Infrastructure\Overview\Pagination;
use App\Infrastructure\ValueObject\Geography\Coordinates;
use App\Infrastructure\ValueObject\Time\DateRange;
use App\Infrastructure\ValueObject\Time\SerializableDateTime;
use Doctrine\DBAL\Connection;

readonly class CompetitionRepository
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public function findOneBy(
        Pagination $pagination,
        Country $country = null,
        int $year = null,
        int $month = null,
        int $day = null,
        string $eventId = null,
    ): Overview {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->select('SQL_CALC_FOUND_ROWS comp.*, c.iso2')
            ->from('Competitions', 'comp')
            ->innerJoin('comp', 'Countries', 'c', 'comp.countryId = c.id')
            ->setFirstResult($pagination->getOffset())
            ->setMaxResults($pagination->getLimit())
            ->addOrderBy('year', 'DESC')
            ->addOrderBy('month', 'DESC')
            ->addOrderBy('day', 'DESC');

        if ($country) {
            $queryBuilder->andWhere('c.iso2 = :iso2')
                ->setParameter('iso2', $country->getIso2Code());
        }

        if ($year) {
            $queryBuilder->andWhere('comp.year = :year')
                ->setParameter('year', $year);
        }

        if ($year && $month) {
            $queryBuilder->andWhere('comp.month = :month')
                ->setParameter('month', $month);
        }

        if ($year && $month && $day) {
            $queryBuilder->andWhere('comp.day = :day')
                ->setParameter('day', $day);
        }

        if ($eventId) {
            $queryBuilder->andWhere('comp.eventSpecs LIKE :event')
                ->setParameter('event', '%'.$eventId.'%');
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
            $overview->addItem($this->buildResult($result));
        }

        return $overview;
    }

    /**
     * @return string[]
     */
    public function findCompetitionIdsByPerson(string $personId): array
    {
        $query = '
            SELECT id
            FROM Competitions comp
            WHERE comp.id IN (SELECT DISTINCT competitionId FROM Results WHERE personId = :personId)
        ';

        return $this->connection->executeQuery($query, [
            'personId' => $personId,
        ])->fetchFirstColumn();
    }

    public function countUniqueCompetitionDays(): int
    {
        $query = "
          SELECT COUNT(DISTINCT DATE(CONCAT_WS('-', `year`, `month`, `day`)))
           FROM Competitions
        ";

        return (int) $this->connection->executeQuery($query)->fetchOne();
    }

    public function find(string $competitionId): Competition
    {
        $query = '
            SELECT comp.*, c.iso2
            FROM Competitions comp
            INNER JOIN Countries c ON comp.countryId = c.id
            WHERE comp.id = :competitionId
        ';

        $result = $this->connection->executeQuery($query, [
            'competitionId' => $competitionId,
        ])->fetchAssociative();

        if (!$result) {
            throw new EntityNotFound();
        }

        return $this->buildResult($result);
    }

    /**
     * @param array<mixed> $result
     */
    private function buildResult(array $result): Competition
    {
        $wcaDelegates = [$result['wcaDelegate']];
        if (preg_match_all('/\[\{(?<name>[\s\S]+)\}\{mailto:(?<email>[\s\S]+)\}\]/U', $result['wcaDelegate'], $matches)) {
            $wcaDelegates = [];
            foreach ($matches['name'] as $key => $match) {
                $wcaDelegates[] = [
                    'name' => $match,
                    'email' => $matches['email'][$key],
                ];
            }
        }
        $organisers = [$result['organiser']];
        if (preg_match_all('/\[\{(?<name>[\s\S]+)\}\{mailto:(?<email>[\s\S]+)\}\]/U', $result['organiser'] ?? '', $matches)) {
            $organisers = [];
            foreach ($matches['name'] as $key => $match) {
                $organisers[] = [
                    'name' => $match,
                    'email' => $matches['email'][$key],
                ];
            }
        }

        return Competition::fromState(
            id: $result['id'],
            name: $result['name'],
            city: $result['cityName'],
            country: Iso2Code::fromString($result['iso2']),
            date: DateRange::fromFromDateAndTillDate(
                SerializableDateTime::fromString($result['year'].'-'.$result['month'].'-'.$result['day']),
                SerializableDateTime::fromString($result['year'].'-'.$result['endMonth'].'-'.$result['endDay']),
            ),
            isCanceled: $result['cancelled'],
            events: explode(' ', $result['eventSpecs']),
            wcaDelegates: $wcaDelegates,
            venue: Venue::fromValues(
                $result['venue'],
                $result['venueAddress'],
                $result['venueDetails'],
                Coordinates::fromIntegers(
                    $result['latitude'],
                    $result['longitude'],
                )
            ),
            organisers: $organisers,
            information: $result['information'],
            externalWebsite: $result['external_website'],
        );
    }
}
