<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/App.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/Language.php');



$csvcontent = $_POST['csvcontent'];
$tempfilepath = tempnam(sys_get_temp_dir(), 'mod_serioustextualgame');
file_put_contents($tempfilepath, $csvcontent);
$app = App::get_instance();
if ($app == null) {
    $app = new App(null, $tempfilepath);
}
$action = $app->get_game()->do_action($_POST['inputText'], $_POST['debug']);
if (count($action[0]) == 0) {
    echo '["donne une autre commande", ["'.implode('", "', $action[1]).'"]]';
} else {
    echo '["'.implode(' / ', $action[0]).'", ["'.implode('", "', $action[1]).'"]]';
}

exit();
