<?php
/**
 * Conductor Query Builder - Beaver Builder - Conductor REST API
 *
 * @class Conductor_Query_Builder_Beaver_Builder_Conductor_REST_API
 * @author Slocum Studio
 * @version 1.0.0
 * @since 1.0.4
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Conductor_Query_Builder_Beaver_Builder_Conductor_REST_API' ) ) {
	final class Conductor_Query_Builder_Beaver_Builder_Conductor_REST_API {
		/**
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * @var array
		 */
		public $current_widget_instance = array();

		/**
		 * @var array
		 */
		public $current_module = false;

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
			// Conductor REST API Hooks
			add_filter( 'conductor_query_builder_conductor_rest_api_conductor_widget_instance', array( $this, 'conductor_query_builder_conductor_rest_api_conductor_widget_instance' ), 10, 9 ); // Conductor Query Builder - Conductor REST API - Conductor Widget Instance
			add_filter( 'conductor_query_builder_conductor_rest_api_conductor_widget_id', array( $this, 'conductor_query_builder_conductor_rest_api_conductor_widget_id' ), 10, 10 ); // Conductor Query Builder - Conductor REST API - Conductor Widget ID
			add_filter( 'conductor_query_builder_conductor_rest_api_query_builder_query_args', array( $this, 'conductor_query_builder_conductor_rest_api_query_builder_query_args' ), 10, 10 ); // Conductor Query Builder - Conductor REST API - Query Builder - Query Arguments
			add_filter( 'conductor_query_builder_conductor_rest_api_query_builder_mode', array( $this, 'conductor_query_builder_conductor_rest_api_query_builder_mode' ), 10, 10 ); // Conductor Query Builder - Conductor REST API - Query Builder Mode
			add_filter( 'conductor_query_builder_conductor_rest_api_query_builder_conductor_widget_instance', array( $this, 'conductor_query_builder_conductor_rest_api_query_builder_conductor_widget_instance' ), 10, 10 ); // Conductor Query Builder - Conductor REST API - Query Builder - Conductor Widget Instance
			add_action( 'conductor_rest_widget_query_after', array( $this, 'conductor_rest_widget_query_after' ), 10, 8 ); // Conductor REST - Widget Query after
		}


		/**
		 * This function adjusts the Conductor Query Builder Conductor REST API Conductor Widget instance.
		 */
		public function conductor_query_builder_conductor_rest_api_conductor_widget_instance( $instance, $type, $query_builder_data, $widget_number, $data, $conductor_options, $conductor_widget, $conductor_rest_api, $conductor_query_builder_conductor_rest_api ) {
			global $post;

			// Bail if this isn't a Beaver Builder "widget"
			if ( $type !== 'beaver-builder' )
				return $instance;

			// Grab the post ID from the pagenum link
			$post_id = ( isset( $_REQUEST['pagenum_link'] ) ) ? url_to_postid( sanitize_text_field( $_REQUEST['pagenum_link'] ) ) : false;

			// If we have a post ID
			if ( $post_id ) {
				// Set the global post reference
				$post = get_post( $post_id );

				// Grab the Beaver Builder modules on the post
				$modules = FLBuilderModel::get_all_modules();

				// If we have modules
				if ( ! empty( $modules ) ) {
					// Loop through the modules
					foreach ( $modules as $module ) {
						// if this module's node matches the query builder data post ID
						if ( $module->node === $query_builder_data['post_id'] ) {
							// Grab the Conductor Query Builder instance
							$conductor_query_builder = Conduct_Query_Builder();

							// Conductor Query Builder Conductor Widget key
							$conductor_query_builder_conductor_widget_key = $conductor_query_builder->meta_key_prefix . $conductor_query_builder->conductor_widget_meta_key_suffix;

							// Conductor Query Builder Title key
							$conductor_query_builder_title_key = $conductor_query_builder->meta_key_prefix . 'title';

							// Set the instance
							$instance = $module->settings->{$conductor_query_builder_conductor_widget_key};

							// Set the title on the instance
							$instance['title'] = $module->settings->{$conductor_query_builder_title_key};

							// Set the post ID (node) on the instance
							$instance['post_id'] = $module->node;

							// Set the current widget instance
							$this->current_widget_instance = $instance;

							// Set the current module
							$this->current_module = $module;

							// Break from the loop
							break;
						}
					}
				}

				// Reset the global post reference
				wp_reset_postdata();
			}

			return $instance;
		}

		/**
		 * This function adjusts the Conductor Query Builder Conductor REST API Conductor Widget ID.
		 */
		public function conductor_query_builder_conductor_rest_api_conductor_widget_id( $widget_id, $type, $query_builder_data, $conductor_rest_widget_query, $instance, $paged, $conductor_widget_settings, $conductor_widget, $conductor_rest_api, $conductor_query_builder_conductor_rest_api ) {
			// Bail if this isn't a Beaver Builder "widget" or we don't have a current widget instance
			if ( $type !== 'beaver-builder' || ! $this->current_widget_instance )
				return $widget_id;

			// Set the widget ID
			$widget_id = $conductor_query_builder_conductor_rest_api->widget_id_prefix . $query_builder_data['type'] . '-' . $query_builder_data['number'];

			return $widget_id;
		}

		/**
		 * This function adjusts the Conductor Query Builder Conductor REST API query builder query arguments.
		 */
		public function conductor_query_builder_conductor_rest_api_query_builder_query_args( $query_args, $type, $data, $instance, $widget_number, $paged, $conductor_widget_settings, $conductor_widget, $conductor_rest_api, $conductor_query_builder_conductor_rest_api ) {
			// Bail if this isn't a Beaver Builder "widget", we don't have a current widget instance, or we don't have a current module
			if ( $type !== 'beaver-builder' || ! $this->current_widget_instance || ! $this->current_module )
				return $query_args;

			// Grab the Conductor Query Builder Beaver Builder instance
			$conductor_query_builder_beaver_builder = Conduct_Query_Builder_Beaver_Builder();

			// Set the query arguments
			$query_args = $conductor_query_builder_beaver_builder->get_query_args( $this->current_module->settings, $this->current_module );

			return $query_args;
		}

		/**
		 * This function adjusts the Conductor Query Builder Conductor REST API query builder mode
		 */
		public function conductor_query_builder_conductor_rest_api_query_builder_mode( $query_builder_mode, $type, $data, $instance, $widget_number, $paged, $conductor_widget_settings, $conductor_widget, $conductor_rest_api, $conductor_query_builder_conductor_rest_api ) {
			// Bail if this isn't a Beaver Builder "widget", we don't have a current widget instance, or we don't have a current module
			if ( $type !== 'beaver-builder' || ! $this->current_widget_instance || ! $this->current_module )
				return $query_builder_mode;

			// Grab the Conductor Query Builder instance
			$conductor_query_builder = Conduct_Query_Builder();

			// Conductor Query Builder Mode key
			$conductor_query_builder_mode_key = $conductor_query_builder->meta_key_prefix . $conductor_query_builder->query_builder_mode_meta_key_suffix;

			// Set the query builder mode
			$query_builder_mode = $this->current_module->settings->{$conductor_query_builder_mode_key};

			return $query_builder_mode;
		}

		/**
		 * This function adjusts the Conductor Query Builder Conductor REST API Conductor Widget instance.
		 */
		public function conductor_query_builder_conductor_rest_api_query_builder_conductor_widget_instance( $conductor_widget_instance, $type, $data, $instance, $widget_number, $paged, $conductor_widget_settings, $conductor_widget, $conductor_rest_api, $conductor_query_builder_conductor_rest_api ) {
			// Bail if this isn't a Beaver Builder "widget" or we don't have a current widget instance
			if ( $type !== 'beaver-builder' || ! $this->current_widget_instance )
				return $conductor_widget_instance;

			// Set the Conductor Widget instance
			$conductor_widget_instance = $this->current_widget_instance;

			return $conductor_widget_instance;
		}

		/**
		 * This function runs after the Conductor REST API widget query.
		 */
		public function conductor_rest_widget_query_after( $conductor_rest_widget_query, $data, $instance, $widget_number, $paged, $conductor_widget_settings, $conductor_widget, $conductor_rest_api ) {
			// If we have a current widget instance
			if ( ! empty( $this->current_widget_instance ) ) {
				// Reset the current widget instance
				$this->current_widget_instance = array();

				// Reset the current module
				$this->current_module = false;
			}
		}
	}

	/**
	 * Create an instance of the Conductor_Query_Builder_Beaver_Builder_Conductor_REST_API class.
	 */
	function Conduct_Query_Builder_Beaver_Builder_Conductor_REST_API() {
		return Conductor_Query_Builder_Beaver_Builder_Conductor_REST_API::instance();
	}

	Conduct_Query_Builder_Beaver_Builder_Conductor_REST_API(); // Conduct your content!
}