<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Condition_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Node_Condition.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Leaf_Condition.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Condition.php');
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Entity_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Character_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Location_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Reaction.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Item.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Character.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Character_Reaction.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Location_Reaction.php');
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Inventory_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Action.php');
require_once($CFG->dirroot . '/mod/stg/src/interfaces/Default_Action_Interface.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Id_Class.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/App.php');
require_once($CFG->dirroot . '/mod/stg/src/classes/Hint.php');
require_once($CFG->dirroot . '/mod/stg/src/Language.php');

use core\check\check;
use PHPUnit\Framework\TestCase;

/**
 * Class LocationENTest
 * @package mod_stg
 */
class LocationENTest extends TestCase {
    /**
     * vérifie que quand on appelle la méthode do_conditions, les conditions sont bien effectuées
     */
    public function testdoconditions() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv');
        $game = $app->get_game();

        // Prendre la canne a peche dans la hutte.
        $currentlocation = $game->get_current_location();
        $action = $currentlocation->check_actions("Take Fishing rod");
        $player = $game->get_player();
        $canneapeche = $game->get_entity("fishing rod");
        $this->assertTrue($player->has_item_character($canneapeche));
        $this->assertTrue(in_array("you now have a fishing rod in your inventory. / ", $action));

        // Description de la hutte.
        $action = $currentlocation->check_actions("description");
        $this->assertTrue(in_array(
            "welcome to the adventure! you are standing in a small hut. there's a fishing rod here.", $action));

        // Examiner la canne a peche.
        $action = $currentlocation->check_actions("Examine Fishing rod");
        $this->assertTrue(in_array("the fishing rod is a simple fishing rod.", $action));

        // Examiner la hutte.
        $action = $currentlocation->check_actions("search hut");
        $this->assertTrue(in_array("you don't find anything special", $action));

        // Vérifier que la location a des indices(hints).
        $hints = $currentlocation->get_hints();
        $this->assertTrue(count($hints) > 0);

        $action = $currentlocation->check_actions("search");
        // Aller dans les jardins royaux.
        $action = $currentlocation->check_actions("go royal gardens");
        $currentlocation = $game->get_current_location();
        $this->assertEquals("royal gardens", $currentlocation->get_name());
        $this->assertNotEquals("hut", $currentlocation->get_name());
        $statuscurrentlocation = $currentlocation->get_status();
        $this->assertTrue(in_array("open", $statuscurrentlocation));

        // Description des jardins royaux.
        $action = $currentlocation->check_actions("description");

        // Aller dans l'etang.
        $action = $currentlocation->check_actions("go pond");
        $currentlocation = $game->get_current_location();
        $this->assertEquals("pond", $currentlocation->get_name());

        // Utiliser la canne a peche.
        $action = $currentlocation->check_actions("Use Fishing rod");
        $poisson = $game->get_entity("fish");
        $this->assertTrue($player->has_item_character($poisson));

        // Aller dans les jardins royaux.
        $action = $currentlocation->check_actions("go royal gardens");
        $currentlocation = $game->get_current_location();
        // Sentir les roses.
        $action = $currentlocation->check_actions("smell rose");
        $this->assertTrue(in_array("the rose smell good", $action));

        // Cueillir une rose quand le lieu en contient.
        $action = $currentlocation->check_actions("gather rose");
        $player = $game->get_player();
        $rose = $game->get_entity("rose");
        $this->assertTrue($player->has_item_character($rose));
        $this->assertTrue(in_array('you now have a rose in your inventory / ', $action));
        $this->assertFalse($currentlocation->has_item_location($rose));
        // Sentir la rose quand le joueur l'a dans son inventaire.

        // Aller sentier sinueux.
        $action = $currentlocation->check_actions("go winding path");
        $currentlocation = $game->get_current_location();
        $this->assertEquals("winding path", $currentlocation->get_name());

        // Aller au pont levis.
        $action = $currentlocation->check_actions("go Drawbridge");
        $currentlocation = $game->get_current_location();
        $this->assertEquals("drawbridge", $currentlocation->get_name());
        // Attaquer Troll.

        // Voir le status de la cour avant que le troll ait le poisson.
        $courstatus = $game->get_entity("court")->get_status();
        $this->assertTrue(in_array("closed", $courstatus));
        // Donner poissons à troll.
        $action = $currentlocation->check_actions("Give fish to troll");
        $this->assertFalse($player->has_item_character($poisson));

        // Voir le status de la cour après que le troll ait le poisson.
        $currentstatus = $currentlocation->get_status();
        $this->assertTrue(in_array("open", $currentstatus));
        // Aller Cour.
        $action = $currentlocation->check_actions("go Court");
        $currentlocation = $game->get_current_location();
        $this->assertEquals("court", $currentlocation->get_name());

        // Voir la description de la cour.
        $action = $currentlocation->check_actions("description");
    }

    public function test_description() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv');
        $game = $app->get_game();
        $currentlocation = $game->get_current_location();

        // La description est demandable en permanance plusieurs fois.
        $action = $currentlocation->check_actions("description");
        $this->assertTrue(in_array(
            "welcome to the adventure! you are standing in a small hut. there's a fishing rod here.", $action));
        $action = $currentlocation->check_actions("description");
        $this->assertTrue(in_array(
            "welcome to the adventure! you are standing in a small hut. there's a fishing rod here.", $action));
        $action = $currentlocation->check_actions("description");
        $this->assertTrue(in_array(
            "welcome to the adventure! you are standing in a small hut. there's a fishing rod here.", $action));
        $action = $currentlocation->check_actions("description");
        $this->assertTrue(in_array(
            "welcome to the adventure! you are standing in a small hut. there's a fishing rod here.", $action));

        // La description est demandable dans n'importe quel lieu.
        $action = $currentlocation->check_actions("go royal gardens");
        $currentlocation = $game->get_current_location();
        $action = $currentlocation->check_actions("description");
        $this->assertTrue(in_array(
            "you are in the royal gardens, their vegetation is luxuriant. there are roses. you also see a hut."
        , $action));
    }

    public function test_deplacements() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv');
        $game = $app->get_game();
        $currentlocation = $game->get_current_location();

        // On test d'aller à un endroit pas accessible d'ici (trop loin).
        $action = $currentlocation->check_actions("go court");

        // On test d'aller à un endroit qui n'existe pas.
        $action = $currentlocation->check_actions("go cave");

        // On test d'aller à un endroit fermé.
        $currentlocation->check_actions("go royal gardens");
        $currentlocation = $game->get_current_location();
        $currentlocation->check_actions("go winding path");
        $currentlocation = $game->get_current_location();
        $currentlocation->check_actions("go Drawbridge");
        $currentlocation = $game->get_current_location();
        $this->assertTrue($currentlocation->get_name() === "drawbridge");
        $currentlocation->check_actions("go Court");
        $currentlocation = $game->get_current_location();
        $this->assertTrue($currentlocation->get_name() !== "court");
    }

    public function test_objets() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/stg/tests/Template_PFE_Sheet5.csv');
        $game = $app->get_game();
        $currentlocation = $game->get_current_location();

        // On test qu'on peut récupérer un objet qu'une seule fois.
        $action = $currentlocation->check_actions("Take fishing rod");
        $action = $currentlocation->check_actions("Take fishing rod");
        $count = 0;
        foreach ($game->get_player()->get_inventory()->get_items() as $item) {
            if ($item->get_name() == "fishing rod") {
                $count ++;
            }
        }
        $this->assertTrue($count == 1);
    }
}

