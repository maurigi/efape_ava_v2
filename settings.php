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
 * Plugin administration pages are defined here.
 *
 * @package     theme_efape_ava_v2
 * @category    admin
 * @copyright   2021 FCAV
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    $settings = new theme_boost_admin_settingspage_tabs('themesettingefape_ava_v2', get_string('configtitle', 'theme_efape_ava_v2'));

    $page = new admin_settingpage('theme_efape_ava_v2_general', get_string('generalsettings', 'theme_efape_ava_v2'));

    // Raw SCSS to include before the content.                                                                                      
    $setting = new admin_setting_configtextarea(
        'theme_efape_ava_v2/scsspre',
        get_string('rawscsspre', 'theme_efape_ava_v2'),
        get_string('rawscsspre_desc', 'theme_efape_ava_v2'),
        '',
        PARAM_RAW
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Raw SCSS to include after the content.                                                                                       
    $setting = new admin_setting_configtextarea(
        'theme_efape_ava_v2/scss',
        get_string('rawscss', 'theme_efape_ava_v2'),
        get_string('rawscss_desc', 'theme_efape_ava_v2'),
        '',
        PARAM_RAW
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);
}