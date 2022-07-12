<?php

namespace AppGallery\models\cage;

use pocketmine\block\BlockFactory;
use pocketmine\world\Position;

final class Cage{

    protected array $blocks;
    protected string $name;

    public function __construct(string $name, array $blocks){
        $this->name = $name;
        $this->blocks = $blocks;
    }

    public function paste(Position $position): void{
        foreach ($this->blocks as $block) {
            $b = explode(":", $block['Block']);
            $position->getWorld()->setBlockAt($block['x'], $block['y'], $block['z'], BlockFactory::getInstance()->get($b[0], $b[1]));
        }
    }

}