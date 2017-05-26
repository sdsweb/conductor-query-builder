<?php
/**
 * Shortcode Query Builder Insert UnderscoreJS Template
 *
 * @var $post WP_Post
 * @var $post_meta array
 * @var $conductor_query_builder Conductor_Query_Builder
 * @var $conductor_widget_instance array
 * @var $conductor_widget Conductor_Widget
 */
?>

<script type="text/template" id="tmpl-conductor-qb-shortcode-query-builder-insert">
	<?php do_action( 'conductor_query_builder_shortcode_insert_inner_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

		<#
			// If we have queries
			if ( data.queries.length ) {
				/*
				 * Queries
				 */
		#>
				<?php do_action( 'conductor_query_builder_shortcode_insert_inner_shortcode_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

				<p class="conductor-qb-shortcode-setting">
					<label for="conductor-qb-shortcode-insert-query" class="conductor-qb-label conductor-qb-shortcode-insert-label conductor-qb-shortcode-insert-query-label"><strong><?php _e( 'Select an existing Conductor Query', 'conductor-query-builder' ); ?></strong></label>
					<br />
					<select id="conductor-qb-shortcode-insert-query" class="conductor-qb-select conductor-qb-select2 conductor-qb-shortcode-insert-select conductor-qb-shortcode-insert-query-select" name="conductor_query_builder_shortcode_insert_query">
						<option value="">{{{ conductor_query_builder.l10n.query.select }}}</option>
						<#
							// Loop through the queries
							_.each( data.queries, function ( query_data ) {
						#>
								<option value="{{ query_data.ID }}">{{{ query_data.post_title || ( conductor_query_builder.l10n.query.no_title + ' (#' + query_data.ID + ')' ) }}}</option>
						<#
							} );
						#>
					</select>
				</p>

				<?php do_action( 'conductor_query_builder_shortcode_insert_inner_shortcode_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
		<#
				/*
				 * Title
				 */
		#>
				<?php do_action( 'conductor_query_builder_shortcode_insert_inner_title_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

				<p class="conductor-qb-shortcode-setting">
					<label for="conductor-qb-shortcode-insert-title" class="conductor-qb-label conductor-qb-shortcode-insert-label conductor-qb-shortcode-insert-title-label"><strong><?php _e( 'Add a title (optional)', 'conductor-query-builder' ); ?></strong></label>
					<br />
					<input type="text" id="conductor-qb-shortcode-insert-title" class="regular-text conductor-qb-input conductor-qb-shortcode-insert-input conductor-qb-shortcode-insert-title-input" name="conductor_query_builder_shortcode_insert_title" value="" />
				</p>

				<?php do_action( 'conductor_query_builder_shortcode_insert_inner_title_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
		<#
			}
			// Otherwise there are no queries to insert
			else {
		#>
			<?php do_action( 'conductor_query_builder_shortcode_insert_inner_no_queries_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

			<p class="conductor-qb-shortcode-notice">{{{ conductor_query_builder.l10n.query.none }}}</p>

			<?php do_action( 'conductor_query_builder_shortcode_insert_inner_no_queries_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
		<#
			}
		#>

	<?php do_action( 'conductor_query_builder_shortcode_insert_inner_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
</script>