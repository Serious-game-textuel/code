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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Condition_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Node_Condition.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Leaf_Condition.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Condition.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Entity_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Character_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Location_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Reaction.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Item.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Character.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Character_Reaction.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Location_Reaction.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Inventory_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Action.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Default_Action_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Id_Class.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/App.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/Language.php');

use core\check\check;
use PHPUnit\Framework\TestCase;

class LocationTest extends TestCase {
    /**
     * vérifie que quand on appelle la méthode do_conditions, les conditions sont bien effectuées
     */
    public function testdoconditions() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = $app->get_game();

        // Prendre la canne a peche dans la hutte.
        $currentlocation = $game->get_current_location();
        $action = $currentlocation->check_actions("Prendre canne a peche");
        $player = $game->get_player();
        $canneapeche = $game->get_entity("canne a peche");
        $this->assertTrue($player->has_item_character($canneapeche));
        $this->assertTrue(in_array("vous avez maintenant une canne a peche dans votre inventaire. / ", $action));

        // Description de la hutte.
        $action = $currentlocation->check_actions("description");
        $this->assertTrue(in_array(
        "bienvenue dans l'aventure ! vous etes debout dans une petite hutte. il y a une canne a peche, ici.", $action));

        // Examiner la canne a peche.
        $action = $currentlocation->check_actions("examiner canne à pêche");
        $this->assertTrue(in_array("la canne a peche est une simple canne a peche.", $action));

        // Examiner la hutte.
        $action = $currentlocation->check_actions("fouiller hutte");
        $this->assertTrue(in_array("vous ne trouvez rien de particulier", $action));

        $action = $currentlocation->check_actions("fouiller");
        // Aller dans les jardins royaux.
        $action = $currentlocation->check_actions("aller jardins royaux");
        $currentlocation = $game->get_current_location();
        $this->assertEquals("jardins royaux", $currentlocation->get_name());
        $this->assertNotEquals("hutte", $currentlocation->get_name());
        $statuscurrentlocation = $currentlocation->get_status();
        $this->assertTrue(in_array("ouvert", $statuscurrentlocation));

        // Description des jardins royaux.
        $action = $currentlocation->check_actions("description");

        // Aller dans l'etang.
        $action = $currentlocation->check_actions("aller etang");
        $currentlocation = $game->get_current_location();
        $this->assertEquals("etang", $currentlocation->get_name());

        // Utiliser la canne a peche.
        $action = $currentlocation->check_actions("Utiliser canne a peche");
        $poisson = $game->get_entity("poisson");
        $this->assertTrue($player->has_item_character($poisson));

        // Aller dans les jardins royaux.
        $action = $currentlocation->check_actions("aller jardins royaux");
        $currentlocation = $game->get_current_location();
        // Sentir les roses.
        $action = $currentlocation->check_actions("sentir rose");
        $this->assertTrue(in_array("la rose sent bon", $action));

        // Cueillir une rose quand le lieu en contient.
        $action = $currentlocation->check_actions("cueillir rose");
        $player = $game->get_player();
        $rose = $game->get_entity("rose");
        $this->assertTrue($player->has_item_character($rose));
        $this->assertTrue(in_array('vous avez maintenant une rose dans votre inventaire / ', $action));
        $this->assertFalse($currentlocation->has_item_location($rose));
        // Sentir la rose quand le joueur l'a dans son inventaire.

        // Aller sentier sinueux.
        $action = $currentlocation->check_actions("aller sentier sinueux");
        $currentlocation = $game->get_current_location();
        $this->assertEquals("sentier sinueux", $currentlocation->get_name());

        // Aller au pont levis.
        $action = $currentlocation->check_actions("Aller Pont-Levis");
        $currentlocation = $game->get_current_location();
        $this->assertEquals("pont-levis", $currentlocation->get_name());
        // Attaquer Troll.

        // Voir le status de la cour avant que le troll ait le poisson.
        $courstatus = $game->get_entity("cour")->get_status();
        $this->assertTrue(in_array("ferme", $courstatus));
        // Donner poissons à troll.
        $action = $currentlocation->check_actions("Donner poisson a troll");
        $this->assertFalse($player->has_item_character($poisson));

        // Voir le status de la cour après que le troll ait le poisson.
        $currentstatus = $currentlocation->get_status();
        $this->assertTrue(in_array("ouvert", $currentstatus));
        // Aller Cour.
        $action = $currentlocation->check_actions("Aller Cour");
        $currentlocation = $game->get_current_location();
        $this->assertEquals("cour", $currentlocation->get_name());

        // Voir la description de la cour.
        $action = $currentlocation->check_actions("description");
    }

    public function test_description() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = $app->get_game();
        $currentlocation = $game->get_current_location();

        // La description est demandable en permanance plusieurs fois.
        $action = $currentlocation->check_actions("description");
        $this->assertTrue(in_array(
        "bienvenue dans l'aventure ! vous etes debout dans une petite hutte. il y a une canne a peche, ici.", $action));
        $action = $currentlocation->check_actions("description");
        $this->assertTrue(in_array(
        "bienvenue dans l'aventure ! vous etes debout dans une petite hutte. il y a une canne a peche, ici.", $action));
        $action = $currentlocation->check_actions("description");
        $this->assertTrue(in_array(
        "bienvenue dans l'aventure ! vous etes debout dans une petite hutte. il y a une canne a peche, ici.", $action));
        $action = $currentlocation->check_actions("description");
        $this->assertTrue(in_array(
        "bienvenue dans l'aventure ! vous etes debout dans une petite hutte. il y a une canne a peche, ici.", $action));

        // La description est demandable dans n'importe quel lieu.
        $action = $currentlocation->check_actions("aller jardins royaux");
        $currentlocation = $game->get_current_location();
        $action = $currentlocation->check_actions("description");
        $this->assertTrue(in_array(
        "vous etes dans les jardins royaux, leur vegetation est luxuriante. il y a des rose. vous apercevez aussi une hutte."
        , $action));
    }

    public function test_deplacements() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = $app->get_game();
        $currentlocation = $game->get_current_location();

        // On test d'aller à un endroit pas accessible d'ici (trop loin).
        $action = $currentlocation->check_actions("aller cour");

        // On test d'aller à un endroit qui n'existe pas.
        $action = $currentlocation->check_actions("aller cave");

        // On test d'aller à un endroit fermé.
        $currentlocation->check_actions("aller jardins royaux");
        $currentlocation = $game->get_current_location();
        $currentlocation->check_actions("aller sentier sinueux");
        $currentlocation = $game->get_current_location();
        $currentlocation->check_actions("Aller Pont-Levis");
        $currentlocation = $game->get_current_location();
        $this->assertTrue($currentlocation->get_name() === "pont-levis");
        $currentlocation->check_actions("Aller Cour");
        $currentlocation = $game->get_current_location();
        $this->assertTrue($currentlocation->get_name() !== "cour");
    }

    public function test_objets() {
        global $CFG;
        $app = new App($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv', Language::FR);
        $game = $app->get_game();
        $currentlocation = $game->get_current_location();

        // On test qu'on peut récupérer un objet qu'une seule fois.
        $action = $currentlocation->check_actions("Prendre canne a peche");
        $action = $currentlocation->check_actions("Prendre canne a peche");
        $count = 0;
        foreach ($game->get_player()->get_inventory()->get_items() as $item) {
            if ($item->get_name() == "canne a peche") {
                $count ++;
            }
        }
        $this->assertTrue($count == 1);
    }
}

