<?php

namespace App\Infrastructure\Overview;

class Overview implements \JsonSerializable
{
    /** @var \App\Infrastructure\Overview\Item[] */
    private array $items = [];

    private function __construct(
        private readonly Pagination $pagination,
        private readonly int $total)
    {
    }

    public static function empty(
        Pagination $pagination,
        int $total = 0): Overview
    {
        return new self($pagination, $total);
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'pagination' => $this->getPagination(),
            'total' => $this->getTotal(),
            'items' => $this->items,
        ];
    }

    public function addItem(Item $item): void
    {
        $this->items[] = $item;
    }

    /**
     * @return \App\Infrastructure\Overview\Item[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getPagination(): Pagination
    {
        return $this->pagination;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}
