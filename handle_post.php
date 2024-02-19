<?php
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
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Entity.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Location.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Player_Character.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Game.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Hint.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Inventory.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/No_Entity_Reaction.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Npc_Character.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Util.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Default_Action.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Character_Reaction.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Location_Reaction.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Inventory_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Action.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Default_Action_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Action_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/App_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Game_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Hint_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Item_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/interfaces/Reaction_Interface.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/Id_Class.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/classes/App.php');
require_once($CFG->dirroot . '/mod/serioustextualgame/src/Language.php');



$csvcontent = $_POST['csvcontent'];
$tempfilepath = tempnam(sys_get_temp_dir(), 'mod_serioustextualgame');
file_put_contents($tempfilepath, $csvcontent);

$app = new App($tempfilepath, Language::FR);
if (isset($_SESSION['conditionsdone'])) {
    $conditionsdone = $_SESSION['conditionsdone'];
    $conditionsdone= unserialize($conditionsdone);
    var_dump($conditionsdone);
    $app->do_actionsdone($conditionsdone);
}
$game = $app->get_game();
$inputText = $_POST['inputText'];
$currentlocation = $game->get_current_location();

$action = $currentlocation->check_actions($inputText);


if ($action[0][0] == "") {
    echo "mauvais parsage";
} else {
    echo $action[0][0];
}

$conditionsdone = $app->get_actionsdone();
$conditionsdone = serialize($conditionsdone);
$_SESSION['conditionsdone'] = $conditionsdone;


exit();
