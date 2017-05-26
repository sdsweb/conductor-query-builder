<?php
/**
 * Shortcode Query Builder Create Title UnderscoreJS Template
 *
 * @var $post WP_Post
 * @var $post_meta array
 * @var $conductor_query_builder Conductor_Query_Builder
 * @var $conductor_widget_instance array
 * @var $conductor_widget Conductor_Widget
 */

// TODO: Need to allow a value to be populated from data
?>

<script type="text/template" id="tmpl-conductor-qb-shortcode-query-builder-create-title">
	<?php do_action( 'conductor_query_builder_shortcode_create_title_inner_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

		<#
			/*
			 * Title
			 */
		#>
		<p class="conductor-qb-shortcode-setting">
			<label for="conductor-qb-shortcode-create-title" class="conductor-qb-label conductor-qb-shortcode-create-label conductor-qb-shortcode-create-title-label"><strong><?php printf( __( 'Title %1$s', 'conductor-query-builder' ), '<span class="required conductor-qb-required">*</span>' ); ?></strong></label>
			<br />
			<input type="text" id="conductor-qb-shortcode-create-title" class="regular-text conductor-qb-input conductor-qb-shortcode-create-input conductor-qb-shortcode-create-title-input" name="conductor_query_builder_shortcode_create_title" value="{{ data.title }}" />
			<br />
			<span class="description conductor-description conductor-qb-description"><?php _e( 'You must enter a title to save this query.', 'conductor-query-builder' ); ?></span>
		</p>

	<?php do_action( 'conductor_query_builder_shortcode_create_title_inner_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
</script>