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
require(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('reportmodstats', '', null, '', array('pagelayout'=>'report'));

const ALL_CATEGORIES = -1;

echo $OUTPUT->header();

$result = $DB->get_records('course_categories', null, 'name');
		
$table = new html_table();
$table->size = array( '80%', '20%');

$row = array();
$row[] = '<a href=' . $CFG->wwwroot . '/report/modstats/modusage.php?category=' . ALL_CATEGORIES . '>' . get_string('lb_all_categories', 'report_modstats') . '</a>';
$row[] = '<a href=' . $CFG->wwwroot . '/report/modstats/summary.php?category=' . ALL_CATEGORIES . '>' . get_string('link_summary', 'report_modstats') . '</a>';
$table->data[] = $row;

$table->head = array(	get_string('lb_choose_category', 'report_modstats'));
foreach ($result as $cs) {
    $row = array();
    $row[] = '<a href=' . $CFG->wwwroot . '/report/modstats/modusage.php?category=' . $cs->id . '>' . $cs->name . '</a>';
    $row[] = '<a href=' . $CFG->wwwroot . '/report/modstats/summary.php?category=' . $cs->id . '>' . get_string('link_summary', 'report_modstats') . '</a>';
    
    $table->data[] = $row;
}

echo html_writer::table($table);

echo $OUTPUT->footer();