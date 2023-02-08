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

/**
 * A two column layout for the EFAPE AVA V2 theme.
 *
 * @package   theme_efape_ava_v2
 * @copyright 2021 FCAV
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$PAGE->requires->js_call_amd('theme_efape_ava_v2/efape', 'init');

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
require_once($CFG->libdir . '/behat/lib.php');

if (isloggedin()) {
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
} else {
    $navdraweropen = false;
}
$extraclasses = [];
if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}

// User role based CSS classes
for ($i = 1; $i <= 30; $i++) {
    if (user_has_role_assignment($USER->id, $i)) {
        $extraclasses[] = "has-role-" . $i;
    }
}

// número de identificação do curso
$idcourse = $COURSE->id;
$idnumber = $COURSE->idnumber;
$hasidnumber = false;

if ($idnumber != '') {
    $hasidnumber = true;
}

if ($idcourse == 1) {
    $hasidnumber = true;
    $idnumber = 'ava';
}

$date = date('d-m-y h:i:s');

// adiciona class no body com o número de identificação do curso
$extraclasses[] = $idnumber;

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;
$buildregionmainsettings = !$PAGE->include_region_main_settings_in_header_actions();
// If the settings menu will be included in the header then don't add it here.
$regionmainsettingsmenu = $buildregionmainsettings ? $OUTPUT->region_main_settings_menu() : false;
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'navdraweropen' => $navdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'hasidnumber' => $hasidnumber,
    'idnumber' => $idnumber,
    'date' => $date
];

$nav = $PAGE->flatnav;
$templatecontext['flatnavigation'] = $nav;
$templatecontext['firstcollectionlabel'] = $nav->get_collectionlabel();
echo $OUTPUT->render_from_template('theme_efape_ava_v2/columns2', $templatecontext);