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
require __DIR__ . '/../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once __DIR__ . '/modules.php';

admin_externalpage_setup('reportmodstats', '', null, '', array('pagelayout' => 'report'));

global $SESSION;
const ALL_CATEGORIES = -1;
const NO_DEFINED = -2;

$category = optional_param('category', NO_DEFINED, PARAM_INT);

if ($category != NO_DEFINED) {
  $SESSION->category = $category;
  if ($category != ALL_CATEGORIES) {
    $cat = $DB->get_record('course_categories', array("id" => $category));
    $SESSION->catname = $cat->name;
  } else {
    $SESSION->catname = get_string('lb_all_categories', 'report_modstats');
  }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(
  $SESSION->catname . ' - ' . 
    html_writer::link(
      $CFG->wwwroot . '/report/modstats/index.php', 
      get_string('btn_back', 'report_modstats')
    )
);

$mform = new modules_form();
$mform->display();

if ($SESSION->category == ALL_CATEGORIES) {
  $courses = $DB->get_records_sql(
    'SELECT C.id, C.fullname FROM {course} as C JOIN {course_modules} CM ON C.id = CM.course WHERE C.visible=1 AND CM.module = :mod',
    array('mod' => $mform->get_data()->module)
  );
} else {  
  $courses = $DB->get_records_sql(
    'SELECT C.id, C.fullname FROM {course} as C JOIN {course_modules} CM ON C.id = CM.course WHERE C.visible=1 AND C.category = :cat AND CM.module = :mod',
    array('cat' => $SESSION->category, 'mod' => $mform->get_data()->module)
  );
}

if ($fromform = $mform->get_data()) {
    
    $table = new html_table();
    $table->head = array(
      get_string('lb_course', 'report_modstats')
    );
    foreach ($courses as $course) {
      $table->data[] = array(
          html_writer::link(
              $CFG->wwwroot . '/course/view.php?id=' . $course->id, 
              $course->fullname
            )        
      );
    }
    echo html_writer::table($table);
  }
  

echo $OUTPUT->footer();