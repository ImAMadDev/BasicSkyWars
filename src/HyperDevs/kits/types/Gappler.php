<?php

namespace HyperDevs\kits\types;

use HyperDevs\kits\Kit;
use pocketmine\item\{
    ItemFactory,
    ItemIds};
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class Gappler extends Kit
{

    public function __construct()
    {
        $this->setName(TextFormat::colorize("&6Gappler"));
        $this->setPermission("kit.gap");
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param null $permission
     */
    public function setPermission($permission): void
    {
        $this->permission = $permission;
    }

    /**
     * @return array
     */
    public function getItems() : array
    {
        return [ItemFactory::getInstance()->get(ItemIds::GOLDEN_APPLE, 0, 10)];
    }

    /**
     * @return array
     */
    public function getArmor() : array
    {
        return [
            ItemFactory::air(),
            ItemFactory::air(),
            ItemFactory::air(),
            ItemFactory::air()
        ];
    }

    /**
     * @param Player $player
     */
    public function sendTo(Player $player): void
    {
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getInventory()->setContents($this->getItems());
        $player->getArmorInventory()->setContents($this->getArmor());
    }
}