<?php

namespace HyperDevs\task;

use HyperDevs\arena\Arena;
use HyperDevs\event\PlayerWinArenaEvent;
use HyperDevs\utils\MessagesUtils;
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
    public function onRun(int $currentTick)
    {
        if(!$this->getArena() instanceof Arena) $this->getArena()->getScheduler()->cancelTask($this->getTaskId());
        switch ($this->getArena()->getStatus()){
            case Arena::STATUS_WAITING:
                if(count($this->getArena()->getPlayers()) === 2){
                    $this->getArena()->setStatus(Arena::STATUS_RUNNING);
                    $this->getArena()->start();
                }
                break;
            case Arena::STATUS_RUNNING:
                if(count($this->getArena()->getPlayers()) === 1 && $this->getArena()->runningTime <= 5){
                    $this->getArena()->setStatus(Arena::STATUS_WAITING);
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
                    $this->getArena()->setStatus(Arena::STATUS_RESETTING);
                }
                break;
            case Arena::STATUS_RESETTING:
                $this->getArena()->resetTime--;
                if(0 == count($this->getArena()->getPlayers())) {
                    $this->getArena()->reset();
                } else {
                    if ($this->getArena()->resetTime > 0 and $this->getArena()->resetTime <= 10) {
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
                            $player->setGamemode($player::SURVIVAL);
                            $player->teleport($this->getArena()->getServer()->getDefaultLevel()->getSpawnLocation());
                        }
                    }
                }
                break;
            default:
                break;
        }
    }
}