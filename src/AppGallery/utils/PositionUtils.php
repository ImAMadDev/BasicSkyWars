<?php

namespace AppGallery\utils;

use pocketmine\world\Position;
use pocketmine\math\Vector3;
use pocketmine\Server;

class PositionUtils
{

    /**
     * @param Position $position
     * @return string
     */
    public static function posToStr(Position $position) : string
    {
        return implode(":", [$position->getFloorX(), $position->getFloorY(), $position->getFloorZ(), $position->getWorld()->getFolderName()]);
    }

    /**
     * @param Vector3 $vector3
     * @return string
     */
    public static function vecToStr(Vector3 $vector3) : string
    {
        return implode(":", [$vector3->getFloorX(), $vector3->getFloorY(), $vector3->getFloorZ()]);
    }

    /**
     * @param string $vector
     * @return Vector3
     */
    public static function strToVec(string $vector) : Vector3
    {
        $vec = explode(":", $vector);
        if (isset($vec)) {
            return new Vector3((int)$vec[0], (int)$vec[1], (int)$vec[2]);
        }
        return new Vector3(0, 0, 0);
    }

    /**
     * @param string $position
     * @return Position
     */
    public static function strToPos(string $position) : Position
    {
        $pos = explode(":", $position);
        $level = Server::getInstance()->getWorldManager()->getWorldByName($pos[3]);
        return new Position((int)$pos[0], (int)$pos[1], (int)$pos[2], $level);
    }

}