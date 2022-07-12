<?php

namespace AppGallery\arena;

use AppGallery\utils\ChestContent;
use AppGallery\utils\MessagesUtils;
use pocketmine\player\Player;

final class ArenaSettings
{

    const TYPE_NORMAL = 0;
    const TYPE_OP = 1;

    /**
     * @var Arena
     */
    private Arena $arena;

    /**
     * @var int
     */
    private int $op_votes = 0;

    /**
     * @var int
     */
    private int $normal_votes = 0;

    /**
     * @var array
     */
    public $vote_cache = [];

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
     * @return int
     */
    public function getNormalVotes(): int
    {
        return $this->normal_votes;
    }

    /**
     * @return int
     */
    public function getOpVotes(): int
    {
        return $this->op_votes;
    }

    /**
     * @return string
     */
    public function getMostVoted() : string
    {
        if ($this->getOpVotes() > $this->getNormalVotes()) return ChestContent::TYPE_OVERPOWERED; else return ChestContent::TYPE_NORMAL;
    }

    /**
     * @param Player $player
     */
    public function addOpVote(Player $player) : void
    {
        if ($this->hasPlayerVote($player) == true){
            $player->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("already_vote"));
            return;
        }
        $this->op_votes += 1;
        $this->vote_cache[$player->getName()] = ArenaSettings::TYPE_OP;
        $player->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("vote_confirm_for", ["vote" => "OverPower"]));
    }

    /**
     * @param Player $player
     */
    public function addNormalVote(Player $player) : void
    {
        if ($this->hasPlayerVote($player) == true){
            $player->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("already_vote"));
            return;
        }
        $this->normal_votes += 1;
        $this->vote_cache[$player->getName()] = ArenaSettings::TYPE_NORMAL;
        $player->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("vote_confirm_for", ["vote" => "Normal"]));
    }

    /**
     * @param Player $player
     * @return int
     */
    public function getPlayerVote(Player $player) : int

    {
        return $this->vote_cache[$player->getName()] ?? -1;
    }

    public function removePlayerVote(Player $player) : void
    {
        if($this->getPlayerVote($player) === -1) return;
        if($this->getPlayerVote($player) == 0){
            $this->normal_votes--;
            unset($this->vote_cache[$player->getName()]);
        } else {
            $this->op_votes--;
            unset($this->vote_cache[$player->getName()]);
        }
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function hasPlayerVote(Player $player) : bool
    {
        return isset($this->vote_cache[$player->getName()]);
    }

    public function reset() : void
    {
        $this->vote_cache = [];
        $this->normal_votes = 0;
        $this->op_votes = 0;
        $this->chestType = ArenaSettings::TYPE_NORMAL;
    }

}