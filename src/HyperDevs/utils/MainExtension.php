<?php

namespace HyperDevs\utils;

use HyperDevs\Main;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;

class MainExtension
{

    /**
     * @var Main
     */
    public Main $main;

    /**
     * @param Main $main
     */
    public function  __construct(Main $main)
    {
       $this->main = $main;
    }

    /**
     * @return Main
     */
    public function getMain() : Main
    {
        return $this->main;
    }

    /**
     * @return string
     */
    public function getDataFolder() : string
    {
        return $this->getMain()->getDataFolder();
    }

    /**
     * @return Server
     */
    public function getServer() : Server
    {
        return $this->getMain()->getServer();
    }

    /**
     * @return TaskScheduler
     */
    public function getScheduler() : TaskScheduler
    {
        return $this->getMain()->getScheduler();
    }

}