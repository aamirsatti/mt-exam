<?php
class EM_Ajax {

    public static function init(){

        add_action('wp_ajax_em_get_exams',[__CLASS__,'get_exams']);
    }

    public static function get_exams(){

        $term_id = isset($_POST['term_id']) ? intval($_POST['term_id']) : 0;
        $page    = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = 10;

        $args = [
            'post_type'      => 'em_exam',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'post_status'    => 'publish'
        ];

        if($term_id){
            $args['tax_query'] = [
                [
                    'taxonomy' => 'em_term',
                    'field'    => 'term_id',
                    'terms'    => $term_id
                ]
            ];
        }

        $query = new WP_Query($args);

        $today = current_time('Y-m-d');

        $ongoing  = [];
        $upcoming = [];
        $past     = [];

        if($query->have_posts()){

            while($query->have_posts()){
                $query->the_post();

                $id    = get_the_ID();
                $start = get_post_meta($id,'exam_start_date',true);
                $end   = get_post_meta($id,'exam_end_date',true);

                $exam = [
                    'id'    => $id,
                    'title' => get_the_title(),
                    'start' => $start,
                    'end'   => $end
                ];

                if($start && $end){

                    if($start <= $today && $end >= $today){
                        $ongoing[] = $exam;
                    }
                    elseif($start > $today){
                        $upcoming[] = $exam;
                    }
                    else{
                        $past[] = $exam;
                    }
                } else {
                    $past[] = $exam;
                }
            }

            wp_reset_postdata();
        }

        // Required ordering
        $data = array_merge($ongoing,$upcoming,$past);

        wp_send_json_success([
            'exams' => $data,
            'page'  => $page,
            'total' => $query->found_posts
        ]);
    }
}