<?php
/**
 * Shortcode Create Query Builder Tab Content
 *
 * @var $post WP_Post
 * @var $post_meta array
 * @var $conductor_query_builder Conductor_Query_Builder
 * @var $conductor_widget_instance array
 * @var $conductor_widget Conductor_Widget
 * @var $form_element string
 */

?>
<div id="conductor-qb-shortcode-output-tab-content" class="conductor-qb-tab conductor-qb-shortcode-output-tab-content" data-type="conductor-qb-shortcode">
	<?php do_action( 'conductor_query_builder_shortcode_create_tab_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

	<<?php echo $form_element; ?> id="conductor-qb-shortcode-create-form" class="conductor-qb-form conductor-qb-shortcode-create-form">
		<?php
			/**
			 * Title
			 */
		?>
		<?php do_action( 'conductor_query_builder_shortcode_create_tab_title_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

		<div id="conductor-qb-shortcode-create-title-content" class="conductor-qb-shortcode-create-title-content conductor-qb-cf">
			<?php do_action( 'conductor_query_builder_shortcode_create_tab_title', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
		</div>

		<?php do_action( 'conductor_query_builder_shortcode_create_tab_title_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

		<?php
			/**
			 * Query Builder
			 */

			// Output the Conductor Query Builder Meta box
			$conductor_query_builder->meta_box_query_builder( $post, array(), $conductor_widget_instance );
		?>
	</<?php echo $form_element; ?>>

	<?php do_action( 'conductor_query_builder_shortcode_create_tab_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
</div>