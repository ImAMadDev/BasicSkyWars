<?php

namespace AppGallery\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;

class SkyWarsCommand extends Command {


    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        $args = array_map(function($arg){ return strtolower($arg); }, $args);
        if(!$sender->hasPermission("sw.creator")){
            $sender->sendMessage(new Translatable('pocketmine.command.notFound', ['{commandName}' => $commandLabel, '{helpCommand}' => 'help']));
            return;
        }
        if($args[0] == "help"){
            $sender->sendMessage("SkyWars Commands");
        }
    }

}