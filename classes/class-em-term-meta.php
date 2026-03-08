<?php
class EM_Term_meta {
    public static function init() {

        add_action('em_term_add_form_fields', [__CLASS__, 'add_fields']);
        add_action('em_term_edit_form_fields', [__CLASS__, 'edit_fields']);

        add_action('created_em_term', [__CLASS__, 'save']);
        add_action('edited_em_term', [__CLASS__, 'save']);
    }

    public static function add_fields() {
        ?>

        <div class="form-field">
            <label>Start Date</label>
            <input type="date" name="start_date">
        </div>

        <div class="form-field">
            <label>End Date</label>
            <input type="date" name="end_date">
        </div>

        <?php
    }

    public static function edit_fields($term) {

        $start = get_term_meta($term->term_id, 'start_date', true);
        $end = get_term_meta($term->term_id, 'end_date', true);
        ?>

        <tr class="form-field">
            <th>Start Date</th>
            <td><input type="date" name="start_date" value="<?php echo esc_attr($start); ?>"></td>
        </tr>

        <tr class="form-field">
            <th>End Date</th>
            <td><input type="date" name="end_date" value="<?php echo esc_attr($end); ?>"></td>
        </tr>

        <?php
    }

    public static function save($term_id) {

        if(isset($_POST['start_date']))
            update_term_meta($term_id, 'start_date', $_POST['start_date']);

        if(isset($_POST['end_date']))
            update_term_meta($term_id, 'end_date', $_POST['end_date']);
    }
}   
