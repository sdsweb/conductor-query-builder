<?php
/**
 * Conductor Admin Query Builder Views (controller)
 *
 * @class Conductor_Query_Builder_Admin_Views
 * @author Slocum Studio
 * @version 1.0.4
 * @since 1.0.0
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Conductor_Query_Builder_Admin_Views' ) ) {
	final class Conductor_Query_Builder_Admin_Views {
		/**
		 * @var string
		 */
		public $version = '1.0.4';

		/**
		 * @var array
		 */
		public static $options = false;
		/**
		 * @var Conductor, Instance of the class
		 */
		protected static $_instance;

		/**
		 * Function used to create instance of class.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) )
				self::$_instance = new self();

			return self::$_instance;
		}


		/**
		 * This function sets up all of the actions and filters on instance. It also loads (includes)
		 * the required files and assets.
		 */
		function __construct() {
			add_action( 'admin_footer', array( $this, 'admin_footer' ) ); // Admin Footer
		}

		/**
		 * This function outputs scripts in the admin footer.
		 */
		public function admin_footer( $the_hook_suffix = '' ) {
			global $post, $hook_suffix;

			// If we don't have a hook suffix argument
			$the_hook_suffix = ( $the_hook_suffix === '' ) ? $hook_suffix : $the_hook_suffix;

			// Grab the Conductor Query Builder instance
			$conductor_query_builder = Conduct_Query_Builder();

			// Grab the post meta
			$post_meta = $conductor_query_builder->get_post_meta( get_post_field( 'ID', $post ) );

			// Grab the Conductor Widget instance
			$conductor_widget = Conduct_Widget();

			// Grab the Conductor Widget instance data
			$conductor_widget_instance = $conductor_query_builder->get_conductor_widget_instance( get_post_field( 'ID', $post ) );

			// Meta Box Query Builder Tab Content Actions UnderscoreJS Template
			self::meta_box_query_builder_tab_content_actions( $post, $post_meta, $conductor_query_builder, $conductor_widget_instance, $conductor_widget );

			// Meta Box Query Builder Tab Content Clause Group UnderscoreJS Template
			self::meta_box_query_builder_tab_content_clause_group( $post, $post_meta, $conductor_query_builder, $conductor_widget_instance, $conductor_widget );

			// Meta Box Query Builder Tab Content Sub-Clause Group UnderscoreJS Template
			self::meta_box_query_builder_tab_content_sub_clause_group( $post, $post_meta, $conductor_query_builder, $conductor_widget_instance, $conductor_widget );

			// If we're on a page that supports the Conductor Query Builder
			if ( apply_filters( 'conductor_query_builder_admin_footer', get_post_type( $post ) !== $conductor_query_builder->post_type_name && $the_hook_suffix && in_array( $the_hook_suffix, array( 'post.php', 'post-new.php', 'page.php', 'page-new.php' ) ), $the_hook_suffix, $hook_suffix, $post, $conductor_query_builder, $conductor_widget, $this ) ) {
				// Shortcode query builder template
				$conductor_query_builder->shortcode_query_builder();

				// Shortcode insert UnderscoreJS Template
				self::shortcode_query_builder_insert( $post, $post_meta,$conductor_query_builder, $conductor_widget_instance, $conductor_widget );

				// Shortcode actions UnderscoreJS Template
				self::shortcode_query_builder_actions_js( $post, $post_meta,$conductor_query_builder, $conductor_widget_instance, $conductor_widget );

				// Shortcode create title UnderscoreJS Template
				self::shortcode_query_builder_create_title_js( $post, $post_meta,$conductor_query_builder, $conductor_widget_instance, $conductor_widget );
			}
		}


		/********************
		 * Helper Functions *
		 ********************/

		// TODO: Filters here to allow for templates to be over-ridden

		/**
		 * This function renders the shortcode insert query builder content.
		 */
		public static function shortcode_query_builder_insert_tab_content( $post, $post_meta, $conductor_query_builder, $conductor_widget_instance, $conductor_widget ) {
			require 'views/html-shortcode-query-builder-insert-tab-content.php';
		}

		/**
		 * This function renders the shortcode create query builder content.
		 */
		public static function shortcode_query_builder_create_tab_content( $post, $post_meta, $conductor_query_builder, $conductor_widget_instance, $conductor_widget, $form_element ) {
			require 'views/html-shortcode-query-builder-create-tab-content.php';
		}

		/**
		 * This function renders the shortcode actions query builder content.
		 */
		public static function shortcode_query_builder_actions( $post, $post_meta, $conductor_query_builder, $conductor_widget_instance, $conductor_widget ) {
			require 'views/html-shortcode-query-builder-actions.php';
		}

		/**
		 * This function renders the shortcode query builder insert UnderscoreJS template.
		 */
		public static function shortcode_query_builder_insert( $post, $post_meta, $conductor_query_builder, $conductor_widget_instance, $conductor_widget ) {
			require_once 'views/js-shortcode-query-builder-insert.php';
		}

		/**
		 * This function renders the shortcode query builder actions UnderscoreJS template.
		 */
		public static function shortcode_query_builder_actions_js( $post, $post_meta, $conductor_query_builder, $conductor_widget_instance, $conductor_widget ) {
			require_once 'views/js-shortcode-query-builder-actions.php';
		}

		/**
		 * This function renders the shortcode query builder create title UnderscoreJS template.
		 */
		public static function shortcode_query_builder_create_title_js( $post, $post_meta, $conductor_query_builder, $conductor_widget_instance, $conductor_widget ) {
			require_once 'views/js-shortcode-query-builder-create-title.php';
		}

		/**
		 * This function renders the meta box query builder tab content.
		 */
		public static function meta_box_query_builder_tab_content( $post, $post_meta, $conductor_query_builder, $conductor_widget_instance, $conductor_widget ) {
			require 'views/html-meta-box-query-builder-tab-content.php';
		}

		/**
		 * This function renders the meta box query builder tab content actions UnderscoreJS template.
		 */
		public static function meta_box_query_builder_tab_content_actions( $post, $post_meta, $conductor_query_builder, $conductor_widget_instance, $conductor_widget ) {
			require_once 'views/js-meta-box-query-builder-tab-content-actions.php';
		}

		/**
		 * This function renders the meta box query builder tab content clause group UnderscoreJS template.
		 */
		public static function meta_box_query_builder_tab_content_clause_group( $post, $post_meta, $conductor_query_builder, $conductor_widget_instance, $conductor_widget ) {
			require_once 'views/js-meta-box-query-builder-tab-content-clause-group.php';
		}

		/**
		 * This function renders the meta box query builder tab content sub-clause group UnderscoreJS template.
		 */
		public static function meta_box_query_builder_tab_content_sub_clause_group( $post, $post_meta, $conductor_query_builder, $conductor_widget_instance, $conductor_widget ) {
			// TODO: Adjust filter name
			$field_brackets = apply_filters( 'conductor_query_builder_sub_clause_group_field_brackets', array( 'left' => '[', 'right' => ']' ), $post, $post_meta, $conductor_query_builder, $conductor_widget_instance, $conductor_widget );

			// Setup the left and right field brackets
			$left_field_bracket = esc_attr( $field_brackets['left'] );
			$right_field_bracket = esc_attr( $field_brackets['right'] );

			// Setup the meta key prefix
			// TODO: Adjust filter name
			$meta_key_prefix = apply_filters( 'conductor_query_builder_sub_clause_group_meta_key_prefix', esc_attr( rtrim( $conductor_query_builder->meta_key_prefix, '_' ) ), $conductor_query_builder->meta_key_prefix, $field_brackets, $left_field_bracket, $right_field_bracket, $post, $post_meta, $conductor_query_builder, $conductor_widget_instance, $conductor_widget );

			require_once 'views/js-meta-box-query-builder-tab-content-sub-clause-group.php';
		}

		/**
		 * This function renders the meta box output tab content.
		 */
		public static function meta_box_output_tab_content( $post, $post_meta, $conductor_query_builder, $conductor_widget_instance, $conductor_widget ) {
			require 'views/html-meta-box-output-tab-content.php';
		}

		/**
		 * This function renders the meta box advanced tab content.
		 */
		public static function meta_box_advanced_tab_content( $post, $post_meta, $conductor_query_builder, $conductor_widget_instance, $conductor_widget ) {
			require 'views/html-meta-box-advanced-tab-content.php';
		}
	}

	/**
	 * Create an instance of the Conductor_Query_Builder_Admin_Views class.
	 */
	function Conduct_Query_Builder_Admin_Views() {
		return Conductor_Query_Builder_Admin_Views::instance();
	}

	Conduct_Query_Builder_Admin_Views(); // Conduct your content!
}