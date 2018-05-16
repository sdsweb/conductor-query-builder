<?php
/**
 * Conductor Query Builder Beaver Builder Module
 *
 * @class Conductor_Query_Builder_Beaver_Builder_Module
 * @author Slocum Studio
 * @version 1.0.4
 * @since 1.0.3
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Conductor_Query_Builder_Beaver_Builder_Module' ) ) {
	final class Conductor_Query_Builder_Beaver_Builder_Module extends FLBuilderModule {
		/**
		 * @var string
		 */
		public $version = '1.0.4';

		/**
		 * @var Conductor, Instance of the class
		 */
		protected static $_instance;

		/**
		 * Function used to create instance of class.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) )
				self::$_instance = new self();

			return self::$_instance;
		}


		/**
		 * This function sets up all of the actions and filters on instance. It also loads (includes)
		 * the required files and assets.
		 */
		function __construct() {
			// Call the parent __construct()
			parent::__construct( array(
				'name' => __( 'Conductor Query Builder', 'conductor-query-builder' ),
				'description' => __( 'The Conductor Query Builder Module', 'conductor-query-builder' ),
				'category' => __( 'Conductor', 'conductor-query-builder' ),
				'dir' => Conductor_Query_Builder_Add_On::plugin_dir() . '/includes/beaver-builder/',
				'url' => Conductor_Query_Builder_Add_On::plugin_url() . '/includes/beaver-builder/',
				'partial_refresh' => true
			) );
		}

		/**
		 * This function sanitizes/adjusts settings when the module is saved.
		 */
		public function update( $settings ) {
			// Grab the Conductor Query Builder Beaver Builder instance
			$conductor_query_builder_beaver_builder = Conduct_Query_Builder_Beaver_Builder();

			// Grab the Conductor Query Builder instance
			$conductor_query_builder = Conduct_Query_Builder();

			// Grab the clause types
			$clause_types = $conductor_query_builder->get_clause_types();

			// Grab the field types
			$field_types = $conductor_query_builder->get_field_types();

			// Grab the Conductor Widget instance
			$conductor_widget = Conduct_Widget();

			// Grab the query builder mode
			$query_builder_mode = $conductor_query_builder->get_query_builder_mode();

			// Conductor Widget key
			$conductor_widget_key = 'widget-' . $conductor_widget->id_base;

			// Conductor Query Builder key
			$conductor_query_builder_key = rtrim( $conductor_query_builder->meta_key_prefix, '_' );

			// Conductor Query Builder Update Count key
			$conductor_query_builder_update_count_key = $conductor_query_builder->meta_key_prefix . 'beaver_builder_update_count';

			// Conductor Query Builder title
			$conductor_query_builder_title = '';

			// Conductor Query Builder setting data
			$conductor_query_builder_setting_data = array();

			// Loop through settings
			foreach ( $settings as $key => &$value ) {
				// If this is a Conductor Query Builder setting
				if ( strpos( $key, $conductor_query_builder->meta_key_prefix ) !== false ) {
					// Switch based on key
					switch ( $key ) {
						// Conductor Query Builder Shortcode - Create Title
						case $conductor_query_builder->meta_key_prefix . 'shortcode_create_title':
							// Store the Conductor Query Builder title
							$conductor_query_builder_title = sanitize_text_field( $value );
						break;

						// Default
						default:
							// If the left field bracket exists in the key
							if ( strpos( $key, $conductor_query_builder_beaver_builder->left_field_bracket ) !== false ) {
								// Explode the key into parts (based on the left field bracket)
								$key_parts = explode( $conductor_query_builder_beaver_builder->left_field_bracket, $key );

								// If we have 5 key parts
								if ( count( $key_parts ) === 5 ) {
									// List the key parts
									list( $prefix, $clause_type, $clause_group_id, $sub_clause_group_id, $field_type ) = $key_parts;

									// If this is a valid clause type
									if ( in_array( $clause_type, $clause_types ) ) {
										// Sanitize "keys"
										$clause_group_id = ( int ) $clause_group_id;
										$sub_clause_group_id = ( int ) $sub_clause_group_id;
										$field_type = sanitize_text_field( $field_type );

										// If this clause type doesn't exist in setting data
										if ( ! isset( $conductor_query_builder_setting_data[$clause_type] ) )
											// Create the clause type in setting data
											$conductor_query_builder_setting_data[$clause_type] = array();

										// If this clause group ID doesn't exist in the clause type setting data
										if ( ! isset( $conductor_query_builder_setting_data[$clause_type][$clause_group_id] ) )
											// Create the clause group ID in setting data
											$conductor_query_builder_setting_data[$clause_type][$clause_group_id] = array();

										// If this sub-clause group ID doesn't exist in the clause group setting data
										if ( ! isset( $conductor_query_builder_setting_data[$clause_type][$clause_group_id][$sub_clause_group_id] ) )
											// Create the sub-clause group ID in setting data
											$conductor_query_builder_setting_data[$clause_type][$clause_group_id][$sub_clause_group_id] = array();

										// If this is a valid field type
										if ( in_array( $field_type, $field_types ) )
											// If this field type doesn't exist in the sub-clause group setting data
											if ( ! isset( $conductor_query_builder_setting_data[$clause_type][$clause_group_id][$sub_clause_group_id][$field_type] ) )
												// Add the field type data to setting data
												$conductor_query_builder_setting_data[$clause_type][$clause_group_id][$sub_clause_group_id][$field_type] = $value;
									}
								}
							}
						break;
					}

					// Unset this setting
					unset( $settings->{$key} );
				}
				// Otherwise if this is the Conductor Widget setting
				else if ( $key === $conductor_widget_key ) {
					// Encode the output setting value (Conductor Widget sanitizing requires output to be a JSON encoded string)
					$value['output'] = wp_json_encode( $value['output'] );

					// Filter the value
					$value = apply_filters( 'conductor_query_builder_beaver_builder_update_conductor_widget_settings', $value, $settings, $conductor_widget_key, $conductor_query_builder_beaver_builder, $conductor_query_builder, $conductor_widget, $this );

					// Wrap the value in an array
					$value = array( $value );
				}
			}

			// If we have a Conductor Query Builder title
			if ( ! empty( $conductor_query_builder_title ) )
				// Create the Conductor Query Builder title setting
				$settings->{$conductor_query_builder->meta_key_prefix . 'title'} = sanitize_text_field( $conductor_query_builder_title );

			// If we have Conductor Query Builder setting data
			if ( ! empty( $conductor_query_builder_setting_data ) )
				// Add the Conductor Query Builder setting data to the settings
				$settings->{$conductor_query_builder_key} = $conductor_query_builder_setting_data;

			// Cast the settings into an array
			$settings_arr = ( array ) $settings;

			// Grab the Conductor Query Builder data (default to an empty array)
			$conductor_query_builder_data = $conductor_query_builder->get_query_builder_data( $settings_arr, $query_builder_mode );

			/*
			 * Bail if:
			 *
			 * - we don't have any data
			 * - we don't have a valid nonce
			 */
			if ( empty( $conductor_query_builder_data ) || ! property_exists( $settings, 'conductor_qb_nonce' ) || ! wp_verify_nonce( $settings->conductor_qb_nonce, 'conductor_query_builder_meta_box' ) ) {
				// Unset the Conductor Query Builder title
				unset( $settings->{$conductor_query_builder->meta_key_prefix . 'title'} );

				// Unset the Conductor Query Builder data
				unset( $settings->{$conductor_query_builder_key} );

				// Unset the Conductor Widget data
				unset( $settings->{$conductor_widget_key} );

				// Unset the Conductor Query Builder nonce
				unset( $settings->conductor_qb_nonce );

				// Unset the Conductor Query Builder update count value
				unset( $settings->{$conductor_query_builder_update_count_key} );

				return $settings;
			}

			// Setup the Conductor Widget instance POST data (POST data is required for Conductor_Query_Builder::get_simple_query_builder_data())
			$_POST[$conductor_widget_key] = $settings->{$conductor_widget_key};

			// Grab the simple query builder data
			$simple_conductor_query_builder_data = $conductor_query_builder->get_simple_query_builder_data( ( $query_builder_mode === 'simple' ) ? $settings_arr : $conductor_query_builder_data, $query_builder_mode );

			// If we have simple query builder data
			if ( ! empty( $simple_conductor_query_builder_data ) )
				// Add the simple query builder data to the settings
				$settings->{$conductor_query_builder->meta_key_prefix . $conductor_query_builder->conductor_widget_meta_key_suffix} = $simple_conductor_query_builder_data;

			// If we have a query builder mode
			if ( $query_builder_mode )
				// Add the query builder mode to the settings
				$settings->{$conductor_query_builder->meta_key_prefix . $conductor_query_builder->query_builder_mode_meta_key_suffix} = $query_builder_mode;

			// Loop through the clause types
			foreach ( $clause_types as $clause_type ) {
				// Grab the post meta for this clause type
				$post_meta = $conductor_query_builder->get_clause_type_post_meta( $this->node, $clause_type, $conductor_query_builder_data );

				// If we have post meta
				if ( ! empty( $post_meta ) ) {
					// Add the post meta to the settings
					$settings->{$conductor_query_builder->meta_key_prefix . $clause_type} = $post_meta;

					// Grab the clause type query arguments
					$clause_type_query_args = $conductor_query_builder->get_clause_type_query_args( $this->node, $clause_type, $post_meta );

					// If we have clause type query arguments
					if ( ! empty( $clause_type_query_args ) )
						// Add the clause type query arguments to the settings
						$settings->{$conductor_query_builder->meta_key_prefix . $clause_type . $conductor_query_builder->query_args_meta_key_suffix} = $clause_type_query_args;
				}
			}

			// Unset the Conductor Query Builder data
			unset( $settings->{$conductor_query_builder_key} );

			// Unset the Conductor Widget data
			unset( $settings->{$conductor_widget_key} );

			// Unset the Conductor Query Builder nonce
			unset( $settings->conductor_qb_nonce );

			// Unset the Conductor Query Builder update count value
			unset( $settings->{$conductor_query_builder_update_count_key} );

			return $settings;
		}
	}
}