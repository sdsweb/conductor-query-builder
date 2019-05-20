<?php
/**
 * Conductor Query Builder Query Tags
 *
 * @class Conductor_Query_Builder_Query_Tags
 * @author Slocum Studio
 * @version 1.0.0
 * @since 1.0.6
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Conductor_Query_Builder_Query_Tags' ) ) {
	final class Conductor_Query_Builder_Query_Tags {
		/**
		 * @var string
		 */
		public $version = '1.0.0';

		/**
		 * @var Boolean
		 */
		public $query_args_has_query_tag = false;

		/**
		 * @var Boolean, Flag to determine if the current query has query tags that have not met their condition
		 */
		public $current_query_has_invalid_query_tags = false;

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
			// Conductor Query Builder Hooks
			add_filter( 'conductor_query_builder_parameters', array( $this, 'conductor_query_builder_parameters' ), 1, 2 ); // Conductor Query Builder - Parameters (early)
			add_filter( 'conductor_query_builder_values', array( $this, 'conductor_query_builder_values' ), 1, 2 ); // Conductor Query Builder - Values (early)
			add_filter( 'conductor_query_builder_clause_type_query_args', array( $this, 'conductor_query_builder_clause_type_query_args' ), 10, 6 ); // Conductor Query Builder - Clause Type Query Arguments
			add_action( 'conductor_query_builder_render_preview_before', array( $this, 'conductor_query_builder_render_preview_before' ) ); // Conductor Query Builder - Render Preview Before
			add_action( 'conductor_query_builder_render_preview_after', array( $this, 'conductor_query_builder_render_preview_after' ) ); // Conductor Query Builder - Render Preview After
			add_action( 'conductor_query_builder_render_after', array( $this, 'conductor_query_builder_render_after' ), 10, 9 ); // Conductor Query Builder - Render After

			// Conductor Hooks
			add_filter( 'conductor_query_args', array( $this, 'conductor_query_args' ), 2147483647, 4 ); // Conductor - Query Arguments (late)
		}

		/**
		 * This function adjusts the Conductor Query Builder parameters.
		 */
		public function conductor_query_builder_parameters( $parameters, $conductor_query_builder ) {
			// Grab the query tags
			$query_tags = $this->get_query_tags();

			// If we have query tags
			if ( ! empty( $query_tags ) ) {
				// Loop through the query tags
				foreach ( $query_tags as $tag => $properties ) {
					// Loop through the properties
					foreach ( $properties as $property => $query_tag_data ) {
						// Loop through the parameters for this query tag
						foreach ( $query_tag_data['parameters'] as $parameter => $parameter_or_nested_parameters ) {
							// Set the parameter
							$parameter = ( ! is_array( $parameter_or_nested_parameters ) ) ? $parameter_or_nested_parameters : $parameter;

							// Grab the nested parameters
							$nested_parameters = ( is_array( $parameter_or_nested_parameters ) ) ? $parameter_or_nested_parameters : false;

							// If we have nested parameters
							if ( ! empty( $nested_parameters ) ) {
								// If this parameter exists in the parameters and the type data doesn't exist for this parameter or the type isn't set to "query_tag"
								if ( isset( $parameters[$parameter] ) && ( ! isset( $parameters[$parameter]['type'] ) || $parameters[$parameter]['type'] !== 'query_tag' ) )
									// Set the type
									$parameters[$parameter]['type'] = 'query_tag';

								// Loop through the parameter data
								foreach ( $nested_parameters as $nested_parameter ) {
									// If this nested parameter doesn't exist in the parameters
									if ( ! isset( $parameters[$parameter][$nested_parameter] ) )
										// Add this nested parameter to the parameters
										$parameters[$parameter][$nested_parameter] = array();

									// If the type data doesn't exist for this nested parameter, and the type isn't set to "query_tag"
									if ( ! isset( $parameters[$parameter][$nested_parameter]['type'] ) || $parameters[$parameter][$nested_parameter]['type'] !== 'query_tag' )
										// Set the type
										$parameters[$parameter][$nested_parameter]['type'] = 'query_tag';
								}
							}
							// If this parameter exists in the parameters and the type data exists for this parameter or the type isn't set to "query_tag"
							else if ( isset( $parameters[$parameter] ) && ( ! isset( $parameters[$parameter]['type'] ) || $parameters[$parameter]['type'] !== 'query_tag' ) )
								// Set the type
								$parameters[$parameter]['type'] = 'query_tag';
						}
					}
				}
			}

			return $parameters;
		}

		/**
		 * This function adjusts the Conductor Query Builder values.
		 */
		public function conductor_query_builder_values( $values, $conductor_query_builder ) {
			// Grab the query tags
			$query_tags = $this->get_query_tags();

			// If we have query tags
			if ( ! empty( $query_tags ) ) {
				// Loop through the query tags
				foreach ( $query_tags as $tag => $properties ) {
					// Loop through the properties
					foreach ( $properties as $property => $query_tag_data ) {
						// Query tag value data
						$query_tag_value_data = array(
							'label' => $query_tag_data['label'],
							'value' => ( isset( $query_tag_data['query_tag'] ) ) ? $query_tag_data['query_tag'] : $this->get_query_tag_for_query_builder( $tag, $property )
						);

						// Loop through the parameters for this query tag
						foreach ( $query_tag_data['parameters'] as $parameter => $parameter_or_nested_parameters ) {
							// Set the parameter
							$parameter = ( ! is_array( $parameter_or_nested_parameters ) ) ? $parameter_or_nested_parameters : $parameter;

							// Grab the nested parameters
							$nested_parameters = ( is_array( $parameter_or_nested_parameters ) ) ? $parameter_or_nested_parameters : false;

							// If this parameter doesn't exist in the values
							if ( ! isset( $values[$parameter] ) )
								// Add the parameter data to the values
								$values[$parameter] = array();

							// If we have nested parameters
							if ( ! empty( $nested_parameters ) )
								// Loop through the parameter data
								foreach ( $nested_parameters as $nested_parameter ) {
									// If this nested parameter doesn't exist in the values
									if ( ! isset( $values[$parameter][$nested_parameter] ) )
										// Add the nested parameter data to the values
										$values[$parameter][$nested_parameter] = array();

									// Add this query tag to the nested parameter values
									$values[$parameter][$nested_parameter][] = $query_tag_value_data;
								}
							// Otherwise we don't have parameter data
							else
								// Add this query tag to the parameter values
								$values[$parameter][] = $query_tag_value_data;
						}
					}
				}
			}

			return $values;
		}

		/**
		 * This function adjusts the Conductor Query Builder clause type query arguments.
		 */
		public function conductor_query_builder_clause_type_query_args( $clause_type_query_args, $post_id, $clause_type_post_meta, $clause_type, $context, $conductor_query_builder ) {
			// Grab the query tags
			$query_tags = $this->get_query_tags();

			// Grab the Conductor Query Builder parameters
			$parameters = $conductor_query_builder->parameters;

			// Bail if we don't have query tags or we don't have clause type query arguments
			if ( empty( $query_tags ) || empty( $clause_type_query_args ) )
				return $clause_type_query_args;

			// Loop through the query tags
			foreach ( $query_tags as $tag => $properties ) {
				// Loop through the properties
				foreach ( $properties as $property => $query_tag_data ) {
					// Loop through the parameters for this query tag
					foreach ( $query_tag_data['parameters'] as $parameter => $parameter_or_nested_parameters ) {
						// Set the parameter
						$parameter = ( ! is_array( $parameter_or_nested_parameters ) ) ? $parameter_or_nested_parameters : $parameter;

						// If this parameter exists in the parameters, the type data exists for this parameter, and the type is a query tag
						if ( isset( $parameters[$parameter] ) && isset( $parameters[$parameter]['type'] ) && $parameters[$parameter]['type'] === 'query_tag' ) {
							// Flag to determine if this parameter exists in the clause type query arguments
							$parameter_exists_in_clause_type_query_args = isset( $clause_type_query_args[$parameter] );

							// Set the query argument
							$the_query_arg = $parameter;

							// If the parameter exists in clause type query arguments flag isn't set and this parameter has operators
							if ( ! $parameter_exists_in_clause_type_query_args && isset( $parameters[$parameter]['operators'] ) ) {
								// Grab the operators query arguments
								$operators_query_arguments = array_keys( $parameters[$parameter]['operators'] );

								// Loop through the operators query arguments
								foreach ( $operators_query_arguments as $query_arg )
									// If this query argument exists in the clause type query arguments
									if ( isset( $clause_type_query_args[$query_arg] ) ) {
										// Set the parameter exists in clause type query arguments flag
										$parameter_exists_in_clause_type_query_args = true;

										// Set the parameter
										$the_query_arg = $query_arg;

										// Break from the loop
										break;
									}
							}
							// If this parameter exists in the clause type query arguments
							if ( $parameter_exists_in_clause_type_query_args )
								// Switch based on the context
								switch ( $context ) {
									// Database
									case 'database':
										// Sanitize this query tag
										$clause_type_query_args[$the_query_arg] = $this->sanitize_query_tag( $clause_type_query_args[$the_query_arg], $the_query_arg, $parameter );
									break;

									// Query
									case 'query':
										// Convert this query tag to the query argument value
										$clause_type_query_args[$the_query_arg] = $this->convert_query_tag_to_query_arg_value( $clause_type_query_args[$the_query_arg], $the_query_arg, $parameter );
									break;
								}
						}
					}
				}
			}

			return $clause_type_query_args;
		}

		/**
		 * This function runs before the Conductor Query Builder preview has been rendered.
		 */
		public function conductor_query_builder_render_preview_before() {
			// Hook into "conductor_widget_content_pieces_other"
			add_action( 'conductor_widget_content_pieces_other', array( $this, 'conductor_widget_content_pieces_other' ), 20 );
		}

		/**
		 * This function runs after the Conductor Query Builder preview has been rendered.
		 */
		public function conductor_query_builder_render_preview_after() {
			// Remove the "conductor_widget_content_pieces_other" hook
			remove_action( 'conductor_widget_content_pieces_other', array( $this, 'conductor_widget_content_pieces_other' ), 20 );

			// If the query arguments has query tag flag is set
			if ( $this->query_args_has_query_tag )
				// Reset the query arguments has query tag flag
				$this->query_args_has_query_tag = false;
		}

		/**
		 * This function runs after the Conductor Query Builder query has been rendered.
		 */
		public function conductor_query_builder_render_after( $type, $post_id, $title, $args, $current_query_args, $current_query_builder_mode, $current_conductor_widget_instance, $number, $conductor_query_builder ) {
			// Reset the current query has invalid query tags flag
			$this->current_query_has_invalid_query_tags = false;
		}


		/*************
		 * Conductor *
		 *************/

		/**
		 * This function adjusts the Conductor query arguments.
		 */
		public function conductor_query_args( $query_args, $type, $widget_instance, $conductor_widget_query ) {
			// Bail if the current query doesn't have invalid query tags
			if ( ! $this->current_query_has_invalid_query_tags )
				return $query_args;

			// Reset the query arguments
			$query_args = array();

			return $query_args;
		}

		/**
		 * This function runs when the Conductor Widget "other" content pieces are displayed.
		 */
		public function conductor_widget_content_pieces_other( $query ) {
			// Grab the Conductor Query Builder instance
			$conductor_query_builder = Conduct_Query_Builder();

			// Bail if we're not doing a preview, the query arguments has query tag flag isn't set, or we're in the admin and we have posts
			if ( ! $conductor_query_builder->doing_preview || ! $this->query_args_has_query_tag || ( is_admin() && $query->have_posts() ) )
				return;
		?>
			<p class="conductor-qb-preview-no-results-query-tags conductor-qb-preview-no-posts-query-tags">
				<strong style="color: #f00;"><?php _e( 'Note: This query contains at least one query tag which can skew the results in this preview. Please be aware that, when this query is added to your website, it may return results depending on which query tag was used.', 'conductor-query-builder' ); ?></strong>
			</p>
		<?php
		}


		/**********************
		 * Internal Functions *
		 **********************/

		/**
		 * This function returns the query tags.
		 *
		 * Query Tag Formats:
		 *
		 * 1. {tag:property}
		 * 		- This query tag format supports a tag and a property. The tag portion
		 * 		  can be thought of as the "category" in which this query tag relates to.
		 * 		  The property portion is the individual property that this query tag uses
		 * 		  in the query arguments.
		 * 		- To create a query tag using this format, you must specify an array with
		 * 		  the following structure:
		 *
		 * 			'tag' => array(
		 *				'property' => array(
		 *					'label' => 'Label' // Required
		 *					'parameters' => array(), // Required
		 *					'value' => true // Required
		 * 				)
		 *			)
		 *
		 * 			- There are three required properties:
		 * 				- 'label': This is the label which is displayed in the query builder
		 * 				- 'parameters': This is a list of parameters in which this query tag
		 * 				  is able to be utilized.
		 * 				- 'value': This is the value which the query tag is converted to in
		 * 				  the query on the front-end.
		 *
		 * 			- There is one optional property:
		 * 				- 'condition': This is the condition which must be met for this query tag
		 * 				  to be used in the front-end query.
		 */
		public function get_query_tags() {
			return apply_filters( 'conductor_query_builder_query_tags', array(
				// Current User
				'current_user' => array(
					// Current User ID
					'ID' => array(
						// Label
						'label' => __( '*{Current (Logged In) User ID}*', 'conductor-query-builder' ),
						/*
						 * Parameters
						 *
						 * These are the parameters in which this query tag is able to be utilized.
						 */
						'parameters' => array(
							// Author
							'author',
							// Meta Query
							'meta_query' => array(
								// All meta query parameters
								'all'
							)
						),
						/*
						 * Value
						 *
						 * This is the value which the query tag is converted to in the query on the front-end.
						 */
						'value' => get_current_user_id(),
						// Condition
						'condition' => is_user_logged_in()
					)
					// TODO: Future: 'user_nicename'
				)
				// TODO: Future: Date (different formats)
			), $this );
		}

		/**
		 * This function returns a query tag.
		 */
		public function get_query_tag( $tag, $property ) {
			// Grab the query tags
			$query_tags = $this->get_query_tags();

			return ( isset( $query_tags[$tag] ) && isset( $query_tags[$tag][$property] ) ) ? $query_tags[$tag][$property] : false;
		}

		/**
		 * This function returns the query tag formats.
		 */
		public function get_query_tag_formats() {
			return apply_filters( 'conductor_query_builder_query_tag_formats', array(
				// TODO: Future: 'tag' => '{%s}', would need to adjust self::convert_query_tag_to_query_arg_value() to account for this
				'property' => '{%s:%s}'
			), $this );
		}

		/**
		 * This function returns the query tag for use in the query builder.
		 */
		public function get_query_tag_for_query_builder( $tag, $property ) {
			// Grab the query tag formats
			$query_tag_formats = $this->get_query_tag_formats();

			return apply_filters( 'conductor_query_builder_query_tag_for_query_builder', sprintf( $query_tag_formats['property'], $tag, $property ), $tag, $property, $this );
		}

		/**
		 * This function sanitizes a query tag.
		 */
		public function sanitize_query_tag( $value, $query_arg, $query_tag_parameter ) {
			// Grab the query tags
			$query_tags = $this->get_query_tags();

			// Bail if we don't have query tags
			if ( empty( $query_tags ) )
				return $value;

			// Grab the query tag formats
			$query_tag_formats = $this->get_query_tag_formats();

			// Flag to determine if we've sanitized this query tag
			$has_sanitized_query_tag = false;

			// Flag to determine if we've have a query tag format match
			$query_tag_matches_format = false;

			// Loop through the query tag formats
			foreach ( $query_tag_formats as $name => $format ) {
				// If we haven't sanitized the query tag
				if ( ! $has_sanitized_query_tag ) {
					// Grab the format regular expression
					$format_regular_expression = '/' . str_replace( '%s', '[^:]+', $format ) . '/';

					// If the value is an array
					if ( is_array( $value ) ) {
						// Switch based on the query tag parameter
						switch ( $query_tag_parameter ) {
							// WHERE Meta (Custom Field)
							case 'meta_query':
								// Loop through the values
								foreach ( $value as $index => $meta_query_clause ) {
									// If we have at least one value
									if ( isset( $meta_query_clause['value'] ) ) {
										// Grab the values
										$the_values = ( array ) $meta_query_clause['value'];

										// Flag to determine if we converted the values to an array
										$converted_values_to_array = ( ! is_array( $meta_query_clause['value'] ) );

										// Loop through the values
										foreach ( $the_values as $the_value_index => $the_value ) {
											// If the value matches this format
											if ( preg_match( $format_regular_expression, $the_value ) ) {
												// If the query tag matches format flag isn't set
												if ( ! $query_tag_matches_format )
													// Set the query tag matches format
													$query_tag_matches_format = true;

												// TODO: Future: Transition to get_query_tag()

												// Loop through the query tags
												foreach ( $query_tags as $tag => $properties ) {
													// Loop through the properties
													foreach ( $properties as $property => $query_tag_data ) {
														// The query tag
														$the_query_tag = ( isset( $query_tag_data['query_tag'] ) ) ? $query_tag_data['query_tag'] : $this->get_query_tag_for_query_builder( $tag, $property );

														// If this value matches this query tag
														if ( $the_value === $the_query_tag ) {
															// Set the value
															$the_values[$the_value_index] = array(
																'is_query_tag' => true,
																'value' => $the_value,
																'tag' => $tag,
																'property'=> $property
															);

															// Set the sanitized query tag flag
															$has_sanitized_query_tag = true;

															// Break from the loops
															break 2;
														}
													}
												}
											}

											// If the query tag matches a format and we didn't sanitize the query tag
											if ( $query_tag_matches_format && ! $has_sanitized_query_tag ) {
												// Unset this value
												unset( $the_values[$the_value_index] );

												// Reset the sanitized query tag flag
												$has_sanitized_query_tag = false;

												// Reset the query tag matches format flag
												$query_tag_matches_format = false;
											}
										}

										// If we converted the values to an array and we have the value
										if ( $converted_values_to_array && ! empty( $the_values ))
											// Reset the values
											$the_values = end( $the_values );

										// Set the meta query clause value
										$value[$index]['value'] = $the_values;
									}
								}
							break;

							// Default
							default:
								// Loop through the values
								foreach ( $value as $index => $the_value ) {
									// If the value matches this format
									if ( preg_match( $format_regular_expression, $the_value ) ) {
										// If the query tag matches format flag isn't set
										if ( ! $query_tag_matches_format )
											// Set the query tag matches format
											$query_tag_matches_format = true;

										// TODO: Future: Transition to get_query_tag()

										// Loop through the query tags
										foreach ( $query_tags as $tag => $properties ) {
											// Loop through the properties
											foreach ( $properties as $property => $query_tag_data ) {
												// The query tag
												$the_query_tag = ( isset( $query_tag_data['query_tag'] ) ) ? $query_tag_data['query_tag'] : $this->get_query_tag_for_query_builder( $tag, $property );

												// If this value matches this query tag
												if ( $the_value === $the_query_tag ) {
													// Set the value
													$value[$index] = array(
														'is_query_tag' => true,
														'value' => $the_value,
														'tag' => $tag,
														'property'=> $property
													);

													// Set the sanitized query tag flag
													$has_sanitized_query_tag = true;

													// Break from the loops
													break 2;
												}
											}
										}
									}

									// If the query tag matches a format and we didn't sanitize the query tag
									if ( $query_tag_matches_format && ! $has_sanitized_query_tag ) {
										// Unset this value
										unset( $value[$index] );

										// Reset the sanitized query tag flag
										$has_sanitized_query_tag = false;

										// Reset the query tag matches format flag
										$query_tag_matches_format = false;
									}
								}
							break;
						}
					}
					// Otherwise if the value matches this format
					else if ( preg_match( $format_regular_expression, $value ) ) {
						// If the query tag matches format flag isn't set
						if ( ! $query_tag_matches_format )
							// Set the query tag matches format
							$query_tag_matches_format = true;

						// TODO: Future: Transition to get_query_tag()

						// Loop through the query tags
						foreach ( $query_tags as $tag => $properties ) {
							// Loop through the properties
							foreach ( $properties as $property => $query_tag_data ) {
								// The query tag
								$the_query_tag = ( isset( $query_tag_data['query_tag'] ) ) ? $query_tag_data['query_tag'] : $this->get_query_tag_for_query_builder( $tag, $property );

								// If this value matches this query tag
								if ( $value === $the_query_tag ) {
									// Set the value
									$value = array(
										'is_query_tag' => true,
										'value' => $value,
										'tag' => $tag,
										'property'=> $property
									);

									// Set the sanitized query tag flag
									$has_sanitized_query_tag = true;

									// Break from the loops
									break 2;
								}
							}
						}
					}
				}
			}

			// If the query tag matches a format and we didn't sanitize the query tag
			if ( $query_tag_matches_format && ! $has_sanitized_query_tag )
				// Reset the query tag
				$value = false;

			return $value;
		}

		/**
		 * This function converts a query tag to a query argument value.
		 */
		public function convert_query_tag_to_query_arg_value( $value, $query_arg, $query_tag_parameter ) {
			// Grab the query tags
			$query_tags = $this->get_query_tags();

			// Bail if we don't have query tags or the current query has invalid query tags
			if ( empty( $query_tags ) || $this->current_query_has_invalid_query_tags )
				return $value;

			// Flag to determine if the value is a query tag
			$is_query_tag = ( is_array( $value ) && isset( $value['is_query_tag'] ) );

			// Flag to determine if the value has a query tag
			$has_query_tag = false;

			// If we don't have a query tag and the value is an array
			if ( ! $is_query_tag && is_array( $value ) ) {
				// Switch based on the query tag parameter
				switch ( $query_tag_parameter ) {
					// WHERE Meta (Custom Field)
					case 'meta_query':
						// Loop through the values
						foreach ( $value as $index => $meta_query_clause ) {
							// If we have at least one value
							if ( isset( $meta_query_clause['value'] ) ) {
								// Grab the values
								$the_values = ( array ) $meta_query_clause['value'];

								// Set the has query tag flag
								$has_query_tag = ( isset( $the_values['is_query_tag'] ) );

								// If the has query tag flag isn't set
								if ( ! $has_query_tag )
									// Loop through the values
									foreach ( $the_values as $the_value_index => $the_value ) {
										// Set the has query tag flag
										$has_query_tag = ( is_array( $the_value ) && isset( $the_value['is_query_tag'] ) );

										// If we have a query tag
										if ( $has_query_tag )
											// Break from the loops
											break 2;
									}
							}
						}
					break;

					// Default
					default:
						// Loop through the values
						foreach ( $value as $index => $the_value ) {
							// Set the has query tag flag
							$has_query_tag = ( is_array( $the_value ) && isset( $the_value['is_query_tag'] ) );

							// If we have a query tag
							if ( $has_query_tag )
								// Break from the loop
								break;
						}
					break;
				}
			}

			// Bail if we don't have a query tag
			if ( ! $is_query_tag && ! $has_query_tag )
				return $value;

			// If the query arguments has query tag flag isn't set
			if ( ! $this->query_args_has_query_tag )
				// Set the query arguments has query tag flag
				$this->query_args_has_query_tag = true;

			// If this value is a query tag
			if ( $is_query_tag ) {
				// Grab the query tag
				$query_tag = $this->get_query_tag( $value['tag'], $value['property'] );

				// Bail if we don't have the query tag
				if ( empty( $query_tag ) )
					return $value;

				// Bail if this query tag has a condition and the query tag condition is not met
				if ( isset( $query_tag['condition'] ) && ! $query_tag['condition'] ) {
					// Set the current query has invalid query tags flag
					$this->current_query_has_invalid_query_tags = true;

					return $value;
				}

				// Set the value to the query tag data value
				$value = $query_tag['value'];
			}
			// Otherwise this value has a query tag
			else {
				// Switch based on the query tag parameter
				switch ( $query_tag_parameter ) {
					// WHERE Meta (Custom Field)
					case 'meta_query':
						// Loop through the values
						foreach ( $value as $index => $meta_query_clause ) {
							// Grab the values
							$the_values = ( array ) $meta_query_clause['value'];

							// If the values is a query tag
							if ( isset( $the_values['is_query_tag'] ) ) {
								// Grab the query tag
								$query_tag = $this->get_query_tag( $the_values['tag'], $the_values['property'] );

								// If we have the query tag
								if ( ! empty( $query_tag ) ) {
									// If this query tag has a condition and the query tag condition is not met
									if ( isset( $query_tag['condition'] ) && ! $query_tag['condition'] )
										// Set the current query has invalid query tags flag
										$this->current_query_has_invalid_query_tags = true;
									// Otherwise the query tag doesn't have a condition or the query tag condition is met
									else
										// Set the value to the query tag data value
										$value[$index]['value'] = $query_tag['value'];
								}
							}
							// Otherwise the values isn't a query tag
							else {
								// Loop through the values
								foreach ( $the_values as $the_value_index => $the_value ) {
									// Grab the query tag
									$query_tag = ( is_array( $the_value ) && isset( $the_value['is_query_tag'] ) ) ? $this->get_query_tag( $the_value['tag'], $the_value['property'] ) : false;

									// If we have the query tag
									if ( ! empty( $query_tag ) ) {
										// If this query tag has a condition and the query tag condition is not met
										if ( isset( $query_tag['condition'] ) && ! $query_tag['condition'] )
											// Set the current query has invalid query tags flag
											$this->current_query_has_invalid_query_tags = true;
										// Otherwise the query tag doesn't have a condition or the query tag condition is met
										else
											// Set the value to the query tag data value
											$value[$index]['value'][$the_value_index] = $query_tag['value'];
									}
								}
							}
						}
					break;

					// Default
					default:
						// Loop through the values
						foreach ( $value as $index => $the_value ) {
							// Grab the query tag
							$query_tag = $this->get_query_tag( $the_value['tag'], $the_value['property'] );

							// If we have the query tag
							if ( ! empty( $query_tag ) ) {
								// If this query tag has a condition and the query tag condition is not met
								if ( isset( $query_tag['condition'] ) && ! $query_tag['condition'] )
									// Set the current query has invalid query tags flag
									$this->current_query_has_invalid_query_tags = true;
								// Otherwise the query tag doesn't have a condition or the query tag condition is met
								else
									// Set the value to the query tag data value
									$value[$index] = $query_tag['value'];
							}
						}
					break;
				}
			}

			return $value;
		}
	}

	/**
	 * Create an instance of the Conductor_Query_Builder_Query_Tags class.
	 */
	function Conduct_Query_Builder_Query_Tags() {
		return Conductor_Query_Builder_Query_Tags::instance();
	}

	Conduct_Query_Builder_Query_Tags(); // Conduct your content!
}