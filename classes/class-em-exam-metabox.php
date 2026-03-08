<?php
class EM_Exam_Metabox {
    public static function init() {

        add_action('add_meta_boxes', [__CLASS__, 'add_box']);
        add_action('save_post_em_exam', [__CLASS__, 'save']);
    }

    public static function add_box() {

        add_meta_box(
            'em_exam_details',
            'Exam Details',
            [__CLASS__, 'render'],
            'em_exam'
        );
    }

    public static function render($post) {

        $start = get_post_meta($post->ID,'start_datetime',true);
        $end = get_post_meta($post->ID,'end_datetime',true);

        $subjects = get_posts([
            'post_type'=>'em_subject',
            'numberposts'=>-1
        ]);

        $selected = get_post_meta($post->ID,'subjects',true);

        ?>

        <p>
            Start Time
            <input type="datetime-local" name="start_datetime" value="<?php echo esc_attr($start); ?>">
        </p>

        <p>
            End Time
            <input type="datetime-local" name="end_datetime" value="<?php echo esc_attr($end); ?>">
        </p>

        <p>Subjects</p>

        <?php

        foreach($subjects as $sub){

            $checked = (is_array($selected) && in_array($sub->ID,$selected)) ? 'checked' : '';

            echo '<label>
                    <input type="checkbox" name="subjects[]" value="'.$sub->ID.'" '.$checked.'>
                    '.$sub->post_title.'
                 </label><br>';
        }

    }

    public static function save($post_id) {

        if(isset($_POST['start_datetime']))
            update_post_meta($post_id,'start_datetime',$_POST['start_datetime']);

        if(isset($_POST['end_datetime']))
            update_post_meta($post_id,'end_datetime',$_POST['end_datetime']);

        if(isset($_POST['subjects']))
            update_post_meta($post_id,'subjects',$_POST['subjects']);
    }
}
