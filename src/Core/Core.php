<?php

declare(strict_types=1);

namespace Cosmetic;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use Join\Tasks\JoinTask;
use pocketmine\utils\Config;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\scheduler\PluginTask;
use Join\Form\FormUI;

class Join extends PluginBase implements Listener {

    private static $ui = [
        "G"=>"§l§6Cosmetics",
    ];

    private static $msg = [
        "C"=>"§l§aSelect a button",
    ];

    public static $instance = null;

    public function onEnable() : void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("working");
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $config->set("guardian-curse", "enabled");
        $config->save();
        self::$instance = $this;
    }

    public static function getInstance() : Join {
        return self::$instance;
    }

    public static function getConfigs(string $value) : Config {
        return new Config(self::getInstance()->getDataFolder() . "{$value}.yml", Config::YAML);
    }

    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();
        $event->setJoinMessage("§7[§a+§7] §f$name !");
        $player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
        $this->getScheduler()->scheduleDelayedTask(new JoinTask($player), 30);
        $player->setFlying(false);
        $player->setAllowFlight(false);
        $player->setScale(1.0);
        $player->setGamemode(0);
        $player->setFood(20);
        $player->setHealth(20);
        $player->getInventory()->setItem(8, Item::get(Item::BOOK, 0, 1)->setCustomName(self::$ui['G']));
    }

    public function onRespawn(PlayerRespawnEvent $event) {
        $player = $event->getPlayer();
        $player->getInventory()->setItem(8, Item::get(Item::BOOK, 0, 1)->setCustomName(self::$ui['G']));
    }

    public function onDrop(PlayerDropItemEvent $event) {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        if ($item->getCustomName() == self::$ui['G']) {
            $event->setCancelled(true);
        }
    }

    public function onInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $item = $event->getItem();

        if($item->getCustomName() == self::$ui['G']) {
            $player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_BOTTLE_DRAGONBREATH);
            $this->getGadgets($player);
        }
    }

    public function onExit(PlayerQuitEvent $event) {
        $event->setQuitMessage("");
    }

    use FormUI;

    public function getGadgets(Player $player) {
        $form = $this->createSimpleFor(function(Player $player, ?int $data) {
            if (!is_null($data)) {
                switch ($data) {
                    case 0:
                        if (!$player->hasPermission("core.fly")) {
                            $player->sendMessage("§7[§5Core§7] §You don't have permissions");
                            return false;
                        }
                        $this->getFly($player);
                        break;
                    case 1:
                        if (!$player->hasPermission("core.size")) {
                            $player->sendMessage("§7[§5Core§7] §You don't have permissions");
                            return false;
                        }
                        $this->getSize($player);
                        break;
                    case 2:
                        if (!$player->hasPermission("core.px")) {
                            $player->sendMessage("§7[§5Core§7] §You don't have permissions");
                            return false;
                        }
                        //$this->getParticulas($player);
                        break;
                    case 3:
                        if (!$player->hasPermission("core.colorchat")) {
                            $player->sendMessage("§7[§5Core§7] §You don't have permissions");
                            return false;
                        }
                        //$this->getColorChat($player);
                        break;
                    case 4:
                        if (!$player->hasPermission("core.arrowtrails")) {
                            $player->sendMessage("§7[§5Core§7] §You don't have permissions");
                            return false;
                        }
                        //$this->getArrowTrails($player);
                        break;
                    case 5:
                        $player->sendMessage("§7[§5Core§7] §4Sorry but this feature is not yet available");
                        break;
                }
            }
        });
        $form->setTitle(self::$ui['G']);
        $form->setContent(self::$msg['C']);
	    $form->addButton("§l§bFLY\n§r§e Tap to use",0,"textures/ui/slow_falling_effect");
	    $form->addButton("§l§6SIZE\n§r§eTap to use",0,"textures/items/totem");
	    $form->addButton("§l§3PARTICLES\n§r§e Tap to use",0,"textures/items/fireworks");
	    $form->addButton("§l§aC§bO§cL§dO§eR §fC§gH§6A§3T\n§r§eTap to use",0,"textures/ui/comment");
	    $form->addButton("§l§cARROW TRAILS\n§r§eSoon...",0,"textures/items/arrow");
	    $form->addButton("§l§gPETS\n§r§eSoon...",0,"textures/ui/icon_panda");
	    $form->sendToPlayer($player);
	      }

    public function getFly(Player $player) {
        $form = $this->createSimpleFor(function(Player $player, ?int $data) {
            if (!is_null($data)) {
                switch ($data) {
                    case 0:
                        $player->setAllowFlight(true);
                        $player->sendTip("§7[§5Core§7] §bFly Enable");
                        break;
                    case 1:
                        $player->setAllowFlight(false);
                        $player->sendTip("§7[§5Core§7] §cFly disable");
                        break;
                }
            }
        });
        $form->setTitle("§l§bFly");
        $form->addButton("§l§bFLY ON\n§r§7Tap to enable",0,"textures/ui/check");
        $form->addButton("§l§cFLY OFF\n§r§7Tap to disable",0,"textures/ui/crossout");
        $form->sendToPlayer($player);
    }

    public function getSize(Player $player) {
        $form = $this->createSimpleFor(function(Player $player, ?int $data) {
            if (!is_null($data)) {
                switch ($data) {
                    case 0:
                        $player->setScale(0.7);
                        $player->sendTip("§7[§5Core§7] §bSize small");
                        break;
                    case 1:
                        $player->setScale(1);
                        $player->sendTip("§7[§5Core§7] §bSize Normal");
                        break;
                    case 2:
                        $player->setScale(1.8);
                        $player->sendTip("§7[§5Core§7] §bSize big");
                        break;
                }
            }
        });
        $form->setTitle("§6§lSize");
        $form->addButton("§l§6Small\n§r§eToca para Cambiar",0,"textures/items/totem");
        $form->addButton("§l§6Normal\n§r§eToca para Cambiar",0,"textures/items/totem");
        $form->addButton("§l§6Big\n§r§eToca para Cambiar",0,"textures/items/totem");
        $form->sendToPlayer($player);
    }

    public function onDisable() : void {
        $this->getLogger()->info("Core disable");
    }
}
