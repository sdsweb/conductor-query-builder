<?php
/**
 * Conductor Query Builder Beaver Builder Front-End Template
 *
 * @var $module Conductor_Query_Builder_Beaver_Builder_Module
 * @var $id string
 * @var $settings array
 */

// Grab the Conductor Query Builder Beaver Builder instance
$conductor_query_builder_beaver_builder = Conduct_Query_Builder_Beaver_Builder();

// Grab the Conductor Query Builder instance
$conductor_query_builder = Conduct_Query_Builder();

// Conductor Query Builder Title key
$conductor_query_builder_title_key = $conductor_query_builder->meta_key_prefix . 'title';

// Conductor Query Builder Mode key
$conductor_query_builder_mode_key = $conductor_query_builder->meta_key_prefix . $conductor_query_builder->query_builder_mode_meta_key_suffix;

// Conductor Query Builder Conductor Widget key
$conductor_query_builder_conductor_widget_key = $conductor_query_builder->meta_key_prefix . $conductor_query_builder->conductor_widget_meta_key_suffix;

// If we have all of the necessary data
if ( property_exists( $settings, $conductor_query_builder_mode_key ) && property_exists( $settings, $conductor_query_builder_conductor_widget_key ) )
	// Render this Conductor Query
	$conductor_query_builder->render( $module->node, ( property_exists( $settings, $conductor_query_builder_title_key ) ) ? $settings->{$conductor_query_builder_title_key} : '', 'beaver-builder', $conductor_query_builder_beaver_builder->get_query_args( $settings, $module ), $settings->{$conductor_query_builder_mode_key}, $settings->{$conductor_query_builder_conductor_widget_key} );
?>
