<?php
class EM_Reports {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'menu']);
        add_action('admin_post_em_export_pdf', [__CLASS__, 'export_pdf']);
    }

    public static function menu() {
        add_menu_page(
            'Exam Reports',
            'Exam Reports',
            'manage_options',
            'em-reports',
            [__CLASS__, 'page'],
            'dashicons-media-spreadsheet',
            30
        );
    }

    public static function page() {

        // Get all terms ordered latest first
        $terms = get_terms([
            'taxonomy' => 'em_term',
            'hide_empty' => false,
            'order' => 'DESC'
        ]);

        $term_names = [];
        foreach ($terms as $t) $term_names[] = $t->name;

        // Get all results
        $results = get_posts([
            'post_type' => 'em_result',
            'numberposts' => -1,
            'post_status' => 'publish'
        ]);

        $data = []; // student_id => ['name'=>student_name, 'terms'=>[term_name=>total], 'average'=>0]

        foreach ($results as $res) {

            $exam_id = get_post_meta($res->ID, '_exam_id', true);
            if (!$exam_id) continue;

            $marks = get_post_meta($res->ID, '_marks', true);
            $marks = maybe_unserialize($marks);
            if (!is_array($marks)) continue;

            // get term of exam
            $exam_terms = wp_get_post_terms($exam_id, 'em_term');
            if (empty($exam_terms)) $exam_terms = [(object)['name' => 'Unknown Term']];

            foreach ($marks as $student_id => $subjects) {
                $student_name = get_the_title($student_id) ?: "Student #{$student_id}";

                foreach ($exam_terms as $term) {
                    $term_name = $term->name;

                    if (!isset($data[$student_name])) {
                        $data[$student_name] = ['terms' => [], 'average' => 0];
                    }

                    if (!isset($data[$student_name]['terms'][$term_name])) {
                        $data[$student_name]['terms'][$term_name] = 0;
                    }

                    foreach ($subjects as $sub_id => $mark) {
                        $data[$student_name]['terms'][$term_name] += intval($mark);
                    }
                }
            }
        }

        // calculate average
        foreach ($data as $student_name => $d) {
            $total_sum = 0;
            foreach ($term_names as $tname) {
                $total_sum += isset($d['terms'][$tname]) ? $d['terms'][$tname] : 0;
            }
            $data[$student_name]['average'] = count($term_names) ? round($total_sum / count($term_names), 2) : 0;
        }

        // display table
        echo '<div class="wrap" ><h1>Student Statistics Report</h1>';
        echo '<button id="print-report" class="button button-primary">Print / Export</button>';
        echo '<div id="student-report"><table class="widefat striped">';
        echo '<thead><tr><th>Student</th>';
        foreach ($term_names as $tname) echo '<th>' . esc_html($tname) . '</th>';
        echo '<th>Average</th></tr></thead><tbody>';

        foreach ($data as $student_name => $d) {
            echo '<tr>';
            echo '<td>' . esc_html($student_name) . '</td>';
            foreach ($term_names as $tname) {
                $total = isset($d['terms'][$tname]) ? $d['terms'][$tname] : 0;
                echo '<td>' . esc_html($total) . '</td>';
            }
            echo '<td>' . esc_html($d['average']) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table></div></div>';
        ?>
        <script>
            document.getElementById('print-report').addEventListener('click', function() {
                var divToPrint = document.getElementById('student-report');
                var newWin = window.open('', 'Print-Window');
                newWin.document.open();
                newWin.document.write('<html><head><title>Student Report</title>');
                newWin.document.write('<style>table{width:100%;border-collapse:collapse;}table, th, td{border:1px solid #000;padding:5px;}th{background:#f0f0f0;}</style>');
                newWin.document.write('</head><body>');
                newWin.document.write(divToPrint.innerHTML);
                newWin.document.write('</body></html>');
                newWin.document.close();
                newWin.focus();
                newWin.print();
                newWin.close();
            });
            </script>
        <?php
    }

}