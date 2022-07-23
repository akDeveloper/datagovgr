<?php

declare(strict_types=1);

namespace Gov\Data\Schema;

abstract class ApiCollection implements Collection
{
    public function __construct(protected array $items)
    {
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function key(): int
    {
        return key($this->items);
    }

    public function next(): void
    {
        next($this->items);
    }

    public function rewind(): void
    {
        rewind($this->items);
    }

    public function valid(): bool
    {
        return key($this->items) !== null;
    }
}
