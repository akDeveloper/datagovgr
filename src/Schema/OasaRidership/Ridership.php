<?php

declare(strict_types=1);

namespace Gov\Data\Schema\OasaRidership;

use DateTime;

final class Ridership
{
    public function __construct(
        private readonly int $dv_validations,
        private readonly string $dv_agency,
        private readonly string $dv_platenum_station,
        private readonly ?string $dv_route,
        private readonly ?int $routes_per_hour,
        private readonly DateTime $load_dt,
        private readonly DateTIme $date_hour
    ) {
    }

    public function getValidations(): int
    {
        return $this->dv_validations;
    }

    public function getAgency(): string
    {
        return $this->dv_agency;
    }

    public function getPlatenumStation(): string
    {
        return $this->dv_platenum_station;
    }

    public function getRoute(): ?string
    {
        return $this->dv_route;
    }

    public function getRoutesPerHour(): int
    {
        return $this->routes_per_hour;
    }

    public function getLoadTime(): DateTime
    {
        return $this->load_dt;
    }

    public function getDateHour(): DateTime
    {
        return $this->date_hour;
    }
}
