<?php
/**
 * Conductor Query Builder - Conductor REST API
 *
 * @class Conductor_Query_Builder_Conductor_REST_API
 * @author Slocum Studio
 * @version 1.0.0
 * @since 1.0.4
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Conductor_Query_Builder_Conductor_REST_API' ) ) {
	final class Conductor_Query_Builder_Conductor_REST_API {
		/**
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * @var mixed
		 */
		public $current_widget_number = -1;

		/**
		 * @var string
		 */
		public $widget_number_prefix = 'conductor-query-builder-';

		/**
		 * @var string
		 */
		public $widget_id_prefix = 'conductor-query-builder-';

		/**
		 * @var array
		 */
		public $current_widget_instance = array();

		/**
		 * @var array
		 */
		public $query_builder_data = array();

		/**
		 * @var string
		 */
		public $current_query_builder_mode = false;

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
			add_filter( 'conductor_rest_widget_query_widget_number', array( $this, 'conductor_rest_widget_query_widget_number' ), 10, 6 ); // Conductor REST API - Widget Query - Widget Number
			add_filter( 'conductor_rest_widget_query_conductor_widget_settings', array( $this, 'conductor_rest_widget_query_conductor_widget_settings' ), 10, 7 ); // Conductor REST API - Widget Query - Conductor Widget Settings
			add_action( 'conductor_rest_widget_query_before', array( $this, 'conductor_rest_widget_query_before' ), 1, 7 ); // Conductor REST - Widget Query Before (early)
			add_filter( 'conductor_rest_widget_query', array( $this, 'conductor_rest_widget_query' ), 10, 6 ); // Conductor REST API - Widget Query
			add_action( 'conductor_rest_widget_query_after', array( $this, 'conductor_rest_widget_query_after' ), 10, 8 ); // Conductor REST - Widget Query after

			// TODO: Future: Add arguments to REST API route
			// TODO: Future: Add query builder render before/after hooks like in Conductor_Query_Builder::render()
		}


		/**
		 * This function adjusts the Conductor REST API widget query widget number.
		 */
		public function conductor_rest_widget_query_widget_number( $widget_number, $raw_widget_number, $data, $conductor_options, $conductor_widget, $conductor_rest_api ) {
			// Bail if the Conductor REST API isn't enabled
			if ( ! $conductor_options['rest']['enabled'] || ! isset( $_REQUEST['conductor_query_builder'] ) || ! $_REQUEST['conductor_query_builder'] || ! isset( $_REQUEST['query_builder'] ) || empty( $_REQUEST['query_builder'] ) || ! isset( $_REQUEST['query_builder']['post_id'] ) || empty( $_REQUEST['query_builder']['post_id'] ) || ! isset( $_REQUEST['query_builder']['type'] ) || empty( $_REQUEST['query_builder']['type'] ) || ! isset( $_REQUEST['query_builder']['number'] ) || empty( $_REQUEST['query_builder']['number'] ) || ! isset( $_REQUEST['query_builder']['widget_number'] ) || empty( $_REQUEST['query_builder']['widget_number'] ) )
				return $widget_number;

			// Grab the query builder post ID
			$query_builder_post_id = $_REQUEST['query_builder']['post_id'];

			// Grab the query builder data
			$query_builder_data = array(
				'post_id' => ( preg_match( '/[^0-9]/', $query_builder_post_id ) ) ? sanitize_text_field( $query_builder_post_id ) : ( int ) $query_builder_post_id,
				'type' => sanitize_text_field( $_REQUEST['query_builder']['type'] ),
				'number' => ( int ) $_REQUEST['query_builder']['number'],
				'widget_number' => ( int ) $_REQUEST['query_builder']['widget_number']
			);

			// Switch based on query builder type
			switch ( $query_builder_data['type'] ) {
				// Function and Shortcode
				case 'function':
				case 'shortcode':
					// If the number and widget number are equal
					if ( $query_builder_data['number'] === $query_builder_data['widget_number'] ) {
						// Grab the Conductor Query Builder instance
						$conductor_query_builder = Conduct_Query_Builder();

						// Grab the Conductor Query Builder queries
						$query_builder_queries = $conductor_query_builder->get_queries();

						// Grab Conductor Query Builder query IDs
						$query_builder_query_ids = array_column( $query_builder_queries, 'ID' );

						// If the post ID is in the Conductor Query Builder query IDs
						if ( in_array( $query_builder_data['post_id'], $query_builder_query_ids ) ) {
							// Set the query builder data
							$this->query_builder_data = $query_builder_data;

							// Set the current widget instance
							$this->current_widget_instance = array(
								'post_id' => $query_builder_data['post_id'],
								'title' => ''
							);

							// Set the current widget number
							$this->current_widget_number = $this->widget_number_prefix . $query_builder_data['widget_number'];

							// Set the widget number
							$widget_number = $this->current_widget_number;
						}
					}
				break;

				// Widget
				case 'widget':
					// Grab the Conductor Query Builder Widget instance
					$conductor_query_builder_widget = Conduct_Query_Builder_Widget();

					// Grab the Conductor Query Builder Widget settings (all Conductor Query Builder Widgets)
					$conductor_query_builder_widget_settings = $conductor_query_builder_widget->get_settings();

					// If this widget is a valid Conductor Query Builder Widget
					if ( array_key_exists( $query_builder_data['widget_number'], $conductor_query_builder_widget_settings ) ) {
						// Grab the Conductor Query Builder Widget instance
						$instance = apply_filters( 'conductor_query_builder_conductor_rest_api_conductor_widget_instance', $conductor_query_builder_widget_settings[$query_builder_data['widget_number']], $query_builder_data['type'], $query_builder_data, $widget_number, $data, $conductor_options, $conductor_widget, $conductor_rest_api, $this );

						// If we have an instance and the query builder post ID matches the Conductor Query Builder Widget instance post ID
						if ( $instance && $query_builder_data['post_id'] === $instance['post_id'] ) {
							// Set the query builder data
							$this->query_builder_data = $query_builder_data;

							// Set the current widget instance
							$this->current_widget_instance = $instance;

							// Set the current widget number
							$this->current_widget_number = $this->widget_number_prefix . $query_builder_data['widget_number'];

							// Set the widget number
							$widget_number = $this->current_widget_number;
						}
					}
				break;

				// Default
				default:
					// Instance
					$instance = apply_filters( 'conductor_query_builder_conductor_rest_api_conductor_widget_instance', false, $query_builder_data['type'], $query_builder_data, $widget_number, $data, $conductor_options, $conductor_widget, $conductor_rest_api, $this );

					// If we have an instance
					if ( $instance ) {
						// Set the query builder data
						$this->query_builder_data = $query_builder_data;

						// Set the current widget instance
						$this->current_widget_instance = $instance;

						// Set the current widget number
						$this->current_widget_number = $this->widget_number_prefix . $query_builder_data['widget_number'];

						// Set the widget number
						$widget_number = $this->current_widget_number;
					}
				break;
			}

			return $widget_number;
		}

		/**
		 * This function adjusts the Conductor REST API widget query Conductor Widget settings.
		 */
		public function conductor_rest_widget_query_conductor_widget_settings( $conductor_widget_settings, $widget_number, $paged, $data, $conductor_options, $conductor_widget, $conductor_rest_api ) {
			// Bail if the Conductor REST API isn't enabled, we don't have query builder data, we don't have a current widget instance, or we don't have a current widget number
			if ( ! $conductor_options['rest']['enabled'] || empty( $this->query_builder_data ) || empty( $this->current_widget_instance ) || $this->current_widget_number === -1 )
				return $conductor_widget_settings;

			// Instance
			$instance = apply_filters( 'conductor_query_builder_conductor_rest_api_conductor_widget_instance', false, $this->query_builder_data['type'], $this->query_builder_data, $widget_number, $data, $conductor_options, $conductor_widget, $conductor_rest_api, $this );

			// If we don't have an instance
			if ( ! $instance ) {
				// Grab the Conductor Query Builder instance
				$conductor_query_builder = Conduct_Query_Builder();

				// Grab the Conductor Widget instance data for this query
				$instance = $conductor_query_builder->get_conductor_widget_instance( $this->current_widget_instance['post_id'], $this->current_widget_instance['title'] );
			}

			// Set the current widget instance in the Conductor Widget Settings
			$conductor_widget_settings[$this->current_widget_number] = $instance;

			return $conductor_widget_settings;
		}

		/**
		 * This function runs before the Conductor REST API widget query.
		 */
		public function conductor_rest_widget_query_before( $data, $instance, $widget_number, $paged, $conductor_widget_settings, $conductor_widget, $conductor_rest_api ) {
			// Bail if we don't have query builder data, we don't have a current widget instance, or we don't have a current widget number
			if ( empty( $this->query_builder_data ) || empty( $this->current_widget_instance ) || $this->current_widget_number === -1 )
				return;

			// Grab the Conductor Query Builder instance
			$conductor_query_builder = Conduct_Query_Builder();


			// Adjust the Conductor Widget settings
			add_filter( 'conductor_widget_settings', array( $conductor_query_builder, 'conductor_widget_settings' ) );

			// Adjust the Conductor query arguments
			add_filter( 'conductor_query_args', array( $conductor_query_builder, 'conductor_query_args' ), 10, 4 );

			// Adjust the number of found posts
			add_filter( 'conductor_query_found_posts', array( $conductor_query_builder, 'conductor_query_found_posts' ), 10, 3 );

			// Adjust the has pagination flag
			add_filter( 'conductor_query_has_pagination', array( $conductor_query_builder, 'conductor_query_has_pagination' ), 10, 2 );


			// Grab the query builder query arguments
			$query_builder_query_args = apply_filters( 'conductor_query_builder_conductor_rest_api_query_builder_query_args', ( empty( $conductor_query_builder->current_query_args ) ) ? $conductor_query_builder->get_query_args( $this->current_widget_instance['post_id'] ) : $conductor_query_builder->current_query_args, $this->query_builder_data['type'], $this->query_builder_data, $data, $instance, $widget_number, $paged, $conductor_widget_settings, $conductor_widget, $conductor_rest_api, $this );

			// Grab the query builder mode
			$this->current_query_builder_mode = apply_filters( 'conductor_query_builder_conductor_rest_api_query_builder_mode', $conductor_query_builder->get_query_builder_mode_for_query( $this->current_widget_instance['post_id'] ), $this->query_builder_data['type'], $this->query_builder_data, $data, $instance, $widget_number, $paged, $conductor_widget_settings, $conductor_widget, $conductor_rest_api, $this );

			// Grab the Conductor Widget instance
			$conductor_widget_instance = apply_filters( 'conductor_query_builder_conductor_rest_api_query_builder_conductor_widget_instance', $conductor_query_builder->get_conductor_widget_instance( $this->current_widget_instance['post_id'], $this->current_widget_instance['title'] ), $this->query_builder_data['type'], $this->query_builder_data, $data, $instance, $widget_number, $paged, $conductor_widget_settings, $conductor_widget, $conductor_rest_api, $this );

			// Add this Conductor Query to the list of rendered queries
			$conductor_query_builder->rendered[$this->query_builder_data['type']][] = $this->current_widget_instance['post_id'];

			// Set the query builder mode for this query
			$conductor_query_builder->current_query_builder_mode = $this->current_query_builder_mode;

			// Set the Conductor Widget instance data for this query and set the global reference
			$conductor_query_builder->current_conductor_widget_instance = $conductor_widget_instance;

			// Set the global query arguments reference
			$conductor_query_builder->current_query_args = $query_builder_query_args;

			// Set the global doing Conductor Query flag
			$conductor_query_builder->doing_conductor_query = true;

			// TODO
		}

		/**
		 * This function adjusts the Conductor REST API widget query data.
		 */
		public function conductor_rest_widget_query( $conductor_rest_widget_query, $instance, $paged, $conductor_widget_settings, $conductor_widget, $conductor_rest_api ) {
			// Bail if we don't have query builder data, we don't have a current widget instance, or we don't have a current widget number
			if ( empty( $this->query_builder_data ) || empty( $this->current_widget_instance ) || $this->current_widget_number === -1 )
				return $conductor_rest_widget_query;

			// Switch based on query builder type
			switch ( $this->query_builder_data['type'] ) {
				// Function
				case 'function':
					// Set the widget ID
					$conductor_rest_widget_query['data']['widget_id'] = $this->widget_id_prefix . $this->query_builder_data['type'] . '-' . $this->query_builder_data['number'];
				break;

				// Shortcode
				case 'shortcode':
					// Set the widget ID
					$conductor_rest_widget_query['data']['widget_id'] = $this->widget_id_prefix . $this->query_builder_data['type'] . '-' . $this->query_builder_data['number'];
				break;

				// Widget
				case 'widget':
					// Grab the Conductor Query Builder Widget instance
					$conductor_query_builder_widget = Conduct_Query_Builder_Widget();

					// Set the Conductor Query Builder Widget number
					$conductor_query_builder_widget->_set( $this->query_builder_data['widget_number'] );

					// Set the widget ID to the Conductor Query Builder Widget ID
					$conductor_rest_widget_query['data']['widget_id'] = $conductor_query_builder_widget->id;
				break;
			}

			// Widget ID
			$conductor_rest_widget_query['data']['widget_id'] = apply_filters( 'conductor_query_builder_conductor_rest_api_conductor_widget_id', $conductor_rest_widget_query['data']['widget_id'], $this->query_builder_data['type'], $this->query_builder_data, $conductor_rest_widget_query, $instance, $paged, $conductor_widget_settings, $conductor_widget, $conductor_rest_api, $this );

			return $conductor_rest_widget_query;
		}

		/**
		 * This function runs after the Conductor REST API widget query.
		 */
		public function conductor_rest_widget_query_after( $conductor_rest_widget_query, $data, $instance, $widget_number, $paged, $conductor_widget_settings, $conductor_widget, $conductor_rest_api ) {
			// If we have query builder data, and we have a current widget instance, and we have a current widget number
			if ( ! empty( $this->query_builder_data ) && ! empty( $this->current_widget_instance ) && $this->current_widget_number !== -1 ) {
				// Reset the query builder data
				$this->query_builder_data = array();

				// Reset the current widget instance
				$this->current_widget_instance = array();

				// Reset the current widget number
				$this->current_widget_number = -1;

				// Reset the current query builder mode
				$this->current_query_builder_mode = false;


				// Grab the Conductor Query Builder instance
				$conductor_query_builder = Conduct_Query_Builder();


				// Remove the Conductor Widget settings adjustment
				remove_filter( 'conductor_widget_settings', array( $conductor_query_builder, 'conductor_widget_settings' ) );

				// Remove the Conductor query arguments adjustment
				remove_filter( 'conductor_query_args', array( $conductor_query_builder, 'conductor_query_args' ) );

				// Remove the Conductor query found posts adjustment
				remove_filter( 'conductor_query_found_posts', array( $conductor_query_builder, 'conductor_query_found_posts' ) );

				// Remove the has pagination flag adjustment
				remove_filter( 'conductor_query_has_pagination', array( $conductor_query_builder, 'conductor_query_has_pagination' ) );

				// Remove all display hooks
				remove_all_actions( 'conductor_widget_display_content_' . $conductor_query_builder->the_widget_number );


				// Reset the global doing Conductor Query flag
				$conductor_query_builder->doing_conductor_query = false;

				// Reset the global query arguments reference
				$conductor_query_builder->current_query_args = array();

				// Reset the global Conductor Widget instance reference
				$conductor_query_builder->current_conductor_widget_instance = array();

				// Reset the global query builder mode reference
				$conductor_query_builder->current_query_builder_mode = false;
			}
		}
	}

	/**
	 * Create an instance of the Conductor_Query_Builder_Conductor_REST_API class.
	 */
	function Conduct_Query_Builder_Conductor_REST_API() {
		return Conductor_Query_Builder_Conductor_REST_API::instance();
	}

	Conduct_Query_Builder_Conductor_REST_API(); // Conduct your content!
}