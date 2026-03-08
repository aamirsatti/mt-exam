<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class EM_Result_Metabox {

    public function __construct() {

        add_action( 'add_meta_boxes', [ $this, 'add_metabox' ] );
        add_action( 'save_post', [ $this, 'save_result' ] );

        add_action( 'wp_ajax_em_load_exam_data', [ $this, 'ajax_load_exam_data' ] );
    }

    /*
    * Add Result Metabox
    */
    public function add_metabox() {

        add_meta_box(
            'em_result_metabox',
            'Result Details',
            [ $this, 'render_metabox' ],
            'em_result',
            'normal',
            'default'
        );

    }

    /*
    * Render Metabox UI
    */
    public function render_metabox( $post ) {

        $selected_exam  = get_post_meta( $post->ID, '_exam_id', true );
        $selected_class = get_post_meta( $post->ID, '_class_id', true );

        $exams = get_posts([
            'post_type'   => 'em_exam',
            'numberposts' => -1
        ]);

        $classes = get_terms([
            'taxonomy'   => 'em_class',
            'hide_empty' => false,
        ]);

        ?>

        <p>
            <label><strong>Select Exam</strong></label>
        </p>

        <select id="em_exam_select" name="exam_id">
            <option value="">Select Exam</option>
            <?php foreach ( $exams as $exam ) : ?>
                <option value="<?php echo $exam->ID; ?>"
                    <?php selected( $selected_exam, $exam->ID ); ?>>
                    <?php echo esc_html( $exam->post_title ); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <p>
            <label><strong>Select Class</strong></label>
        </p>

        <select id="em_class_select" name="class_id">
            <option value="">Select Class</option>
            <?php foreach ( $classes as $class ) : ?>
                <option value="<?php echo $class->term_id; ?>"
                    <?php selected( $selected_class, $class->term_id ); ?>>
                    <?php echo esc_html( $class->name ); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <hr>

        <div id="em_result_table">
            <?php
            if ( $selected_exam ) {
                $this->render_table( $selected_exam, $post->ID, $selected_class );
            }
            ?>
        </div>

        <script>
        jQuery(document).ready(function($){

            function load_exam_data() {
                var exam_id = $('#em_exam_select').val();
                var class_id = $('#em_class_select').val();

                if( !exam_id ) return;

                $('#em_result_table').html('Loading...');

                $.post(ajaxurl, {
                    action   : 'em_load_exam_data',
                    exam_id  : exam_id,
                    class_id : class_id
                }, function(response){
                    $('#em_result_table').html(response);
                });
            }

            $('#em_exam_select, #em_class_select').change(load_exam_data);

        });
        </script>

        <?php
    }

    /*
    * Render Result Table
    */
    private function render_table( $exam_id, $result_post_id, $class_id = 0 ) {

        $subjects = get_post_meta( $exam_id, 'subjects', true );

        if ( empty($subjects) ) {
            echo '<p>No subjects found for this exam.</p>';
            return;
        }

        if ( ! is_array($subjects) ) {
            $subjects = explode(',', $subjects);
        }

        // Fetch students with optional class filter
        $tax_query = [];
        if ( $class_id ) {
            $tax_query[] = [
                'taxonomy' => 'em_class',
                'field'    => 'term_id',
                'terms'    => intval($class_id),
            ];
        }

        $students = get_posts([
            'post_type'   => 'em_student',
            'numberposts' => -1,
            'tax_query'   => $tax_query
        ]);

        $marks = get_post_meta( $result_post_id, '_marks', true );
        if ( ! is_array( $marks ) ) {
            $marks = [];
        }

        echo '<table class="widefat striped">';
        echo '<thead><tr>';
        echo '<th>Student</th>';
        foreach ( $subjects as $subject ) {
            echo '<th>' . esc_html( get_the_title( $subject ) ) . '</th>';
        }
        echo '</tr></thead>';

        echo '<tbody>';
        foreach ( $students as $student ) {
            echo '<tr>';
            echo '<td>' . esc_html( $student->post_title ) . '</td>';
            foreach ( $subjects as $subject ) {
                $value = $marks[$student->ID][$subject] ?? '';
                echo '<td><input type="number" name="marks['.$student->ID.']['.$subject.']" value="'.esc_attr($value).'" style="width:80px;" max="100"></td>';
            }
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';

    }

    /*
    * Save Result
    */
    public function save_result( $post_id ) {

        if ( isset( $_POST['exam_id'] ) ) {
            update_post_meta( $post_id, '_exam_id', intval( $_POST['exam_id'] ) );
        }

        if ( isset( $_POST['class_id'] ) ) {
            update_post_meta( $post_id, '_class_id', intval( $_POST['class_id'] ) );
        }

        if ( isset( $_POST['marks'] ) ) {
            update_post_meta( $post_id, '_marks', $_POST['marks'] );
        }

    }

    /*
    * AJAX Load Students + Subjects
    */
    public function ajax_load_exam_data() {

        $exam_id  = intval( $_POST['exam_id'] );
        $class_id = intval( $_POST['class_id'] ?? 0 );

        $subjects = get_post_meta( $exam_id, 'subjects', true );

        if ( empty($subjects) ) {
            echo '<p>No subjects found for this exam.</p>';
            wp_die();
        }

        if ( ! is_array($subjects) ) {
            $subjects = explode(',', $subjects);
        }

        $tax_query = [];
        if ( $class_id ) {
            $tax_query[] = [
                'taxonomy' => 'em_class',
                'field'    => 'term_id',
                'terms'    => $class_id,
            ];
        }

        $students = get_posts([
            'post_type'   => 'em_student',
            'numberposts' => -1,
            'tax_query'   => $tax_query
        ]);

        echo '<table class="widefat striped">';
        echo '<thead><tr>';
        echo '<th>Student</th>';
        foreach ( $subjects as $subject ) {
            echo '<th>' . esc_html( get_the_title( $subject ) ) . '</th>';
        }
        echo '</tr></thead>';

        echo '<tbody>';
        foreach ( $students as $student ) {
            echo '<tr>';
            echo '<td>' . esc_html( $student->post_title ) . '</td>';
            foreach ( $subjects as $subject ) {
                echo '<td><input type="number" name="marks['.$student->ID.']['.$subject.']" style="width:80px;" max="100"></td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';

        wp_die();

    }

}