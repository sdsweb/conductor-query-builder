<?php
/**
 * Shortcode Query Builder Actions UnderscoreJS Template
 *
 * @var $post WP_Post
 * @var $post_meta array
 * @var $conductor_query_builder Conductor_Query_Builder
 * @var $conductor_widget_instance array
 * @var $conductor_widget Conductor_Widget
 */
?>

<script type="text/template" id="tmpl-conductor-qb-shortcode-query-builder-actions">
	<?php do_action( 'conductor_query_builder_shortcode_actions_inner_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

	<button class="button button-primary conductor-qb-button conductor-qb-shortcode-action-button" data-type="{{ data.button_type }}" <?php disabled( true ); ?>>{{{ data.label }}}</button>

	<span class="loading conductor-qb-loading spinner conductor-qb-spinner"></span>

	<?php do_action( 'conductor_query_builder_shortcode_actions_inner_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
</script>