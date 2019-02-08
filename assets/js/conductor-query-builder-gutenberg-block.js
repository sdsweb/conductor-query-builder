/**
 * Conductor Query Builder Gutenberg Block
*/

( function ( conductor_query_builder_gutenberg_block, blocks, i18n, element, components, editor ) {
	"use strict";

	/**
	 * This function registers the Conductor Query Builder Conductor Gutenberg block.
	 */
	blocks.registerBlockType( 'conductor-query-builder/conductor', {
		title: i18n.__( 'Conductor' ), // TODO: Text domain?
		description: i18n.__( 'Display Conductor queries.' ), // TODO: Text domain?
		category: 'common',
		attributes: {
			query_id: {
				type: 'integer',
				default: 0
			},
			query_title: {
				type: 'string',
				default: ''
			}
		},
		/**
		 * This function renders the edit state of this block.
		 */
		edit: function( props ) {
			var select_options = [];

			// If we have Conductor Queries
			if ( conductor_query_builder_gutenberg_block.queries.length ) {
				// Add the select label option
				select_options.push( {
					label: String.fromCharCode( 8212 ) + ' ' + conductor_query_builder_gutenberg_block.l10n.query.select + ' ' + String.fromCharCode( 8212 ),
					value: ''
				} );

				// Loop through the Conductor Queries
				for ( var i = 0, l = conductor_query_builder_gutenberg_block.queries.length; i < l; i++ ) {
					// Add this query to the select options
					select_options.push( {
						label: ( conductor_query_builder_gutenberg_block.queries[i].post_title === '' ) ? conductor_query_builder_gutenberg_block.l10n.query.no_title + ' (#' + conductor_query_builder_gutenberg_block.queries[i].ID + ')' : conductor_query_builder_gutenberg_block.queries[i].post_title,
						value: conductor_query_builder_gutenberg_block.queries[i].ID
					} );
				}
			}

			return [
				/*
				 * Inspector Controls
				 */
				element.createElement( editor.InspectorControls, {
						key: 'controls'
					},

					// Panel Body
					( conductor_query_builder_gutenberg_block.current_user_can.edit_post ) ? element.createElement( components.PanelBody, {
							className: props.className + '-edit-query-panel-body',
							'title': ( select_options.length ) ? i18n.__( 'Edit the selected Conductor Query' ) : i18n.__( 'Create a New Conductor Query' ), // TODO: Text domain?
						},

						// Description
						element.createElement( 'p', {
								className: props.className + '-edit-query-panel-body-description description',
							},
							( select_options.length ) ? i18n.__( 'Edit this query in the Query Builder screen' ) : i18n.__( 'Create a new query in the Query Builder screen' ), // TODO: Text domain?
						),

						// Edit Query
						element.createElement( components.IconButton, {
								className: props.className + '-edit-query',
								disabled: ( select_options.length && ( ! conductor_query_builder_gutenberg_block.current_user_can.edit_post || ! props.attributes.query_id ) ),
								icon: ( select_options.length ) ? 'edit' : 'plus',
								label: ( select_options.length ) ? i18n.__( 'Edit the selected Conductor Query' ) : i18n.__( 'Create a New Conductor Query' ), // TODO: Text domain?
								onClick: function() {
									var url = conductor_query_builder_gutenberg_block.edit_post_link_placeholder.replace( conductor_query_builder_gutenberg_block.post_type_object.edit_link, conductor_query_builder_gutenberg_block.post_type_object.edit_link.replace( '%d', props.attributes.query_id ) );

									// If we don't have any select options
									if ( ! select_options.length ) {
										// Set the URL
										url = conductor_query_builder_gutenberg_block.add_new_link;
									}

									// If we have a URL
									if ( url ) {
										// Open a new window
										window.open( url, '_blank' );
									}
								},
							},

							// Button Label
							element.createElement( 'span', {
									className: props.className + '-edit-query-label',
								},
								( select_options.length ) ? i18n.__( 'Edit' ) : i18n.__( 'Create' ), // TODO: Text domain?
							)
						)
					) : false
				),

				/*
				 * Conductor Query Builder
				 */

				// Outer wrapper element
				element.createElement( 'div', {
						className: props.className,
						key: 'editable'
					},

					// Inner Wrapper element
					element.createElement( 'div', {
							className: props.className + '-inner'
						},

						// Query ID
						( select_options.length ) ? element.createElement( components.SelectControl, {
							className: ( conductor_query_builder_gutenberg_block.current_user_can.edit_post ) ? props.className + '-query-id ' + props.className + '-can-edit' : props.className + '-query-id',
							label: i18n.__( 'Select an existing Conductor Query:' ), // TODO: Text domain?
							value: props.attributes.query_id,
							options: select_options,
							onChange: function( value ) {
								// Set the Conductor Query Builder query ID attribute
								props.setAttributes( {
									query_id: parseInt( value, 10 )
								} );
							}
						} ) : element.createElement( 'p', {
								className: props.className + '-no-queries'
							},
							( conductor_query_builder_gutenberg_block.current_user_can.edit_post ) ? i18n.__( 'Whoops! You haven\'t published a query for Conductor to display. Create and publish a query by clicking on the button to the right.' ) : i18n.__( 'Whoops! There aren\'t any published queries for Conductor to display. Please try again once a query has been published.' ), // TODO: Text domain?
						),

						// Title
						( select_options.length ) ? element.createElement( components.TextControl, {
							className: props.className + '-query-title',
							label: i18n.__( 'Add a Title (optional):' ), // TODO: Text domain?
							value: props.attributes.query_title,
							options: select_options,
							onChange: function( value ) {
								// Set the Conductor Query Builder query title attribute
								props.setAttributes( {
									query_title: value
								} );
							}
						} ) : false
					)
				)
			];
		},
		/**
		 * This function renders the saved state of this block.
		 */
		save: function( props ) {
			var config = {
					className: props.className
				},
				shortcode = '';

			// If we have a query ID
			if ( props.attributes.query_id ) {
				/*
				 * Build the shortcode string.
				 */
				shortcode += '[' + conductor_query_builder_gutenberg_block.shortcode;

				// Loop through the attributes
				for ( var i = 0, l = conductor_query_builder_gutenberg_block.shortcode_attributes.length; i < l; i++ ) {
					// If we have an attribute value
					if ( conductor_query_builder_gutenberg_block.shortcode_attributes[i] && props.attributes['query_' + conductor_query_builder_gutenberg_block.shortcode_attributes[i]] ) {
						// Append the attribute to the shortcode
						shortcode += ' ' + conductor_query_builder_gutenberg_block.shortcode_attributes[i] + '="' + props.attributes['query_' + conductor_query_builder_gutenberg_block.shortcode_attributes[i]] + '"';
					}
				}

				shortcode += ']';
			}

			return element.createElement(
				'div',
				config,
				shortcode
			);
		}
	} );
}( conductor_query_builder_gutenberg_block, window.wp.blocks, window.wp.i18n, window.wp.element, window.wp.components, window.wp.editor ) );