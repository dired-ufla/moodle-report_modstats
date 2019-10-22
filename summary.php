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

$category = required_param('category', PARAM_INT);

if ($category != ALL_CATEGORIES) {
    $catdb = $DB->get_record('course_categories', array("id" => $category));
    $catname = $catdb->name; 
} else {
    $catname = get_string('lb_all_categories', 'report_modstats');
}

echo $OUTPUT->header();
echo $OUTPUT->heading(
  $catname . ' - ' . 
    html_writer::link(
      $CFG->wwwroot . '/report/modstats/index.php', 
      get_string('link_back', 'report_modstats')
    )
);

if ($category == ALL_CATEGORIES) {
  $data = $DB->get_records_sql(
    'SELECT M.name, COUNT(CM.id) as amount FROM {modules} as M INNER JOIN {course_modules} as CM INNER JOIN {course} as C ON M.id = CM.module AND C.id = CM.course WHERE C.visible = 1 GROUP BY M.name'
  );
  $total = $DB->count_records_sql(
    'SELECT COUNT(CM.id) FROM {course} as C INNER JOIN {course_modules} as CM ON C.id = CM.course WHERE C.visible = 1'
  );
} else {  
    $data = $DB->get_records_sql(
        'SELECT M.name, COUNT(CM.id) as amount FROM {modules} as M INNER JOIN {course_modules} as CM INNER JOIN {course} as C ON M.id = CM.module AND C.id = CM.course WHERE C.visible = 1 AND C.category = :cat GROUP BY M.name',
        array("cat" => $category)
    );
    $total = $DB->count_records_sql(
        'SELECT COUNT(CM.id) FROM {course} as C INNER JOIN {course_modules} as CM ON C.id = CM.course WHERE C.visible = 1 AND C.category = :cat',
        array("cat" => $category)
      ); 
}

if ($total > 0) {

    $chart_labels = array();
    $chart_values = array();

    foreach ($data as $item) {
        $chart_labels[] = $item->name;
        $chart_values[] = number_format(($item->amount / $total) * 100, 2);
    }

    if (class_exists('core\chart_bar')) {
        $chart = new core\chart_bar();
        $serie = new core\chart_series(
            get_string('lb_chart_serie', 'report_modstats'), $chart_values
        );
        $chart->add_series($serie);
        $chart->set_labels($chart_labels);
        echo $OUTPUT->render_chart($chart);
    }
}

echo $OUTPUT->footer();