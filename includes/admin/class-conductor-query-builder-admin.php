<?php
/**
 * Conductor Query Builder
 *
 * @class Conductor_Query_Builder_Admin
 * @author Slocum Studio
 * @version 1.0.5
 * @since 1.0.0
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Conductor_Query_Builder_Admin' ) ) {
	class Conductor_Query_Builder_Admin {
		/**
		 * @var string
		 */
		public $version = '1.0.5';

		/**
		 * @var string
		 */
		public static $sub_menu_page = 'edit.php?post_type=';

		/**
		 * @var string
		 */
		public static $sub_menu_page_prefix = 'conductor_page_';

		/**
		 * @var string
		 */
		public static $sub_menu_page_slug = 'edit.php?post_type=conductor_qb_queries';

		/**
		 * @var string
		 */
		public static $sub_menu_page_slug_prefix = 'edit.php?post_type=';

		/**
		 * @var string, current WordPress admin page
		 */
		public static $self = 'conductor-qb-default';

		/**
		 * @var string, current WordPress post type
		 */
		public static $typenow = 'conductor-qb-default';

		/**
		 * @var string, current WordPress page
		 */
		public static $pagenow = 'conductor-qb-default';

		/**
		 * @var string, current WordPress sub-menu file
		 */
		public static $submenu_file = 'conductor-qb-default';

		/**
		 * @var Conductor_Query_Builder_Admin, Instance of the class
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
		function __construct( $args = array() ) {
			// Load required assets
			$this->includes();

			// Hooks
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 20 ); // Admin Menu (after Conductor)
			add_filter( 'parent_file', array( $this, 'parent_file' ) ); // Parent File
			add_filter( 'submenu_file', array( $this, 'submenu_file' ) ); // Sub-Menu File
			add_action( 'adminmenu', array( $this, 'adminmenu' ) ); // Admin Menu (after WordPress outputs the admin menu; no underscore in action name)
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		private function includes() {
			include_once 'class-conductor-query-builder-admin-views.php'; // Conductor Query Builder Admin View Controller
		}


		/**
		 * This function creates the admin menu item for Conductor Query Builder post type. We're not using
		 * WordPress' logic for this due to the fact that the init action is triggered before admin_menu
		 * which is where Conductor adds it's admin menu. This also allows us to set custom menu item labels and
		 * the menu item capability to administrators only.
		 */
		public function admin_menu() {
			// Grab the Conductor Admin Options menu page slug
			$conductor_admin_options_menu_page = ( class_exists( 'Conductor_Admin_Options' ) && method_exists( 'Conductor_Admin_Options', 'get_menu_page' ) ) ? Conductor_Admin_Options::get_menu_page() : ( ( class_exists( 'Conductor_Admin_Options' ) ) ? str_replace( 'toplevel_page_', '', Conductor_Admin_Options::$menu_page ) : 'conductor' );

			// Grab the Conductor Query Builder instance
			$conductor_query_builder = Conduct_Query_Builder();

			// Setup the sub-menu page slug
			$sub_menu_page_slug = self::$sub_menu_page_slug = self::$sub_menu_page_slug_prefix . $conductor_query_builder->post_type_name;

			// Conductor Query Builder Post Type Page
			self::$sub_menu_page = add_submenu_page( $conductor_admin_options_menu_page, $conductor_query_builder->post_type_object->labels->name, $conductor_query_builder->post_type_object->labels->name, Conductor::$capability, $sub_menu_page_slug );

			return self::$sub_menu_page;
		}

		/**
		 * This function adjusts several global variables to ensure the correct WordPress menu
		 * and sub-menu items are set as the current items. Most of this logic is necessary to set
		 * the correct sub-menu item.
		 */
		public function parent_file( $parent_file ) {
			global $self, $typenow, $pagenow, $post;

			// Grab the Conductor Query Builder instance
			$conductor_query_builder = Conduct_Query_Builder();

			// Bail if we're not on the Conductor Query Builder post type
			if ( ! in_array( $self, array( 'post.php', 'post-new.php' ) ) || empty( $post ) || get_post_field( 'post_type', $post ) !== $conductor_query_builder->post_type_name )
				return $parent_file;

			// Store a reference to the current self value
			self::$self = $self;

			// Grab the Conductor Admin Options menu page slug TODO: Remove fallback in a future version
			$conductor_admin_options_menu_page = ( class_exists( 'Conductor_Admin_Options' ) && method_exists( 'Conductor_Admin_Options', 'get_menu_page' ) ) ? Conductor_Admin_Options::get_menu_page() : str_replace( 'toplevel_page_', '', Conductor_Admin_Options::$menu_page );

			// Set the self value to the Conductor admin page slug
			$self = $conductor_admin_options_menu_page;


			// Store a reference to the current post type
			self::$typenow = $typenow;

			// Set the current post type to an empty value
			$typenow = '';


			// Store a reference to the current page
			self::$pagenow = $pagenow;

			// Set the current page to the sub-menu slug
			$pagenow = self::$sub_menu_page_slug;

			// Adjust the parent file
			$parent_file = $self;

			return $parent_file;
		}

		/**
		 * This function adjusts the sub-menu file value.
		 */
		public function submenu_file( $submenu_file ) {
			global $post;

			// Grab the Conductor Query Builder instance
			$conductor_query_builder = Conduct_Query_Builder();

			// Bail if we're not on the Conductor Query Builder post type
			if ( self::$self !== 'post-new.php' || empty( $post ) || get_post_field( 'post_type', $post ) !== $conductor_query_builder->post_type_name )
				return $submenu_file;

			// Store a reference to the current sub-menu file
			self::$submenu_file = $submenu_file;

			// Set the current sub-menu file to the sub-menu slug
			$submenu_file = self::$sub_menu_page_slug;

			return $submenu_file;
		}

		/**
		 * This function resets all global references after the WordPress admin menu has been rendered.
		 */
		public function adminmenu() {
			global $self, $typenow, $pagenow, $submenu_file, $post;

			// Grab the Conductor Query Builder instance
			$conductor_query_builder = Conduct_Query_Builder();

			// Bail if we're not on the Conductor Query Builder post type
			if ( ! in_array( $self, array( 'post.php', 'post-new.php' ) ) || empty( $post ) || get_post_field( 'post_type', $post ) !== $conductor_query_builder->post_type_name )
				return;

			// If the global self value doesn't match the original
			if ( self::$self !== 'conductor-qb-default' && $self !== self::$self )
				// Reset self
				$self = self::$self;

			// If the global post type value doesn't match the original
			if ( self::$typenow !== 'conductor-qb-default' && $typenow !== self::$typenow )
				// Reset the post type
				$typenow = self::$typenow;

			// If the global page value doesn't match the original
			if ( self::$pagenow !== 'conductor-qb-default' && $pagenow !== self::$pagenow )
				// Reset the page
				$pagenow = self::$pagenow;

			// If the global sub-menu file value doesn't match the original
			if ( self::$submenu_file !== 'conductor-qb-default' && $submenu_file !== self::$submenu_file )
				// Reset the sub-menu file
				$submenu_file = self::$submenu_file;
		}


		/**********************
		 * Internal Functions *
		 **********************/

		/**
		 * This function returns the sub-menu page. The optional $strip_prefix parameter allows the prefix
		 * added by WordPress to be stripped
		 */
		public static function get_sub_menu_page( $strip_prefix = true ) {
			return ( $strip_prefix ) ? str_replace( self::$sub_menu_page_prefix, '', self::$sub_menu_page ) : self::$sub_menu_page;
		}

		/**
		 * This function returns the sub-menu page. The optional $strip_prefix parameter allows the prefix
		 * added by WordPress to be stripped
		 */
		public static function get_sub_menu_page_slug( $strip_prefix = false ) {
			return ( $strip_prefix ) ? str_replace( self::$sub_menu_page_slug_prefix, '', self::$sub_menu_page_slug ) : self::$sub_menu_page_slug;
		}
	}

	/**
	 * Create an instance of the Conductor_Query_Builder_Admin class.
	 */
	function Conduct_Query_Builder_Admin() {
		return Conductor_Query_Builder_Admin::instance();
	}

	Conduct_Query_Builder_Admin(); // Conduct your content!
}