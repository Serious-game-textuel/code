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

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/App.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/Language.php');
// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$s = optional_param('s', 0, PARAM_INT);

foreach (Language::get_all_languages() as $lang) {
    $comparedescription = $DB->sql_compare_text('name');
    $comparedescriptionplaceholder = $DB->sql_compare_text(':name');
    $todogroups = $DB->record_exists_sql(
        "SELECT id FROM {language} WHERE {$comparedescription} = {$comparedescriptionplaceholder}",
        ['name' => $lang]
    );
    if (!$todogroups) {
        $DB->insert_record('language', ['name' => $lang]);
    }
}

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
$csvcontent = $moduleinstance->filecontent;

$rows = str_getcsv($csvcontent, "\n");
$columns = str_getcsv($rows[2], ",");

$element = $columns[1];

echo $OUTPUT->header();
if (isset($_SESSION['conditionsdone'])) {
    unset($_SESSION['conditionsdone']);
}
$fs = get_file_storage();
$file = $fs->get_file_by_id($moduleinstance->fileid);
if ($file) {
    $url = moodle_url::make_pluginfile_url(
        $file->get_contextid(),
        $file->get_component(),
        $file->get_filearea(),
        $file->get_itemid(),
        $file->get_filepath(),
        $file->get_filename()
    );
}

?>

<div id="container" style="background-color: black; color: white; width: 100%; height: 500px; overflow: auto; position: relative;">
    <div id="text" style="padding: 10px;"></div>
    <button id="helpButton" style="position: absolute; top: 0; right: 0; background-color: white; color: black;">?</button>
    <div id="helpText" style="display: none; position: absolute; top: 30px; right: 0;
     background-color: white; color: black; padding: 10px;">
        <?php if ($element === 'FR'): ?>
            Avoir des indices = indices <br>
            Connaître les sorties = sortie <br>
            Fouiller un endroit = fouiller [nom de l'endroit] <br>
            Connaître son inventaire = inventaire <br>
        <?php else: ?>
            Get hints = hints <br>
            Know the exits = exit <br>
            Search a place = search [place name] <br>
            Know your inventory = inventory <br>
        <?php endif; ?>
    </div>
    <?php if ($file): ?>
    <button id="mapButton" style="position: absolute; top: 30px; right: 0; background-color: white; color: black;">🗺️</button>
    <img id="mapImage" src="<?php echo $url; ?>" 
    alt="<?php echo format_string($moduleinstance->name); ?>" style="display: none; position: absolute; top: 60px; right: 0;">
    <?php endif; ?>
</div>
<input type="text" id="inputText" placeholder="Écrivez quelque chose ici..." style="width: 100%;">
<button onclick="displayInputText()">Valider</button>
<?php
global $COURSE, $USER;
global $USER;
$context = context_course::instance($COURSE->id);

$roles = get_user_roles($context, $USER->id, true);
$role = key($roles);
$rolename = $roles[$role]->shortname;

if ($rolename !== "student") {
    echo '<div>'.
        '<input type="checkbox" id="debug" name="debug" />'.
        '<label for="debug">Debug : </label>'.
        '</div>';
}
?>


<script type = "text/javascript">
    const container = document.getElementById('container');

// Function to scroll to bottom
function scrollToBottom() {
  container.scrollTop = container.scrollHeight;
}

// Call scrollToBottom initially
scrollToBottom();

// Add event listener to detect content changes (replace with your logic)
document.getElementById('text').addEventListener('DOMSubtreeModified', scrollToBottom);


    var mapButton = document.getElementById('mapButton');
    var mapImage = document.getElementById('mapImage');

    if (mapButton && mapImage) {
    mapButton.addEventListener('mouseover', function() {
        mapImage.style.display = 'block';
    });

    mapButton.addEventListener('mouseout', function() {
        mapImage.style.display = 'none';
    });
}

    document.getElementById('helpButton').addEventListener('mouseover', function() {
        document.getElementById('helpText').style.display = 'block';
    });
    document.getElementById('helpButton').addEventListener('mouseout', function() {
        document.getElementById('helpText').style.display = 'none';
    });
    function displayDescription() {
        var csvcontent = <?php echo json_encode($csvcontent); ?>;
        var returnedtext = '';

        fetch(`handle_post.php`, { 
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'inputText=description&debug=false&csvcontent=' + encodeURIComponent(csvcontent),
        })
        .then(response => response.text())
        .then(text => {
            returnedtext = text;
            jsontext = JSON.parse(text);
            typeWriter(document.getElementById("text"), jsontext[0], "red");
        })
        .then(() => {
            inputText.disabled = false;
            inputText.value = '';
        })
        .catch((error) => {
            typeWriter(document.getElementById("text"), returnedtext, "white");
            typeWriter(document.getElementById("text"), 'Error : '+error, "yellow");
            inputText.disabled = false;
            inputText.value = '';
        });
    }

    window.onload = displayDescription;

    function typeWriter(element, txt, color) {
        return new Promise((resolve, reject) => {
            element.innerHTML += `<span style="color:${color};">${txt}</span>`;
            element.innerHTML += "<br>";
        });
    }

    document.getElementById("inputText").addEventListener("keyup", function(event) {
        if (event.keyCode === 13) { // Vérifie si la touche est "Entrée"
            displayInputText(); // Appelle la fonction displayInputText()
        }
    });

    function displayInputText() {
        var inputText = document.getElementById("inputText");
        inputText.disabled = true;
        var csvcontent = <?php echo json_encode($csvcontent); ?>;
        var debug = document.getElementById('debug').checked;
        var returnedtext = '';
        fetch(`handle_post.php`, { 
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'inputText=' + encodeURIComponent(inputText.value)
            + '&debug=' + debug + '&csvcontent=' + encodeURIComponent(csvcontent),
        })
        .then((response) => response.text())
        .then((text) => {
            returnedtext = text;
            jsontext = JSON.parse(text);
            typeWriter(document.getElementById("text"), inputText.value, "blue");
            var debug = document.getElementById('debug').checked;
            if (debug) {
                jsontext[1].forEach((element) => {
                    if(element.length > 0) {
                        typeWriter(document.getElementById("text"), "debug : "+element, "yellow");
                    }
                })
            }
            typeWriter(document.getElementById("text"), jsontext[0], "red");
            inputText.disabled = false;
            inputText.value = '';
        })
        .catch((error) => {
            typeWriter(document.getElementById("text"), returnedtext, "white");
            typeWriter(document.getElementById("text"), 'Error : '+error, "yellow");
            inputText.disabled = false;
            inputText.value = '';
        });
    }
</script>

<?php
echo $OUTPUT->footer();

