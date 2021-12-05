<?php

namespace HyperDevs\kits\types;

use HyperDevs\kits\Kit;
use pocketmine\block\BlockIds;
use pocketmine\block\BlockLegacyIds;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;
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
        $axe = ItemFactory::getInstance()->get(ItemIds::IRON_AXE);
        $axe->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::EFFICIENCY), 4));
        $axe->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::SHARPNESS), 1));
        $block = ItemFactory::getInstance()->get(BlockLegacyIds::WOOD, 0, 16);
        return [$axe, $block];
    }

    /**
     * @return array
     */
    public function getArmor() : array
    {
        return [
            ItemFactory::air(),
            ItemFactory::getInstance()->get(ItemIds::IRON_CHESTPLATE),
            ItemFactory::air(),
            ItemFactory::getInstance()->get(ItemIds::IRON_BOOTS)
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