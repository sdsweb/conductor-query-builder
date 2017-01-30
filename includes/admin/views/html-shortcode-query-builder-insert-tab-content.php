<?php
/**
 * Shortcode Insert Query Builder Tab Content
 *
 * @var $post WP_Post
 * @var $post_meta array
 * @var $conductor_query_builder Conductor_Query_Builder
 * @var $conductor_widget_instance array
 * @var $conductor_widget Conductor_Widget
 */

?>
<div id="conductor-qb-shortcode-insert-tab-content" class="conductor-qb-tab conductor-qb-shortcode-insert-tab-content active" data-type="conductor-qb-shortcode">
	<?php do_action( 'conductor_query_builder_shortcode_insert_tab_before', $post, $conductor_query_builder ); ?>

	<?php
		/**
		 * Insert
		 */
	?>
	<?php do_action( 'conductor_query_builder_shortcode_create_tab_insert_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

	<div id="conductor-qb-shortcode-insert" class="conductor-qb-shortcode-insert conductor-qb-cf">
		<?php do_action( 'conductor_query_builder_shortcode_create_tab_insert', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
	</div>

	<?php do_action( 'conductor_query_builder_shortcode_create_tab_insert_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

	<?php do_action( 'conductor_query_builder_shortcode_insert_tab_after', $post, $conductor_query_builder ); ?>
</div>