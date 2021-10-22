<?php

namespace HyperDevs\kits\types;

use HyperDevs\kits\Kit;
use pocketmine\block\BlockIds;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\Player;
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
        return [Item::get(ItemIds::GOLDEN_APPLE, 0, 10)];
    }

    /**
     * @return array
     */
    public function getArmor() : array
    {
        return [
            Item::get(BlockIds::AIR),
            Item::get(ItemIds::AIR),
            Item::get(BlockIds::AIR),
            Item::get(ItemIds::AIR)
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