<?php
/**
 * Conductor Query Builder Widget
 *
 * @class Conductor_Query_Builder_Widget
 * @author Slocum Studio
 * @version 1.0.4
 * @since 1.0.0
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Conductor_Query_Builder_Widget' ) ) {
	final class Conductor_Query_Builder_Widget extends WP_Widget {
		/**
		 * @var string
		 */
		public $version = '1.0.4';

		/**
		 * @var array, Conductor Query Builder Widget defaults
		 */
		public $defaults = array();

		/**
		 * @var Conductor_Query_Builder_Widget, Instance of the class
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
		 * This function sets up all of the actions and filters on instance. It also initializes widget options
		 * including class name, description, width/height, and creates an instance of the widget
		 */
		function __construct() {
			// Load required assets
			$this->includes();


			// Set up the default widget settings
			$this->defaults = apply_filters( 'conductor_query_builder_widget_defaults', array(
				'title' => false,
				'post_id' => false
			), $this );


			// Widget/Control options
			$widget_options = array(
				'classname' => 'conductor-query-builder-widget',
				'description' => __( 'Display a Conductor Query.', 'conductor-query-builder' )
			);
			$widget_options = apply_filters( 'conductor_query_builder_widget_widget_options', $widget_options, $this );

			$control_options = apply_filters( 'conductor_query_builder_widget_control_options', array( 'id_base' => 'conductor-query-builder-widget' ), $this );

			// Call the parent constructor
			parent::__construct( 'conductor-query-builder-widget', __( 'Conductor Query Widget', 'conductor-query-builder' ), $widget_options, $control_options );
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		private function includes() {
			// TODO
		}


		/**
		 * This function configures the form on the Widgets Admin Page.
		 */
		public function form( $instance ) {
			global $wpdb;

			// Parse any saved arguments into defaults
			$instance = wp_parse_args( ( array ) $instance, $this->defaults );

			// Grab the Conductor Query Builder instance
			$conductor_query_builder = Conduct_Query_Builder();
		?>
			<?php do_action( 'conductor_query_builder_widget_settings_before', $instance, $this ); ?>

			<div class="conductor-query-builder-widget-setting conductor-query-builder-widget-title conductor-widget-setting">
				<?php do_action( 'conductor_query_builder_widget_settings_title_before', $instance, $this ); ?>

				<?php // TODO: Add ability to hide title like Conductor Widget ?>

				<?php // Widget Title ?>
				<label for="<?php echo $this->get_field_id( 'title' ) ; ?>"><strong><?php _e( 'Title', 'conductor-query-builder' ); ?></strong></label>
				<br />
				<input type="text" class="conductor-query-builder-input conductor-input" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />

				<?php do_action( 'conductor_query_builder_widget_settings_title_after', $instance, $this ); ?>
			</div>

			<div class="conductor-query-builder-widget-setting conductor-query-builder-widget-post-id conductor-widget-setting">
				<?php do_action( 'conductor_query_builder_widget_settings_post_id_before', $instance, $this ); ?>

				<?php
					// Grab the Conductor Query post type name
					$conductor_query_builder_post_type_name = $conductor_query_builder->post_type_name;

					// Post Count
					if ( ! $post_count = wp_cache_get( $conductor_query_builder_post_type_name . '_post_type_count', 'conductor-query-builder-widget' ) ) {
						$post_count = $conductor_query_builder->get_query_count();
						wp_cache_add( $conductor_query_builder_post_type_name . '_post_type_count', $post_count, 'conductor-query-builder-widget' ); // Store cache
					}

					// Post Data
					if ( ! $queries = wp_cache_get( $conductor_query_builder_post_type_name . '_data', 'conductor-query-builder-widget' ) ) {
						$queries = $conductor_query_builder->get_queries();

						wp_cache_add( $conductor_query_builder_post_type_name . '_data', $queries, 'conductor-query-builder-widget' ); // Store cache
					}

					// Display queries
					if ( ! empty( $queries ) ) :
					?>
						<label for="<?php echo $this->get_field_id( 'post_id' ); ?>"><strong><?php _e( 'Select a Query', 'conductor-query-builder' ); ?></strong></label>
						<br />
						<select class="conductor-query-builder-select conductor-select" id="<?php echo $this->get_field_id( 'post_id' ); ?>" name="<?php echo $this->get_field_name( 'post_id' ); ?>">
							<option value=""><?php _e( '&mdash; Select &mdash;', 'conductor' ); ?></option>
								<?php
									foreach ( $queries as $post ) :
										// Grab the post ID
										$post_id = get_post_field( 'ID', $post );

										// Grab the post title
										$post_title = get_the_title( $post );
								?>
									<option value="<?php echo esc_attr( $post_id ); ?>" <?php selected( ( $instance['post_id'] === $post_id ) ); ?>><?php echo ( empty( $post_title ) ) ? sprintf( __( '#%d (no title)', 'conductor-query-builder' ), $post_id ) : $post_title; ?></option>
								<?php
									endforeach;
								?>
						</select>
					<?php
					endif;
				?>

				<?php do_action( 'conductor_query_builder_widget_settings_post_id_after', $instance, $this ); ?>
			</div>

			<?php do_action( 'conductor_query_builder_widget_settings_after', $instance, $this ); ?>

			<div class="clear"></div>

			<p class="conductor-query-builder-widget-slug conductor-widget-slug">
				<?php printf( __( 'Content management brought to you by <a href="%1$s" target="_blank">Conductor</a>','conductor-query-builder' ), esc_url( 'https://conductorplugin.com/?utm_source=conductor-query-builder&utm_medium=link&utm_content=conductor-query-builder-widget-branding&utm_campaign=conductor' ) ); ?>
			</p>
		<?php
		}

		/**
		 * This function handles updating (saving) widget options
		 */
		public function update( $new_instance, $old_instance ) {
			// Sanitize all input data
			$new_instance['title'] = ( isset($new_instance['title'] ) && ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : $this->defaults['title']; // Widget Title
			$new_instance['post_id'] = ( isset($new_instance['post_id'] ) && ! empty( $new_instance['post_id'] ) ) ? abs( ( int ) $new_instance['post_id'] ) : $this->defaults['post_id']; // Post ID

			return apply_filters( 'conductor_query_builder_widget_update', $new_instance, $old_instance, $this );
		}

		/**
		 * This function controls the display of the widget on the website
		 */
		public function widget( $args, $instance ) {
			// Grab the Conductor Query Builder instance
			$conductor_query_builder = Conduct_Query_Builder();

			// Instance filter
			$instance = apply_filters( 'conductor_query_builder_widget_instance', $instance, $args, $conductor_query_builder, $this );

			extract( $args ); // $before_widget, $after_widget, $before_title, $after_title

			// Start of widget output
			echo $before_widget;

			do_action( 'conductor_query_builder_widget_before', $instance, $args, $conductor_query_builder, $this );

			// If we have a post ID
			if ( isset( $instance['post_id'] ) && ! empty( $instance['post_id'] ) )
				// Render this Conductor Query
				$conductor_query_builder->render( $instance['post_id'], ( isset( $instance['title'] ) ) ? $instance['title'] : '', array(
					'type' => 'widget',
					'widget' => $this
				) );

			do_action( 'conductor_query_builder_widget_after', $instance, $args, $conductor_query_builder, $this );

			// End of widget output
			echo $after_widget;
		}
	}

	/**
	 * Create an instance of the Conductor_Query_Builder_Widget class.
	 */
	function Conduct_Query_Builder_Widget() {
		return Conductor_Query_Builder_Widget::instance();
	}

	//Conduct_Widget(); // Conduct your content!

	register_widget( 'Conductor_Query_Builder_Widget' );
}