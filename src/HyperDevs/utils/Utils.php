<?php

namespace HyperDevs\utils;

use formapi\SimpleForm;
use HyperDevs\arena\Arena;
use HyperDevs\kits\Kit;
use HyperDevs\Main;
use pocketmine\player\Player;

class Utils
{

    public static function getKitsForm(Player $player, Arena $arena) : void
    {
        $form = new SimpleForm(function (Player $player, $data) use($arena){
            if($data === null) return;
            $kit = Main::getInstance()->getKit($data);
            if($kit instanceof Kit){
                if(!($player->hasPermission($kit->getPermission()))){
                    $player->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("no_permissions"));
                    return;
                }
                $arena->selectKit($player, $kit);
                $player->sendMessage(MessagesUtils::PREFIX. MessagesUtils::getMessage("select_kit", ["kit" => $kit->getName()]));
            } else{
                $player->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("kit_not_exist"));
            }
        });
        $form->setTitle(MessagesUtils::PREFIX . "KITS");
        foreach (Main::getInstance()->getKits() as $kit) $form->addButton($kit->getName(), -1,"", $kit->getName());
        $player->sendForm($form);
    }

}