<?php

namespace AppGallery\sessions;

use AppGallery\arena\Arena;
use AppGallery\arena\models\ArenaStatus;
use AppGallery\Main;
use AppGallery\utils\MainExtension;
use AppGallery\utils\MessagesUtils;
use AppGallery\utils\PositionUtils;
use pocketmine\world\World;
use pocketmine\player\Player;

class CreatorSession extends MainExtension{
    /** @var Player */
    public Player $player;

    /** @var array */
    public array $data = [];

    /**
     * @param Player $player
     */
    public function __construct(Player $player){
        $this->player = $player;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player{
        return $this->player;
    }

    /**
     * @param int $slotNumber
     */
    public function setSlot(int $slotNumber): void{
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
    public function setArenaName(string $name): void{
        $this->data['name'] = $name;
        $this->getPlayer()->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("arena_name", ["name" => $name]));
    }

    /**
     * @param World $world
     */
    public function setMap(World $world): void{
        $this->data["mapName"] = $world->getFolderName();
        $this->getPlayer()->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("map_name", ["name" => $world->getFolderName()]));
    }

    /**
     * @return array
     */
    public function getData(): array{
        return $this->data;
    }

    /**
     * @param bool $pedestals
     */
    public function setPedestals(bool $pedestals): void{
        $this->data["pedestals"] = $pedestals;
    }

     // finish arena setup
    public function finishSetup(): void{
        $this->data["status"] = ArenaStatus::DISABLED;
        $this->getMain()->createArena($this);
    }

}