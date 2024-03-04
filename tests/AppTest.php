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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Game_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/App_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/App.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/Language.php');
use PHPUnit\Framework\TestCase;

class AppTest extends TestCase {
    /**
     * vérifie que le constructeur initialise correctement les propriétés
     */
    public function testgetsetgame() {
        global $CFG;
        try {
            $app = new App(
                file_get_contents($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv'),
                Language::FR
            );
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->assertFalse(false);
        }
        $game = $app->get_game();
        $this->assertTrue(isset($game));
        $entities = $app->get_startentities();

        $items = [];

        $characters = [];

        $locations = [];

        foreach ($entities as $e) {
            if ($e instanceof Item) {
                array_push($items, $e);
            } else if ($e instanceof Character) {
                array_push($characters, $e);
            } else if ($e instanceof Location) {
                array_push($locations, $e);
            } else {
                $this->assertTrue(false);
            }
        }

        $this->assertTrue(count($items) == 10);
        $this->assertTrue(count($characters) == 5);
        $this->assertTrue(count($locations) == 13);

        $this->testitems($items);
        $this->testcharacters($characters);
        $this->testlocations($locations);

    }

    private function testitems($items) {
        $this->testitem($items[0], 1, "torche", null, []);
        $this->testitem($items[1], 2, "canne a peche", null, []);
        $this->testitem($items[2], 3, "rose", null, []);
        $this->testitem($items[3], 4, "branche morte", null, []);
        $this->testitem($items[4], 5, "bougie", null, []);
        $this->testitem($items[5], 6, "clef", null, []);
        $this->testitem($items[6], 7, "epee", null, []);
        $this->testitem($items[7], 8, "couronne", null, []);
        $this->testitem($items[8], 9, "poisson", null, []);
        $this->testitem($items[9], 10, "tete couronnee", null, []);
    }

    private function testitem($item, $id, $name, $description, $statuses) {
        $this->assertTrue($item->get_id() == $id);
        $this->assertTrue($item->get_name() == $name);
        $this->assertTrue($item->get_description() == $description);
        $istatuses = $item->get_status();
        $this->assertTrue(count($istatuses) == count($statuses));
        for ($i = 0; $i < count($statuses); $i++) {
            $this->assertTrue($istatuses[$i] == $statuses[$i]);
        }
    }

    private function testcharacters($characters) {
        $this->testcharacter($characters[0], 11, "joueur", null, [], [1], 16);
        $this->testcharacter($characters[1], 12, "troll", null, [], [], 21);
        $this->testcharacter($characters[2], 13, "garde", null, [], [6, 7], 22);
        $this->testcharacter($characters[3], 14, "princesse", null, [], [], 24);
        $this->testcharacter($characters[4], 15, "fantome", null, [], [8], 26);
    }

    private function testcharacter($character, $id, $name, $description, $statuses, $itemids, $locationid) {
        $this->assertTrue($character->get_id() == $id);
        $this->assertTrue($character->get_name() == $name);
        $this->assertTrue($character->get_description() == $description);
        $cstatuses = $character->get_status();
        $this->assertTrue(count($cstatuses) == count($statuses));
        for ($i = 0; $i < count($statuses); $i++) {
            $this->assertTrue($cstatuses[$i] == $statuses[$i]);
        }
        $citems = $character->get_inventory()->get_items();
        $this->assertTrue(count($citems) == count($itemids));
        for ($i = 0; $i < count($itemids); $i++) {
            $this->assertTrue($citems[$i]->get_id() == $itemids[$i]);
        }
        $this->assertTrue($character->get_current_location()->get_id() == $locationid);
    }

    private function testlocations($locations) {
        $this->testlocation($locations[0], 16, "hutte", ["ouvert"], [2], []);
        $this->testlocation($locations[1], 17, "jardins royaux", ["ouvert"], [3], []);
        $this->testlocation($locations[2], 18, "etang", ["ouvert"], [9], []);
        $this->testlocation($locations[3], 19, "sentier sinueux", ["ouvert"], [], []);
        $this->testlocation($locations[4], 20, "tres grand z'arbre", ["ouvert"], [4], []);
        $this->testlocation($locations[5], 21, "pont-levis", ["ouvert"], [], []);
        $this->testlocation($locations[6], 22, "cour", ["ferme"], [], []);
        $this->testlocation($locations[7], 23, "escalier de la tour", ["ouvert"], [], []);
        $this->testlocation($locations[8], 24, "tour", ["ferme"], [], []);
        $this->testlocation($locations[9], 25, "escalier des catacombes", ["ouvert"], [], []);
        $this->testlocation($locations[10], 26, "catacombes", ["ferme"], [], []);
        $this->testlocation($locations[11], 27, "salle de banquet", ["ferme"], [5], []);
        $this->testlocation($locations[12], 28, "salle du trone", ["ouvert"], [], []);
    }

    private function testlocation($location, $id, $name, $statuses, $itemids, $hints) {
        $this->assertTrue($location->get_id() == $id);
        $this->assertTrue($location->get_name() == $name);
        $lstatuses = $location->get_status();
        $this->assertTrue(count($lstatuses) == count($statuses));
        for ($i = 0; $i < count($statuses); $i++) {
            $this->assertTrue($lstatuses[$i] == $statuses[$i]);
        }
        $litems = $location->get_inventory()->get_items();
        $this->assertTrue(count($litems) == count($itemids));
        for ($i = 0; $i < count($itemids); $i++) {
            $this->assertTrue($litems[$i]->get_id() == $itemids[$i]);
        }
    }

}


