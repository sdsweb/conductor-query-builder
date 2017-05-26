<?php
/**
 * Meta Box Query Builder Tab Content Actions UnderscoreJS Template
 *
 * @var $post WP_Post
 * @var $post_meta array
 * @var $conductor_query_builder Conductor_Query_Builder
 * @var $conductor_widget_instance array
 * @var $conductor_widget Conductor_Widget
 */
?>

<script type="text/template" id="tmpl-conductor-qb-meta-box-query-builder-actions">
	<?php do_action( 'conductor_query_builder_meta_box_query_builder_actions_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

	<#
		// If this is a clause group
		if ( data.flags.clause_group ) {
	#>
		<button id="conductor-qb-add-where-clause-group-button" class="button button-secondary conductor-qb-button conductor-qb-action-button conductor-qb-add-action-button conductor-qb-add-clause-group-button conductor-qb-add-where-clause-group-button <?php echo esc_attr( ( $conductor_query_builder->get_query_builder_mode() !== 'advanced' ) ? 'hide hidden conductor-qb-hide conductor-qb-hidden' : false ); ?>" data-clause-group-type="where" data-query-builder-mode="advanced" <?php echo ( $conductor_query_builder->is_action_button_disabled( 'where' ) ) ? disabled( true, true, false ) : false; ?>>
			{{{ conductor_query_builder.l10n.add + ' ' + conductor_query_builder.clauses.where.config.title }}}
		</button>
		<button id="conductor-qb-add-meta-query-where-clause-group-button" class="button button-secondary conductor-qb-button conductor-qb-action-button conductor-qb-add-action-button conductor-qb-add-clause-group-button conductor-qb-add-meta-query-where-clause-group-button <?php echo esc_attr( ( $conductor_query_builder->get_query_builder_mode() !== 'advanced' ) ? 'hide hidden conductor-qb-hide conductor-qb-hidden' : false ); ?>" data-clause-group-type="meta_query" data-query-builder-mode="advanced" <?php echo ( $conductor_query_builder->is_action_button_disabled( 'meta_query' ) ) ? disabled( true, true, false ) : false; ?>>
			{{{ conductor_query_builder.l10n.add + ' ' + conductor_query_builder.clauses.meta_query.config.title }}}
		</button>
		<button id="conductor-qb-add-tax-query-where-clause-group-button" class="button button-secondary conductor-qb-button conductor-qb-action-button conductor-qb-add-action-button conductor-qb-add-clause-group-button conductor-qb-add-tax-query-where-clause-group-button <?php echo esc_attr( ( $conductor_query_builder->get_query_builder_mode() !== 'advanced' ) ? 'hide hidden conductor-qb-hide conductor-qb-hidden' : false ); ?>" data-clause-group-type="tax_query" data-query-builder-mode="advanced" <?php echo ( $conductor_query_builder->is_action_button_disabled( 'tax_query' ) ) ? disabled( true, true, false ) : false; ?>>
			{{{ conductor_query_builder.l10n.add + ' ' + conductor_query_builder.clauses.tax_query.config.title }}}
		</button>
		<button id="conductor-qb-add-order-by-clause-group-button" class="button button-secondary conductor-qb-button conductor-qb-action-button conductor-qb-add-action-button conductor-qb-add-clause-group-button conductor-qb-add-order-by-clause-group-button <?php echo esc_attr( ( $conductor_query_builder->get_query_builder_mode() !== 'advanced' ) ? 'hide hidden conductor-qb-hide conductor-qb-hidden' : false ); ?>" data-clause-group-type="order_by" data-query-builder-mode="advanced" <?php echo ( $conductor_query_builder->is_action_button_disabled( 'order_by' ) ) ? disabled( true, true, false ) : false; ?>>
			{{{ conductor_query_builder.l10n.add + ' ' + conductor_query_builder.clauses.order_by.config.title }}}
		</button>
		<button id="conductor-qb-add-limit-clause-group-button" class="button button-secondary conductor-qb-button conductor-qb-action-button conductor-qb-add-action-button conductor-qb-add-clause-group-button conductor-qb-add-limit-clause-group-button <?php echo esc_attr( ( $conductor_query_builder->get_query_builder_mode() !== 'advanced' ) ? 'hide hidden conductor-qb-hide conductor-qb-hidden' : false ); ?>" data-clause-group-type="limit" data-query-builder-mode="advanced" <?php echo ( $conductor_query_builder->is_action_button_disabled( 'limit' ) ) ? disabled( true, true, false ) : false; ?>>
			{{{ conductor_query_builder.l10n.add + ' ' + conductor_query_builder.clauses.limit.config.title }}}
		</button>
	<#
		}
	#>

	<#
		// If this isn't a clause group
		if ( ! data.flags.clause_group ) {
	#>
		<button class="button button-primary conductor-qb-button conductor-qb-button-right conductor-qb-toggle-mode-button" data-simple-label="<?php esc_attr_e( 'Switch to Builder', 'conductor-query-builder' ); ?>" data-advanced-label="<?php esc_attr_e( 'Switch to Simple', 'conductor-query-builder' ); ?>"><?php echo ( $conductor_query_builder->get_query_builder_mode() !== 'advanced' ) ? __( 'Switch to Builder', 'conductor-query-builder' ) : __( 'Switch to Simple', 'conductor-query-builder' ); ?></button>
	<#
		}
	#>

	<?php do_action( 'conductor_query_builder_meta_box_query_builder_actions_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
</script>