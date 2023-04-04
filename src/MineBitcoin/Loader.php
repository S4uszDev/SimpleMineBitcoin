<?php

namespace MineBitcoin;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
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
        $this->saveResource('config.yml');
        $this->config = new Config($this->getDataFolder() . 'config.yml', Config::YAML);

        $this->infobreak = new Config($this->getDataFolder() . "Blocks.yml", Config::YAML);
        $this->infobreak->save();
        $this->cash = $this->getServer()->getPluginManager()->getPlugin("Bitcoin");
    }
    public function onDisable(): void
    {
        $this->infobreak->save();
    }
    public function onBreak(BlockBreakEvent $ev)
    {
        $player = $ev->getPlayer();
        $block = $ev->getBlock();
        $configBlocks = $this->config->get('blocks');

        if ($ev->isCancelled()) return true;

        if (!in_array($player->getLevel()->getFolderName(), $this->config->get('Mundos'))) return true;

        if (!in_array($block->getId(), $this->config->get('ids'))) return true;

        if (!$this->infobreak->exists($player->getName())) {
            $this->infobreak->set($player->getName(), 0);
            $this->infobreak->save();
        } else {
            if ($this->infobreak->get($player->getName()) > ($configBlocks - 2)) {

                $valueBtc = mt_rand($this->config->get('valor-minimo'), $this->config->get('valor-maximo'));

                $this->infobreak->set($player->getName(), 0);
                $this->infobreak->save();
                $this->cash->addBitcoin($player, $valueBtc);

                $player->sendActionBarMessage(TextFormat::AQUA . $configBlocks . TextFormat::GRAY . '/' . TextFormat::AQUA . $configBlocks . TextFormat::WHITE . ' blocos quebrados');

                $player->sendMessage(implode("\n", str_replace("{value}", $valueBtc, $this->config->get('mensagem'))));
                return;
            }
            $this->infobreak->set($player->getName(), $this->infobreak->get($player->getName()) + 1);
            $this->infobreak->save();
            
            $blocks = $this->infobreak->get($player->getName());

            $player->sendActionBarMessage(TextFormat::AQUA . $blocks . TextFormat::GRAY . '/' . TextFormat::AQUA . $configBlocks . TextFormat::WHITE . ' blocos quebrados');
        }
    }
}
