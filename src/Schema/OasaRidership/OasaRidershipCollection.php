<?php

declare(strict_types=1);

namespace Gov\Data\Schema\OasaRidership;

use Gov\Data\Schema\ApiCollection;

final class OasaRidershipCollection extends ApiCollection
{
    public function current()
    {
        return current($this->items);
    }
}
