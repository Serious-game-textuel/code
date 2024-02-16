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
            $app = new App($CFG->dirroot . '/mod/serioustextualgame/tests/Template_PFE_Sheet5.csv', Language::FR);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->assertFalse(false);
        }
        $game = $app->get_game();
        $this->assertTrue(isset($game));
    }
}


