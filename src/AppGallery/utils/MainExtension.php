<?php

namespace AppGallery\utils;

use AppGallery\Main;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;

class MainExtension{

    /** @return Main */
    public function getMain(): Main{
        return Main::getInstance();
    }

    /**
     * @return string
     */
    public function getDataFolder(): string{
        return $this->getMain()->getDataFolder();
    }

    /**
     * @return Server
     */
    public function getServer(): Server{
        return $this->getMain()->getServer();
    }

    /**
     * @return TaskScheduler
     */
    public function getScheduler(): TaskScheduler{
        return $this->getMain()->getScheduler();
    }
}