<?php

namespace HyperDevs\kits\types;

use HyperDevs\kits\Kit;
use pocketmine\block\BlockLegacyIds;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\{
    ItemFactory,
    ItemIds};
use pocketmine\player\Player;
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
        $sword = ItemFactory::getInstance()->get(ItemIds::IRON_SWORD);
        $sword->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::SHARPNESS), 1));
        $block = ItemFactory::getInstance()->get(BlockLegacyIds::WOOD, 0, 16);
        return [$sword, $block];
    }

    /**
     * @return array
     */
    public function getArmor() : array
    {
        return [
            ItemFactory::getInstance()->get(ItemIds::IRON_HELMET),
            ItemFactory::getInstance()->get(ItemIds::IRON_CHESTPLATE),
            ItemFactory::getInstance()->get(ItemIds::IRON_LEGGINGS),
            ItemFactory::getInstance()->get(ItemIds::IRON_BOOTS)
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