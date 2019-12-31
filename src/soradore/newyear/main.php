<?php

namespace soradore\newyear;


use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\entity\Entity;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\utils\Color;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;

use BlockHorizons\Fireworks\item\Fireworks;
use BlockHorizons\Fireworks\entity\FireworksRocket;


class main extends PluginBase implements Listener{

	const FIRE_COLORS = [
		Fireworks::COLOR_GREEN,
		Fireworks::COLOR_YELLOW,
		Fireworks::COLOR_GOLD,
		Fireworks::COLOR_PINK
	];

	const FIRE_TYPE = [
		Fireworks::TYPE_CREEPER_HEAD,
		Fireworks::TYPE_STAR,
		Fireworks::TYPE_SMALL_SPHERE,
		Fireworks::TYPE_HUGE_SPHERE
	];

    public function onEnable(){
		//$this->getServer()->getPluginManager()->loadPlugin("Fireworks");
		date_default_timezone_set("Asia/Tokyo");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		//$this->newYear = strtotime("2020/1/1 0:0:0 Asia/Tokyo");
		$this->newYear = strtotime("2019/12/31 17:36:0");
		if(!file_exists($this->getDataFolder())){
			mkdir($this->getDataFolder(), 0744, true);
		}
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML,
		[
			"ViewPoint" => ["x"=>0, "y"=>0, "z"=>0],
			"LunchPoint" => ["x"=>0, "y"=>0, "z"=>0],
		]);

		$this->getScheduler()->scheduleRepeatingTask(new class($this) extends Task{
			
			public function __construct($main){
				$this->main = $main;
				$this->newYear = $main->newYear;
			}

			public function onRun(int $currentTick): void{
				$diff = $this->newYear - time();
				$this->main->getServer()->broadcastPopup(((string)$diff) . "s");
				if($diff < 1){
					$this->main->startLunch();
					$this->getHandler()->cancel();
				}
			}
		},20);
	}

	public function startLunch(){
		$this->getScheduler()->scheduleRepeatingTask(new class($this) extends Task{
			
			public function __construct($main){
				$this->main = $main;
				$this->count = 7;
			}

			public function onRun(int $currentTick): void{
				$this->main->lunch();
				--$this->count;
				if($this->count == 0)
				    $this->getHandler()->cancel();
			}
		},20);
	}

	/*public function onTouch(PlayerInteractEvent $ev){
		$player = $ev->getPlayer();
		//$player->teleport($this->getViewPoint());
		$this->lunch();
	}*/

	public function getViewPoint(){
		//
		$data = $this->config->get("ViewPoint");
		return new Vector3($data["x"], $data["y"], $data["z"]);
	}


	public function getLunchPoint(){
		$data = $this->config->get("LunchPoint");
		return new Vector3($data["x"], $data["y"], $data["z"]);
	}


	public function lunch(){
		$level = $this->getServer()->getDefaultLevel();
		$level->setTime(18000);
		for($i=0;$i<10;$i++){
			$fw = Item::get(Item::FIREWORKS);
			$fw->addExplosion(self::FIRE_TYPE[rand(0,3)], self::FIRE_COLORS[rand(0, 3)], self::FIRE_COLORS[rand(0, 3)], true, false);
			$fw->setFlightDuration(rand(1,3));
			// Choose some coordinates
			$vector3 = $this->getLunchPoint()->add(rand(-20,20), 0, rand(-20,20));
			// Create the NBT data
			$nbt = FireworksRocket::createBaseNBT($vector3, new Vector3(0.001, 0.05, 0.001), lcg_value() * 360, 90);
			// Construct and spawn
			$entity = FireworksRocket::createEntity("FireworksRocket", $level, $nbt, $fw);
			if ($entity instanceof FireworksRocket) {
				$entity->spawnToAll();
			}
		}
		
	}
}


    

