<?php

namespace App\Infrastructure\Overview;

readonly class Pagination implements \JsonSerializable
{
    private function __construct(
        private int $offset = 0,
        private int $limit = 1000)
    {
        if ($this->limit < 1) {
            throw new \InvalidArgumentException('Invalid limit: '.$this->limit);
        }
    }

    public static function fromPageNumberAndSize(int $pageNumber, int $pageSize): self
    {
        return new self(($pageNumber - 1) * $pageSize, $pageSize);
    }

    public static function fromOffsetAndLimit(int $offset, int $limit): self
    {
        return new self($offset, $limit);
    }

    public static function default(): Pagination
    {
        return new self();
    }

    public static function all(): Pagination
    {
        return new self(0, 10000000);
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getPageNumber(): int
    {
        return (int) \floor($this->offset / $this->limit) + 1;
    }

    public function next(): Pagination
    {
        return new self($this->offset + $this->limit, $this->limit);
    }

    public function getPageSize(): int
    {
        return $this->limit;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'page' => $this->getPageNumber(),
            'size' => $this->getPageSize(),
        ];
    }
}
