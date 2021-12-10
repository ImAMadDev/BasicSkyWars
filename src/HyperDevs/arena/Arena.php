<?php

namespace HyperDevs\arena;

use HyperDevs\kits\Kit;
use HyperDevs\Main;
use HyperDevs\task\ArenaTask;
use HyperDevs\utils\BackupUtils;
use HyperDevs\utils\BlockUtils;
use HyperDevs\utils\ChestContent;
use HyperDevs\utils\MainExtension;
use HyperDevs\utils\MessagesUtils;
use HyperDevs\utils\PositionUtils;
use HyperDevs\utils\Utils;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\ItemIds;
use pocketmine\world\Position;
use pocketmine\nbt\tag\StringTag;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\world\World;
use pocketmine\Server;
use pocketmine\block\tile\Chest;
use pocketmine\item\ItemFactory;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Arena extends MainExtension implements Listener
{

    const STATUS_DISABLED = -1;
    const STATUS_WAITING = 0;
    const STATUS_RUNNING = 1;
    const STATUS_RESETTING = 2;
    const ARENA_ITEM = "skywars_item";
    const ANNOUNCEMENT_TYPE_TIP = 0;
    const ANNOUNCEMENT_TYPE_MESSAGE = 1;
    const ANNOUNCEMENT_TYPE_TITLE = 2;

    /**
     * @var array
     */
    public array $data = [];

    /**
     * @var array
     */
    public array $players = [];

    /**
     * @var int
     */
    public int $status = self::STATUS_DISABLED;

    /**
     * @var BackupUtils
     */
    public BackupUtils $backupUtils;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var array
     */
    public array $cache_slots = [1 => "", 2 => "", 3 => "", 4 => "", 5 => "", 6 => "", 7 => "", 8 => "", 9 => "", 10 => "", 11 => "", 12 => ""];

    /**
     * @var array
     */
    public array $cache_kits = [];
    /**
     * @var int
     */
    public int $runningTime = 0;

    /**
     * @var int
     */
    public int $resetTime = 10;

    /**
     * @var ArenaSettings
     */
    public ArenaSettings $arenaSettings;

    /**
     * @param Main $main
     * @param array $data
     */
    public function __construct(Main $main, array $data = [])
    {
        parent::__construct($main);
        $this->data = $data;
        $this->config = new Config($this->getDataFolder() . "arenas" . DIRECTORY_SEPARATOR . $this->getName() . ".yml", Config::YAML);
        $this->backupUtils = new BackupUtils($main, $this);
        $this->arenaSettings = new ArenaSettings($this);
        if (!file_exists($this->getDataFolder() . "backups" . DIRECTORY_SEPARATOR . $this->getMap() . ".zip")) {
            $this->backupUtils->saveMap();
        } else {
            $this->reset();
        }
        $this->setStatus($this->config->get("status", self::STATUS_DISABLED));
        $this->getScheduler()->scheduleRepeatingTask(new ArenaTask($this), 20);
        $this->getServer()->getPluginManager()->registerEvents($this, $main);
        $this->loadSpaws();
    }

    public function loadSpaws() : void
    {
        foreach ($this->data["slots"] as $index => $slot) {
            $this->cache_slots[$index] = new SpawnCache($this, PositionUtils::strToPos($slot));
        }
        var_dump($this->cache_slots);
    }

    /**
     * @return ArenaSettings
     */
    public function getArenaSettings(): ArenaSettings
    {
        return $this->arenaSettings;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->data['name'];
    }

    /**
     * @return string
     */
    public function getMap(): string
    {
        return $this->data['mapName'];
    }

    /**
     * @return Level|null
     */
    public function getWold(): ?World
    {
        if (($level = Server::getInstance()->getWorldManager()->getWorldByName($this->getMap())) instanceof World) {
            return $level;
        }
        return null;
    }

    /**
     * @return array
     */
    public function getPlayers(): array
    {
        return array_filter($this->getWold()->getPlayers(), function ($player): bool {
            return $player instanceof Player && $player->isOnline() && $player->getGamemode() === GameMode::SURVIVAL() && $player->isAlive();
        });
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return bool
     */
    public function hasPedestals() : bool
    {
        return $this->data["pedestals"];
    }

    public function activate(): void
    {
        $this->config->set("status", self::STATUS_WAITING);
        $this->config->save();
        $this->setStatus(self::STATUS_WAITING);
    }

    public function forceStart() : void
    {
        if(count($this->getPlayers()) <= 1) return;
        $this->setStatus(Arena::STATUS_RUNNING);
        $this->runningTime = 1;
    }

    public function disable() : void
    {
        $this->config->set("status", self::STATUS_DISABLED);
        $this->config->save();
        $this->setStatus(self::STATUS_DISABLED);
    }

    /**
     * @param int $index
     * @return Position|null
     */
    public function getSlotByIndex(int $index) : ?Position
    {
        $str = $this->data["slots"][$index] ?? null;
        if($str === null) return null;
        return PositionUtils::strToPos($str);
    }

    /**
     * @param Player $player
     * @return int
     */
    public function teleportToSlot(Player $player) : int
    {
        for ($i = 1; $i < 12; $i++){
            if ($this->cache_slots[$i] == ""){
                $this->cache_slots[$i] = $player->getName();
                return $i;
            }
        }/*
        foreach($this->data['slots'] as $i => $slot) {
            if($slot === null){
                $this->slots[$i] = $player->getName();
                return $i;
            }
        }*/
        return -1;
    }

    /**
     * @param string $player
     */
    public function removePlayerFromSlot(string $player) : void
    {
        $this->cache_slots[array_search($player, $this->cache_slots)] = "";
    }

    /**
     * @return Player|null
     */
    public function getWinner() : ?Player
    {
        if(count($this->getPlayers()) == 0) return null;
        $players = $this->getPlayers();
        return array_shift($players);
    }

    /**
     * @param $player
     * @return bool
     */
    public function isPlaying($player) : bool
    {
        if($player instanceof Player) {
            return $player->getWorld()->getFolderName() === $this->getMap();
        }
        return array_search($player, $this->cache_slots) !== false;
    }

    /**
     * @param Player $player
     */
    public function join(Player $player) : void
    {
        if($this->isPlaying($player) === true){
            $player->sendMessage(MessagesUtils::getMessage("player_joined"));
        }

        $slot = $this->teleportToSlot($player);
        if($this->getSlotByIndex($slot) === null){
            $player->sendMessage(MessagesUtils::PREFIX . MessagesUtils::getMessage("unknown_error"));
            return;
        }
        $this->getWold()->loadChunk($this->getSlotByIndex($slot)->getFloorX(), $this->getSlotByIndex($slot)->getFloorZ());
        $player->teleport($this->getSlotByIndex($slot));
        if ($this->hasPedestals()) BlockUtils::trapPlayerInBox($player);
        $player->getCursorInventory()->clearAll();
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->setContents($this->getArenaConfigurationItems());
        $player->sendMessage(MessagesUtils::getMessage("player_join_arena", ["arena" => $this->getName()]));
    }

    /**
     * @param string $message
     * @param int $type
     */
    public function sendAnnouncement(string $message, int $type = self::ANNOUNCEMENT_TYPE_MESSAGE) : void
    {
        switch ($type){
            case self::ANNOUNCEMENT_TYPE_TIP:
                foreach ($this->getPlayers() as $player) {
                    $player->sendTip($message);
                }
                break;
            case self::ANNOUNCEMENT_TYPE_MESSAGE:
                foreach($this->getPlayers() as $player){
                    $player->sendMessage(MessagesUtils::PREFIX . $message);
                }
                break;
            case self::ANNOUNCEMENT_TYPE_TITLE:
                $t = explode(",", $message);
                $subtitle = $t[1] ?? "";
                foreach ($this->getPlayers() as $player) {
                    $player->sendTitle($t[0], $subtitle);
                }
                break;
            default:
                break;
        }
    }

    /**
     * @param Player $player
     * @param Kit $kit
     */
    public function selectKit(Player $player, Kit $kit) : void
    {
        $this->cache_kits[$player->getName()] = $kit->getName();
    }

    /**
     * @param Player $player
     * @return Kit|null
     */
    public function getKitFromCache(Player $player) : ? Kit
    {
        if(isset($this->cache_kits[$player->getName()])){
            return $this->getMain()->getKit($this->cache_kits[$player->getName()]);
        }
        return null;
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function onPlayerQuitEvent(PlayerQuitEvent $event) : void
    {
        $player = $event->getPlayer();
        if($this->isPlaying($player)) {
            $this->getArenaSettings()->removePlayerVote($player);
            $this->removePlayerFromSlot($player->getName());
        }
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onPlayerJoinEvent(PlayerJoinEvent $event) : void
    {
        $player = $event->getPlayer();
        if($this->isPlaying($player)) {
            $player->teleport($this->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
            $this->removePlayerFromSlot($player->getName());
            $this->getArenaSettings()->removePlayerVote($player);
        }
    }

    /**
     * @param BlockBreakEvent $event
     */
    public function onBlockBreakEvent(BlockBreakEvent $event) : void
    {
        $player = $event->getPlayer();
        if ($this->isPlaying($player)){
            if($this->getStatus() === Arena::STATUS_WAITING or $this->getStatus() === Arena::STATUS_RESETTING){
                $event->cancel();
            }
        }
    }

    /**
     * @param BlockPlaceEvent $event
     */
    public function onBlockPlaceEvent(BlockPlaceEvent $event) : void
    {
        $player = $event->getPlayer();
        if ($this->isPlaying($player)){
            if($this->getStatus() === Arena::STATUS_WAITING or $this->getStatus() === Arena::STATUS_RESETTING){
                $event->cancel();
            }
        }
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function onPlayerInteractEvent(PlayerInteractEvent $event) : void
    {
        $player = $event->getPlayer();
        if ($this->isPlaying($player)){
            if ($this->getStatus() === Arena::STATUS_WAITING){
                $item = $player->getInventory()->getItemInHand();
                if($item->getNamedTag()->getTag(Arena::ARENA_ITEM) instanceof StringTag){
                    switch ($item->getNamedTag()->getString(Arena::ARENA_ITEM, "empty")){
                        case "start_item":
                            $this->forceStart();
                            break;
                        case "op_vote_item":
                            $this->getArenaSettings()->addOpVote($player);
                            break;
                        case "normal_vote_item":
                            $this->getArenaSettings()->addNormalVote($player);
                            break;
                        case "kits_item":
                            Utils::getKitsForm($player, $this);
                            break;
                        default:
                            break;
                    }
                }
            }
        }
    }

    /**
     * @param EntityLevelChangeEvent $event
     */
    public function onChangeLevel(EntityTeleportEvent $event) : void
    {
        $player = $event->getEntity();
        $level = $event->getFrom()->getWorld();
        if(!$player instanceof Player) return;
        if(($arena = $this->getMain()->getArenaByWorld($level)) instanceof Arena) {
            $arena->getArenaSettings()->removePlayerVote($player);
            $arena->removePlayerFromSlot($player->getName());
        }
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function onDamage(EntityDamageEvent $event) : void
    {
        $player = $event->getEntity();
        if ($player instanceof Player && $this->isPlaying($player) && $this->getStatus() !== Arena::STATUS_RUNNING) {
            $event->cancel();
            if ($event->getCause() === EntityDamageEvent::CAUSE_VOID){
                if (isset($this->data["slots"][array_search($player->getName(), $this->cache_slots)])) {
                    $player->teleport(PositionUtils::strToPos($this->data["slots"][array_search($player->getName(), $this->cache_slots)]));
                }
            }
        }
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function onEntityDamage(EntityDamageEvent $event) : void
    {
        $player = $event->getEntity();
        if(!($player instanceof Player)) return;
        if (!$this->isPlaying($player)) return;
        if ($event instanceof EntityDamageByEntityEvent){
            $damager = $event->getDamager();
            if(($player->getHealth() - $event->getFinalDamage()) <= 0){
                $event->cancel();
                $player->setGamemode(GameMode::SPECTATOR());
                $this->removePlayerFromSlot($player->getName());
                if ($damager instanceof Player){
                    $this->sendAnnouncement(TextFormat::RED . $player->getName() . TextFormat::GRAY . " was killed by " . TextFormat::RED . $damager->getName());
                }
            }
        }
    }

    public function refillChest() : void
    {
        foreach ($this->getWold()->getLoadedChunks() as $chunk){
            foreach ($chunk->getTiles() as $tile){
                if($tile instanceof Chest){
                    $tile->getInventory()->clearAll();
                    $chest = new ChestContent();
                    $items = $chest->getItems(rand(8, 15), $this->getArenaSettings()->getMostVoted());
                    foreach ($items as $item){
                        $tile->getInventory()->setItem(rand(0, 25), $item);
                    }  
                }
            }
        }
    }

    public function start() : void
    {
        foreach ($this->getPlayers() as $player) {
            if (($kit =$this->getKitFromCache($player)) instanceof Kit){
                $kit->sendTo($player);
            } else {
                $player->getInventory()->clearAll();
                $player->getArmorInventory()->clearAll();
                $player->getCursorInventory()->clearAll();
            }
            if (isset($this->data["slots"][array_search($player->getName(), $this->cache_slots)])) {
                $player->teleport(PositionUtils::strToPos($this->data["slots"][array_search($player->getName(), $this->cache_slots)]));
                if ($this->hasPedestals()) BlockUtils::trapPlayerInBox($player);
            }
            $player->setHealth($player->getMaxHealth());
        }
        $this->sendAnnouncement(TextFormat::colorize("&cChest contents selected: "). strtoupper($this->getArenaSettings()->getMostVoted()), self::ANNOUNCEMENT_TYPE_MESSAGE);
    }

    /**
     * @param bool $resetBackup
     */
    public function reset(bool $resetBackup = true) : void
    {
        for ($i = 1; $i < 13; $i++){
            $this->cache_slots[$i] = "";
        }
        $this->cache_kits = [];
        $this->runningTime = 0;
        if($resetBackup){
            $this->backupUtils->loadMap();
        }
        $this->setStatus(Arena::STATUS_WAITING);
    }

    public function forceStop() : void
    {
        $this->getArenaSettings()->reset();
        $this->setStatus(self::STATUS_DISABLED);
        $this->config->set("status", self::STATUS_DISABLED);
        foreach($this->getWold()->getPlayers() as $player) {
            $player->teleport($this->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
        }
        $this->reset(false);
    }

    /**
     * @return array
     */
    public function getArenaConfigurationItems() : array
    {
        $start = ItemFactory::getInstance()->get(ItemIds::NETHER_STAR);
        $start->getNamedTag()->setString(self::ARENA_ITEM, "start_item", true);
        $start->setCustomName(TextFormat::colorize("&eForce Start"));

        $op_vote = ItemFactory::getInstance()->get(ItemIds::ENDER_CHEST);
        $op_vote->getNamedTag()->setString(self::ARENA_ITEM, "op_vote_item", true);
        $op_vote->setCustomName(TextFormat::colorize("&6Over Powered Chests"));

        $normal_vote = ItemFactory::getInstance()->get(ItemIds::CHEST);
        $normal_vote->getNamedTag()->setString(self::ARENA_ITEM, "normal_vote_item", true);
        $normal_vote->setCustomName(TextFormat::colorize("&aNormal Chests"));

        $kits = ItemFactory::getInstance()->get(ItemIds::FIREBALL);
        $kits->getNamedTag()->setString(self::ARENA_ITEM, "kits_item", true);
        $kits->setCustomName(TextFormat::colorize("&eKits"));
        return [$start, $op_vote, $normal_vote, $kits];
    }
}