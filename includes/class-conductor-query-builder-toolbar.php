<?php
/**
 * Conductor Query Builder Toolbar (Admin Bar)
 *
 * @class Conductor_Query_Builder_Toolbar
 * @author Slocum Studio
 * @version 1.0.5
 * @since 1.0.0
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Conductor_Query_Builder_Toolbar' ) ) {
	final class Conductor_Query_Builder_Toolbar {
		/**
		 * @var string
		 */
		public $version = '1.0.5';

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
			add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 9999 ); // Admin Bar Menu (late)
		}

		/**
		 * This function runs when the admin bar is initialized and adds a Conductor node.
		 */
		public function admin_bar_menu( $wp_admin_bar ) {
			// Bail if the current user doesn't have the Conductor capability
			if ( ! current_user_can( Conductor::$capability ) )
				return;

			// Grab the Conductor Admin menu page slug
			$conductor_admin_menu_page = ( class_exists( 'Conductor_Admin_Options' ) && method_exists( 'Conductor_Admin_Options', 'get_menu_page' ) ) ? Conductor_Admin_Options::get_menu_page() : ( ( class_exists( 'Conductor_Admin_Options' ) ) ? str_replace( 'toplevel_page_', '', Conductor_Admin_Options::$menu_page ) : 'conductor' );

			// Grab the Conductor Query Builder instance
			$conductor_query_builder = Conduct_Query_Builder();

			// Query Builder Menu
			$wp_admin_bar->add_menu( array(
				'id' => Conductor_Query_Builder_Admin::get_sub_menu_page(),
				'parent' => $conductor_admin_menu_page,
				'title' => $conductor_query_builder->post_type_object->labels->name,
				'href' => admin_url( Conductor_Query_Builder_Admin::get_sub_menu_page_slug() ),
				'meta' => array(
					'class' => 'conductor conductor-child conductor-qb conductor-qb-child ' . Conductor_Query_Builder_Admin::get_sub_menu_page()
				)
			) );
		}
	}

	/**
	 * Create an instance of the Conductor_Query_Builder_Toolbar class.
	 */
	function Conduct_Query_Builder_Toolbar() {
		return Conductor_Query_Builder_Toolbar::instance();
	}

	Conduct_Query_Builder_Toolbar(); // Conduct your content!
}