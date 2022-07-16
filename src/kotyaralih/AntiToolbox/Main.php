<?php

declare(strict_types=1);

namespace kotyaralih\AntiToolbox;

use DateTime;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener
{
    public Config $config;
    
    private string $logs;

    public function onEnable() : void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->logs = $this->getDataFolder() . "toolbox.log";
        if(!file_exists($this->logs)) $this->createLogFile();
    }

    /**
     * @param PlayerPreLoginEvent $event
     * @priority NORMAL
     * @ignoreCancelled TRUE
     */
    public function onPreLogin(PlayerPreLoginEvent $event)
    {
        $player = $event->getPlayerInfo();
        $extraData = $player->getExtraData();
        $deviceOS = (int)$extraData["DeviceOS"];
        $deviceModel = (string)$extraData["DeviceModel"];

        if ($deviceOS !== 1) //AndroidOS
        {
            return;
        }

        /**
         * Something about device model check.
         * For example:
         * Original client: XIAOMI Note 8 Pro
         * Toolbox client: Xiaomi Note 8 Pro
         *
         * Another example:
         * Original client: SAMSUNG SM-A105F
         * Toolbox client: samsung SM-A105F
         */

        $model = explode(" ", $deviceModel);
        if (!isset($model[0]))
        {
            return;
        }
        $check = $model[0];
        $check = strtoupper($check);
        if ($check !== $model[0])
        {
            $event->setKickReason(0, $this->config->get("kick-message"));
            if($this->getConfig()->get("log-attempt", true)) $this->log($player->getUsername(), $event->getIp(), $deviceModel, $extraData["DeviceId"]);
        }
    }
    
    private function log($name, $ip, $devicemodel, $deviceid){
        $dateTime = (new DateTime())->format("Y-m-d H:i:s.v");
        $message = "$dateTime $name $ip $devicemodel $deviceid\n";

        file_put_contents($this->logs, $message, FILE_APPEND);
        clearstatcache(true, $this->logs);
    }

    private function createLogFile(){
        file_put_contents($this->logs, "#date time player-name player-ip player-devicemodel player-deviceid\n");
    }
    
}
