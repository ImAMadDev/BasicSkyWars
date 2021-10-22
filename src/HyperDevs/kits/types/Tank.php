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

class Tank extends Kit
{

    public function __construct()
    {
        $this->setName(TextFormat::colorize("&7Tank"));
        $this->setPermission("kit.tank");
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string|null $permission
     */
    public function setPermission(?string $permission): void
    {
        $this->permission = $permission;
    }

    /**
     * @return array
     */
    public function getItems() : array
    {
        $sword = Item::get(ItemIds::IRON_SWORD);
        $sword->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::SHARPNESS), 1));
        $block = Item::get(BlockIds::WOOD, 0, 16);
        return [$sword, $block];
    }

    /**
     * @return array
     */
    public function getArmor() : array
    {
        return [
            Item::get(ItemIds::IRON_HELMET),
            Item::get(ItemIds::IRON_CHESTPLATE),
            Item::get(ItemIds::IRON_LEGGINGS),
            Item::get(ItemIds::IRON_BOOTS)
        ];
    }

    /**
     * @inheritDoc
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