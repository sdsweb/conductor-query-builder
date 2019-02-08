<?php
/**
 * Conductor Query Builder Gutenberg (Admin Bar)
 *
 * @class Conductor_Query_Builder_Gutenberg
 * @author Slocum Studio
 * @version 1.0.0
 * @since 1.0.5
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Conductor_Query_Builder_Gutenberg' ) ) {
	final class Conductor_Query_Builder_Gutenberg {
		/**
		 * @var string
		 */
		public $version = '1.0.0';

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
		public function __construct() {
			// Hooks
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) ); // Enqueue Block Editor Assets
		}

		/**
		 * This function enqueues block editor assets.
		 */
		public function enqueue_block_editor_assets() {
			// Grab the Conductor Query Builder instance
			$conductor_query_builder = Conduct_Query_Builder();

			// Grab the Conductor Query Builder queries
			$queries = $conductor_query_builder->get_queries();
			
			// Grab the first Conductor Query Builder query
			$first_query = ( ! empty( $queries ) ) ? reset( $queries ) : false;

			// Grab the first query post
			$first_query_post = ( ! empty( $first_query ) ) ? get_post( $first_query->ID ) : false;

			// Grab the first query post type object
			$first_query_post_type = get_post_type( $first_query_post );

			// Grab the first query post type object
			$first_query_post_type_object = get_post_type_object( $first_query_post_type );
			
			// Grab the edit post link placeholder
			$edit_post_link_placeholder = ( ! empty( $first_query ) && current_user_can( Conductor::$capability ) ) ? str_replace( sprintf( $first_query_post_type_object->_edit_link, $first_query->ID ), $first_query_post_type_object->_edit_link, get_edit_post_link( $first_query->ID ) ) : '';

			// Conductor Query Builder Gutenberg Block Script
			wp_enqueue_script( 'conductor-query-builder-gutenberg-block', Conductor_Query_Builder_Add_On::plugin_url() . '/assets/js/conductor-query-builder-gutenberg-block.js', array( 'wp-editor' ), Conductor_Query_Builder_Add_On::$version );
			wp_localize_script( 'conductor-query-builder-gutenberg-block', 'conductor_query_builder_gutenberg_block', apply_filters( 'conductor_query_builder_gutenberg_block_localize', array(
				// Add New Link
				'add_new_link' => ( $first_query_post_type ) ? add_query_arg( 'post_type', $first_query_post_type, admin_url( 'post-new.php' ) ) : '',
				// Current User Can
				'current_user_can' => array(
					'edit_post' => ( $edit_post_link_placeholder )
				),
				// Edit Post Link Placeholder
				'edit_post_link_placeholder' => $edit_post_link_placeholder,
				// Localization
				'l10n' => array(
					'query' => array(
						'no_title' => __( '(no title)', 'conductor-query-builder' ),
						'none' => _x( 'We weren\'t able to find any existing queries. You can create a query with the query builder the "Create" tab.', 'label for no queries', 'conductor-query-builder' ),
						'select' => _x( 'Select a Conductor Query', 'label for selecting a query', 'conductor-query-builder' )
					)
				),
				// Internationalization
				'i18n' => array(
					'text_domain' => 'conductor-query-builder'
				),
				// Post Type Object
				'post_type_object' => array(
					'edit_link' => $first_query_post_type_object->_edit_link
				),
				// Queries
				'queries' => $queries,
				// Shortcode
				'shortcode' => $conductor_query_builder->shortcode,
				// Shortcode Attributes
				'shortcode_attributes' => array(
					'id',
					'title'
				)
			), $conductor_query_builder, $this ) );

			// Conductor Query Builder Gutenberg Block Stylesheet
			wp_enqueue_style( 'conductor-query-builder-gutenberg-block', Conductor_Query_Builder_Add_On::plugin_url() . '/assets/css/conductor-query-builder-gutenberg-block.css', array( 'wp-edit-blocks' ), Conductor_Query_Builder_Add_On::$version);
		}
	}

	/**
	 * Create an instance of the Conductor_Query_Builder_Gutenberg class.
	 */
	function Conduct_Query_Builder_Gutenberg() {
		return Conductor_Query_Builder_Gutenberg::instance();
	}

	Conduct_Query_Builder_Gutenberg(); // Conduct your content!
}