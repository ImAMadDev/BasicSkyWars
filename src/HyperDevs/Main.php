<?php

namespace HyperDevs;

use Exception;
use HyperDevs\arena\Arena;
use HyperDevs\event\PlayerWinArenaEvent;
use HyperDevs\kits\Kit;
use HyperDevs\kits\types\Gappler;
use HyperDevs\kits\types\LumberJack;
use HyperDevs\kits\types\Tank;
use HyperDevs\sessions\CreatorSession;
use HyperDevs\utils\MessagesUtils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;

class Main extends PluginBase implements Listener
{

    /**
     * @var Main
     */
    public static Main $instance;

    /**
     * @var array
     */
    public static array $arenas = [];

    /**
     * @var array
     */
    public static array $sessions = [];

    /**
     * @var array
     */
    public static array $kits = [];

    public function onLoad() : void
    {
       self::$instance = $this;
    }

    public function onEnable() : void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->initKits();
        $this->initArenas();
    }

    public function onDisable() : void
    {
    }

    /**
     * @return Main
     */
    public static function getInstance() : Main
    {
        return self::$instance;
    }

    public function initArenas() : void
    {
        if(!(is_dir($this->getDataFolder() . "arenas/"))) {
            @mkdir($this->getDataFolder() . "arenas");
        }
        if (!(is_dir($this->getDataFolder() . "backups/"))){
            @mkdir($this->getDataFolder() . "backups/");
        }
        foreach (glob($this->getDataFolder() . "arenas/" . "*.yml") as $file) {
            if (!($this->getArena(basename($file, ".yml")) instanceof Arena)){
                $config = new Config($file, Config::YAML);
                self::$arenas[basename($file, ".yml")] = new Arena($this, $config->getAll());
            }
        }
        $this->getLogger()->info(sprintf("%s%sNumber of arenas loaded: %s", MessagesUtils::PREFIX, TextFormat::GREEN, count($this->getArenas())));
    }

    public function initKits() : void
    {
        $this->addKit(new LumberJack());
        $this->addKit(new Tank());
        $this->addKit(new Gappler());
        $this->getLogger()->info(sprintf("%s%sNumber of kits loaded: %s", MessagesUtils::PREFIX, TextFormat::GREEN, count($this->getKits())));
    }

    /**
     * @param string $name
     * @return Kit|null
     */
    public function getKit(string $name) : ?Kit
    {
        return self::$kits[$name] ?? null;
    }

    /**
     * @return array
     */
    public function getKits(): array
    {
        return self::$kits;
    }

    /**
     * @param Kit $kit
     */
    public function addKit(Kit $kit) : void
    {
        self::$kits[$kit->getName()] = $kit;
    }

    /**
     * @param string $name
     * @return Arena|null
     */
    public function getArena(string $name) : ?Arena
    {
        return self::$arenas[$name] ?? null;
    }

    /**
     * @param Player $player
     * @return Arena|null
     */
    public function getArenaByPlayer(Player $player) : ? Arena
    {
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
    public function getArenaByWorld(World $world) : ?Arena
    {
        foreach (self::$arenas as $arena) if ($arena->getMap() === $world->getFolderName()){
            return $arena;
        }
        return null;
    }

    /**
     * @param Player $player
     * @return CreatorSession|null
     */
    public function getSession(Player $player) : ? CreatorSession
    {
        return self::$sessions[$player->getName()] ?? null;
    }

    /**
     * @return array
     */
    public function getArenas() : array
    {
        return self::$arenas;
    }

    /**
     * @return array
     */
    public function getSessions() : array
    {
        return self::$sessions;
    }

    /**
     * @param CreatorSession $creatorSession
     */
    public function createArena(CreatorSession $creatorSession) : void
    {
        if ($this->getArena($creatorSession->data["name"]) instanceof Arena) return;
        $config = new Config($this->getDataFolder() . "arenas" . DIRECTORY_SEPARATOR . $creatorSession->data["name"] . ".yml", Config::YAML, $creatorSession->getData());
        $config->save();
        self::$arenas[$creatorSession->data["name"]] = new Arena($this, $creatorSession->getData());
        $this->getLogger()->info(MessagesUtils::PREFIX . "Arena {$creatorSession->data['name']} was successful created!");
    }

    public function clearArenaFiles(string $name) : void
    {
        if ($this->getArena($name) instanceof Arena){
            $this->getArena($name)->forceStop();
            @unlink($this->getDataFolder() . "arenas" . DIRECTORY_SEPARATOR . $name . ".yml");
            @unlink($this->getDataFolder() . "backups" . DIRECTORY_SEPARATOR . $this->getArena($name)->getMap() . ".zip");
        }
    }

    /**
     * @param Player $player
     */
    public function openSession(Player $player): void
    {
        if ( isset(self::$sessions[$player->getName()])){
            $player->sendMessage((MessagesUtils::PREFIX . MessagesUtils::getMessage("session_opened")));
            return;
        }
        self::$sessions[$player->getName()] = new CreatorSession($this, $player);
        $player->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("session_open"));
    }

    /**
     * @param Player $player
     */
    public function joinRandomArena(Player $player) : void
    {
        foreach ($this->getArenas() as $arena){
            if($arena->getStatus() === Arena::STATUS_WAITING) {
                $arena->join($player);
            }
        }
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onJoin(PlayerJoinEvent $event) : void
    {
        $player = $event->getPlayer();
        $player->sendMessage("Welcome");
    }

    /**
     * @throws Exception
     */
    public function onChat(PlayerChatEvent $event) : void
    {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $args = explode(" ", $message);
        if ($args[0] == "sw"){
            if(!$player->hasPermission("sw.creator")) return;
            switch ($args[1]){
                case "create":
                    $this->openSession($player);
                    break;
                case "enable":
                    if (!isset($args[2])){
                        $player->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("missing_args"));
                        return;
                    }
                    if ($this->getArena($args[2]) instanceof Arena){
                        $this->getArena($args[2])->activate();
                        $player->sendMessage("arena enabled");
                    }
                    break;
                case "delete":
                    if (!isset($args[2])){
                        $player->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("missing_args"));
                        return;
                    }
                    if ($this->getArena($args[2]) instanceof Arena){
                        $this->clearArenaFiles($args[2]);
                        $player->sendMessage("arena deleted");
                    }
                    break;
                case "join":
                    if(!isset($args[2])){
                        $this->joinRandomArena($player);
                    }
                    break;
                case "slot":
                    if(!isset($args[2])){
                        $player->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("missing_args"));
                        return;
                    }
                    if (($session = $this->getSession($player)) instanceof  CreatorSession){
                        $session->setSlot($args[2]);
                    }
                    break;
                case "pedestals":
                    if (!(isset($args[2]))){
                        $player->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("missing_args"));
                    }
                    $pedestals = $args[2] == "true";
                    if (($session = $this->getSession($player)) instanceof Player){
                        $session->setPedestals($pedestals);
                        if ($pedestals) $player->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("pedestals_now_status", ["status" => "enabled"])); else {
                            $player->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("pedestals_now_status", ["status" => "disabled"]));
                        }
                    }
                    break;
                case "map":
                    if (($session = $this->getSession($player)) instanceof  CreatorSession){
                        if(!isset($args[2])){
                            $map = $player->getWorld();
                        } else {
                            if(!$this->getServer()->getWorldManager()->isWorldGenerated($args[2])){
                                $player->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("level_dont_exist", ["level" => $args[2]]));
                                return;
                            }
                            if (!$this->getServer()->getWorldManager()->isWorldLoaded($args[2])){
                                $this->getServer()->getWorldManager()->loadWorld($args[2]);
                            }
                            $map = $this->getServer()->getWorldManager()->getWorldByName($args[2]);
                        }
                        $session->setMap($map);
                    }
                    break;
                case "arena":
                    if(!isset($args[2])){
                        $player->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("missing_args"));
                    }
                    if($this->getArena($args[2]) instanceof Arena){
                        $player->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("arena_exist",["arena" => $args[2]]));
                        return;
                    }
                    if (($session = $this->getSession($player)) instanceof  CreatorSession){
                        $session->setArenaName($args[2]);
                    }
                    break;
                case "save":
                    if (($session = $this->getSession($player)) instanceof  CreatorSession){
                        $session->finishSetup();
                    }
                    break;
                case "cancel":
                    if ($this->getSession($player) instanceof  CreatorSession){
                        unset(self::$sessions[$player->getName()]);
                        $player->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("session.cancelled"));
                    }
                    break;
                default:
                    throw new Exception('Unexpected value');
            }
        }
    }

    public function onArenaWin(PlayerWinArenaEvent $event) : void
    {
        $player = $event->getPlayer();
        $arena = $event->getArena();
        $this->getServer()->broadcastMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("player_win", ["player" => $player->getName(), "arena" => $arena->getName()]));
    }
}