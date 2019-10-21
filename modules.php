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
 * Report main page
 *
 * @package    report
 * @copyright  2019 Paulo Jr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once $CFG->libdir . '/formslib.php';

class modules_form extends moodleform {
    public function definition() {
        global $DB;

        $mform = $this->_form; // Don't forget the underscore! 

        $modules = $DB->get_records_menu('modules', null, 'name');
        
        $mform->addElement('select', 'module', get_string('lb_choose_module', 'report_modstats'), $modules);
        $this->add_action_buttons(false, get_string('btn_refresh', 'report_modstats'));
    }
}
