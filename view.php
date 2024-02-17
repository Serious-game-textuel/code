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

/**
 * Prints an instance of mod_serioustextualgame.
 *
 * @package     mod_serioustextualgame
 * @copyright   2024 Your Name <serioustextualgame@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG;
require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
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
// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$s = optional_param('s', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('serioustextualgame', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('serioustextualgame', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $moduleinstance = $DB->get_record('serioustextualgame', ['id' => $s], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $moduleinstance->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('serioustextualgame', $moduleinstance->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$event = \mod_serioustextualgame\event\course_module_viewed::create([
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('serioustextualgame', $moduleinstance);
$event->trigger();

$PAGE->set_url('/mod/serioustextualgame/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);
// Récupérez le contenu CSV de $moduleinstance->intro.
$csvcontent = $moduleinstance->filecontent;

// Convertissez le contenu CSV en un fichier temporaire.
$tempfilepath = tempnam(sys_get_temp_dir(), 'mod_serioustextualgame');
file_put_contents($tempfilepath, $csvcontent);

// Utilisez le chemin du fichier temporaire comme entrée pour votre application.
$app = new App($tempfilepath, Language::FR);
$game = $app->get_game();
$currentlocation = $game->get_current_location();
$action = $currentlocation->check_actions("description");
if ($action[0][0] == "") {
    $texte="mauvais parsage";
} else {
    $texte = $action[0][0];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputText = $_POST['inputText'];
    // Récupérez à nouveau la currentlocation juste avant d'appeler check_actions
    $game = $app->get_game();
    $currentlocation = $game->get_current_location();
    $action = $currentlocation->check_actions($inputText);
    $currentlocation = $game->get_current_location();
    if ($action[0][0] == "") {
        echo "mauvais parsage";
    } else {
        echo $action[0][0];
    }
    exit();
}

echo $OUTPUT->header();

?>

<div id="container" style="background-color: black; color: white; width: 100%; height: 500px; overflow: auto; position: relative;">
    <div id="text" style="padding: 10px;"></div>
    <input type="text" id="inputText" 
        placeholder="Écrivez quelque chose ici..." 
        style="position: absolute; bottom: 0; width: 100%;">
</div>
<button onclick="displayInputText()">Valider</button>

<script type = "text/javascript">

function typeWriter(element, txt, color) {
    return new Promise((resolve, reject) => {
        function type(i) {
            if (i < txt.length) {
                element.innerHTML += `<span style="color:${color};">${txt.charAt(i)}</span>`;
                setTimeout(() => type(i + 1), 50);
            } else {
                element.innerHTML += "<br>";
                resolve();
            }
        }
        type(0);
    });
}
    typeWriter(document.getElementById("text"), "<?php echo $texte; ?>", "red");

    document.getElementById("inputText").addEventListener("keyup", function(event) {
        if (event.keyCode === 13) { // Vérifie si la touche est "Entrée"
            displayInputText(); // Appelle la fonction displayInputText()
        }
    });

    function displayInputText() {
    var inputText = document.getElementById("inputText");
    // Désactivez le champ d'entrée
    inputText.disabled = true;
    // Affichez d'abord le texte saisi par l'utilisateur
    typeWriter(document.getElementById("text"), inputText.value, "blue")
    .then(() => {
        // Ensuite, faites une requête AJAX à ce même script PHP
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'inputText=' + encodeURIComponent(inputText.value),
        })
        .then(response => response.text())
        .then(text => {
            // Affichez le résultat de check_actions
            return typeWriter(document.getElementById("text"), text, "red");
        })
        .then(() => {
            // Réactivez le champ d'entrée une fois que tout le texte a été affiché
            inputText.disabled = false;
            inputText.value = '';
        });
    });
}


</script>

<?php
echo $OUTPUT->footer();

