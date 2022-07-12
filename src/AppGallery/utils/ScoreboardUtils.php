<?php

namespace AppGallery\utils;

use AppGallery\Main;
use pocketmine\player\Player;

use pocketmine\network\mcpe\protocol\{RemoveObjectivePacket, SetDisplayObjectivePacket, SetScorePacket};
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;

class ScoreboardUtils {


    /**
     * @var array
     */
    protected static array $scoreboards = [];

    /**
     * @param Player $player
     * @param string $objectiveName
     * @param string $displayName
     */
    public function newScoreboard(Player $player, string $objectiveName, string $displayName) : void {
        if(isset(self::$scoreboards[$player->getName()])){
            unset(self::$scoreboards[$player->getName()]);
        }
        $pk = new SetDisplayObjectivePacket();
        $pk->displaySlot = "sidebar";
        $pk->objectiveName = $objectiveName;
        $pk->displayName = $displayName;
        $pk->criteriaName = "dummy";
        $pk->sortOrder = 0;
        $player->getNetworkSession()->sendDataPacket($pk);
        self::$scoreboards[$player->getName()] = $objectiveName;
    }

    /**
     * @param Player $player
     */
    public function removePrimary(Player $player) : void {
        if(isset(self::$scoreboards[$player->getName()])){
            $objectiveName = $this->getObjectiveName($player);
            $pk = new RemoveObjectivePacket();
            $pk->objectiveName = $objectiveName;
            $player->getNetworkSession()->sendDataPacket($pk);
            unset(self::$scoreboards[$player->getName()]);
        }
    }

    /**
     * @param Player $player
     * @param $key
     */
    public function remove(Player $player, $key) : void {
        if(isset(self::$scoreboards[$player->getName()])){
            $objectiveName = $this->getObjectiveName($player);
            $pk = new RemoveObjectivePacket();
            $pk->objectiveName = $objectiveName;
            $player->getNetworkSession()->sendDataPacket($pk);
            unset(self::$scoreboards[$player->getName()], $key);
        }
    }

    /**
     * @param Player $player
     * @param int $score
     * @param string|null $message
     */
    public function setLine(Player $player, int $score, ?string $message) : void {
        if(!isset(self::$scoreboards[$player->getName()])){
            $this->plugin->getLogger()->info("Error");
            return;
        }
        if($score > 15){
            $this->plugin->getLogger()->info("Error, you exceeded the limit of parameters 1-15");
            return;
        }
        $objectiveName = $this->getObjectiveName($player);
        $entry = new ScorePacketEntry();
        $entry->objectiveName = $objectiveName;
        $entry->type = $entry::TYPE_FAKE_PLAYER;
        $entry->customName = $message;
        $entry->score = $score;
        $entry->scoreboardId = $score;
        $pk = new SetScorePacket();
        $pk->type = $pk::TYPE_CHANGE;
        $pk->entries[] = $entry;
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    /**
     * @param Player $player
     * @return string|null
     */
    public function getObjectiveName(Player $player) : ?string {
        return isset(self::$scoreboards[$player->getName()]) ? self::$scoreboards[$player->getName()] : null;
    }
}





