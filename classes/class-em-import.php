<?php
class EM_Import {

    public static function init(){
        add_action('admin_menu',[__CLASS__,'menu']);
    }

    public static function menu(){

        add_submenu_page(
            'edit.php?post_type=em_result',
            'Import Results',
            'Import CSV',
            'manage_options',
            'em-import',
            [__CLASS__,'page']
        );
    }

    public static function page()
    {
        if(isset($_POST['import']) && !empty($_FILES['csv']['tmp_name'])){

            $file = $_FILES['csv']['tmp_name'];
            $handle = fopen($file,'r');
            $header = fgetcsv($handle); 
            $results = [];

            while(($data = fgetcsv($handle)) !== false){

                $row = array_combine($header,$data);

                $student_id = intval($row['student_id']);
                $exam_id    = intval($row['exam_id']);
                $subject_id = intval($row['subject_id']);
                $marks      = intval($row['marks']);

                // Validation
                $student = get_post($student_id);
                $exam    = get_post($exam_id);
                $subject = get_post($subject_id);

                if(!$student || $student->post_type !== 'em_student'){
                    continue; // skip invalid student
                }
                if(!$exam || $exam->post_type !== 'em_exam'){
                    continue; // skip invalid exam
                }
                if(!$subject || $subject->post_type !== 'em_subject'){
                    continue; // skip invalid subject
                }
                
                $marks = intval($marks);
                if($marks < 0 || $marks > 100){
                    continue; // skip if marks are greater than 100
                }

                // group results by exam -> student -> subject
                $results[$exam_id][$student_id][$subject_id] = $marks;
            }

            fclose($handle);

            // Save results
            foreach($results as $exam_id => $students){

                $result = get_posts([
                    'post_type'   => 'em_result',
                    'meta_key'    => '_exam_id',
                    'meta_value'  => $exam_id,
                    'numberposts' => 1
                ]);

                if(empty($result)){
                    continue;
                }

                $result_id = $result[0]->ID;
                $existing_marks = get_post_meta($result_id,'_marks',true);

                if(!is_array($existing_marks)){
                    $existing_marks = [];
                }

                foreach($students as $student_id => $subjects){
                    foreach($subjects as $subject_id => $marks){
                        $existing_marks[$student_id][$subject_id] = $marks;
                    }
                }

                update_post_meta($result_id, '_marks', $existing_marks);
            }

            echo '<div class="updated"><p>Import completed successfully.</p></div>';
        }

        ?>

        <div class="wrap">
            <h2>CSV Sample File</h2>
            <a href="<?php echo EM_PLUGIN_URL.'sample-CSV.csv'; ?>" download class="button">
                Download Sample CSV
            </a>
            <br/><br/>
            <h2>Import Results CSV</h2>
            <p>CSV Format: student_id, exam_id, subject_id, marks</p>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="csv" required>
                <br><br>
                <button class="button button-primary" name="import">
                    Import Results
                </button>
            </form>
        </div>
     <?php
    }
}