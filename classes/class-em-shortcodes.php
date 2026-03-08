<?php
class EM_Shortcodes {

    public static function init(){
        add_shortcode('em_top_students',[__CLASS__,'top_students']);
    }

    public static function top_students($atts)
    {  
        $atts = shortcode_atts([
            'limit_terms' => 3,
            'limit_per_term' => 3,
            'show_term_dates' => false,
        ], $atts);

        // Get terms ordered by start date DESC
        $terms = get_terms([
            'taxonomy' => 'em_term',
            'hide_empty' => false,
            'order' => 'DESC',
        ]);
   
        if(empty($terms)){
            return "<p>No academic terms found.</p>";
        }

        ob_start();
        echo "<div class='em-top-students'>";

        $term_count = 0;
        
        foreach($terms as $term){
            if($term_count >= $atts['limit_terms']) break;

            $students = self::get_top_students_in_term($term->term_id, $atts['limit_per_term']);
            
            if(empty($students)) continue;

            echo "<section class='em-term'>";
            echo "<h3>{$term->name}";
            if($atts['show_term_dates']){
                $start = get_term_meta($term->term_id,'em_start_date',true);
                $end = get_term_meta($term->term_id,'em_end_date',true);
                if($start || $end){
                    echo " <small>({$start} – {$end})</small>";
                }
            }
            echo "</h3>";

            echo "<ol class='em-students-list'>";
            foreach($students as $s){
                $avg = round($s['avg'],1);
                echo "<li><strong>{$s['name']}</strong> — {$s['total']}/{$s['max']} <span class='em-average'>({$avg}%)</span></li>";
            }
            echo "</ol>";
            echo "</section>";

            $term_count++;
        }

        echo "</div>";

        // Cache the output
        set_transient('em_top_students_cache', ob_get_clean(), HOUR_IN_SECONDS);

        return get_transient('em_top_students_cache');
    }

    private static function get_top_students_in_term($term_id, $limit){
        // Get all results
        $results = get_posts([
            'post_type' => 'em_result',
            'numberposts' => -1,
            'post_status' => 'publish',
        ]);

        $data = []; // student_id => ['name'=>..., 'total'=>..., 'max'=>...]

        foreach($results as $res){
            $exam_id = get_post_meta($res->ID, '_exam_id', true);
            if(!$exam_id) continue;

            // Check if this exam belongs to this term
            $exam_terms = wp_get_post_terms($exam_id, 'em_term');
            $exam_term_ids = wp_list_pluck($exam_terms, 'term_id');
            if(!in_array($term_id, $exam_term_ids)) continue;

            $marks_arr = get_post_meta($res->ID, '_marks', true);
            $marks_arr = maybe_unserialize($marks_arr);
            if(!is_array($marks_arr)) continue;

            foreach($marks_arr as $student_id => $subjects){
                $student_name = get_the_title($student_id) ?: "Student #{$student_id}";

                if(!isset($data[$student_id])){
                    $data[$student_id] = [
                        'name' => $student_name,
                        'total' => 0,
                        'max' => 0
                    ];
                }

                foreach($subjects as $mark){
                    $mark = intval($mark);
                    if($mark >= 0 && $mark <= 100){
                        $data[$student_id]['total'] += $mark;
                        $data[$student_id]['max'] += 100;
                    }
                }
            }
        }

        // Calculate average per student
        $students = [];
        foreach($data as $d){
            $d['avg'] = $d['max'] > 0 ? ($d['total'] / $d['max']) * 100 : 0;
            $students[] = $d;
        }

        // Sort by average descending
        usort($students, function($a,$b){
            return $b['avg'] <=> $a['avg'];
        });

        return array_slice($students, 0, $limit);
    }
}