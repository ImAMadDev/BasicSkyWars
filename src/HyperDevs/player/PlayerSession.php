<?php

namespace HyperDevs\player;

use HyperDevs\Main;
use pocketmine\player\Player;
use pocketmine\Server;

class PlayerSession {

    public function __construct(public array $data)
    {
        
    }

    public function getName() : string
    {
        return $this->getData()['name'];
    }

    public function getPlayer() : ?Player
    {
        return Server::getInstance()->getPlayerByPrefix($this->getName());
    }

    public function getData() : array
    {
        return $this->data;
    }

    public function getJsonData() : string
    {
        return json_encode($this->data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING);
    }

    public function saveData() : void
    {
        if (file_exists(Main::getInstance()->getDataFolder() . 'players' . DIRECTORY_SEPARATOR . $this->getData()['name'] . '.json')) {
            file_put_contents(Main::getInstance()->getDataFolder() . 'players' . DIRECTORY_SEPARATOR . $this->getData()['name'] . '.json', $this->getJsonData());
        }
    }

    public function __destruct()
    {
        if (file_exists(Main::getInstance()->getDataFolder() . 'players' . DIRECTORY_SEPARATOR . $this->getData()['name'] . '.json')) {
            file_put_contents(Main::getInstance()->getDataFolder() . 'players' . DIRECTORY_SEPARATOR . $this->getData()['name'] . '.json', $this->getJsonData());
        }
    }
}