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
        $this->assertEquals("vous avez maintenant une canne a peche dans votre inventaire. / ", $action[0][0]);

        // Description de la hutte.
        $action = $currentlocation->check_actions("description");

        // Examiner la canne a peche.
        $action = $currentlocation->check_actions("examiner canne à pêche");
        $this->assertEquals("la canne a peche est une simple canne a peche.", $action[0][0]);

        // Examiner la hutte.
        $action = $currentlocation->check_actions("fouiller hutte");
        $this->assertEquals("vous ne trouvez rien de particulier", $action[0][0]);

        $action = $currentlocation->check_actions("fouiller");
        // Aller dans les jardins royaux.
        $action = $currentlocation->check_actions("aller jardins royaux");
        //$this->assertEquals("deplacement vers : jardins royaux", $action[0][0]);
        $currentlocation = $game->get_current_location();
        $this->assertEquals("jardins royaux", $currentlocation->get_name());
        $this->assertNotEquals("hutte", $currentlocation->get_name());
        $statuscurrentlocation = $currentlocation->get_status();
        $this->assertEquals("ouvert", $statuscurrentlocation[0]);

        // Description des jardins royaux.
        $action = $currentlocation->check_actions("description");

        // Aller dans l'etang.
        $action = $currentlocation->check_actions("aller etang");
        //$this->assertEquals("deplacement vers : etang", $action[0][0]);
        $currentlocation = $game->get_current_location();
        $this->assertEquals("etang", $currentlocation->get_name());

        // Utiliser la canne a peche.
        $action = $currentlocation->check_actions("Utiliser canne a peche");
        $poisson = $game->get_entity("poisson");
        $this->assertTrue($player->has_item_character($poisson));

        // Aller dans les jardins royaux.
        $action = $currentlocation->check_actions("aller jardins royaux");
        //$this->assertEquals("deplacement vers : jardins royaux", $action[0][0]);
        $currentlocation = $game->get_current_location();
        // Sentir les roses.
        $action = $currentlocation->check_actions("sentir rose");
        $this->assertEquals("la rose sent bon", $action[0][0]);

        // Cueillir une rose quand le lieu en contient.
        $action = $currentlocation->check_actions("cueillir rose");
        $player = $game->get_player();
        $rose = $game->get_entity("rose");
        $this->assertTrue($player->has_item_character($rose));
        $this->assertEquals('vous avez maintenant une rose dans votre inventaire / ', $action[0][0]);
        $this->assertFalse($currentlocation->has_item_location($rose));
        // Sentir la rose quand le joueur l'a dans son inventaire.

        // Aller sentier sinueux.
        $action = $currentlocation->check_actions("aller sentier sinueux");
        //$this->assertEquals("deplacement vers : sentier sinueux", $action[0][0]);
        $currentlocation = $game->get_current_location();
        $this->assertEquals("sentier sinueux", $currentlocation->get_name());

        // Aller au pont levis.
        $action = $currentlocation->check_actions("Aller Pont-Levis");
        //$this->assertEquals("deplacement vers : pont-levis", $action[0][0]);
        $currentlocation = $game->get_current_location();
        $this->assertEquals("pont-levis", $currentlocation->get_name());
        // Attaquer Troll.

        // Voir le status de la cour avant que le troll ait le poisson.
        $courstatus = $game->get_entity("cour")->get_status();
        $this->assertEquals("ferme", $courstatus[0]);
        // Donner poissons à troll.
        $action = $currentlocation->check_actions("Donner poisson a troll");
        $this->assertFalse($player->has_item_character($poisson));

        // Voir le status de la cour après que le troll ait le poisson.
        $currentstatus = $currentlocation->get_status();
        $this->assertEquals("ouvert", $currentstatus[0]);
        // Aller Cour.
        $action = $currentlocation->check_actions("Aller Cour");
        //$this->assertEquals('le troll devore son poisson cru & ne fait pas attention a vous', $action[0][0]);
        $currentlocation = $game->get_current_location();
        $this->assertEquals("cour", $currentlocation->get_name());

        // voir la description de la cour.
        $action = $currentlocation->check_actions("description");
    }
}

