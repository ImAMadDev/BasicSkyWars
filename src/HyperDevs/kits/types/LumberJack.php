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

class LumberJack extends Kit
{

    public function __construct()
    {
        $this->setName(TextFormat::colorize("&3LumberJack"));
        $this->setPermission("kit.jack");
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
        $axe = Item::get(ItemIds::IRON_AXE);
        $axe->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::EFFICIENCY), 4));
        $axe->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::SHARPNESS), 1));
        $block = Item::get(BlockIds::WOOD, 0, 16);
        return [$axe, $block];
    }

    /**
     * @return array
     */
    public function getArmor() : array
    {
        return [
            Item::get(BlockIds::AIR),
            Item::get(ItemIds::IRON_CHESTPLATE),
            Item::get(BlockIds::AIR),
            Item::get(ItemIds::IRON_BOOTS)
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