<?php

namespace HyperDevs\event;

use HyperDevs\arena\Arena;
use pocketmine\player\Player;

class PlayerWinArenaEvent extends ArenaEvent
{

    public function __construct(Player $player, Arena $arena)
    {
        parent::__construct($player, $arena);
    }

}