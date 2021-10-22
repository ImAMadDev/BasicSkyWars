<?php

namespace HyperDevs\kits;

use pocketmine\Player;

abstract class Kit
{

    /**
     * @var string
     */
    public string $name;

    /**
     * @var string|null
     */
    public ?string $permission;

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getPermission() : ?string
    {
        return $this->permission;
    }

    /**
     * @param Player $player
     */
    abstract public function sendTo(Player $player) : void;

}