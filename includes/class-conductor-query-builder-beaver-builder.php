<?php
/**
 * Conductor Query Builder Beaver Builder
 *
 * @class Conductor_Query_Builder_Beaver_Builder
 * @author Slocum Studio
 * @version 1.0.5
 * @since 1.0.3
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Conductor_Query_Builder_Beaver_Builder' ) ) {
	final class Conductor_Query_Builder_Beaver_Builder {
		/**
		 * @var string
		 */
		public $version = '1.0.5';

		/**
		 * @var string
		 */
		public $left_field_bracket = '_conductor_qb_bb_';

		/**
		 * @var string
		 */
		public $right_field_bracket = '';

		/**
		 * @var object
		 */
		public $wp_widget_factory = false;

		/**
		 * @var object
		 */
		public $pagenum_link = false;

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
			// Hooks
			add_action( 'init', array( $this, 'init' ) ); // Init
			add_filter( 'sidebars_widgets', array( $this, 'sidebars_widgets' ), 20 ); // Sidebars Widgets
			add_filter( 'conductor_widget_before_widget_data_attributes', array( $this, 'conductor_widget_before_widget_data_attributes' ), 10, 5 ); // Conductor Widget - Before Widget Data Attributes
			add_filter( 'conductor_query_builder_widget_before_widget_data_attributes', array( $this, 'conductor_query_builder_widget_before_widget_data_attributes' ), 10, 8 ); // Conductor Query Builder - Widget Before - Widget Data Attributes
			add_filter( 'conductor_query_builder_populate_values_data', array( $this, 'conductor_query_builder_populate_values_data' ), 10, 2 ); // Conductor Query Builder - Populate Values Data
			add_filter( 'conductor_query_builder_populate_clause_data', array( $this, 'conductor_query_builder_populate_clause_data' ), 10, 2 ); // Conductor Query Builder - Populate Clause Data
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) ); // WordPress Enqueue Scripts
			add_filter( 'conductor_query_builder_admin_enqueue_scripts', array( $this, 'conductor_query_builder_admin_enqueue_scripts' ), 10, 4 ); // Conductor Query Builder - Admin Enqueue Scripts
			add_filter( 'conductor_query_paginate_links_args', array( $this, 'conductor_query_paginate_links_args' ), 10, 4 ); // Conductor - Query - Paginate Links Arguments
			add_action( 'wp_footer', array( $this, 'wp_footer' ) ); // WordPress Footer
			add_filter( 'conductor_query_builder_admin_footer', array( $this, 'conductor_query_builder_admin_footer' ), 10, 7 ); // Conductor Query Builder - Admin Footer
			add_filter( 'fl_builder_module_categories', array( $this, 'fl_builder_module_categories' ) ); // Beaver Builder - Module Categories
			add_filter( 'fl_builder_render_ui_panel', array( $this, 'fl_builder_render_ui_panel' ) ); // Beaver Builder - Render UI Panel
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		private function includes() {
			include_once 'beaver-builder/class-conductor-query-builder-beaver-builder-module.php'; // Conductor Query Builder - Beaver Builder Module
		}


		/**
		 * This function runs on initialization, sets up properties on this class, and allows other
		 * plugins and themes to adjust those properties via filters.
		 */
		public function init() {
			// Load required assets
			$this->includes();

			// Register the Conductor Query Builder Beaver Builder module
			FLBuilder::register_module( 'Conductor_Query_Builder_Beaver_Builder_Module', apply_filters( 'conductor_query_builder_beaver_builder_module_settings', array(
				// Conductor Query Builder Tab
				'conductor-query-builder' => array(
					'title' => __( 'Query Builder', 'conductor-query-builder' ),
					'file' => Conductor_Query_Builder_Add_On::plugin_dir() . '/includes/beaver-builder/tabs/conductor-query-builder-beaver-builder.php',
					'fields' => array(
						'conductor_query_builder_beaver_builder_update_count' => array(
							'class' => 'conductor-query-builder-beaver-builder-update-count',
							'default' => '0',
							'label' => __( 'Conductor Query Builder Beaver Builder Update Count', 'conductor-query-builder' ),
							'type' => 'text'
						)
					)
				)
			), $this ) );
		}

		/**
		 * This function adjusts the sidebars widgets.
		 */
		public function sidebars_widgets( $sidebars_widgets ) {
			// Bail if we're in the admin, Beaver Builder isn't enabled, we're not doing the wp_enqueue_scripts action, this is an AJAX request, or our temporary sidebar already exists
			if ( is_admin() || ! FLBuilderModel::is_builder_enabled() || ! doing_action( 'wp_enqueue_scripts' ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $sidebars_widgets['conductor-qb-temporary-sidebar'] ) )
				return $sidebars_widgets;

			// Flag to determine if the Conductor Query Builder Beaver Builder module exists on this piece of content
			$has_conductor_query_builder_module = false;

			// Create a new reflection class of the Conductor Query Builder Beaver Builder module
			$conductor_query_builder_beaver_builder_reflection = new ReflectionClass( 'Conductor_Query_Builder_Beaver_Builder_Module' );

			// Grab the Conductor Query Builder Beaver Builder module
			$conductor_query_builder_beaver_builder_module_name =  pathinfo( $conductor_query_builder_beaver_builder_reflection->getFileName(), PATHINFO_FILENAME );

			// If we have Beaver Builder rows
			if ( ( $rows = FLBuilderModel::get_nodes( 'row' ) ) )
				// Loop through Beaver Builder rows
				foreach ( $rows as $row ) {
					// Grab the Beaver Builder groups (column groups) for this row
					$column_groups = FLBuilderModel::get_nodes( 'column-group', $row );

					// If this row is visible and we have groups
					if ( FLBuilderModel::is_node_visible( $row ) && ! empty( $column_groups ) )
						// Loop through the column groups
						foreach ( $column_groups as $column_group ) {
							// Grab the Beaver Builder columns for this column group
							$columns = FLBuilderModel::get_nodes( 'column', $column_group );

							// If we have columns
							if ( ! empty( $columns ) )
								// Loop through the columns
								foreach ( $columns as $column ) {
									// Grab the Beaver Builder nodes within this column
									$nodes = FLBuilderModel::get_nodes( null, $column );

									// If we have nodes
									if ( ! empty( $nodes ) )
										// Loop through the nodes
										foreach ( $nodes as $node )
											// If this is a Conductor Query Builder Beaver Builder node
											if ( $node->type === 'module' && $node->settings->type === $conductor_query_builder_beaver_builder_module_name )
												$has_conductor_query_builder_module = true;
								}
						}
				}

			// Bail if we don't have a Conductor Query Builder Beaver Builder module
			if ( ! $has_conductor_query_builder_module )
				return $sidebars_widgets;

			// Grab the Conductor Widget instance
			$conductor_widget = Conduct_Widget();

			// Add our temporary sidebar with a mock Conductor Widget (this ensures Conductor scripts and styles are enqueued)
			$sidebars_widgets['conductor-qb-temporary-sidebar'] = array(
				$conductor_widget->id_base . '-0'
			);

			return $sidebars_widgets;
		}

		/**
		 * This function adjusts the Conductor Widget before widget data attributes
		 */
		public function conductor_widget_before_widget_data_attributes( $before_widget_data_attrs, $params, $instance, $conductor_widget_settings, $conductor_widget ) {
			// Bail if Beaver Builder isn't active
			if ( is_admin() || ! FLBuilderModel::is_builder_active() )
				return $before_widget_data_attrs;

			// If we don't have a pagenum link on this class
			if ( ! $this->pagenum_link ) {
				// Grab the pagenum link
				$pagenum_link = get_pagenum_link();

				// Grab the pagenum link query arguments
				parse_str( parse_url( $pagenum_link, PHP_URL_QUERY ), $pagenum_link_query_args );

				// If the pagenum link contains the "fl_builder" query argument
				if ( array_key_exists( 'fl_builder', $pagenum_link_query_args ) ) {
					// Remove the "fl_builder" query argument
					$the_pagenum_link = str_replace( $pagenum_link, remove_query_arg( 'fl_builder', $pagenum_link ), $before_widget_data_attrs['data-pagenum-link'] );

					// Set the pagenum link on this class
					$this->pagenum_link = $the_pagenum_link;
				}
			}

			// If we have a pagenum link on this class
			if ( $this->pagenum_link )
				// Set the pagenum link before widget data attribute
				$before_widget_data_attrs['data-pagenum-link'] = $this->pagenum_link;

			return $before_widget_data_attrs;
		}

		/**
		 * This function adjusts the Conductor Query Builder "before_widget" widget data attributes.
		 */
		public function conductor_query_builder_widget_before_widget_data_attributes( $before_widget_data_attrs, $number, $widget, $post_id, $type, $query_args, $conductor_widget, $conductor_query_builder ) {
			// Bail if this isn't a Beaver Builder "widget"
			if ( $type !== 'beaver-builder' )
				return $before_widget_data_attrs;

			// Set the widget ID widget data attribute TODO: Future: Convert prefix into a variable?
			$before_widget_data_attrs['data-query-builder-widget-id'] = 'conductor-query-builder-beaver-builder-' . $number;

			return $before_widget_data_attrs;
		}

		/**
		 * This function adjusts whether or not the Conductor Query Builder values data should be populated
		 */
		public function conductor_query_builder_populate_values_data( $populate_values_data, $conductor_query_builder ) {
			// Bail if Beaver Builder isn't active (checking fl_builder GET parameter)
			if ( ! isset( $_GET['fl_builder'] ) )
				return $populate_values_data;

			// Set the populate scripts and styles flag
			$populate_values_data = true;

			return $populate_values_data;
		}

		/**
		 * This function adjusts whether or not the Conductor Query Builder clause data should be populated
		 */
		public function conductor_query_builder_populate_clause_data( $populate_clause_data, $conductor_query_builder ) {
			// Bail if Beaver Builder isn't active (checking fl_builder GET parameter)
			if ( ! isset( $_GET['fl_builder'] ) )
				return $populate_clause_data;

			// Set the populate scripts and styles flag
			$populate_clause_data = true;

			return $populate_clause_data;
		}

		/**
		 * This function enqueues scripts and styles.
		 */
		public function wp_enqueue_scripts() {
			// Bail if we're in the admin or Beaver Builder isn't active
			if ( is_admin() || ! FLBuilderModel::is_builder_active() )
				return;

			// Grab the Conductor Query Builder instance
			$conductor_query_builder = Conduct_Query_Builder();

			// Ensure the admin widgets script is registered (Conductor Widget relies on this script as a dependency)
			wp_register_script( 'admin-widgets', admin_url( '/js/widgets.min.js' ), array( 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable' ) );

			// Call the admin enqueue scripts function
			$conductor_query_builder->admin_enqueue_scripts( 'conductor-query-builder-beaver-builder' );
		}

		/**
		 * This function adjusts whether or not the Conductor Query Builder admin scripts and styles
		 * should be enqueued.
		 */
		public function conductor_query_builder_admin_enqueue_scripts( $enqueue_scripts_and_styles, $hook, $post, $conductor_query_builder ) {
			// Bail if we're in the admin, the Beaver Builder isn't active or this isn't the Conductor Query Builder Beaver Builder hook
			if ( is_admin() || ! FLBuilderModel::is_builder_active() || $hook !== 'conductor-query-builder-beaver-builder' )
				return $enqueue_scripts_and_styles;

			// Set the enqueue scripts and styles flag
			$enqueue_scripts_and_styles = true;

			return $enqueue_scripts_and_styles;
		}

		/**
		 * This function adjusts Conductor query paginate_links() arguments.
		 */
		public function conductor_query_paginate_links_args( $paginate_links_args, $query, $echo, $conductor_query ) {
			// Bail if Beaver Builder isn't active
			if ( is_admin() || ! FLBuilderModel::is_builder_active() )
				return $paginate_links_args;

			// If we don't have a pagenum link on this class
			if ( ! $this->pagenum_link ) {
				// Grab the pagenum link
				$pagenum_link = get_pagenum_link();

				// Grab the pagenum link query arguments
				parse_str( parse_url( $pagenum_link, PHP_URL_QUERY ), $pagenum_link_query_args );

				// If the pagenum link contains the "fl_builder" query argument
				if ( array_key_exists( 'fl_builder', $pagenum_link_query_args ) ) {
					// Remove the "fl_builder" query argument
					$the_pagenum_link = str_replace( $pagenum_link, remove_query_arg( 'fl_builder', $pagenum_link ), $pagenum_link );

					// Set the pagenum link on this class
					$this->pagenum_link = $the_pagenum_link;
				}
			}

			// If we have a pagenum link on this class
			if ( $this->pagenum_link ) {
				// Set the base URL
				$paginate_links_args['base'] = $this->pagenum_link;

				// Hook into "get_pagenum_link"
				add_filter( 'get_pagenum_link', array( $this, 'get_pagenum_link' ) );
			}

			return $paginate_links_args;
		}

		/**
		 * This function adjusts the pagenum link.
		 */
		public function get_pagenum_link( $pagenum_link ) {
			// Remove this hook
			remove_filter( 'get_pagenum_link', array( $this, 'get_pagenum_link' ) );

			// Bail if we don't have a pagenum link on this class
			if ( ! $this->pagenum_link )
				return $pagenum_link;

			// Set the pagenum link
			$pagenum_link = $this->pagenum_link;

			// TODO: Future: Reset the pagenum link on this class?

			return $pagenum_link;
		}

		/**
		 * This function outputs content before the closing </body> tag.
		 */
		public function wp_footer() {
			// Bail if Beaver Builder isn't active
			if ( ! FLBuilderModel::is_builder_active() )
				return;

			// Grab the Conductor Query Builder instance
			$conductor_query_builder_admin_views = Conduct_Query_Builder_Admin_Views();

			// Hook into conductor_query_builder_output_shortcode_query_builder_markup
			add_filter( 'conductor_query_builder_output_shortcode_query_builder_markup', array( $this, 'conductor_query_builder_output_shortcode_query_builder_markup' ), 10, 3 );

			// Hook into conductor_query_builder_sub_clause_group_field_brackets
			add_filter( 'conductor_query_builder_sub_clause_group_field_brackets', array( $this, 'conductor_query_builder_sub_clause_group_field_brackets' ), 10, 6 );

			// Hook into conductor_query_builder_sub_clause_group_meta_key_prefix
			add_filter( 'conductor_query_builder_sub_clause_group_meta_key_prefix', array( $this, 'conductor_query_builder_sub_clause_group_meta_key_prefix' ), 10, 10 );

			// Call the admin footer function
			$conductor_query_builder_admin_views->admin_footer( 'conductor-query-builder-beaver-builder', '_' );
		}

		/**
		 * This function adjusts whether or not the Conductor Query Builder admin scripts and styles
		 * should be enqueued.
		 */
		public function conductor_query_builder_admin_footer( $print_admin_footer_templates, $the_hook_suffix, $hook_suffix, $post, $conductor_query_builder, $conductor_widget, $conductor_query_builder_admin_views ) {
			// Bail if we're in the admin or the Beaver Builder isn't active or this isn't the Conductor Query Builder Beaver Builder hook
			if ( is_admin() || ! FLBuilderModel::is_builder_active() || $the_hook_suffix !== 'conductor-query-builder-beaver-builder' )
				return $print_admin_footer_templates;

			// Set the print admin footer templates flag
			$print_admin_footer_templates = true;

			return $print_admin_footer_templates;
		}

		/**
		 * This function adjusts the Beaver Builder Module categories.
		 */
		function fl_builder_module_categories( $categories ) {
			global $wp_widget_factory;

			// If the Conductor category doesn't already exist
			if ( ! in_array( 'Conductor', $categories ) )
				// Add the Conductor category to the list of categories
				$categories[] = __( 'Conductor', 'conductor-query-builder' );

			// Store the current WordPress widget factory data on this class
			$this->wp_widget_factory = $wp_widget_factory;

			// Grab the Conductor Widget instance
			$conductor_widget = Conduct_Widget();

			// Unset the Conductor Widget from the WordPress widget factory data
			unset( $wp_widget_factory->widgets[get_class( $conductor_widget )] );

			return $categories;
		}

		/**
		 * This function adjusts whether or not to render the Beaver Builder UI panel.
		 */
		function fl_builder_render_ui_panel( $render ) {
			global $wp_widget_factory;

			// Reset the current WordPress widget factory
			$wp_widget_factory = $this->wp_widget_factory;

			return $render;
		}

		/**
		 * This function adjusts whether or not the Conductor Query Builder shortcode query builder markup
		 * should be output.
		 */
		public function conductor_query_builder_output_shortcode_query_builder_markup( $output_shortcode_query_builder_markup,$post, $conductor_query_builder ) {
			// Set the output shortcode query builder markup flag to false
			$output_shortcode_query_builder_markup = false;

			return $output_shortcode_query_builder_markup;
		}

		/**
		 * This function adjusts the Conductor Query Builder sub-clause group field brackets.
		 */
		public function conductor_query_builder_sub_clause_group_field_brackets( $field_brackets, $post, $post_meta, $conductor_query_builder, $conductor_widget_instance, $conductor_widget ) {
			// Adjust the field brackets
			$field_brackets['left'] = $this->left_field_bracket;
			$field_brackets['right'] = $this->right_field_bracket;

			return $field_brackets;
		}

		/**
		 * This function adjusts the Conductor Query Builder sub-clause group meta key prefix.
		 */
		public function conductor_query_builder_sub_clause_group_meta_key_prefix( $meta_key_prefix, $conductor_query_builder_meta_key_prefix, $field_brackets, $left_field_bracket, $right_field_bracket, $post, $post_meta, $conductor_query_builder, $conductor_widget_instance, $conductor_widget ) {
			// Adjust the meta key prefix
			$meta_key_prefix = $conductor_query_builder_meta_key_prefix;

			return $meta_key_prefix;
		}


		/**********************
		 * Internal Functions *
		 **********************/

		/**
		 * This function returns the Conductor Query Builder meta.
		 */
		public function get_post_meta( $settings ) {
			// Post meta
			$post_meta = array();

			// Grab the Conductor Query Builder instance
			$conductor_query_builder = Conduct_Query_Builder();

			// Grab the clause types
			$clause_types = $conductor_query_builder->get_clause_types();

			// Loop through the clause types
			foreach ( $clause_types as $clause_type ) {
				// Grab the clause type meta key
				$clause_type_meta_key = $conductor_query_builder->meta_key_prefix . $clause_type;

				// Grab the meta value for this clause type
				$post_meta[$clause_type] = ( property_exists( $settings, $clause_type_meta_key ) ) ? $settings->{$clause_type_meta_key} : array();

				// Ensure we have an array for empty meta values (as of WordPress 4.6, an empty string is returned when meta does not exist)
				if ( empty( $post_meta[$clause_type] ) && ! is_array( $post_meta[$clause_type] ) )
					$post_meta[$clause_type] = array();
			}

			return apply_filters( 'conductor_query_builder_beaver_builder_post_meta', $post_meta, $settings, $clause_types, $conductor_query_builder, $this );
		}

		/**
		 * This function returns the query arguments.
		 */
		public function get_query_args( $settings, $module, $post_meta = array() ) {
			// Query arguments
			$query_args = array();

			// Post meta
			$post_meta = ( empty( $post_meta ) ) ? $this->get_post_meta( $settings ) : $post_meta;

			// Grab the Conductor Query Builder instance
			$conductor_query_builder = Conduct_Query_Builder();

			// Grab the clause types
			$clause_types = $conductor_query_builder->get_clause_types();

			// Loop through the clause types
			foreach ( $clause_types as $clause_type ) {
				// Grab the clause type meta key
				$clause_type_meta_key = $conductor_query_builder->meta_key_prefix . $clause_type . $conductor_query_builder->query_args_meta_key_suffix;

				// If we meta for this clause type and the clause type query arguments exist
				if ( isset( $post_meta[$clause_type] ) && ! empty( $post_meta[$clause_type] ) && ( property_exists( $settings, $clause_type_meta_key ) ) ) {
					// Grab the clause type query arguments
					$clause_type_query_args = $conductor_query_builder->get_clause_type_query_args( $module->node, $clause_type, $settings->{$clause_type_meta_key}, 'query' );

					// Merge the clause type query arguments
					$query_args += $clause_type_query_args;
				}
			}

			return apply_filters( 'conductor_query_builder_beaver_builder_query_args', $query_args, $settings, $module, $post_meta, $clause_types, $conductor_query_builder, $this );
		}
	}

	/**
	 * Create an instance of the Conductor_Query_Builder_Beaver_Builder class.
	 */
	function Conduct_Query_Builder_Beaver_Builder() {
		return Conductor_Query_Builder_Beaver_Builder::instance();
	}

	Conduct_Query_Builder_Beaver_Builder(); // Conduct your content!
}