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

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

$action = required_param('action', PARAM_ALPHA);
$instanceid = required_param('instanceid', PARAM_INT);

if (!$bi = $DB->get_record('block_instances', array('id' => $instanceid))) {
    print_error('Missing block instance!'); //TODO: Stringify
}
$config = unserialize(base64_decode($bi->configdata));

// Check login and get context.
$context = get_context_instance(CONTEXT_BLOCK, $instanceid);
$cid = get_courseid_from_context($context);
if ($cid === false) {
    $cid = SITEID;
}
require_login($cid);
require_capability('block/poll:editpoll', $context);

if ($action == 'configblock') {
    $url = new moodle_url('/course/view.php', array('id' => $cid, 'sesskey' => $USER->sesskey, 'bui_editid' => $instanceid));
    if ($bi->pagetypepattern == 'my-index') {
        $url = new moodle_url('/my/index.php', array('sesskey' => $USER->sesskey, 'bui_editid' => $instanceid));
    }
    redirect($url);
}

$tabs = array();
$tabnames = array('configblock', 'editpoll', 'managepolls', 'responses');
foreach ($tabnames as $tabname) {
    $params = array('action' => $tabname, 'cid' => $cid, 'instanceid' => $instanceid);
    $url = new moodle_url('/blocks/poll/tabs.php', $params);
    $tabs[] = new tabObject($tabname, $url, get_string('tab' . $tabname, 'block_poll'));
}

if (!in_array($action, $tabnames)) {
    $action = 'configblock';
}

$PAGE->set_url('/blocks/poll/tabs.php');
$PAGE->set_context($context);
$PAGE->requires->css('/blocks/poll/styles.css');
echo $OUTPUT->header();

print_tabs(array($tabs), $action);

echo html_writer::empty_tag('br');
include("tab_$action.php");

echo $OUTPUT->footer();
