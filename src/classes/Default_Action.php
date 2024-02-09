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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Default_Action_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Action.php');

class Default_Action extends Action implements Default_Action_Interface {
    public function do_conditions_verb(string $verb) {
        $game = Game::getinstance();
        $game->add_action();
        $tokendescription = explode('"', $this->get_description());
        $result = [];
        foreach ($tokendescription as $token) {
            if (str_replace(' ', '', $token) == '+verbe+' ||
            str_replace(' ', '', $token) == 'verbe+' ||
            str_replace(' ', '', $token) == '+verbe' ||
            str_replace(' ', '', $token) == 'verbe') {
                array_push($result, $verb);
            } else {
                array_push($result, $token);
            }
        }
        return implode("", $result);
    }

    public function do_conditions() {
        return $this->do_conditions_verb('');
    }
}