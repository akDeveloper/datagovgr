<?php

declare(strict_types=1);

namespace Gov\Data\Transformer;

use DateTime;
use Gov\Data\Schema\RoadTrafficAttica\Traffic;

class RoadTrafficAtticaTransformer
{
    public function transform(array $item)
    {
        return new Traffic(
            $item['deviceid'],
            $item['countedcars'],
            DateTime::createFromFormat("Y-m-d\TH:i:s\Z", $item['appprocesstime']),
            $item['road_name'],
            $item['road_info'],
            $item['average_speed']
        );
    }
}
