<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Values;

/**
 * Point — a named 2D or 3D spatial point.
 */
final readonly class Point
{
    public function __construct(
        public float|int $x,
        public float|int $y,
        public float|int $z = 0,
        public string    $label = '',
    ) {}

    public function toCoordinate() : Coordinate
    {
        return new Coordinate($this->x, $this->y, $this->z);
    }

    public function distanceTo(self $other) : float
    {
        $dx = $this->x - $other->x;
        $dy = $this->y - $other->y;
        $dz = $this->z - $other->z;

        return sqrt($dx * $dx + $dy * $dy + $dz * $dz);
    }

    /**
     * @return array{x: float|int, y: float|int, z: float|int, label: string}
     */
    public function toArray() : array
    {
        return ['x' => $this->x, 'y' => $this->y, 'z' => $this->z, 'label' => $this->label];
    }
}
