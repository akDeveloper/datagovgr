<?php

declare(strict_types=1);

namespace Gov\Data\Schema\RoadTrafficAttica;

use DateTime;

final class Traffic
{
    public function __construct(
        private readonly string $deviceid,
        private readonly int $countedcars,
        private readonly DateTime $appprocesstime,
        private readonly string $road_name,
        private readonly ?string $road_info,
        private readonly float $average_speed
    ) {
    }

    public function getDeviceId(): string
    {
        return $this->deviceid;
    }

    public function getCountedCars(): int
    {
        return $this->countedcars;
    }

    public function getProcessTime(): DateTime
    {
        return $this->appprocesstime;
    }

    public function getRoadName(): string
    {
        return $this->road_name;
    }

    public function getRoadInfo(): ?string
    {
        return $this->road_info;
    }

    public function getAverageSpeed(): float
    {
        return $this->average_speed;
    }
}
