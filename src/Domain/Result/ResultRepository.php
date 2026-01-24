<?php

namespace App\Domain\Result;

use App\Infrastructure\Overview\Overview;
use App\Infrastructure\Overview\Pagination;
use Doctrine\DBAL\Connection;

readonly class ResultRepository
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public function findOneBy(
        string $competitionId,
        string $eventId = null,
    ): Overview {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('r.*, rt.name as roundName, rt.final as isFinalRound, f.name as formatName')
            ->from('Results', 'r')
            ->innerJoin('r', 'round_types', 'rt', 'r.round_type_id = rt.id')
            ->innerJoin('r', 'formats', 'f', 'r.format_id = f.id')
            ->innerJoin('r', 'competitions', 'c', 'r.competition_id = c.id')
            ->innerJoin('r', 'events', 'e', 'r.event_id = e.id')
            ->andWhere('r.competition_id = :competition_id')
            ->setParameter('competition_id', $competitionId)
            ->addOrderBy('e.rank', 'ASC')
            ->addOrderBy('rt.rank', 'DESC')
            ->addOrderBy('r.pos', 'ASC');

        if ($eventId) {
            $queryBuilder
                ->andWhere('r.event_id = :event')
                ->setParameter('event', $eventId);
        }

        $results = $queryBuilder->executeQuery()->fetchAllAssociative();
        $total = $this->connection->executeQuery('SELECT FOUND_ROWS() as total;')->fetchOne();

        if (0 === count($results)) {
            return Overview::empty(Pagination::default());
        }

        $overview = Overview::empty(
            Pagination::fromPageNumberAndSize(
                1,
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
     * @return \App\Domain\Result\Result[]
     */
    public function findByPerson(string $personId): array
    {
        $query = '
            SELECT r.*, rt.name as round_name, rt.final as is_final_round, f.name as format_name,
            v1.value AS value1, v2.value AS value2, v3.value AS value3, v4.value AS value4, v5.value AS value5
            FROM Results r
            INNER JOIN round_types rt ON r.round_type_id = rt.id
            INNER JOIN formats f ON r.format_id = f.id
            INNER JOIN competitions c ON r.competition_id = c.id
            INNER JOIN events e ON r.event_id = e.id
            LEFT JOIN result_attempts v1 ON v1.result_id = r.id AND v1.attempt_number=1
            LEFT JOIN result_attempts v2 ON v2.result_id = r.id AND v2.attempt_number=2
            LEFT JOIN result_attempts v3 ON v3.result_id = r.id AND v3.attempt_number=3
            LEFT JOIN result_attempts v4 ON v4.result_id = r.id AND v4.attempt_number=4
            LEFT JOIN result_attempts v5 ON v5.result_id = r.id AND v5.attempt_number=5
            WHERE person_id = :person_id
            ORDER BY c.year DESC, c.month DESC, c.day DESC, e.rank ASC, rt.rank DESC
        ';

        $results = $this->connection->executeQuery($query, [
            'personId' => $personId,
        ])->fetchAllAssociative();

        return array_map(fn (array $result) => $this->buildResult($result), $results);
    }

    /**
     * @param array<mixed> $result
     */
    private function buildResult(array $result): Result
    {
        return Result::fromState(
            competitionId: $result['competition_id'],
            personId: $result['person_id'],
            eventId: $result['event_id'],
            round: $result['round_name'],
            isFinalRound: !empty($result['is_final_round']),
            position: $result['pos'],
            best: $result['best'],
            average: $result['average'],
            format: $result['format_name'],
            solves: [
                $result['value1'],
                $result['value2'],
                $result['value3'],
                $result['value4'],
                $result['value5'],
            ],
            singleRecord: Record::tryFromMap($result['regional_single_record']),
            averageRecord: Record::tryFromMap($result['regional_average_record'])
        );
    }
}
