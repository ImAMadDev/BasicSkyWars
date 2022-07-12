<?php

namespace AppGallery\models\cage;

use Generator;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

final class CageCreatorSession{

    protected Player $player;
    protected Position $position1;
    protected Position $position2;

    public function __construct(Player $player){
        $this->player = $player;
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player{
        return $this->player;
    }

    /**
     * @param Position $position1
     */
    public function setPosition1(Position $position1): void{
        $this->position1 = $position1;
    }

    /**
     * @param Position $position2
     */
    public function setPosition2(Position $position2): void{
        $this->position2 = $position2;
    }

    public function copy(): void{
        $this->generate();
    }

    private function generate(): Generator{
        $pos1 = $this->position1;
        $pos2 = $this->position2;
        $pos = new Vector3(min($pos1->x, $pos2->x), min($pos1->y, $pos2->y), min($pos1->z, $pos2->z));
        for($x = 0; $x <= abs($pos1->x - $pos2->x); $x++){
            for($y = 0; $y <= abs($pos1->y - $pos2->y); $y++){
                for($z = 0; $z <= abs($pos1->z - $pos2->z); $z++){
                    $block = $this->getPlayer()->getWorld()->getBlock($pos->add($x, $y, $z));
                    yield ["Block" => $block->getId().":".$block->getMeta(),
                        "x" => $block->getPosition()->getX(),
                        "y" => $block->getPosition()->getY(),
                        "z" => $block->getPosition()->getZ()];
                }
            }
        }
        $this->getPlayer()->sendMessage(TextFormat::GREEN . "Copiado");
    }

    public function save(): void{

    }

}