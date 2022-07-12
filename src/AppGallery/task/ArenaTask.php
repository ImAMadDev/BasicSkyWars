<?php

namespace AppGallery\task;

use AppGallery\arena\Arena;
use AppGallery\arena\models\ArenaStatus;
use AppGallery\event\PlayerWinArenaEvent;
use AppGallery\utils\MessagesUtils;
use pocketmine\player\GameMode;
use pocketmine\scheduler\Task;

class ArenaTask extends Task
{

    /**
     * @var Arena
     */
    private Arena $arena;

    /**
     * @param Arena $arena
     */
    public function __construct(Arena $arena)
    {
        $this->arena = $arena;
    }

    /**
     * @return Arena
     */
    public function getArena(): Arena
    {
        return $this->arena;
    }

    /**
     * @inheritDoc
     */
    public function onRun(): void{
        if(!$this->getArena() instanceof Arena) $this->onCancel();
        switch ($this->getArena()->getStatus()){
            case ArenaStatus::WAITING:
                if(count($this->getArena()->getPlayers()) === 2){
                    $this->getArena()->setStatus(ArenaStatus::RUNNING);
                    $this->getArena()->start();
                }
                break;
            case ArenaStatus::RUNNING:
                if(count($this->getArena()->getPlayers()) === 1 && $this->getArena()->runningTime <= 5){
                    $this->getArena()->setStatus(ArenaStatus::WAITING);
                }elseif(count($this->getArena()->getPlayers()) > 1){
                    $this->getArena()->runningTime++;
                    $this->getArena()->sendAnnouncement("Current playing: " . count($this->getArena()->getPlayers()) . " Playing time: " . gmdate("i:s", $this->getArena()->runningTime),
                        Arena::ANNOUNCEMENT_TYPE_TIP);
                    if(0 == ($this->getArena()->runningTime % 30)){
                        $this->getArena()->refillChest();
                        $this->getArena()->sendAnnouncement("Chest refilled", Arena::ANNOUNCEMENT_TYPE_TITLE);
                        $this->getArena()->sendAnnouncement("All Chest has been refilled", Arena::ANNOUNCEMENT_TYPE_MESSAGE);
                    }
                } else {
                    $this->getArena()->setStatus(ArenaStatus::RESETTING);
                }
                break;
            case ArenaStatus::RESETTING:
                $this->getArena()->resetTime--;
                if(0 == count($this->getArena()->getPlayers())) {
                    $this->getArena()->reset();
                } else {
                    if ($this->getArena()->resetTime > 0 && $this->getArena()->resetTime <= 10) {
                        $this->getArena()->sendAnnouncement("&c&r," . MessagesUtils::getMessage("reset_arena", ["time" => $this->getArena()->resetTime]), Arena::ANNOUNCEMENT_TYPE_TITLE);
                        $this->getArena()->sendAnnouncement(MessagesUtils::getMessage("reset_arena", ["time" => $this->getArena()->resetTime]), Arena::ANNOUNCEMENT_TYPE_TITLE);
                    }
                    if($this->getArena()->resetTime == 9){
                        if($this->getArena()->getWinner() !== null) {
                            $ev = new PlayerWinArenaEvent($this->getArena()->getWinner(), $this->getArena());
                            $ev->call();
                        }
                    } elseif($this->getArena()->resetTime <= 0){
                        foreach ($this->getArena()->getWold()->getPlayers() as $player){
                            $player->setHealth($player->getMaxHealth());
                            $player->getInventory()->clearAll();
                            $player->getArmorInventory()->clearAll();
                            $player->getCursorInventory()->clearAll();
                            $player->setGamemode(GameMode::SURVIVAL());
                            $player->teleport($this->getArena()->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                        }
                    }
                }
                break;
            default:
                break;
        }
    }
}