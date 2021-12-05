<?php

namespace HyperDevs\event;

use HyperDevs\arena\Arena;
use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

class ArenaEvent extends PlayerEvent
{

    /**
     * @var Arena
     */
    private Arena $arena;

    /**
     * @param Player $player
     * @param Arena $arena
     */
    public function __construct(Player $player, Arena $arena)
    {
        $this->player = $player;
        $this->arena = $arena;
    }

    /**
     * @return Arena
     */
    public function getArena() : Arena
    {
        return $this->arena;
    }

}