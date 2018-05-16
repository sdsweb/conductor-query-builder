<?php
/**
 * Meta Box Query Builder Tab Content
 *
 * @var $post WP_Post
 * @var $post_meta array
 * @var $conductor_query_builder Conductor_Query_Builder
 * @var $conductor_widget_instance array
 * @var $conductor_widget Conductor_Widget
 */

// TODO: Review/rename actions before/after each section
?>

<div id="conductor-qb-meta-box-query-builder-tab-content" class="conductor-qb-tab conductor-qb-query-builder-meta-box-tab-content active" data-type="conductor-qb-query-builder">
	<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

	<?php
		/**
		 * Actions
		 */
	?>
	<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_actions_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

	<div id="conductor-qb-meta-box-query-builder-actions" class="conductor-qb-meta-box-query-builder-actions conductor-qb-cf">
		<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_actions', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
	</div>

	<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_actions_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>


	<?php
		/**
		 * Simple Query Builder
		 */
	?>
	<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_simple_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

	<div id="conductor-qb-meta-box-query-builder-simple" class="conductor-qb-meta-box-query-builder-mode conductor-qb-meta-box-query-builder-simple conductor-qb-cf <?php echo esc_attr( ( $conductor_query_builder->get_query_builder_mode() !== 'simple' ) ? 'hide hidden conductor-qb-hide conductor-qb-hidden' : false ); ?>" data-query-builder-mode="simple">
		<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_simple_title_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

		<h3 id="conductor-qb-meta-box-query-builder-simple-title" class="conductor-qb-meta-box-query-builder-mode-title conductor-qb-meta-box-query-builder-simple-title"><?php _e( 'Simple Query Builder', 'conductor-query-builder' ); ?></h3>

		<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_simple_title_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>


		<div id="conductor-query-builder-conductor-widget-content-section" class="conductor-query-builder-conductor-widget-section">
			<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_simple_conductor_widget_content_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

			<?php
				// Output the widget Content Settings Section
				$conductor_widget->widget_settings_content_section( $conductor_widget_instance );
			?>

			<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_simple_conductor_widget_content_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
		</div>
	</div>

	<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_simple_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>


	<?php
		/**
		 * Advanced Query Builder
		 */
	?>
	<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

	<div id="conductor-qb-meta-box-query-builder-advanced" class="conductor-qb-meta-box-query-builder-mode conductor-qb-meta-box-query-builder-advanced conductor-qb-cf <?php echo ( $conductor_query_builder->get_query_builder_mode() !== 'advanced' ) ? 'hide hidden conductor-qb-hide conductor-qb-hidden' : false; ?>" data-query-builder-mode="advanced">
		<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_title_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

		<h3 id="conductor-qb-meta-box-query-builder-advanced-title" class="conductor-qb-meta-box-query-builder-mode-title conductor-qb-meta-box-query-builder-advanced-title"><?php _e( 'Advanced Query Builder', 'conductor-query-builder' ); ?></h3>

		<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_title_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>


		<?php
			/**
			 * FROM
			 */
		?>
		<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_from_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

		<div id="conductor-qb-meta-box-query-builder-from-groups" class="conductor-qb-meta-box-query-builder-groups conductor-qb-meta-box-query-builder-from-groups conductor-qb-cf">
			<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_from', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
		</div>

		<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_from_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>


		<?php
			/**
			 * WHERE
			 */
		?>
		<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_where_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

		<div id="conductor-qb-meta-box-query-builder-where-groups" class="conductor-qb-meta-box-query-builder-groups conductor-qb-meta-box-query-builder-where-groups conductor-qb-cf">
			<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_where', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
		</div>

		<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_where_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>


		<?php
			/**
			 * WHERE Meta (Custom Field)
			 */
		?>
		<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_meta_query_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

		<div id="conductor-qb-meta-box-query-builder-meta-query-groups" class="conductor-qb-meta-box-query-builder-groups conductor-qb-meta-box-query-builder-meta-query-groups conductor-qb-cf">
			<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_meta_query', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
		</div>

		<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_meta_query_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>


		<?php
			/**
			 * WHERE Taxonomy
			 */
		?>
		<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_tax_query_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

		<div id="conductor-qb-meta-box-query-builder-tax-query-groups" class="conductor-qb-meta-box-query-builder-groups conductor-qb-meta-box-query-builder-tax-query-groups conductor-qb-cf">
			<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_tax_query', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
		</div>

		<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_tax_query_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>


		<?php
			/**
			 * ORDER BY
			 */
		?>
		<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_order_by_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

		<div id="conductor-qb-meta-box-query-builder-order-by-groups" class="conductor-qb-meta-box-query-builder-groups conductor-qb-meta-box-query-builder-order-by-groups conductor-qb-cf">
			<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_order_by', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
		</div>

		<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_order_by_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>


		<?php
			/**
			 * LIMIT
			 */
		?>
		<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_limit_before', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

		<div id="conductor-qb-meta-box-query-builder-limit-groups" class="conductor-qb-meta-box-query-builder-groups conductor-qb-meta-box-query-builder-limit-groups conductor-qb-cf">
			<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_limit', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
		</div>

		<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_limit_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
	</div>

	<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_advanced_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>

	<?php do_action( 'conductor_query_builder_meta_box_query_builder_tab_after', $post, $post_meta, $conductor_widget_instance, $conductor_widget, $conductor_query_builder ); ?>
</div>