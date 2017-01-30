<?php
/**
 * Shortcode Actions Query Builder
 *
 * @var $post WP_Post
 * @var $post_meta array
 * @var $conductor_query_builder Conductor_Query_Builder
 * @var $conductor_widget_instance array
 * @var $conductor_widget Conductor_Widget
 */

?>
<?php do_action( 'conductor_query_builder_shortcode_actions_before', $post, $conductor_query_builder ); ?>

<?php
	/**
	 * Actions
	 */
?>
<div id="conductor-qb-shortcode-actions" class="conductor-qb-shortcode-actions conductor-qb-cf">
	<?php do_action( 'conductor_query_builder_shortcode_actions', $post, $conductor_query_builder ); ?>
</div>

<?php do_action( 'conductor_query_builder_shortcode_actions_after', $post, $conductor_query_builder ); ?>