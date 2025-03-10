<?php

namespace App\Infrastructure\ValueObject\Time;

readonly class DateRange implements \JsonSerializable
{
    public function __construct(
        private SerializableDateTime $from,
        private SerializableDateTime $till)
    {
        if ($from > $till) {
            throw new \InvalidArgumentException('invalid DateRange: '.$from->iso().' till '.$till->iso());
        }
    }

    public static function fromFromDateAndTillDate(
        SerializableDateTime $from,
        SerializableDateTime $till): DateRange
    {
        if ($from > $till) {
            // Due to limitations in WCA database, we need to fix
            // ranges that run from 31-12-2022 to 01-01-2023
            $till = SerializableDateTime::fromString(sprintf(
                '%d-%s-%s',
                (int) $till->format('Y') + 1,
                $till->format('m'),
                $till->format('d'),
            ));
        }

        return new self($from, $till);
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'from' => $this->from,
            'till' => $this->till,
            'numberOfDays' => $this->from->diff($this->till)->days + 1,
        ];
    }
}
