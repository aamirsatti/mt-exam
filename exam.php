<?php
/**
 * Plugin Name: Exam Management
 * Plugin URI: https://example.com
 * Description: A WordPress plugin for screening senior developer applicants with custom post types for students, exams, results.
 * Version: 1.0.0
 * Author: Muhammad Aamir Atiq
 * Author URI: https://aamirstudio.com/
 * License: GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Define constants
define( 'EM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Custom Post Types and Taxonomy
class EM_CPT {
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_cpts' ) );
		add_action( 'init', array( __CLASS__, 'register_taxonomy' ) );
	}

	public static function register_cpts() {
		// Students CPT
		register_post_type( 'em_student', array(
			'labels'       => array(
				'name'          => 'Students',
				'singular_name' => 'Student',
			),
			'public'       => true,
			'capability_type' => 'post',
			'supports'     => array( 'title' ),
			'menu_icon'    => 'dashicons-groups',
			'show_in_rest' => true,
		) );

		// Subjects CPT
		register_post_type( 'em_subject', array(
			'labels'       => array(
				'name'          => 'Subjects',
				'singular_name' => 'Subject',
			),
			'public'       => true,
			'capability_type' => 'post',
			'supports'     => array( 'title' ),
			'menu_icon'    => 'dashicons-book-alt',
			'show_in_rest' => true,
		) );

		// Exams CPT
		register_post_type( 'em_exam', array(
			'labels'       => array(
				'name'          => 'Exams',
				'singular_name' => 'Exam',
			),
			'public'       => true,
			'capability_type' => 'post',
			'supports'     => array( 'title', ),
			'menu_icon'    => 'dashicons-book',
			'show_in_rest' => true,
		) );

		// Results CPT
		register_post_type( 'em_result', array(
			'labels'       => array(
				'name'          => 'Results',
				'singular_name' => 'Result',
			),
			'public'       => true,
			'capability_type' => 'post',
			'supports'     => array( 'title' ),
			'menu_icon'    => 'dashicons-performance',
			'show_in_rest' => true,
		) );
	}

	public static function register_taxonomy() {
		// Terms Taxonomy
		register_taxonomy( 'em_term', array( 'em_exam' ), array(
			'labels'       => array(
				'name'          => 'Terms',
				'singular_name' => 'Term',
			),
			'public'       => true,
			'hierarchical' => false,
			'show_ui'      => true,
			'show_in_rest' => true,
			"show_admin_column"  => true,
		) );

		// Class taxonomy for Students
		register_taxonomy( 'em_class', array( 'em_student' ), array(
			'labels'       => array(
				'name'          => 'Class',
				'singular_name' => 'Class',
			),
			'public'       		 => true,
			'hierarchical' 		 => true,
			'show_ui'      		 => true,
			'show_in_rest' 		 => true,
			"show_in_menu"       => true,
			"show_in_nav_menus"  => true,
			"show_admin_column"  => true,
		) );
		 
	}
}

// Initialize plugin
function em_init() {
	EM_CPT::init();

	// Load additional classes
	require_once EM_PLUGIN_DIR . 'classes/class-em-term-meta.php';
	require_once EM_PLUGIN_DIR . 'classes/class-em-exam-metabox.php';
	require_once EM_PLUGIN_DIR . 'classes/class-em-result-metabox.php';
	require_once EM_PLUGIN_DIR . 'classes/class-em-ajax.php';
	require_once EM_PLUGIN_DIR . 'classes/class-em-shortcodes.php';
	require_once EM_PLUGIN_DIR . 'classes/class-em-import.php';
	require_once EM_PLUGIN_DIR . 'classes/class-em-reports.php';

	// Initialize classes
	EM_Term_meta::init();
	EM_Exam_Metabox::init();
	EM_Ajax::init();
	EM_Shortcodes::init();
	EM_Import::init();
	EM_Reports::init();

	// Result class
	new EM_Result_Metabox();
}
add_action( 'plugins_loaded', 'em_init' );
