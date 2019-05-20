<?php
/**
 * Plugin Name: Conductor - Query Builder Add-On
 * Plugin URI: https://www.conductorplugin.com/
 * Description: The Conductor Query Builder add-on allows you to craft more complex queries with Conductor. The add-on has a simple view, and a builder view, which are usable in a shortcode for any page or post within your WordPress website.
 * Version: 1.0.6
 * Author: Slocum Studio
 * Author URI: https://www.slocumstudio.com/
 * Requires at least: 4.4
 * Tested up to: 5.1.1
 * License: GPL2+
 *
 * Text Domain: conductor-query-builder
 * Domain Path: /languages/
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Conductor_Query_Builder_Add_On' ) ) {
	final class Conductor_Query_Builder_Add_On {
		/**
		 * @var string
		 */
		public static $version = '1.0.6';

		/**
		 * @var Conductor_Updates, Instance of the Conductor Updates class
		 */
		protected $updater;

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
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) ); // Plugins Loaded
			add_action( 'widgets_init', array( $this, 'widgets_init' ) ); // Init Widgets
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		private function includes() {
			// All
			include_once 'includes/functions/php-array-column.php' ; // PHP - array_column() Function
			include_once 'includes/functions/conductor-query-builder.php' ; // Conductor Query Builder Functions
			include_once 'includes/class-conductor-query-builder.php' ; // Conductor Query Builder Class
			include_once 'includes/admin/class-conductor-query-builder-admin.php' ; // Conductor Query Builder Admin Class
			include_once 'includes/class-conductor-query-builder-conductor-rest-api.php'; // Conductor Query Builder Conductor REST API Class
			include_once 'includes/class-conductor-query-builder-gutenberg.php'; // Conductor Query Builder Gutenberg Class
			include_once 'includes/class-conductor-query-builder-toolbar.php'; // Conductor Query Builder Toolbar (Admin Bar) Class
			include_once 'includes/class-conductor-query-builder-query-tags.php' ; // Conductor Query Builder Query Tags Class

			// Beaver Builder
			if ( class_exists( 'FLBuilder' ) ) {
				include_once 'includes/class-conductor-query-builder-beaver-builder.php'; // Conductor Query Builder - Beaver Builder Class
				include_once 'includes/class-conductor-query-builder-beaver-builder-conductor-rest-api.php'; // Conductor Query Builder - Beaver Builder - Conductor REST API Class
			}

			// Admin Only
			if ( is_admin() ) {}

			// Front-End Only
			if ( ! is_admin() ) {}
		}

		/**
		 * Allow add-on updates.
		 */
		private function updates() {
			// Create a new instance of the Conductor Updates class for this add-on
			if ( class_exists( 'Conductor_Updates' ) )
				$this->updater = new Conductor_Updates( array(
					'version' => Conductor_Query_Builder_Add_On::$version,
					'name' => 'Query Builder Add-On',
					'plugin_file' => Conductor_Query_Builder_Add_On::plugin_file()
				) );
		}

		/**
		 * This function checks to see if Conductor is enabled.
		 */
		public function plugins_loaded() {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';

			// If Conductor is not active
			if ( ! class_exists( 'Conductor' ) || ! version_compare( Conductor::$version, '1.4.0', '>=' ) )
				// If this plugin is active
				if ( is_plugin_active( plugin_basename( self::plugin_file() ) ) ) {
					// De-activate this plugin
					deactivate_plugins( plugin_basename( self::plugin_file() ) );
					unset( $_GET['activate'] );

					// Show admin notice
					add_action( 'admin_notices', array( $this, 'admin_notices' ) );
				}

			// Load the Conductor Query Builder text domain
			load_plugin_textdomain( 'conductor-query-builder', false, basename( self::plugin_dir() ) . '/languages/' );

			// Load required assets
			$this->includes();

			// Updates
			$this->updates();
		}

		/**
		 * This function outputs an admin notice if Conductor is not active
		 */
		public function admin_notices() {
		?>
			<div class="updated error">
				<p><?php printf( __( 'Conductor Query Builder Add-on requires Conductor version 1.4.0 or greater. Please install &amp; activate Conductor version 1.4.0 or greater and try again.', 'conductor-query-builder' ) ); ?></p>
			</div>
		<?php
		}

		/**
		 * This function includes and initializes Conductor Widgets.
		 */
		public function widgets_init() {
			// Conductor Widget
			include_once 'includes/widgets/class-conductor-query-builder-widget.php';
		}


		/********************
		 * Helper Functions *
		 ********************/

		/**
		 * This function returns the plugin url for Conductor without a trailing slash.
		 *
		 * @return string, URL for the Conductor plugin
		 */
		public static function plugin_url() {
			return untrailingslashit( plugins_url( '', __FILE__ ) );
		}

		/**
		 * This function returns the plugin directory for Conductor without a trailing slash.
		 *
		 * @return string, Directory for the Conductor plugin
		 */
		public static function plugin_dir() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

		/**
		 * This function returns a reference to this Conductor class file.
		 *
		 * @return string
		 */
		public static function plugin_file() {
			return __FILE__;
		}
	}

	/**
	 * Create an instance of the Conductor_Query_Builder_Add_On class.
	 */
	function Conduct_Query_Builder_Add_On() {
		return Conductor_Query_Builder_Add_On::instance();
	}

	Conduct_Query_Builder_Add_On(); // Conduct your content!
}