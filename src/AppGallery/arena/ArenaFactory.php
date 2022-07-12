<?php

namespace AppGallery\arena;

use AppGallery\Main;
use AppGallery\sessions\CreatorSession;
use AppGallery\utils\MainExtension;
use AppGallery\utils\MessagesUtils;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\Filesystem;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;

class ArenaFactory extends MainExtension{
    use SingletonTrait;
    /** @var Arena[] */
    private static array $arenas = [];

    public function initialize(): void{
        if(!(is_dir($this->getDataFolder() . "arenas/"))) @mkdir($this->getDataFolder() . "arenas");
        if (!(is_dir($this->getDataFolder() . "backups/"))) @mkdir($this->getDataFolder() . "backups/");
        foreach (glob($this->getDataFolder() . "arenas/" . "*.yml") as $file) {
            if (!($this->getArena(basename($file, ".yml")) instanceof Arena)){
                $config = yaml_parse(Config::fixYAMLIndexes(file_get_contents($file)));
                self::$arenas[basename($file, ".yml")] = new Arena(Main::getInstance(), $config);
            }
        }
        Main::getInstance()->getLogger()->info(sprintf("%s%sNumber of arenas loaded: %s", MessagesUtils::PREFIX, TextFormat::GREEN, count($this->getArenas())));
    }

    /**
     * @param string $name
     * @return Arena|null
     */
    public function getArena(string $name): ?Arena{
        return self::$arenas[$name] ?? null;
    }

    /**
     * @return Arena[]
     */
    public function getArenas(): array{
        return self::$arenas;
    }

    /**
     * @param Player $player
     * @return Arena|null
     */
    public function getArenaByPlayer(Player $player): ? Arena{
        foreach (self::$arenas as $arena){
            if ($arena->isPlaying($player)){
                return $arena;
            }
        }
        return null;
    }

    /**
     * @param World $world
     * @return Arena|null
     */
    public function getArenaByWorld(World $world): ?Arena{
        foreach (self::$arenas as $arena) if ($arena->getMap() === $world->getFolderName()){
            return $arena;
        }
        return null;
    }

    /**
     * @param CreatorSession $creatorSession
     */
    public function createArena(CreatorSession $creatorSession): void{
        if ($this->getArena($creatorSession->data["name"]) instanceof Arena) return;
        Filesystem::safeFilePutContents($this->getDataFolder() . "arenas/" . $creatorSession->data["name"] . ".yml", yaml_emit($creatorSession->getData()));
        self::$arenas[$creatorSession->data["name"]] = new Arena($creatorSession->getData());
        Main::getInstance()->getLogger()->info(MessagesUtils::PREFIX . "Arena {$creatorSession->data['name']} was successful created!");
    }

    public function clearArenaFiles(string $name): void{
        if ($this->getArena($name) instanceof Arena){
            $this->getArena($name)->forceStop();
            @unlink($this->getDataFolder() . "arenas" . DIRECTORY_SEPARATOR . $name . ".yml");
            @unlink($this->getDataFolder() . "backups" . DIRECTORY_SEPARATOR . $this->getArena($name)->getMap() . ".zip");
        }
    }
}