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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Condition.php');
class Node_Condition extends Condition {

    private Condition_Interface $condition1;
    private Condition_Interface $condition2;
    private string $connector1;


    public function __construct(
        Condition_Interface $condition1,
        Condition_Interface $condition2,
        string $connector1,
        ?array $reactions
    ) {
        Util::check_array($reactions, Reaction_Interface::class);
        parent::__construct($reactions);
        $this->condition1 = $condition1;
        $this->condition2 = $condition2;
        $this->connector1 = $connector1;
    }

    public function get_condition1() {
        return $this->condition1;
    }
    public function get_condition2() {
        return $this->condition2;
    }
    public function get_connector1() {
        return $this->connector1;
    }
    public function set_condition1(Condition_Interface $condition1) {
        $this->condition1 = $condition1;
    }
    public function set_condition2(Condition_Interface $condition2) {
        $this->condition2 = $condition2;
    }
    public function set_connector1(string $connector1) {
        $this->connector1 = $connector1;
    }
}

