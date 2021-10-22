<?php

namespace HyperDevs\sessions;

use HyperDevs\arena\Arena;
use HyperDevs\Main;
use HyperDevs\utils\MainExtension;
use HyperDevs\utils\MessagesUtils;
use HyperDevs\utils\PositionUtils;
use pocketmine\level\Level;
use pocketmine\Player;

class CreatorSession extends MainExtension
{
    /**
     * @var Player
     */
    public Player $player;

    /**
     * @var array
     */
    public array $data = [];

    /**
     * @param Main $main
     * @param Player $player
     */
    public function __construct(Main $main, Player $player)
    {
        parent::__construct($main);
        $this->player = $player;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @param int $slotNumber
     */
    public function setSlot(int $slotNumber) : void
    {
        if ($slotNumber >= 13){
            $this->getPlayer()->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("maximum_slots"));
            return;
        }
        $this->data['slots'][$slotNumber] = PositionUtils::posToStr($this->getPlayer()->getPosition());
        $this->getPlayer()->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("slot_added", ["number" => $slotNumber]));
    }

    /**
     * @param string $name
     */
    public function setArenaName(string $name) : void
    {
        $this->data['name'] = $name;
        $this->getPlayer()->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("arena_name", ["name" => $name]));
    }

    /**
     * @param Level $world
     */
    public function setMap(Level $world) : void
    {
        $this->data["mapName"] = $world->getFolderName();
        $this->getPlayer()->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("map_name", ["name" => $world->getFolderName()]));
    }

    /**
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * @param bool $pedestals
     */
    public function setPedestals(bool $pedestals) : void
    {
        $this->data["pedestals"] = $pedestals;
    }

     // finish arena setup
    public function finishSetup() : void
    {
        $this->data["status"] = Arena::STATUS_DISABLED;
        $this->getMain()->createArena($this);
    }

}