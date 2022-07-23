<?php

declare(strict_types=1);

namespace Gov\Data\Schema\RoadTrafficAttica;

use Gov\Data\Schema\ApiCollection;

final class RoadTrafficAtticaCollection extends ApiCollection
{
    public function current(): Traffic
    {
        return current($this->items);
    }
}
