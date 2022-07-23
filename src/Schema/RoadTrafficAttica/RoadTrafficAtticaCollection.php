<?php

declare(strict_types=1);

namespace Gov\Data\Schema\RoadTrafficAttica;

use Iterator;
use Countable;
use ArrayAccess;

final class RoadTrafficAtticaCollection implements Countable, Iterator
{
    public function __construct(private array $items)
    {
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function current(): Traffic
    {
        return current($this->items);
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
