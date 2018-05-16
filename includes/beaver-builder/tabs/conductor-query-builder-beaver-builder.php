<?php
/**
 * Conductor Query Builder Beaver Builder Tab
 *
 * We're "faking" the Thickbox HTML markup/window here due to our shortcode logic.
 *
 * @author Slocum Studio
 * @version 1.0.4
 * @since 1.0.0
 *
 * @var $form array
 * @var $tabs array
 * @var $settings object
 */

// Grab the section
$section = ( isset( $form ) && is_array( $form ) && isset( $form['tabs'] ) ) ? $form['tabs']['conductor-query-builder'] : $tabs['conductor-query-builder'];

// Grab the Conductor Query Builder instance
$conductor_query_builder = Conduct_Query_Builder();

// Grab the Conductor Query Builder Beaver Builder instance
$conductor_query_builder_beaver_builder = Conduct_Query_Builder_Beaver_Builder();

// Conductor Query Builder Mode key
$conductor_query_builder_mode_key = $conductor_query_builder->meta_key_prefix . $conductor_query_builder->query_builder_mode_meta_key_suffix;

// Conductor Query Builder Title key
$conductor_query_builder_title_key = $conductor_query_builder->meta_key_prefix . 'title';

// Conductor Query Builder Conductor Widget key
$conductor_query_builder_conductor_widget_key = $conductor_query_builder->meta_key_prefix . $conductor_query_builder->conductor_widget_meta_key_suffix;
?>

<div id="TB_window" class="conductor-qb-TB_window">
	<div id="TB_ajaxContent" class="conductor-qb-TB_ajaxContent">
		<?php if ( ! empty( $section['title'] ) ) : // Section Title ?>
			<h3 class="fl-builder-settings-title"><?php echo $section['title']; ?></h3>
		<?php endif; ?>

		<?php if ( isset( $section['description'] ) && ! empty( $section['description'] ) ) : // Section description ?>
			<p class="fl-builder-settings-description"><?php echo $section['description']; ?></p>
		<?php endif; ?>

		<?php
			// Conductor Widget instance
			$conductor_widget_instance = array();

			// If we have a widget instance in settings
			if ( ( property_exists( $settings, $conductor_query_builder_conductor_widget_key ) ) )
				// Grab the Conductor Widget instance
				$conductor_widget_instance = $conductor_query_builder->get_conductor_widget_instance( 0, ( property_exists( $settings, $conductor_query_builder_title_key ) ) ? $settings->{$conductor_query_builder_title_key} : '', $settings->{$conductor_query_builder_conductor_widget_key} );

			// Shortcode query builder template
			$conductor_query_builder->shortcode_query_builder( 'div', $conductor_widget_instance );
		?>

		<input id="conductor-query-builder-beaver-builder-post-meta" class="conductor-query-builder-beaver-builder-input conductor-query-builder-beaver-builder-post-meta" name="conductor_query_builder_beaver_builder_post_meta" type="hidden" value="<?php echo esc_attr( wp_json_encode( $conductor_query_builder_beaver_builder->get_post_meta( $settings ) ) ); ?>" />

		<input id="conductor-query-builder-beaver-builder-title" class="conductor-query-builder-beaver-builder-input conductor-query-builder-beaver-builder-title" name="conductor_query_builder_beaver_builder_title" type="hidden" value="<?php echo ( property_exists( $settings, $conductor_query_builder_title_key ) ) ? esc_attr( $settings->{$conductor_query_builder_title_key} ) : false; ?>" />

		<input id="conductor-query-builder-beaver-builder-conductor-widget" class="conductor-query-builder-beaver-builder-input conductor-query-builder-beaver-builder-conductor-widget" name="conductor_query_builder_beaver_builder_conductor_widget" type="hidden" value="<?php echo esc_attr( wp_json_encode( ( property_exists( $settings, $conductor_query_builder_conductor_widget_key ) ) ? $settings->{$conductor_query_builder_conductor_widget_key} : array() ) ); ?>" />
	</div>
</div>
