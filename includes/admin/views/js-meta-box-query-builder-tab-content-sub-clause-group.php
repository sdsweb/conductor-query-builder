<?php
/**
 * Meta Box Query Builder Tab Content Clause Sub-Group UnderscoreJS Template
 *
 * @var $post WP_Post
 * @var $post_meta array
 * @var $conductor_query_builder Conductor_Query_Builder
 * @var $conductor_widget_instance array
 * @var $conductor_widget Conductor_Widget
 */
?>

<script type="text/template" id="tmpl-conductor-qb-meta-box-query-builder-sub-clause-group">
	<?php do_action( 'conductor_query_builder_meta_box_query_builder_sub_clause_group_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

	<?php do_action( 'conductor_query_builder_meta_box_query_builder_sub_clause_group_label_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

	<span class="descriptive-label conductor-qb-descriptive-label descriptive-label-and conductor-qb-descriptive-label-and">{{{ conductor_query_builder.l10n.and }}}</span>

	<?php do_action( 'conductor_query_builder_meta_box_query_builder_sub_clause_group_label_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

	<#
		/*
		 * Parameters Column (1st Column)
		 */

		// If we should render this column
		if ( data.columns.parameters || data.meta.parameters ) {
	#>
			<select class="conductor-qb-select conductor-qb-select2 conductor-qb-parameters-select conductor-qb-{{ data.type }}-select conductor-qb-{{ data.type }}-parameters-select" name="conductor_query_builder[{{ data.type }}][{{ data.parent.count }}][{{ data.count }}][parameters]<# if ( data.columns.parameters.select2 && data.columns.parameters.select2.multiple ) { #>[]<# } #>"<# if ( data.columns.parameters.select2 && data.columns.parameters.select2.multiple ) { #> multiple="multiple" <# } #> <# if ( data.columns.parameters.select2 && data.columns.parameters.select2.placeholder ) { #> data-placeholder="{{ data.columns.parameters.select2.placeholder }}" <# } #> <# if ( data.columns.parameters.select2 && data.columns.parameters.select2.tags ) { #> data-tags="true" <# } #> <# if ( data.columns.parameters.select2 && data.columns.parameters.select2['toggle-action-buttons'] ) { #> data-toggle-action-buttons="true" <# } #> data-select-type="{{ data.type }}" data-type="parameters">
				<option value=""></option>
				<#
					// Loop through the parameters
					_.each( data.parameters, function ( parameter_data, parameter ) {
				#>
						<option value="{{ parameter }}" <# if ( data.meta.parameters && ( ( typeof data.meta.parameters === 'string' && parameter === data.meta.parameters ) || ( typeof data.meta.parameters !== 'string' && data.meta.parameters.length && data.meta.parameters.indexOf( parameter ) !== -1 ) ) ) { #> <?php selected( true ); ?> <# } #> <# if ( typeof parameter_data !== 'string' && parameter_data.parameter ) { #> data-parameter="{{ parameter_data.parameter }}" <# } #> <# if ( typeof parameter_data !== 'string' && parameter_data.field ) { #> data-field="{{ parameter_data.field }}" <# } #>>
							<#
								if ( typeof parameter_data === 'string' ) {
							#>
									{{{ parameter_data }}}
							<#
								}
								else {
							#>
									{{{ parameter_data.label }}}
							<#
								}
							#>
						</option>
				<#
					} );
				#>
			</select>
	<#
		}
	#>


	<#
		/*
		 * Operators Column (2nd Column)
		 */

		// If we should render this column
		if ( data.columns.operators || data.meta.operators ) {
	#>
			<select class="conductor-qb-select conductor-qb-select2 conductor-qb-operators-select conductor-qb-{{ data.type }}-select conductor-qb-{{ data.type }}-operators-select" name="conductor_query_builder[{{ data.type }}][{{ data.parent.count }}][{{ data.count }}][operators]<# if ( data.columns.operators.select2 && data.columns.operators.select2.multiple ) { #>[]<# } #>" <# if ( data.columns.operators.select2 && data.columns.operators.select2.multiple ) { #> multiple="multiple" <# } #> <# if ( data.columns.operators.select2 && data.columns.operators.select2.placeholder ) { #> data-placeholder="{{ data.columns.operators.select2.placeholder }}" <# } #> <# if ( data.columns.parameters.select2 && data.columns.parameters.select2.tags ) { #> data-tags="true" <# } #> data-select-type="{{ data.type }}" data-type="operators" <# if ( ! data.meta.operators ) {#> disabled="disabled" <# } #>>
				<option value=""></option>
				<#
					// Loop through the operators
					_.each( data.operators, function ( operator_data, operator ) {
				#>
						<option value="{{ operator }}" <# if ( data.meta.operators && ( ( typeof data.meta.operators === 'string' && operator === data.meta.operators ) || ( data.meta.operators.length && data.meta.operators.indexOf( operator ) !== -1 ) ) ) { #> <?php selected( true ); ?> <# } #> <# if ( ( typeof operator_data !== 'string' ) && operator_data.multiple ) { #> data-multiple="true" <# } #>>
							<#
								if ( typeof operator_data === 'string' ) {
							#>
									{{{ operator_data }}}
							<#
								}
								else {
							#>
									{{{ operator_data.label }}}
							<#
								}
							#>
						</option>
				<#
					} );
				#>
			</select>
	<#
		}
	#>


	<#
		/*
		 * Values Column (3rd Column)
		 */

		// If we should render this column
		if ( data.columns.values ) {
	#>
			<select class="conductor-qb-select conductor-qb-select2 conductor-qb-values-select conductor-qb-{{ data.type }}-select conductor-qb-{{ data.type }}-values-select" name="conductor_query_builder[{{ data.type }}][{{ data.parent.count }}][{{ data.count }}][values]<# if ( data.columns.values.select2 && data.columns.values.select2.multiple ) { #>[]<# } #>"<# if ( data.columns.values.select2 && data.columns.values.select2.multiple ) { #> multiple="multiple" <# } #> <# if ( data.columns.values.select2 && data.columns.values.select2.placeholder ) { #> data-placeholder="{{ data.columns.values.select2.placeholder }}" <# } #> <# if ( data.columns.values.select2 && data.columns.values.select2.tags ) { #> data-tags="true" <# } #> <# if ( data.columns.values.select2 && data.columns.values.select2['toggle-action-buttons'] ) { #> data-toggle-action-buttons="true" <# } #> data-select-type="{{ data.type }}" data-type="values" <# if ( ! data.meta.values ) {#> disabled="disabled" <# } #>>
				<option value=""></option>
				<#
					// Loop through the values
					_.each( data.values, function ( value_data, value ) {
						// Grab the value
						value = ( typeof value_data === 'string' ) ? value_data : value;
				#>
						<option value="{{ value }}" <# if ( data.meta.values && ( ( typeof data.meta.values === 'string' && value === data.meta.values ) || ( data.meta.values.length && data.meta.values.indexOf( value ) !== -1 ) ) ) { #> <?php selected( true ); ?> <# } #> <# if ( typeof value_data !== 'string' && value_data.type ) { #> data-type="{{ value_data.type }}" <# } #>>
							<#
								if ( typeof value_data === 'string' ) {
							#>
									{{{ value }}}
							<#
								}
								else {
							#>
									{{{ value_data.label }}}
							<#
								}
							#>
						</option>
				<#
					} );
				#>
			</select>
	<#
		}
	#>

	<#
		// If this isn't the default view
		if ( ! data.flags.default ) {
	#>
		<button class="button button-secondary conductor-qb-button conductor-qb-action-button conductor-qb-remove-action-button conductor-qb-remove-sub-clause-group-button conductor-qb-remove-{{ data.type }}-sub-clause-group-button" data-action-button-id="conductor-qb-add-{{ data.type }}-clause-group-button" title=<?php esc_attr_e( 'Remove Sub-Clause Group', 'conductor-qb' ); ?>><span class="dashicons dashicons-minus"></span></button>
	<#
		}
	#>

	<#
		// If this view allows for sub-clause groups
		if ( data.flags.sub_clause_groups ) {
	#>
		<button class="button button-secondary conductor-qb-button conductor-qb-action-button conductor-qb-add-action-button conductor-qb-add-sub-clause-group-button conductor-qb-add-{{ data.type }}-sub-clause-group-button" title="<?php esc_attr_e( 'Add Sub-Clause Group', 'conductor-qb' ); ?>"><span class="dashicons dashicons-plus"></span></button>
	<#
		}
	#>

	<?php do_action( 'conductor_query_builder_meta_box_query_builder_sub_clause_group_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
</script>