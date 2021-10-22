<?php

namespace HyperDevs\utils;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\Potion;

final class ChestContent
{

    const TYPE_NORMAL = "normal";

    const TYPE_OVERPOWERED = "op";

    /**
     * @var array
     */
    public static array $items_normal = [
        [ItemIds::IRON_AXE],
        [ItemIds::IRON_PICKAXE],
        [ItemIds::GOLDEN_APPLE, 0, 1],
        [ItemIds::STEAK, 0, 3],
        [ItemIds::IRON_HELMET],
        [ItemIds::IRON_CHESTPLATE],
        [ItemIds::IRON_LEGGINGS],
        [ItemIds::IRON_BOOTS],
        [ItemIds::CHAIN_HELMET],
        [ItemIds::CHAIN_CHESTPLATE],
        [ItemIds::CHAIN_LEGGINGS],
        [ItemIds::CHAIN_BOOTS],
        [ItemIds::WOOD, 0, "random" => [16, 27]],
        [ItemIds::STONE, 0, 20],
        [ItemIds::EGG, 0, 16],
        [ItemIds::SPLASH_POTION, Potion::STRONG_REGENERATION],
        [ItemIds::SPLASH_POTION, Potion::STRONG_SWIFTNESS],
        [ItemIds::IRON_SWORD, 0, 1, "enchantments" => [["id" => Enchantment::SHARPNESS, "level" => 1]]],
    ];

    public static array $items_op = [
        [ItemIds::IRON_AXE],
        [ItemIds::IRON_PICKAXE],
        [ItemIds::GOLDEN_APPLE, 0, 2],
        [ItemIds::STEAK, 0, 6],
        [ItemIds::IRON_HELMET],
        [ItemIds::IRON_CHESTPLATE],
        [ItemIds::IRON_LEGGINGS],
        [ItemIds::IRON_BOOTS],
        [ItemIds::DIAMOND_HELMET],
        [ItemIds::DIAMOND_CHESTPLATE],
        [ItemIds::DIAMOND_LEGGINGS],
        [ItemIds::DIAMOND_BOOTS],
        [ItemIds::WOOD, 0, 16],
        [ItemIds::STONE, 0, 20],
        [ItemIds::EGG, 0, 16],
        [ItemIds::SPLASH_POTION, Potion::STRONG_REGENERATION],
        [ItemIds::SPLASH_POTION, Potion::STRONG_SWIFTNESS],
        [ItemIds::IRON_SWORD, 0, 1, "enchantments" => [["id" => Enchantment::SHARPNESS, "level" => 3]]],
        [ItemIds::DIAMOND_SWORD, 0, 1, "enchantments" => [["id" => Enchantment::SHARPNESS, "level" => 3]]],
    ];

    /**
     * @param int $items_count
     * @param string $type
     * @return array
     */
    public final function getItems(int $items_count, string $type = self::TYPE_NORMAL) : array
    {
        $items = [];
        $contents = self::getContents($type);
        shuffle($contents);
        foreach($contents as $i){
            $count = $i[2] == "random" ? rand($i[2][0], $i[2][1]) : $i[2];
            $item = Item::get($i[0], ($i[1] ?? 0), ($count ?? 1));
            if (isset($i["enchantments"])) foreach ($i["enchantments"] as $ench){
                if($ench["level"] == "random") $ench["level"] = rand(1, 2);
                $item->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment($ench["id"]), $ench["level"]));
            }
            if(count($items) < $items_count) $items[] = $item;
        }
        return $items;
    }

    /**
     * @param string $type
     * @return array
     */
    public static function getContents(string $type) : array
    {
        if ($type != self::TYPE_OVERPOWERED) return self::$items_normal; else return self::$items_op;
    }
}