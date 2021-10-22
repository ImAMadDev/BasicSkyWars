<?php

namespace HyperDevs\arena;

use pocketmine\level\Position;

class SpawnCache
{

    /**
     * @var Arena
     */
    protected Arena $arena;

    /**
     * @var Position
     */
    protected Position $position;

    /**
     * @var bool|string
     */
    protected bool | string $inUse = false;

    /**
     * @param Arena $arena
     * @param Position $position
     */
    public function __construct(Arena $arena, Position $position)
    {
        $this->arena = $arena;
        $this->position = $position;
    }

    /**
     * @return Position
     */
    public function getPosition(): Position
    {
        return $this->position;
    }

    /**
     * @return Arena
     */
    public function getArena(): Arena
    {
        return $this->arena;
    }

    /**
     * @return bool|string
     */
    public function getInUse(): bool|string
    {
        return $this->inUse;
    }

    /**
     * @param bool|string $inUse
     */
    public function setInUse(bool | string $inUse): void
    {
        $this->inUse = $inUse;
    }
}