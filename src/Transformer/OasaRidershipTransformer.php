<?php

declare(strict_types=1);

namespace Gov\Data\Transformer;

use DateTime;
use Gov\Data\Schema\OasaRidership\Ridership;

class OasaRidershipTransformer
{
    public function transform(array $item): Ridership
    {
        return new Ridership(
            $item['dv_validations'],
            $item['dv_agency'],
            $item['dv_platenum_station'],
            $item['dv_route'],
            $item['routes_per_hour'],
            DateTime::createFromFormat("Y-m-d\TH:i:s\Z", $item['load_dt']),
            DateTime::createFromFormat("Y-m-d\TH:i:s\Z", $item['date_hour'])
        );
    }
}
