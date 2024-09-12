<?php


namespace Azzam\AnvilRepair;


use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\block\Anvil;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\item\ToolTier;
use pocketmine\math\Vector3;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\world\sound\AnvilUseSound;

class Main extends PluginBase implements Listener
{
    public function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function isRepairable(Item $item): bool{
        return $item instanceof Tool || $item instanceof Armor;
    }

    public function MainForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            $re = $data;
            if ($re === null)
            {
                return true;
            }
            switch ($re){
                case 0 :
                    if ($player->getXpManager()->getXpLevel() >= 8 || $player->getGamemode() === GameMode::CREATIVE()){
                        $this->RepairForm($player);
                    }else{
                        $player->sendMessage("§9>> §fVous n'avez pas l'xp §9minimum§f requis pour §9réparer§f votre item !");
                    }
                    break;
                case 1 :
                    if ($player->getXpManager()->getXpLevel() >= 6 || $player->getGamemode() === GameMode::CREATIVE()){
                        $this->UnEnchantForm($player);
                    }else{
                        $player->sendMessage("§9>> §fVous n'avez pas l'xp §9minimum§f requis pour §9désenchanter§f votre item !");
                    }
                    break;
                case 2 :
                    if ($player->getXpManager()->getXpLevel() >= 3 || $player->getGamemode() === GameMode::CREATIVE()){
                        $this->RenameForm($player);
                    }else{
                        $player->sendMessage("§9>> §fVous n'avez pas l'xp §9minimum§f requis pour §9renommer§f votre item !");
                    }
                    break;
            }
            return true;
        });
        $form->setTitle("Enclume");
        $form->setContent("Informations : §7Vous pouvez renomer, désenchanter ou réparer vos items contre de l'xp !");
        if ($player->getXpManager()->getXpLevel() >= 8 || $player->getGamemode() === GameMode::CREATIVE()){
            $form->addButton("Réparer un Item\n§a8 niveaux d'xp requis");
        }else{
            $form->addButton("Réparer un Item\n§c8 niveaux d'xp requis");
        }

        if ($player->getXpManager()->getXpLevel() >= 6 || $player->getGamemode() === GameMode::CREATIVE()){
            $form->addButton("Désenchenter un Item\n§a6 niveaux d'xp requis");
        }else{
            $form->addButton("Désenchenter un Item\n§c6 niveaux d'xp requis");
        }

        if ($player->getXpManager()->getXpLevel() >= 3 || $player->getGamemode() === GameMode::CREATIVE()){
            $form->addButton("Renommer un Item\n§a3 niveaux d'xp requis");
        }else{
            $form->addButton("Renommer un Item\n§c3 niveaux d'xp requis");
        }
        $player->sendForm($form);
        return $form;
    }

    public function RepairForm(Player $player)
    {
        $form = new SimpleForm(function (Player $player, int $data = null) {
            $re = $data;
            if ($re === null)
            {
                return true;
            }
            switch ($re){
                case 0 :
                    $index = $player->getInventory()->getHeldItemIndex();
                    $item = $player->getInventory()->getItem($index);
                    if(!$this->isRepairable($item)){
                        $player->sendMessage("§9>> §fCet §9item§f ne peut pas être réparer !");
                        return true;
                    }
                    if($item->getDamage() > 0){
                        if ($player->getXpManager()->getXpLevel() >= 8){
                            $player->getXpManager()->setXpLevel(($player->getXpManager()->getXpLevel() - 8));
                        }
                        $player->getInventory()->setItem($index, $item->setDamage(0));
                        $player->sendMessage("§9>> §fVotre item a été §9réparé§f avec succès !");

                        $player->getWorld()->addSound(new Vector3($player->getPosition()->getX(), $player->getPosition()->getY(), $player->getPosition()->getZ()), new AnvilUseSound());
                    }else{
                        $player->sendMessage("§9>> §fCet item est déjà réparer !");
                    }
                    break;
                case 1 :
                    $this->MainForm($player);
                    break;
            }
            return true;
        });
        $form->setTitle("Repair");
        $form->setContent("§fÊtes-vous sûr de vouloir réparer votre item pour §98 niveaux§f d'xp ?\nCette action ne pourra pas être annulée par la suite !");
        $form->addButton("Réparer");
        $form->addButton("Retour");
        $form->sendToPlayer($player);
        return $form;
    }

    public function UnEnchantForm(Player $player)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = new SimpleForm(function (Player $player, int $data = null) {
            $re = $data;
            if ($re === null)
            {
                return true;
            }
            switch ($re){
                case 0 :
                    $index = $player->getInventory()->getHeldItemIndex();
                    $item = $player->getInventory()->getItem($index);
                    if ($item->hasEnchantments()){
                        $item->removeEnchantments();
                        $player->getInventory()->setItemInHand($item);
                        if ($player->getXpManager()->getXpLevel() >= 6){
                            $player->getXpManager()->setXpLevel($player->getXpManager()->getXpLevel() - 6);
                        }
                        $player->sendMessage("§9>> §fVotre item a été §9désenchanté§f avec succès !");

                        $player->getWorld()->addSound(new Vector3($player->getPosition()->getX(), $player->getPosition()->getY(), $player->getPosition()->getZ()), new AnvilUseSound());
                    }else{
                        $player->sendMessage("§9>> §fCet item n'a aucun §9enchantement§f !");
                    }
                    break;
                case 1 :
                    $this->MainForm($player);
                    break;
            }
            return true;
        });
        $form->setTitle("Désenchantement");
        $form->setContent("§fÊtes-vous sûr de vouloir désenchanté votre item pour §96 niveaux§f d'xp ?\nCette action ne pourra pas être annulée par la suite !");
        $form->addButton("Désenchanté");
        $form->addButton("Retour");
        $form->sendToPlayer($player);
        return $form;
    }

    public function RenameForm(Player $player)
    {
        $form = new CustomForm(function (Player $player, array $data = null) {
            $index = $player->getInventory()->getHeldItemIndex();
            $item = $player->getInventory()->getItem($index);

            $re = $data;
            if ($re === null)
            {
                return true;
            }
            if ($item->getTypeId() == 0){
                $player->sendMessage("§9>> §fVous ne tenez §9aucun§f item dans votre main !");
            }else{
                $item->setCustomName($data[1]);
                $player->getInventory()->setItemInHand($item);
                $player->sendMessage("§9>> §fVotre item a été §9renommer§f en §o(§9$data[1]§f)§r§f avec succès !");
                if ($player->getXpManager()->getXpLevel() >= 3){
                    $player->getXpManager()->setXpLevel($player->getXpManager()->getXpLevel() - 3);
                }
                $player->getWorld()->addSound(new Vector3($player->getPosition()->getX(), $player->getPosition()->getY(), $player->getPosition()->getZ()), new AnvilUseSound());

            }
        });
        $form->setTitle("Renommer");
        $form->addLabel("§fÊtes-vous sûr de vouloir renommer votre item pour §93 niveaux§f d'xp ?\n\nSi vous ne mettez §9aucun nom§f, votre item reprendra son nom de base !");
        $form->addInput("§9Renommer votre item§r", "Entrez le nouveau nom de votre item");
        $form->sendToPlayer($player);
        return $form;
    }

    public function onInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if ($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK && $block instanceof Anvil) {
            $event->cancel();
            self::MainForm($player);
        }
    }

}