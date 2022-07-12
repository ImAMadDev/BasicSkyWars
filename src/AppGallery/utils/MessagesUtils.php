<?php

namespace AppGallery\utils;

use pocketmine\utils\TextFormat;

final class MessagesUtils
{
    const PREFIX = TextFormat::BLUE . "SkyWars ";
    const MESSAGE_DONT_EXIST = TextFormat::RED . "This message doesnt exist, please contact to the developer: ";

    public static array $messages = [
        "slot_added" => "&aSlot %number% was added!",
        "map_name" => "&aMap %name% has selected for this game! ",
        "arena_name" => "&aArena name has set to %name%",
        "maximum_slots" => "&cMaximum number of slots reached!",
        "player_joined" => "&cYou already have joined!",
        "player_join_arena" => "&eYou have joined the arena %arena%!",
        "session_opened" => "&cYou already have a session opened, try close it typing sw cancel in the chat",
        "session_open" => "&aYou have open a new session!",
        "session.cancelled" => "&aYour session has been cancelled!",
        "missing_args" => "&cMissing arguments",
        "arena_exist" => "&cThis arena already exist!",
        "level_dont_exist" => "&cThis level %level% dont exist!",
        "unknown_error" => "&cAn unknown error occurred attempting executing this action, please contact the developer",
        "player_win" => "&6%player% &3has won a game in the arena: &6%arena%",
        "already_vote" => "&cYou already vote on this game.",
        "vote_confirm_for" => "&7You have voted for: &c%vote%",
        "select_kit" => "&aYou have selected the kit: &c%kit%",
        "kit_not_exist" => "&cThis kits doesnt exist!",
        "reset_arena" => "&eResetting arena in: %time%",
        "no_permissions" => "&cYou dont have permissions to do this action!",
        "pedestals_now_status" => "&7Pedestals now: &3%status%"
    ];

    public static function getMessage(string $index, array $args = []) : string
    {
        if (!isset(self::$messages[strtolower($index)])){
            return self::MESSAGE_DONT_EXIST . strtolower($index);
        }
        if (!empty($args)) {
            $text = self::$messages[strtolower($index)];
            foreach($args as $i => $arg) {
                $text = str_replace("%$i%", $arg, $text);
            }
            return TextFormat::colorize($text);
        }
        return TextFormat::colorize(self::$messages[strtolower($index)]);
    }

}