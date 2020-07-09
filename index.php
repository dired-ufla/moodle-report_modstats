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
 * @copyright  2020 Paulo Jr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require __DIR__ . '/../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once __DIR__ . '/report_modstats_categories_form.php';
require_once __DIR__ . '/constants.php';

admin_externalpage_setup('reportmodstats', '', null, '', array('pagelayout' => 'report'));

$category = optional_param('category', REPORT_MODSTATS_ALL_CATEGORIES, PARAM_INT);

echo $OUTPUT->header();

$mform = new report_modstats_categories_form();
$mform->display();

if ($category == REPORT_MODSTATS_ALL_CATEGORIES) {
    $data = $DB->get_records_sql(
        'SELECT M.name, M.id, COUNT(CM.id) AS amount FROM {modules} AS M INNER JOIN {course_modules} AS CM ON M.id = CM.module INNER JOIN {course} AS C ON C.id = CM.course WHERE C.visible = 1 GROUP BY M.name, M.id'
    );
    $total = $DB->count_records_sql(
        'SELECT COUNT(CM.id) FROM {course} AS C INNER JOIN {course_modules} AS CM ON C.id = CM.course WHERE C.visible = 1'
    );
} else {  
    $data = $DB->get_records_sql(
        'SELECT M.name, M.id, COUNT(CM.id) AS amount FROM {modules} AS M INNER JOIN {course_modules} AS CM ON M.id = CM.module INNER JOIN {course} AS C ON C.id = CM.course WHERE C.visible = 1 AND C.category = :cat GROUP BY M.name, M.id',
        array("cat" => $category)
    );
    $total = $DB->count_records_sql(
        'SELECT COUNT(CM.id) FROM {course} AS C INNER JOIN {course_modules} AS CM ON C.id = CM.course WHERE C.visible = 1 AND C.category = :cat',
        array("cat" => $category)
    ); 
}

if ($total > 0) {

    $table = new html_table();
    $table->size = array( '40%', '20%', '20%', '20%');
    $table->head = array(get_string('lb_module_name', 'report_modstats'), get_string('lb_module_amount', 'report_modstats'),
        get_string('lb_module_usage', 'report_modstats'), 
        "");

    $chart_labels = array();
    $chart_values = array();

    $index = 1;

    foreach ($data as $item) {
        $row = array();

        $percent = number_format(($item->amount / $total) * 100, 2);

        $chart_labels[] = $index; 
        $chart_values[] = $percent;

        $row[] = $index . " - " . get_string('pluginname', 'mod_' . $item->name);
        $row[] = $item->amount;
        $row[] = $percent;
        $row[] = '<a href=' . $CFG->wwwroot . '/report/modstats/modusage.php?category=' . $category . '&module=' . $item->id . '>' . get_string('link_summary', 'report_modstats') . '</a>';
    
        $index = $index + 1;

        $table->data[] = $row;
    }

    if (class_exists('core\chart_bar')) {
        $chart = new core\chart_bar();
        $serie = new core\chart_series(
            get_string('lb_chart_serie', 'report_modstats'), $chart_values
        );
        $chart->add_series($serie);
        $chart->set_labels($chart_labels);
        echo $OUTPUT->render_chart($chart, false);
    }

    echo html_writer::table($table);
}

echo $OUTPUT->footer();