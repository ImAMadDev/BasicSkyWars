<?php

namespace AppGallery\models\cage;

use AppGallery\Main;
use pocketmine\utils\SingletonTrait;

class CageFactory{
    use SingletonTrait;
    /** @var Cage[] */
    private array $cages = [];
    private $pos1;
    private $pos2;

    public function initialize(): void{
        foreach (glob(Main::getInstance()->getDataFolder() . "cages/" . "*.yml") as $file) {

        }
    }

    public function createCage(string $name, string $permission, array $blocks): void{

    }

}