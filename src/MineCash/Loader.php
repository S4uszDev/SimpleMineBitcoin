<?php

namespace MineCash;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\Server;

class Loader extends PluginBase implements Listener
{

    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder());
        $this->infobreak = new Config($this->getDataFolder() . "Blocks.yml", Config::YAML);
        $this->infobreak->save();
        $this->cash = $this->getServer()->getPluginManager()->getPlugin("Cash");
    }
    public function onDisable(): void
    {
        $this->infobreak->save();
    }
    public function onBreak(BlockBreakEvent $ev)
    {
        $player = $ev->getPlayer();
        $block = $ev->getBlock();

        if ($ev->isCancelled()) return true;

        $worlds = [
            "Mundo-1",
            "Mundo-2",
            "Mundo-3"
        ];

        if (!in_array($player->getWorld()->getFolderName(), $worlds)) return true;

        if (!$this->infobreak->exists($player->getName())) {
            $this->infobreak->set($player->getName(), 1);
            $this->infobreak->save();
        } else {
            if ($this->infobreak->get($player->getName()) == 9999) {

                $valueCash = mt_rand(1, 5);

                $this->infobreak->set($player->getName(), 1);
                $this->infobreak->save();
                $this->cash->addCash($player, $valueCash);

                $message = [
                    "",
                    TextFormat::BOLD . TextFormat::AQUA . "Parabéns!",
                    "",
                    TextFormat::RESET . "Você acabou de ganhar" . TextFormat::GREEN . " {$valueCash}" . TextFormat::RESET . " de cash minerando.",
                    ""
                ];

                $player->sendMessage(implode("\n", $message));
                return;
            }
            $this->infobreak->set($player->getName(), $this->infobreak->get($player->getName()) + 1);
            $this->infobreak->save();
            
            $blocks = $this->infobreak->get($player->getName());

            $player->sendActionBarMessage(TextFormat::AQUA . number_format($blocks, 2, '', '.') . TextFormat::GRAY . '/' . TextFormat::AQUA . '10.000' . TextFormat::WHITE . 'blocos quebrados');
        }
    }
}
