<?php
/**
 * Meta Box Query Builder Tab Content Clause Group UnderscoreJS Template
 *
 * @var $post WP_Post
 * @var $post_meta array
 * @var $conductor_query_builder Conductor_Query_Builder
 * @var $conductor_widget_instance array
 * @var $conductor_widget Conductor_Widget
 */
?>

<script type="text/template" id="tmpl-conductor-qb-meta-box-query-builder-clause-group">
	<?php do_action( 'conductor_query_builder_meta_box_query_builder_clause_group_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

	<?php do_action( 'conductor_query_builder_meta_box_query_builder_clause_group_title_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

	<h4 class="conductor-qb-meta-box-query-builder-group-title">
		<#
			if ( ! data.flags.ignore_descriptive_labels ) {
		#>
			<#
				// Or
				if ( data.limit === -1 && data.count > 0 ) {
			#>
				<span class="descriptive-label conductor-qb-descriptive-label descriptive-label-or conductor-qb-descriptive-label-or">{{{ conductor_query_builder.l10n.or }}}</span>
			<#
				}
				// And
				else {
			#>
				<span class="descriptive-label conductor-qb-descriptive-label descriptive-label-and conductor-qb-descriptive-label-and">{{{ conductor_query_builder.l10n.and }}}</span>
			<#
				}
			#>
		<#
			}
		#>

		{{{ data.title }}}
	</h4>

	<?php do_action( 'conductor_query_builder_meta_box_query_builder_clause_group_title_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>


	<?php do_action( 'conductor_query_builder_meta_box_query_builder_clause_group_sub_clause_groups_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

	<div id="conductor-qb-meta-box-query-builder-{{ data.type }}-sub-clause-groups" class="conductor-qb-meta-box-query-builder-sub-groups conductor-qb-meta-box-query-builder-sub-clause-groups conductor-qb-meta-box-query-builder-{{ data.type }}-sub-groups conductor-qb-meta-box-query-builder-{{ data.type }}-sub-clause-groups">
		<?php do_action( 'conductor_query_builder_meta_box_query_builder_clause_group_sub_clause_groups', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
	</div>

	<?php do_action( 'conductor_query_builder_meta_box_query_builder_clause_group_sub_clause_groups_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>


	<#
		// If this view supports actions
		if ( data.flags.actions ) {
	#>
		<?php do_action( 'conductor_query_builder_meta_box_query_builder_clause_group_actions_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

		<div id="conductor-qb-meta-box-query-builder-{{ data.type }}-actions" class="conductor-qb-meta-box-query-builder-actions conductor-qb-meta-box-query-builder-{{ data.type }}-actions conductor-qb-meta-box-query-builder-clause-group-actions conductor-qb-meta-box-query-builder-{{ data.type }}-clause-group-actions conductor-qb-cf">
			<?php do_action( 'conductor_query_builder_meta_box_query_builder_clause_group_actions', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
		</div>

		<?php do_action( 'conductor_query_builder_meta_box_query_builder_clause_group_actions_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
	<#
		}
	#>

	<#
		// If this view can be removed
		if ( data.flags.remove ) {
	#>
		<button class="button button-secondary conductor-qb-button conductor-qb-action-button conductor-qb-remove-action-button conductor-qb-remove-clause-group-button conductor-qb-remove-{{ data.type }}-clause-group-button" data-action-button-id="conductor-qb-add-{{ data.type }}-clause-group-button"><?php _e( 'Remove Group', 'conductor-query-builder' ); ?></button>
	<#
		}
	#>

	<?php do_action( 'conductor_query_builder_meta_box_query_builder_clause_group_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
</script>