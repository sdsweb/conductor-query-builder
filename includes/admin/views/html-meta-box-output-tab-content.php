<?php
/**
 * Meta Box Output Tab Content
 *
 * @var $post WP_Post
 * @var $post_meta array
 * @var $conductor_query_builder Conductor_Query_Builder
 * @var $conductor_widget_instance array
 * @var $conductor_widget Conductor_Widget
 */
?>
<div id="conductor-qb-meta-box-output-tab-content" class="conductor-qb-tab conductor-qb-query-builder-meta-box-tab-content" data-type="conductor-qb-query-builder">
	<div id="conductor-query-builder-conductor-widget-display-section" class="conductor-query-builder-conductor-widget-section">
		<?php
			// Output the widget Display Settings Section
			$conductor_widget->widget_settings_display_section( $conductor_widget_instance );
		?>
	</div>
</div>