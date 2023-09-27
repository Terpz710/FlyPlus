<?php

namespace Terpz710\FlyPlus\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\Plugin;

class FlyCommand extends Command implements PluginOwned {

    private $config;
    private $plugin;

    public function __construct(Plugin $plugin, Config $config) {
        parent::__construct("fly", "Toggle flying");
        $this->plugin = $plugin;
        $this->config = $config;
        $this->setPermission("flyplus.fly");
    }

    public function getOwningPlugin(): Plugin {
        return $this->plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used by players.");
            return true;
        }

        if (!$sender->hasPermission("flyplus.fly")) {
            $sender->sendMessage("You don't have permission to use this command.");
            return true;
        }

        if (empty($args)) {
            $sender->sendMessage("Usage: /fly [on|off]");
            return true;
        }

        $subcommand = strtolower($args[0]);
        $currentStatus = $sender->getAllowFlight();

        if ($subcommand === "on") {
            if ($currentStatus) {
                $sender->sendMessage($this->config->get("fly_already_on", "Fly is already enabled."));
            } else {
                $sender->setAllowFlight(true);
                $sender->sendMessage($this->config->get("fly_message_on", "You are now flying!"));
                $this->sendFlyTitle($sender, "fly_title_on", "fly_subtitle_on");
            }
        } elseif ($subcommand === "off") {
            if (!$currentStatus) {
                $sender->sendMessage($this->config->get("fly_already_off", "Fly is already disabled."));
            } else {
                if ($sender->isFlying()) {
                    $sender->setFlying(false);
                }
                $sender->setAllowFlight(false);

                if (!$sender->isOnGround()) {
                    $sender->setHealth($sender->getMaxHealth());
                }

                $sender->sendMessage($this->config->get("fly_message_off", "You have landed."));
                $this->sendFlyTitle($sender, "fly_title_off", "fly_subtitle_off");
            }
        } else {
            $sender->sendMessage("Usage: /fly [on|off]");
        }

        return true;
    }

    private function sendFlyTitle(Player $player, $titleKey, $subtitleKey) {
        $title = $this->config->get($titleKey, "Fly Mode");
        $subtitle = $this->config->get($subtitleKey, "Toggle your flight");
        $fadeIn = $this->config->get("fly_title_fade_in", 10);
        $stay = $this->config->get("fly_title_stay", 40);
        $fadeOut = $this->config->get("fly_title_fade_out", 10);
        $player->sendTitle($title, $subtitle, $fadeIn, $stay, $fadeOut);
    }
}
