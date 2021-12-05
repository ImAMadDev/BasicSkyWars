<?php

namespace HyperDevs\utils;

use HyperDevs\arena\Arena;
use HyperDevs\Main;
use pocketmine\level\Level;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use ZipArchive;

// Thanks to https://github.com/GamakCZ/SkyWars/blob/master/SkyWars/src/vixikhd/skywars/arena/MapReset.php
class BackupUtils extends MainExtension
{

    /**
     * @var Arena
     */
    public Arena $arena;

    /**
     * @param Main $main
     * @param Arena $arena
     */
    public function __construct(Main $main, Arena $arena)
    {
        parent::__construct($main);
        $this->arena = $arena;
    }

    public function saveMap() : void
    {
        $this->arena->getWold()->save(true);
        $levelPath = $this->getServer()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $this->arena->getMap();
        $zipPath = $this->getDataFolder() . "backups" . DIRECTORY_SEPARATOR . $this->arena->getMap() . ".zip";

        $zip = new ZipArchive();

        if(is_file($zipPath)) {
            @unlink($zipPath);
        }

        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(realpath($levelPath)), RecursiveIteratorIterator::LEAVES_ONLY);

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            if($file->isFile()) {
                $filePath = $file->getPath() . DIRECTORY_SEPARATOR . $file->getBasename();
                $localPath = substr($filePath, strlen($this->getServer()->getDataPath() . "worlds"));
                $zip->addFile($filePath, $localPath);
            }
        }

        $zip->close();
    }
    public function loadMap(bool $justSave = false): ?Level {
        $folderName = $this->arena->getMap();
        if(!$this->getServer()->getWorldManager()->isLevelGenerated($folderName)) {
            return null;
        }

        if($this->getServer()->getWorldManager()->isLevelLoaded($folderName)) $this->getServer()->getWorldManager()->getWorldByName($folderName)->unload(true);

        $zipPath = $this->getDataFolder() . "backups" . DIRECTORY_SEPARATOR . $this->arena->getMap() . ".zip";

        if(!file_exists($zipPath)) {
            $this->getServer()->getLogger()->error("Could not reload arena ($folderName). File wasn't found, try save level in setup mode.");
            return null;
        }

        $zipArchive = new ZipArchive();
        $zipArchive->open($zipPath);
        $zipArchive->extractTo($this->getServer()->getDataPath() . "worlds");
        $zipArchive->close();

        if($justSave) {
            return null;
        }

        $this->getServer()->getWorldManager()->loadLevel($folderName);
        return $this->getServer()->getWorldManager()->getWorldByName($folderName);
    }
}