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
            ->from('results', 'r')
            ->innerJoin('r', 'round_types', 'rt', 'r.round_type_id = rt.id')
            ->innerJoin('r', 'formats', 'f', 'r.format_id = f.id')
            ->innerJoin('r', 'competitions', 'c', 'r.competition_id = c.id')
            ->innerJoin('r', 'events', 'e', 'r.event_id = e.id')
            ->andWhere('r.competition_id = :competitionId')
            ->setParameter('competitionId', $competitionId)
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

        // Batch-fetch attempts for all result IDs
        $attemptsMap = $this->fetchAttemptsForResults($results);

        $overview = Overview::empty(
            Pagination::fromPageNumberAndSize(
                1,
                count($results)
            ),
            $total
        );

        foreach ($results as $result) {
            $solves = $attemptsMap[$result['id']] ?? [];
            $overview->addItem($this->buildResult($result, $solves));
        }

        return $overview;
    }

    /**
     * @return \App\Domain\Result\Result[]
     */
    public function findByPerson(string $personId): array
    {
        $query = '
            SELECT r.*, rt.name as roundName, rt.final as isFinalRound, f.name as formatName
            FROM results r
            INNER JOIN round_types rt ON r.round_type_id = rt.id
            INNER JOIN formats f ON r.format_id = f.id
            INNER JOIN competitions c ON r.competition_id = c.id
            INNER JOIN events e ON r.event_id = e.id
            WHERE person_id = :personId
            ORDER BY c.year DESC, c.month DESC, c.day DESC, e.rank ASC, rt.rank DESC
        ';

        $results = $this->connection->executeQuery($query, [
            'personId' => $personId,
        ])->fetchAllAssociative();

        // Batch-fetch attempts for all result IDs
        $attemptsMap = $this->fetchAttemptsForResults($results);

        return array_map(fn (array $result) => $this->buildResult(
            $result,
            $attemptsMap[$result['id']] ?? []
        ), $results);
    }

    /**
     * Batch-fetch all attempts for a set of results, grouped by result_id.
     *
     * @param array<mixed> $results
     *
     * @return array<int, int[]> Map of result_id => ordered array of attempt values
     */
    private function fetchAttemptsForResults(array $results): array
    {
        if (0 === count($results)) {
            return [];
        }

        $resultIds = array_column($results, 'id');

        $query = '
            SELECT result_id, value
            FROM result_attempts
            WHERE result_id IN (?)
            ORDER BY result_id, attempt_number
        ';

        $rows = $this->connection->executeQuery(
            $query,
            [$resultIds],
            [Connection::PARAM_INT_ARRAY]
        )->fetchAllAssociative();

        $attemptsMap = [];
        foreach ($rows as $row) {
            $attemptsMap[(int) $row['result_id']][] = (int) $row['value'];
        }

        return $attemptsMap;
    }

    /**
     * @param array<mixed> $result
     * @param int[] $solves
     */
    private function buildResult(array $result, array $solves): Result
    {
        // Pad solves to exactly 5 entries for backward compatibility
        // The old schema always had value1-value5; the new result_attempts table
        // only stores actual attempts, so we zero-pad to maintain API contract.
        while (count($solves) < 5) {
            $solves[] = 0;
        }

        return Result::fromState(
            competitionId: $result['competition_id'],
            personId: $result['person_id'],
            eventId: $result['event_id'],
            round: $result['roundName'],
            isFinalRound: !empty($result['isFinalRound']),
            position: $result['pos'],
            best: $result['best'],
            average: $result['average'],
            format: $result['formatName'],
            solves: $solves,
            singleRecord: Record::tryFromMap($result['regional_single_record']),
            averageRecord: Record::tryFromMap($result['regional_average_record'])
        );
    }
}
