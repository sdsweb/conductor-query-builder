/**
 * Conductor Query Builder Admin
 */

// TODO: Create a function to set/check flags
// TODO: Shorten variable and property names
// TODO: For BETWEEN/NOT BETWEEN we need to set maximumSelectionLength to the 'limit' value
// TODO: All render functions that only call the WordPress prototype can likely be removed in a future version

var conductor_query_builder = conductor_query_builder || {};

( function ( wp, $ ) {
	"use strict";

	var	$body,
		$document,
		$conductor_qb_preview,
		Conductor_Query_Builder_Wrapper_View,
		Conductor_Query_Builder_View,
		Conductor_Query_Builder_Actions_View,
		Conductor_Query_Builder_Clause_Group_Collection,
		Conductor_Query_Builder_Sub_Clause_Group_Collection,
		Conductor_Query_Builder_Shortcode_Model,
		Conductor_Query_Builder_Shortcode_Collection,
		Conductor_Query_Builder_Shortcode_View,
		Conductor_Query_Builder_Shortcode_Actions_View,
		Conductor_Query_Builder_Shortcode_Insert_View,
		Conductor_Query_Builder_Shortcode_Create_Title_View;

	// Defaults
	if ( ! conductor_query_builder.hasOwnProperty( 'Backbone' ) ) {
		conductor_query_builder.Backbone = {
			Views: {},
			Models: {},
			Collections: {},
			instances: {
				models: {
					clauses: [],
					sub_clauses: [],
					shortcode: []
				},
				collections: {
					clauses: [],
					sub_clauses: [],
					shortcode: []
				},
				views: {
					clauses: [],
					sub_clauses: [],
					clause_action_buttons: []
				}
			},
			defaults: {
				meta: {
					parameters: false, // Selected parameter(s)
					operators: false, // Selected operator
					values: false // Selected value(s)
				},
				parent: {
					count: -1,
					id: '',
					limit: -1
				}
			}
		};
	}

	if ( ! conductor_query_builder.hasOwnProperty( 'fn' ) ) {
		conductor_query_builder.fn = {
			/**
			 * Query Builder
			 */
			query_builder: {
				/**
				 * This function initializes the query builder Backbone components.
				 */
				init: function( shortcode ) {
					// Defaults
					shortcode = shortcode || false;

					var $conductor_widget = $( '.widget', '#widgets-right' ),
						$conductor_widget_accordion_sections = $conductor_widget.find( '.conductor-accordion-section' );

					/**
					 * Query Builder
					 */

					/**
					 * Backbone Collections
					 */
					// Create a new instance of the Conductor Query Builder Clause Group Backbone Collection
					Conductor_Query_Builder_Clause_Group_Collection = new conductor_query_builder.Backbone.Collections.Query_Builder_Clause_Group();
					conductor_query_builder.Backbone.instances.collections.clauses = Conductor_Query_Builder_Clause_Group_Collection;

					// Create a new instance of the Conductor Query Builder Sub-Clause Group Backbone Collection
					Conductor_Query_Builder_Sub_Clause_Group_Collection = new conductor_query_builder.Backbone.Collections.Query_Builder_Sub_Clause_Group();
					conductor_query_builder.Backbone.instances.collections.sub_clauses = Conductor_Query_Builder_Sub_Clause_Group_Collection;


					/**
					 * Backbone Views
					 */
					// Create a new instance of the Conductor Query Builder Backbone View
					Conductor_Query_Builder_View = new conductor_query_builder.Backbone.Views.Query_Builder( {
						type: 'query-builder',
						shortcode: shortcode
					} );
					conductor_query_builder.Backbone.instances.views.query_builder = Conductor_Query_Builder_View;


					// Create a new instance of the Conductor Query Builder Actions Backbone View
					Conductor_Query_Builder_Actions_View = new conductor_query_builder.Backbone.Views.Query_Builder_Actions( {
						type: 'query-builder-actions',
						shortcode: shortcode
					} );
					conductor_query_builder.Backbone.instances.views.query_builder_actions = Conductor_Query_Builder_Actions_View;

					// Attach the Conductor Query Builder Actions Backbone View to the Conductor Query Builder Backbone View
					Conductor_Query_Builder_View.views.set( Conductor_Query_Builder_Actions_View.el_selector, Conductor_Query_Builder_Actions_View, {
						// No DOM modifications
						silent: true
					} );

					// Render the Conductor Query Builder Backbone View
					Conductor_Query_Builder_View.render();

					// Delay 1ms; new thread
					setTimeout( function() {
						/**
						 * Conductor Widgets
						 */
						// Loop through Conductor Widgets accordion sections
						$conductor_widget_accordion_sections.each( function() {
							var $this = $( this ),
								$accordion_title = $this.find( '.conductor-accordion-section-title' ),
								$accordion_content = $this.find( '.conductor-accordion-section-content' );

							// If this accordion section isn't open
							if ( $accordion_title.length && ! $this.hasClass( 'open' ) ) {
								// Toggle it open now (no delay)
								$accordion_content.slideToggle( 0 );
							}
						} );


						/**
						 * Backbone Views
						 */
						// Create a new instance of the Conductor Query Builder Wrapper Backbone View
						Conductor_Query_Builder_Wrapper_View = new conductor_query_builder.Backbone.Views.Query_Builder_Wrapper( {
							type: 'query-builder-wrapper',
							shortcode: shortcode
						} );
						conductor_query_builder.Backbone.instances.views.query_builder_wrapper = Conductor_Query_Builder_Wrapper_View;
					}, 1 );
				},
				/**
				 * This function resets the query builder Backbone components.
				 */
				reset: function( meta ) {
					// Reset the Conductor Query Builder Backbone View
					Conductor_Query_Builder_View.reset();

					// If we have meta
					if ( typeof meta !== 'undefined' ) {
						// Set the Conductor Query Builder meta
						conductor_query_builder.meta = meta;
					}

					// Re-render the Conductor Query Builder Backbone View
					Conductor_Query_Builder_View.render();
				}
			},
			/**
			 * Shortcode
			 */
			shortcode: {
				/**
				 * This function initializes the shortcode Backbone components.
				 */
				init: function() {
					/*
					 * Backbone Collections
					 */

					// Create a new instance of the Conductor Query Builder Shortcode Backbone Collection
					Conductor_Query_Builder_Shortcode_Collection = new conductor_query_builder.Backbone.Collections.Shortcode();
					conductor_query_builder.Backbone.instances.collections.shortcode.push( Conductor_Query_Builder_Shortcode_Collection );


					/*
					 * Backbone Models
					 */
					// Create a new instance of the Conductor Query Builder Shortcode Backbone Model
					Conductor_Query_Builder_Shortcode_Model = new conductor_query_builder.Backbone.Models.Shortcode();
					Conductor_Query_Builder_Shortcode_Collection.add( Conductor_Query_Builder_Shortcode_Model );
					conductor_query_builder.Backbone.instances.models.shortcode.push( Conductor_Query_Builder_Shortcode_Model );


					/*
					 * Backbone Views
					 */

					// Create a new instance of the Conductor Query Builder Shortcode Backbone View
					Conductor_Query_Builder_Shortcode_View = new conductor_query_builder.Backbone.Views.Shortcode( {
						model: Conductor_Query_Builder_Shortcode_Model,
						queries: conductor_query_builder.queries,
						type: 'shortcode'
					} );
					conductor_query_builder.Backbone.instances.views.shortcode = Conductor_Query_Builder_Shortcode_View;


					// Create a new instance of the Conductor Query Builder Shortcode Actions Backbone View
					Conductor_Query_Builder_Shortcode_Actions_View = new conductor_query_builder.Backbone.Views.Shortcode_Actions( {
						type: 'shortcode-actions',
						button_type: 'insert',
						label: conductor_query_builder.l10n.shortcode.insert
					} );
					conductor_query_builder.Backbone.instances.views.shortcode_actions = Conductor_Query_Builder_Shortcode_Actions_View;

					// Attach the Conductor Query Builder Shortcode Actions Backbone View to the Conductor Query Builder Shortcode Backbone View
					Conductor_Query_Builder_Shortcode_View.views.set( Conductor_Query_Builder_Shortcode_Actions_View.el_selector, Conductor_Query_Builder_Shortcode_Actions_View, {
						// No DOM modifications
						silent: true
					} );


					// Create a new instance of the Conductor Query Builder Shortcode Insert Backbone View
					Conductor_Query_Builder_Shortcode_Insert_View = new conductor_query_builder.Backbone.Views.Shortcode_Insert( {
						queries: Conductor_Query_Builder_Shortcode_View.options.queries,
						type: 'shortcode-insert'
					} );
					conductor_query_builder.Backbone.instances.views.shortcode_insert = Conductor_Query_Builder_Shortcode_Insert_View;

					// Attach the Conductor Query Builder Shortcode Insert Backbone View to the Conductor Query Builder Shortcode Backbone View
					Conductor_Query_Builder_Shortcode_View.views.set( Conductor_Query_Builder_Shortcode_Insert_View.el_selector, Conductor_Query_Builder_Shortcode_Insert_View, {
						// No DOM modifications
						silent: true
					} );


					// Create a new instance of the Conductor Query Builder Shortcode Create Title Backbone View
					Conductor_Query_Builder_Shortcode_Create_Title_View = new conductor_query_builder.Backbone.Views.Shortcode_Create_Title( {
						type: 'shortcode-create-title',
						title: conductor_query_builder.title || ''
					} );
					conductor_query_builder.Backbone.instances.views.shortcode_create_title = Conductor_Query_Builder_Shortcode_Create_Title_View;

					// Attach the Conductor Query Builder Shortcode Create Title Backbone View to the Conductor Query Builder Shortcode Backbone View
					Conductor_Query_Builder_Shortcode_View.views.set( Conductor_Query_Builder_Shortcode_Create_Title_View.el_selector, Conductor_Query_Builder_Shortcode_Create_Title_View, {
						// No DOM modifications
						silent: true
					} );


					// Render the Conductor Query Builder Shortcode Backbone View
					Conductor_Query_Builder_Shortcode_View.render();
				},
				/**
				 * This function resets the shortcode Backbone components.
				 */
				reset: function() {
					// Reset the Conductor Query Builder Shortcode Backbone View
					Conductor_Query_Builder_Shortcode_View.reset();

					// Reset the Conductor Query Builder Shortcode Insert Backbone View
					Conductor_Query_Builder_Shortcode_Insert_View.reset();
				}
			},
			/**
			 * Conductor
			 */
			conductor: {
				// Widgets
				widget: {
					/**
					 * This function resets the Conductor Widget.
					 * @param data
					 */
					reset: function( data ) {
						// Defaults
						data = data || {};

						var $conductor_widget_inputs,
							$input = false,
							output_element_ids = [];

						// Start a new thread; delay 1ms
						setTimeout( function() {
							// Grab the Conductor Widget input elements
							$conductor_widget_inputs = Conductor_Query_Builder_Shortcode_View.$el.find( '.conductor-widget-setting :input' );

							// If we have Conductor Widget input elements
							if ( $conductor_widget_inputs.length ) {
								// Loop through the Conductor Widget input elements
								$conductor_widget_inputs.each( function() {
									var $this = $( this ),
										node_name = this.nodeName.toLowerCase(),
										input_type = ( node_name === 'input' ) ? $this.attr( 'type' ) : null,
										data_default = ( input_type === 'checkbox' || input_type === 'radio' ) ? $this.data( 'default' ) : null,
										data_default_value = ( ! data_default ) ? $this.data( 'default-value' ) : null,
										checked;

									// If we don't have a data default data and we don't have a data default value
									if ( data_default === null && ( data_default_value === null || typeof data_default_value === 'undefined' ) ) {
										// Grab the data default value from the parent element
										data_default_value = $this.parents( '.conductor-widget-setting' ).data( 'default-value' );
									}

									// If we have a data default data and we have a data default value
									if ( data_default !== null || ( data_default_value !== null && typeof data_default_value !== 'undefined' ) ) {
										// If this is the Conductor Widget output data element
										if ( $this.hasClass( 'conductor-output-data' ) ) {
											// Set the value
											$this.val( JSON.stringify( data_default_value ) );

											// Loop through data default values
											_.each( data_default_value, function( sub_default_value, priority ) {
												var $label,
													$label_input;

												// Grab the correct element for this output element
												$input = Conductor_Query_Builder_Shortcode_View.$el.find( '[data-id="' + sub_default_value.id + '"]' );

												// Add this output element ID to the array
												output_element_ids.push( sub_default_value.id );

												// Switch based on ID
												switch ( sub_default_value.id ) {
													// Post Content
													case 'post_content':
														// Reset the post content value
														$input.find( '.conductor-widget-output-element-label-select select' ).val( sub_default_value.value );
													break;
												}

												// Add the link CSS class to the output element
												$input.addClass( 'visible' );

												// Reset the data-visible attribute
												$input.data( 'visible', 'true' ).attr( 'data-visible', 'true' );

												// If this element supports linking
												if ( sub_default_value.link ) {
													// Add the link CSS class to the output element
													$input.addClass( 'link' );

													// Reset the data-link attribute
													$input.data( 'link', 'true' ).attr( 'data-link', 'true' );
												}

												// Grab the label element
												$label = $input.find( '.conductor-widget-output-element-label .label' );

												// Grab the label input element
												$label_input = $input.find( '.conductor-widget-output-element-label-input input' );

												// If don't we have a label element
												if ( ! $label.length ) {
													// Grab the correct label element
													$label = $input.find( '.conductor-widget-output-element-label' );

													// Remove the editing CSS class
													$label.removeClass( 'editing' );
												}
												// Otherwise we have a label element
												else {
													// Remove the editing CSS class from the container element
													$label.parent().removeClass( 'editing' );
												}

												// If we have a label
												if ( $label.length ) {
													// Reset the label
													$label.html( sub_default_value.label );

													// If we have a label input
													if ( $label_input.length ) {
														// Reset the label input
														$label_input.val( '' ).data( 'current', '' ).attr( 'data-current', '' );
													}

													// Reset the data-label attribute
													$input.data( 'label', sub_default_value.label ).attr( 'data-label', sub_default_value.label );
												}

												// Reset the data-priority attribute
												$input.data( 'priority', priority ).attr( 'data-priority', priority );

												// Append this element to the output element list to reset the position
												Conductor_Query_Builder_Shortcode_View.$el.find( '.conductor-widget-output-list' ).append( $input );
											} );

											// Loop through each output element DOM item
											Conductor_Query_Builder_Shortcode_View.$el.find( '.conductor-widget-output-element' ).each( function() {
												var $this = $( this );

												// If this data ID isn't in our list of output element
												if ( output_element_ids.indexOf( $this.data( 'id' ) ) === -1 ) {
													// Remove this element
													$this.remove();
												}
											} );
										}
										// Otherwise this isn't the Conductor Widget output data element
										else {
											// If we have an input type
											if ( input_type !== null ) {
												// Switch based on input type
												switch ( input_type ) {
													// Checkbox and Radio
													case 'checkbox':
													case 'radio':
														// Grab the checked property
														checked = $this.prop( 'checked' );

														// Set the checked property
														$this.prop( 'checked', data_default );

														// If the checked property does not match the data default value
														if ( checked !== data_default ) {
															// Trigger the change event on this input element
															$this.trigger( 'change' );
														}
													break;

													// Default
													default:
														// Set the value (trigger the change and input event event)
														$this.val( data_default_value ).trigger( 'change' ).trigger( 'input' );
													break;
												}
											}
											// Otherwise we don't have an input type
											else {
												// Switch based on node name
												switch ( node_name ) {
													// Select
													case 'select':
														// Set the value (trigger the change event)
														$this.val( data_default_value ).trigger( 'change' );
													break;
												}
											}
										}
									}
								} );

								// Trigger the change event on the on the Conductor Widget feature type element
								Conductor_Query_Builder_Shortcode_View.$el.find( '.conductor-select-feature-type' ).trigger( 'change' );
							}
						}, 1 );
					}
				}
			}
		};
	}


	/*****************
	 * Query Builder *
	 *****************/

	/**
	 * Conductor Query Builder Clause Group Backbone Model
	 */
	conductor_query_builder.Backbone.Models.Query_Builder_Clause_Group = Backbone.Model.extend( {
		defaults: {
			columns: false,
			el_selector: '',
			flags: [],
			operators: [],
			parameters: [],
			title: '',
			limit: -1,
			type: false
			// values: []
		},
		id_prefix: 'conductor-qb-',
		initialize: function() {
			// Bind "this" to all functions
			_.bindAll(
				this,
				'setID',
				'removeFromInstances'
			);

			// Remove the model from the instances
			this.listenTo( this, 'remove', this.removeFromInstances );

			// Stop listening to events on the model when it's removed
			this.listenTo( this, 'remove', this.stopListening );
		},
		/**
		 * This function sets the ID value of this model based on the number of existing models of this model's type.
		 */
		setID: function( view ) {
			// Bail if we don't have a valid count
			if ( view.options.count === -1 ) {
				return;
			}

			this.set( 'id', this.getIDFromView( view ) );
		},
		/**
		 * This function returns an ID based on the view
		 */
		getIDFromView: function( view ) {
			return this.id_prefix + this.get( 'type' ) + '-' + view.options.count;
		},
		/**
		 * This function removes the model from instances.
		 */
		removeFromInstances: function() {
			var model_clause_instances = conductor_query_builder.Backbone.instances.models.clauses,
				model_index,
				self = this;

			// Grab the model index from instances
			model_index = model_clause_instances.map( function( model ) { return model.get( 'id' ); } ).indexOf( self.get( 'id' ) );

			// If we have a model index
			if ( model_index !== -1 ) {
				// Remove this model from instances
				model_clause_instances.splice( model_index, 1 );
			}
		}
	} );

	/**
	 * Conductor Query Builder Sub-Clause Group Backbone Model
	 */
	conductor_query_builder.Backbone.Models.Query_Builder_Sub_Clause_Group = Backbone.Model.extend( {
		defaults: {
			columns: false,
			config: {},
			flags: [],
			meta: _.clone( conductor_query_builder.Backbone.defaults.meta ),
			operators: [],
			parameters: [],
			parent_id: '',
			parent: _.clone( conductor_query_builder.Backbone.defaults.parent ),
			title: '',
			type: false
		},
		id_prefix: 'conductor-qb-',
		initialize: function() {
			// Bind "this" to all functions
			_.bindAll(
				this,
				'setID',
				'setParentID',
				'removeFromInstances'
			);

			// Remove the model from the instances
			this.listenTo( this, 'remove', this.removeFromInstances );

			// Stop listening to events on the model when it's removed
			this.listenTo( this, 'remove', this.stopListening );
		},
		/**
		 * This function sets the ID value of this model based on the number of existing models of this model's type.
		 */
		setID: function( view, id ) {
			// Defaults
			id = id || false;

			// Bail if we don't have a parent view yet or a valid count
			if ( ! view.views.parent || ! view.views.parent.model || view.options.count === -1 ) {
				return;
			}

			// Set the parent ID value
			this.setParentID( view.views.parent.model.get( 'id' ) );

			this.set( 'id', ( id ) ? id : this.getIDFromView( view ) );
		},
		/**
		 * This function returns an ID based on the view
		 */
		getIDFromView: function( view ) {
			return this.id_prefix + view.views.parent.model.get( 'id' ).replace( this.id_prefix, '' ) + '-' + view.options.count;
		},
		/**
		 * This function sets the parent ID value of this model.
		 */
		setParentID: function( id ) {
			var parent_data = _.clone( this.get( 'parent' ) );

			// Update the parent ID
			parent_data.id = id;

			// Set the parent data
			this.set( 'parent', parent_data );

			// Set the parent ID
			this.set( 'parent_id', id );
		},
		/**
		 * This function sets the parent data this model. Parent ID logic is handled in
		 * conductor_query_builder.Backbone.Models.Query_Builder_Sub_Clause_Group.setParentID().
		 */
		setParentData: function( view ) {
			var parent_data = _.clone( this.get( 'parent' ) );

			// Update the parent count
			parent_data.count = view.views.parent.options.count;

			// Update the parent limit
			parent_data.limit = view.views.parent.model.get( 'limit' );

			// Set the parent data
			this.set( 'parent', parent_data );
		},
		/**
		 * This function removes the model from instances.
		 */
		removeFromInstances: function() {
			var model_clause_instances = conductor_query_builder.Backbone.instances.models.sub_clauses,
				model_index,
				self = this;

			// Grab the model index from instances
			model_index = model_clause_instances.map( function( model ) { return model.get( 'id' ); } ).indexOf( self.get( 'id' ) );

			// If we have a model index
			if ( model_index !== -1 ) {
				// Remove this model from instances
				model_clause_instances.splice( model_index, 1 );
			}
		}
	} );

	/**
	 * Conductor Query Builder Clause Group Backbone Collection
	 */
	conductor_query_builder.Backbone.Collections.Query_Builder_Clause_Group = Backbone.Collection.extend( {} );

	/**
	 * Conductor Query Builder Sub-Clause Group Backbone Collection
	 */
	conductor_query_builder.Backbone.Collections.Query_Builder_Sub_Clause_Group = Backbone.Collection.extend( {
		/**
		 * This function determines if all meta values are empty.
		 */
		isMetaEmpty: function() {
			// Bail if we're not in the advanced query builder mode
			if ( window.getUserSetting( conductor_query_builder.user.settings['query-builder'].mode.name ) !== 'advanced' ) {
				return true;
			}

			return this.every( function ( model ) {
				var meta = model.get( 'meta' );

				return ! meta.parameters && ! meta.operators && ! meta.values;
			} );
		}
	} );

	/**
	 * Conductor Query Builder Backbone View
	 */
	conductor_query_builder.Backbone.Views.Query_Builder_Wrapper = wp.Backbone.View.extend( {
		el: '#conductor-qb-query-builder',
		has_user_changed_feature_type: false,
		feature_type_value: '',
		// Events
		events: {
			'change .conductor-select-feature-type': 'setHasUserChangedFeatureTypeFlag',
			'change :input': 'previewQuery',
			'keyup :input': 'previewQuery',
			'change .conductor-widget-size-value': 'maybeShowConductorWidgetColumnsSetting'
		},
		/**
		 * This function runs on initialization of the view.
		 */
		initialize: function( options ) {
			// Bind "this" to all functions
			_.bindAll(
				this,
				'render',
				'setHasUserChangedFeatureTypeFlag',
				'previewQuery'
			);
		},
		/**
		 * This function renders the view.
		 */
		render: function() {
			// Call (apply) the default wp.Backbone.View render function
			wp.Backbone.View.prototype.render.apply( this, arguments );

			return this;
		},
		/**
		 * This function sets the has user changed feature type flag.
		 */
		setHasUserChangedFeatureTypeFlag: function( event ) {
			// Store the current feature type value
			this.feature_type_value = $( event.currentTarget ).val();

			// Bail if the has user changed feature type flag is set
			if ( this.has_user_changed_feature_type ) {
				return;
			}

			// Set the has user changed feature type flag based on the feature type focused flag
			this.has_user_changed_feature_type = true;
		},
		/**
		 * This function previews the query; delay 400ms between each preview.
		 */
		previewQuery: _.debounce( function( event ) {
			var $document = $( document ),
				$el = event && $( event.currentTarget );

			// Bail if the shortcode UI is visible
			if ( this.options.shortcode ) {
				return;
			}

			// Bail if we have an element, this isn't a Conductor Query Builder Select2  field, and we should skip the query builder preview
			if ( $el && $el.length && ! $el.hasClass( 'conductor-qb-select2' ) && $el.data( 'conductor-query-builder-skip-preview' ) ) {
				return;
			}

			// Bail if we have an element, this is a Select2 search field and we should skip the query builder preview
			if ( $el && $el.length && $el.hasClass( 'select2-search__field' ) && $el.parents( '.select2-container' ).prev( '.conductor-qb-select2' ).data( 'conductor-query-builder-skip-preview' ) ) {
				return;
			}

			// Trigger an event on the document
			$document.trigger( 'conductor-query-builder-preview-query', [ this, $conductor_qb_preview ] );

			// Set the loading state on the preview element
			$conductor_qb_preview.addClass( conductor_query_builder.css.classes.loading ).html( '' );

			// If we have a current AJAX request
			if ( this.ajax.current_request ) {
				// Abort the current request
				this.ajax.current_request.abort();
			}
			// Make the AJAX request (POST)
			this.ajax.current_request = wp.ajax.post( conductor_query_builder.ajax.preview.action, this.ajax.setupAJAXData( conductor_query_builder.ID, conductor_query_builder.ajax.preview.nonce, conductor_query_builder.ajax.preview.action ) ).done( this.ajax.success ).fail( this.ajax.fail );
		}, 400 ),
		/**
		 * This function determines if the Conductor Widget columns setting should be displayed.
		 */
		maybeShowConductorWidgetColumnsSetting: function( event ) {
			var $this = $( event.currentTarget ),
				value = $this.val(),
				conductor_widget_displays = conductor.widgets.conductor.displays,
				display_config = ( value && conductor_widget_displays && conductor_widget_displays[value] && _.isObject( conductor_widget_displays[value] ) ) ? conductor_widget_displays[value]: false,
				$widget_parent = $this.parents( '.widget' ),
				$conductor_columns = $widget_parent.find( '.conductor-columns' ),
				query_builder_mode = window.getUserSetting( conductor_query_builder.user.settings['query-builder'].mode.name );

			// If the query builder mode is set to advanced
			if ( query_builder_mode && query_builder_mode === 'advanced' ) {
				// Start a new thread
				setTimeout( function() {
					// Flexbox columns
					conductor.widgets.conductor.renderElement( $conductor_columns, $widget_parent, $this, {
						display: {
							supports: 'columns',
							config: ( display_config ) ? display_config : {}
						},
						feature_type: 'true' // Many
					} );
				}, 1 );
			}
		},

		/**
		 * AJAX
		 *
		 * AJAX data and functions.
		 */
		ajax: {
			current_request: false,
			/**
			 * This function sets up AJAX data.
			 */
			setupAJAXData: function( ID, nonce, nonce_action, $form ) {
				// Defaults
				$form = $form || Conductor_Query_Builder_Wrapper_View.$el.find( ':input' );

				var data = $.extend( {
						ID: ID,
						nonce: nonce,
						nonce_action: nonce_action
					}, this.data ),
					form_data = $form.serializeArray();

				// Loop through form data
				_.each( form_data, function ( the_data ) {
					var $el = $( '[name="' + the_data.name + '"]' ),
						type = $el.data( 'type' ),
						multiple = $el.prop( 'multiple' ),
						value = the_data.value;

					// If this is a select element, a values select element, and it's a multiple select element
					if ( $el[0].nodeName.toLowerCase() && type === 'values' && multiple ) {
						// Set the value to the selected options data
						value = $el.data( 'selected-options' );
					}

					// If this form data name already exists
					if ( data[the_data.name] ) {
						// If the form data isn't an array
						if ( ! _.isArray( data[the_data.name] ) ) {
							// Create an array using the existing data as the first value
							data[the_data.name] = [data[the_data.name]];
						}

						// If this value isn't already in the array
						if ( data[the_data.name].indexOf( the_data.value ) === -1 ) {
							// Push this data to the end of the array
							data[the_data.name].push( the_data.value );
						}
					}
					// Otherwise this form data name doesn't exist
					else {
						// Append this field to the data
						data[the_data.name] = value;
					}
				} );

				return data;
			},
			/**
			 * This function runs on a successful AJAX request.
			 */
			success: function( response ) {
				// Remove the "loading" CSS classes from the query builder preview
				$conductor_qb_preview.removeClass( conductor_query_builder.css.classes.loading );

				// If we have preview data
				if ( response.preview ) {
					// Show the preview
					$conductor_qb_preview.html( response.preview );
				}
			},
			/**
			 * This function runs on a failed AJAX request.
			 */
			fail: function( response ) {
				// If the request wasn't aborted
				if ( ! response.statusText || response.statusText !== 'abort' ) {
					// Show the preview error message (remove loading state)
					$conductor_qb_preview.removeClass( conductor_query_builder.css.classes.loading ).html( conductor_query_builder.l10n.ajax.fail.preview );
				}
			}
		}
	} );

	/**
	 * Conductor Query Builder Backbone View
	 */
	conductor_query_builder.Backbone.Views.Query_Builder = wp.Backbone.View.extend( {
		el: '#conductor-qb-meta-box-query-builder-tab-content',
		query_builder_mode_attr: 'data-query-builder-mode',
		initial_feature_type: false,
		has_user_changed_feature_type: false,
		/**
		 * This function runs on initialization of the view.
		 */
		initialize: function( options ) {
			var self = this,
				query_builder_mode = window.getUserSetting( conductor_query_builder.user.settings['query-builder'].mode.name ),
				view,
				$feature_type;

			// Bind "this" to all functions
			_.bindAll(
				this,
				'render'
			);

			// Delay 100ms; new thread
			setTimeout( function() {
				// Grab the correct view instance based on options
				view = ( self.options.shortcode ) ? Conductor_Query_Builder_Shortcode_View : Conductor_Query_Builder_Wrapper_View;

				// Grab the feature type element
				$feature_type = view.$el.find( '.conductor-select-feature-type' );

				// Store the initial feature type value on this object
				self.initial_feature_type = $feature_type.val();

				// If the new query builder mode is advanced and the current Conductor Widget feature type isn't set to "many"
				if ( query_builder_mode === 'advanced' && $feature_type.val() !== 'true' ) {
					// Set the Conductor Widget feature type to "many" and trigger the "change" event
					$feature_type.val( 'true' ).trigger( 'change' );
				}
			}, 100 );
		},
		/**
		 * This function renders the view.
		 */
		render: function() {
			// Call (apply) the default wp.Backbone.View render function
			wp.Backbone.View.prototype.render.apply( this, arguments );

			// Render clause groups
			this.renderClauseGroups();

			return this;
		},
		/**
		 * This function renders clause groups.
		 */
		renderClauseGroups: function() {
			var self = this,
				Conductor_Query_Builder_Clause_Group_View;

			// Loop through clause groups (delay 1ms; new thread)
			setTimeout( function() {
				_.each( conductor_query_builder.clauses, function( clause_group_data, type ) {
					// If this is a default clause group or we have meta
					if ( clause_group_data.config.default || ( conductor_query_builder.meta[type] && ! _.isEmpty( conductor_query_builder.meta[type] ) ) ) {
						// If this is a default clause group
						if ( clause_group_data.config.default ) {
							// Add the clause group
							Conductor_Query_Builder_Actions_View.addClauseGroup( false, type, clause_group_data, conductor_query_builder.meta[type] );
						}
						// Otherwise, we have meta for this clause group
						else {
							// Loop through the clause groups
							_.each( conductor_query_builder.meta[type], function ( clause_group_meta_data, clause_group_id ) {
								// Add the clause group (grab the Backbone view)
								Conductor_Query_Builder_Clause_Group_View = Conductor_Query_Builder_Actions_View.addClauseGroup( false, type, clause_group_data, conductor_query_builder.meta[type] );

								// Loop through the clause group meta data
								_.each( clause_group_meta_data, function ( sub_clause_group_meta_data, sub_clause_group_id ) {
									// If this isn't the first sub-clause group (the first sub-clause group is always added to the clause group by default)
									if ( sub_clause_group_id !== 0 ) {
										// Add the sub-clause group
										Conductor_Query_Builder_Clause_Group_View.addSubClauseGroup();
									}
								} );
							} );
						}
					}
				} );

				// Remove the loading state
				self.removeLoadingState();
			}, 1 );
		},
		/**
		 * This function resets the view.
		 */
		reset: function() {
			var self = this,
				model,
				el_selector,
				views;

			// Loop through global meta values
			_.each( conductor_query_builder.meta, function ( value, type ) {
				// Reset value to an empty array
				conductor_query_builder.meta[type] = [];
			} );

			// TODO: Transition to "conductor-query-builder-{event}" event name
			// Trigger the conductor-qb-disable-action-buttons event on the Conductor Query Builder Actions View
			Conductor_Query_Builder_Actions_View.trigger( 'conductor-qb-disable-action-buttons' );

			// Loop through clause group action Backbone Views
			_.each( conductor_query_builder.Backbone.instances.views.clause_action_buttons, function ( view ) {
				// TODO: Transition to "conductor-query-builder-{event}" event name
				// Trigger the conductor-qb-disable-action-buttons event on this view
				view.trigger( 'conductor-qb-disable-action-buttons' );
			} );

			// While we have models in the clause group collection
			while ( Conductor_Query_Builder_Clause_Group_Collection.length ) {
				// Grab the first model
				model = Conductor_Query_Builder_Clause_Group_Collection.first();

				// Grab the el_selector
				el_selector = model.get( 'el_selector' );

				// If we have an el_selector
				if ( el_selector ) {
					// Grab all of the clause group views associated with this el_selector
					views = self.views.get( el_selector );

					// If we have views
					if ( views.length ) {
						// Loop through views
						_.each( views, function (view ) {
							// Force remove this clause group
							view.removeClauseGroup( false, true );
						} );
					}
				}
			}
		},
		/**
		 * This function toggles the query mode between simple and advanced
		 */
		toggleQueryBuilderMode: function( new_mode, previous_mode ) {
			var view = ( this.options.shortcode ) ? Conductor_Query_Builder_Shortcode_View : Conductor_Query_Builder_Wrapper_View,
				$feature_type = view.$el.find( '.conductor-select-feature-type' );

			// Hide all previous query builder mode elements
			this.$el.find( '[' + this.query_builder_mode_attr + '="' + previous_mode + '"]' ).addClass( 'hide hidden conductor-qb-hide conductor-qb-hidden' );

			// Show all new query builder mode elements
			this.$el.find( '[' + this.query_builder_mode_attr + '="' + new_mode + '"]' ).removeClass( 'hide hidden conductor-qb-hide conductor-qb-hidden' );

			// Set the CSS class on the content wrapper element
			this.$el.parents( '.conductor-qb-query-builder-meta-box-tab-content-wrapper' ).toggleClass( 'conductor-qb-' + previous_mode + '-mode conductor-qb-' + new_mode + '-mode' );

			// If the new query builder mode is advanced and the current Conductor Widget feature type isn't set to "many"
			if ( new_mode === 'advanced' && $feature_type.val() !== 'true' ) {
				// Set the Conductor Widget feature type to "many" and trigger the "change" event
				$feature_type.val( 'true' ).trigger( 'change' );
			}
			// Otherwise if the query builder mode is simple
			else if ( new_mode === 'simple' ) {
				// If the user changed the feature type value
				if ( view.has_user_changed_feature_type ) {
					// Set the feature type value to the user selected value and trigger the "change" event
					$feature_type.val( view.feature_type_value ).trigger( 'change' );
				}
				// Otherwise the user did not change the feature type value
				else {
					// Set the feature type value to "one" and trigger the "change" event
					$feature_type.val( '' ).trigger( 'change' );
				}
			}

			// TODO: Transition to "conductor-query-builder-{event}" event name
			// Trigger the "conductor-qb-toggle-query-builder-mode" on the view element
			this.$el.trigger( 'conductor-qb-toggle-query-builder-mode', [ new_mode, previous_mode ] );

			// TODO: Transition to "conductor-query-builder-{event}" event name
			// Trigger the "conductor-qb-toggle-query-builder-mode" on the view
			this.trigger( 'conductor-qb-toggle-query-builder-mode', new_mode, previous_mode );
		},
		/**
		 * This function removes the loading state from the content wrapper.
		 */
		removeLoadingState: function() {
			// Remove loading state
			this.$el.parents( '.conductor-qb-query-builder-meta-box-tab-content-wrapper' ).removeClass( conductor_query_builder.css.classes.loading );
		}
	} );

	/**
	 * Conductor Query Builder Actions Backbone View
	 */
	conductor_query_builder.Backbone.Views.Query_Builder_Actions = wp.Backbone.View.extend( {
		className: 'conductor-qb-meta-box-query-builder-actions-inner',
		action_button_selector: '.conductor-qb-action-button',
		el_selector: '',
		el_selector_prefix: '.conductor-qb-meta-box-query-builder-',
		el_selector_suffix: '-actions',
		flags: {
			action_buttons_enabled: false
		},
		template: wp.template( 'conductor-qb-meta-box-query-builder-actions' ),
		// Events
		events: {
			'click .conductor-qb-add-clause-group-button': function( event ) {
				var $this = $( event.currentTarget ),
					type = $this.data( 'clause-group-type' ),
					clause_group_type = this.options.clause_group_type,
					clause_group_config = conductor_query_builder.clauses[type] && conductor_query_builder.clauses[type]['config'],
					sub_clause_group_view_instances = conductor_query_builder.Backbone.instances.views.sub_clauses,
					sub_clause_group_view_index = sub_clause_group_view_instances.map( function( view ) { return view.options.type; } ).indexOf( clause_group_type ),
					sub_clause_group_view = ( sub_clause_group_view_index !== -1 ) ? sub_clause_group_view_instances[sub_clause_group_view_index] : false,
					sub_clause_group_view_meta = sub_clause_group_view && sub_clause_group_view.getMeta(),
					sub_clause_group_view_config = sub_clause_group_view && sub_clause_group_view.getConfig(),
					limit = ( clause_group_config && clause_group_config['limit'] ) ? parseInt( clause_group_config['limit'], 10 ) : -1,
					clause_group_view,
					is_button_disabled = false;

				// Loop through the sub-clause group view config
				_.find( sub_clause_group_view_config, function( config, parameter ) {
					// If the sub-clause group view config has clauses and the sub-clause group view meta value contains this parameters
					if ( config.clauses && sub_clause_group_view_meta['parameters'] && ( ( _.isArray( sub_clause_group_view_meta['parameters'] ) && sub_clause_group_view_meta['parameters'].indexOf( parameter ) !== -1 ) || sub_clause_group_view_meta['parameters'] === parameter ) ) {
						// Loop through the sub-clause group view clauses config data
						_.find( config.clauses, function ( clause_config, clause ) {
							// If this clause matches the action button type
							if ( clause === type ) {
								// If this clause should be disabled
								if ( clause_config.disabled ) {
									// Set the disabled button flag
									is_button_disabled = true;
								}
							}

							return is_button_disabled;
						} );
					}

					return is_button_disabled;
				} );

				// If this button isn't disabled and we have action buttons sub-clause group config for this action button
				if ( ! is_button_disabled && sub_clause_group_view_config && sub_clause_group_view_config['action_buttons'] && sub_clause_group_view_config['action_buttons'][type] ) {
					// Loop through the columns for this action button config
					_.find( sub_clause_group_view_config['action_buttons'][type]['columns'], function( config, column ) {
						// Switch based on the column
						switch ( column ) {
							// Parameters
							case 'parameters':
								// Loop through the config
								_.find( config, function( action_button_config, parameter ) {
									// If the sub-clause group view meta value contains this parameters
									if ( sub_clause_group_view_meta['parameters'] && ( ( _.isArray( sub_clause_group_view_meta['parameters'] ) && sub_clause_group_view_meta['parameters'].indexOf( parameter ) !== -1 ) || sub_clause_group_view_meta['parameters'] === parameter ) ) {
										// If this action button should be disabled
										if ( action_button_config.disabled ) {
											// Set the disabled button flag
											is_button_disabled = true;
										}
									}

									return is_button_disabled;
								} );
								break;
						}

						return is_button_disabled;
					} );
				}

				// Bail if this button is disabled
				if ( is_button_disabled ) {
					// Prevent default
					event.preventDefault();

					return;
				}

				// Add clause group (grab the new Backbone view)
				clause_group_view = this.addClauseGroup( event );

				// If this clause group should be limited and we've reached the limit
				if ( clause_group_view && limit !== -1 && clause_group_view.getCurrentCount() >= limit ) {
					// Disable this button
					this.disableActionButton( '#' + $this.attr( 'id' ) );
				}
			},
			'click .conductor-qb-test-query-button': 'testQuery', // Test query
			'click .conductor-qb-toggle-mode-button' : 'toggleQueryBuilderMode' // Toggle query builder mode
		},
		/**
		 * This function runs on initialization of the view.
		 */
		initialize: function( options ) {
			// Bind "this" to all functions
			_.bindAll(
				this,
				'render',
				'addClauseGroup'
			);

			// Create the flag property if we don't already have one
			if ( ! options.flags ) {
				options.flags = {};
			}

			// Set the el_selector
			this.el_selector = ( options.clause_group_type ) ? this.el_selector_prefix + options.clause_group_type + this.el_selector_suffix : this.el_selector_prefix + this.el_selector_suffix;

			// If we don't have a valid selector, try to replace underscores with hyphens
			if ( options.clause_group_type && ! $( this.el_selector ).length ) {
				// Set the el_selector (replace underscores with hyphens)
				this.el_selector = this.el_selector_prefix + options.clause_group_type.replace( '_', '-' ) + this.el_selector_suffix;
			}

			// Replace any double hyphens if we don't have a clause group type (prefix and suffix contain trailing and starting hyphens)
			if ( ! options.clause_group_type ) {
				this.el_selector = this.el_selector.replace( '--', '-' );
			}

			// Set the clause group flag if it isn't already set
			if ( ! options.flags.clause_group && options.clause_group_type ) {
				options.flags.clause_group = true;
			}

			/*
			 * Event Listeners
			 */
			this.listenTo( this, 'conductor-qb-enable-action-buttons', this.enableActionButtons ); // Enable action buttons
			this.listenTo( this, 'conductor-qb-disable-action-buttons', this.disableActionButtons ); // Disable action buttons
		},
		/**
		 * This function renders the view.
		 */
		render: function() {
			// Call (apply) the default wp.Backbone.View render function
			wp.Backbone.View.prototype.render.apply( this, arguments );

			return this;
		},
		/**
		 * This function adds a clause group view to the Conductor Query Builder Backbone View.
		 */
		addClauseGroup: function( event, clause_group_type, clause_group_data, clause_group_meta ) {
			// Defaults
			clause_group_type = clause_group_type || false;
			clause_group_data = clause_group_data || false;
			clause_group_meta = clause_group_meta || false;

			var has_event = event && !_.isEmpty( event ),
				$this = ( has_event && event.currentTarget ) ? $( event.currentTarget ) : false,
				type = ( clause_group_type ) ? clause_group_type : ( ( $this ) ? $this.data( 'clause-group-type' ) : false ),
				clause_group_config = clause_group_data['config'],
				title = clause_group_config && clause_group_config['title'] || '',
				limit = ( clause_group_config && clause_group_config['limit'] ) ? parseInt( clause_group_config['limit'], 10 ) : -1,
				Conductor_Query_Builder_Clause_Group_Model,
				view_arguments,
				Conductor_Query_Builder_Clause_Group_View,
				Conductor_Query_Builder_Clause_Group_Actions_View;

			// If we have an event, prevent default
			if ( has_event ) {
				event.preventDefault();
			}

			// Bail if we don't have a type
			if ( ! type ) {
				return false;
			}

			// Create a new instance of the Conductor Query Builder Clause Group Backbone Model
			Conductor_Query_Builder_Clause_Group_Model = new conductor_query_builder.Backbone.Models.Query_Builder_Clause_Group( {
				columns: ( conductor_query_builder.clauses[type] && conductor_query_builder.clauses[type].config && conductor_query_builder.clauses[type].config.columns ) ? conductor_query_builder.clauses[type].config.columns : false,
				flags: ( conductor_query_builder.clauses[type] && conductor_query_builder.clauses[type].config && conductor_query_builder.clauses[type].config.flags ) ? conductor_query_builder.clauses[type].config.flags : false,
				operators: ( conductor_query_builder.clauses[type] && conductor_query_builder.clauses[type].operators ) ? conductor_query_builder.clauses[type].operators : false,
				parameters: ( conductor_query_builder.clauses[type] && conductor_query_builder.clauses[type].parameters ) ? conductor_query_builder.clauses[type].parameters : false,
				meta: ( clause_group_meta ) ? clause_group_meta : ( conductor_query_builder.meta[type] ) ? conductor_query_builder.meta[type] : _.clone( conductor_query_builder.Backbone.defaults.meta ),
				title: ( ! title && conductor_query_builder.clauses[type] && conductor_query_builder.clauses[type].config && conductor_query_builder.clauses[type].config.title ) ? conductor_query_builder.clauses[type].config.title : title,
				type: type,
				limit: ( ! isNaN( limit ) && limit > 0 ) ? limit : -1,
				shortcode: this.options.shortcode
				//values: ( conductor_query_builder.clauses[type] && conductor_query_builder.clauses[type].values ) ? conductor_query_builder.clauses[type].values : false
			} );
			Conductor_Query_Builder_Clause_Group_Collection.add( Conductor_Query_Builder_Clause_Group_Model );
			conductor_query_builder.Backbone.instances.models.clauses.push( Conductor_Query_Builder_Clause_Group_Model );

			// Setup the Backbone View arguments
			view_arguments = _.clone( Conductor_Query_Builder_Clause_Group_Model.attributes );
			view_arguments.model = Conductor_Query_Builder_Clause_Group_Model;

			// Create a new instance of the Conductor Query Builder Clause Group Backbone View
			Conductor_Query_Builder_Clause_Group_View = new conductor_query_builder.Backbone.Views.Query_Builder_Clause_Group( view_arguments );
			conductor_query_builder.Backbone.instances.views.clauses.push( Conductor_Query_Builder_Clause_Group_View );

			// Setup the model ID
			Conductor_Query_Builder_Clause_Group_Model.setID( Conductor_Query_Builder_Clause_Group_View );

			// If this clause group supports actions
			if ( view_arguments.flags.actions ) {
				// Create a new instance of the Conductor Query Builder Actions Backbone View
				Conductor_Query_Builder_Clause_Group_Actions_View = new conductor_query_builder.Backbone.Views.Query_Builder_Actions( _.defaults( {
					type: 'query-builder-actions',
					clause_group_type: view_arguments.type,
				}, _.clone( view_arguments ) ) ); // TODO: Remove _.clone() if not necessary
				conductor_query_builder.Backbone.instances.views.clause_action_buttons.push( Conductor_Query_Builder_Clause_Group_Actions_View );

				// Attach the Conductor Query Builder Actions Backbone View to the Conductor Query Builder Clause Group Backbone View
				Conductor_Query_Builder_Clause_Group_View.views.set( Conductor_Query_Builder_Clause_Group_Actions_View.el_selector, Conductor_Query_Builder_Clause_Group_Actions_View, {
					// No DOM modifications
					silent: true
				} );
			}

			// Attach the Conductor Query Builder Clause Group Backbone View to the Conductor Query Builder Backbone View
			Conductor_Query_Builder_View.views.add( Conductor_Query_Builder_Clause_Group_View.el_selector, Conductor_Query_Builder_Clause_Group_View );

			// Return the view
			return Conductor_Query_Builder_Clause_Group_View;
		},
		/**
		 * This function enables all of the action buttons.
		 */
		enableActionButtons: function() {
			// Set the flag
			this.flags.action_buttons_enabled = true;

			// Enable all action buttons
			this.enableActionButton( this.action_button_selector );
		},
		/**
		 * This function enables a single action button.
		 */
		enableActionButton: function( selector ) {
			var $action_button = this.$el.find( selector ),
				clause_group_type = this.options.clause_group_type,
				sub_clause_group_view_instances = conductor_query_builder.Backbone.instances.views.sub_clauses,
				sub_clause_group_view_index = sub_clause_group_view_instances.map( function( view ) { return view.options.type; } ).indexOf( clause_group_type ),
				sub_clause_group_view = ( sub_clause_group_view_index !== -1 ) ? sub_clause_group_view_instances[sub_clause_group_view_index] : false,
				sub_clause_group_view_meta = sub_clause_group_view && sub_clause_group_view.getMeta(),
				sub_clause_group_view_type = sub_clause_group_view && sub_clause_group_view.options.type,
				sub_clause_group_view_config = sub_clause_group_view && sub_clause_group_view.getConfig(),
				sub_clause_group_config = conductor_query_builder.clauses[clause_group_type] && conductor_query_builder.clauses[clause_group_type]['config'];

			// If we don't have an action button
			if ( ! $action_button.length ) {
				// Set the selector (replace underscores with hyphens)
				selector = selector.replace( '_', '-' );

				$action_button = this.$el.find( selector );
			}

			// If we have an action button
			if ( $action_button.length ) {
				// If we're enabling more than one action button (i.e. all of them)
				if ( $action_button.length > 1 ) {
					// Loop through each action button
					$action_button.each( function() {
						var $this = $( this ),
							type = $this.data( 'clause-group-type' ),
							action_button_clause_group_config = conductor_query_builder.clauses[type] && conductor_query_builder.clauses[type]['config'],
							limit = ( action_button_clause_group_config && action_button_clause_group_config['limit'] ) ? parseInt( action_button_clause_group_config['limit'], 10 ) : -1,
							clause_group_length = Conductor_Query_Builder_Clause_Group_Collection.where( { type: type } ).length,
							can_enable_button,
							is_button_disabled = false;

						// If we don't have a clause group length, check in meta
						if ( clause_group_length === 0 ) {
							// If we have meta for this clause group
							if ( conductor_query_builder.meta[type] ) {
								clause_group_length = conductor_query_builder.meta[type].length;
							}
						}

						// Set the can enable button flag
						can_enable_button = ( limit === -1 || clause_group_length < limit );

						// If we can enable this button
						if ( can_enable_button ) {
							// Loop through the sub-clause group view config
							_.find( sub_clause_group_view_config, function( config, parameter ) {
								// If the sub-clause group view config has clauses and the sub-clause group view meta value contains this parameters
								if ( config.clauses && sub_clause_group_view_meta['parameters'] && ( ( _.isArray( sub_clause_group_view_meta['parameters'] ) && sub_clause_group_view_meta['parameters'].indexOf( parameter ) !== -1 ) || sub_clause_group_view_meta['parameters'] === parameter ) ) {
									// Loop through the sub-clause group view clauses config data
									_.find( config.clauses, function ( clause_config, clause ) {
										// If this clause matches the action button type
										if ( clause === type ) {
											// If this clause should be disabled
											if ( clause_config.disabled ) {
												// Set the disabled button flag
												is_button_disabled = true;
											}
										}

										return is_button_disabled;
									} );
								}

								return is_button_disabled;
							} );

							// If this button isn't disabled and we have action buttons sub-clause group config for this action button
							if ( ! is_button_disabled && sub_clause_group_config && sub_clause_group_config['action_buttons'] && sub_clause_group_config['action_buttons'][type] ) {
								// Loop through the columns for this action button config
								_.find( sub_clause_group_config['action_buttons'][type]['columns'], function( config, column ) {
									// Switch based on the column
									switch ( column ) {
										// Parameters
										case 'parameters':
											// Loop through the config
											_.find( config, function( action_button_config, parameter ) {
												// If the sub-clause group view meta value contains this parameters
												if ( sub_clause_group_view_meta['parameters'] && ( ( _.isArray( sub_clause_group_view_meta['parameters'] ) && sub_clause_group_view_meta['parameters'].indexOf( parameter ) !== -1 ) || sub_clause_group_view_meta['parameters'] === parameter ) ) {
													// If this action button should be disabled
													if ( action_button_config.disabled ) {
														// Set the disabled button flag
														is_button_disabled = true;
													}
												}

												return is_button_disabled;
											} );
										break;
									}

									return is_button_disabled;
								} );
							}
						}

						// If this button is disabled
						if ( is_button_disabled && can_enable_button ) {
							// Reset the enable button flag
							can_enable_button = false;
						}

						// If we can enable this button
						if ( can_enable_button ) {
							// Enable the action button
							$this.prop( 'disabled', false );
						}
						// Otherwise if we can't enable this button, the button is disabled, and the button is currently enabled
						else if ( ! can_enable_button && is_button_disabled && ! $this.prop( 'disabled' ) ) {
							// Disable the action button
							$this.prop( 'disabled', 'disabled' );
						}
					} );
				}
				// Otherwise we're just enabling one action button
				else {
					// Enable the action button
					$action_button.prop( 'disabled', false );
				}

			}
		},
		/**
		 * This function disables all of the action buttons.
		 */
		disableActionButtons: function() {
			// Reset the flag
			this.flags.action_buttons_enabled = false;

			// Enable all action buttons
			this.disableActionButton( this.action_button_selector )
		},
		/**
		 * This function disables a single action button.
		 */
		disableActionButton: function( selector ) {
			var $action_button = this.$el.find( selector );

			// If we have an action button
			if ( $action_button.length ) {
				// Disable the action button
				$action_button.prop( 'disabled', true );
			}
		},
		/**
		 * This function tests the current query
		 */
		testQuery: function( event ) {
			// Prevent default
			event.preventDefault();

			// TODO
		},
		/**
		 * This function toggles the query mode between simple and advanced.
		 */
		// TODO: Currently this function is only setup to toggle between two types, expand it to allow for multiple types to be toggled
		toggleQueryBuilderMode: function( event ) {
			var $this = $( event.currentTarget ),
				previous_mode = conductor_query_builder.user.settings['query-builder'].mode.value,
				new_mode;

			// Prevent default
			event.preventDefault();

			// Find the new mode
			new_mode = _.find( conductor_query_builder.user.settings['query-builder'].mode.values, function ( mode, index ) {
				return conductor_query_builder.user.settings['query-builder'].mode.values.indexOf( previous_mode ) !== index;
			} );

			// Toggle the button label to the new mode label
			$this.html( $this.data( new_mode + '-label' ) );

			// Set the new mode in the user's settings
			window.setUserSetting( conductor_query_builder.user.settings['query-builder'].mode.name, new_mode );

			// Set the new mode in the global data
			conductor_query_builder.user.settings['query-builder'].mode.value = new_mode;

			// TODO: Transition to "conductor-query-builder-{event}" event name
			// Trigger the "conductor-qb-toggle-query-builder-mode" on the view element
			this.$el.trigger( 'conductor-qb-toggle-query-builder-mode', [ new_mode, previous_mode ] );

			// TODO: Transition to "conductor-query-builder-{event}" event name
			// Trigger the "conductor-qb-toggle-query-builder-mode" on the view
			this.trigger( 'conductor-qb-toggle-query-builder-mode', new_mode, previous_mode );

			// Call the Conductor Query Builder Backbone View toggle query builder mode function
			Conductor_Query_Builder_View.toggleQueryBuilderMode( new_mode, previous_mode );
		}
	} );

	/**
	 * Conductor Query Builder Clause Group Backbone View
	 */
	conductor_query_builder.Backbone.Views.Query_Builder_Clause_Group = wp.Backbone.View.extend( {
		className: 'conductor-qb-meta-box-query-builder-clause-group-inner',
		el_selector: '',
		el_selector_prefix: '.conductor-qb-meta-box-query-builder-',
		el_selector_suffix: '-groups',
		select2_selector: '.conductor-qb-select2',
		$select2: false,
		template: wp.template( 'conductor-qb-meta-box-query-builder-clause-group' ),
		// Events
		events: {
			'click .conductor-qb-remove-action-button': function( event ) {
				var $this = $( event.currentTarget ),
					selector = '#' + $this.data( 'action-button-id' ),
					$action_button = $( selector ),
					clause_group_config,
					type,
					limit;

				// If we don't have an action button
				if ( ! $action_button.length ) {
					// Set the selector (replace underscores with hyphens)
					selector = selector.replace( '_', '-' );

					$action_button = $( selector );
				}

				// If we have an action button
				if ( $action_button.length ) {
					// Grab the type
					type = $action_button.data( 'clause-group-type' );

					// Grab the config data
					clause_group_config = conductor_query_builder.clauses[type] && conductor_query_builder.clauses[type]['config'];

					// Setup the limit
					limit = ( clause_group_config && clause_group_config['limit'] ) ? parseInt( clause_group_config['limit'], 10 ) : -1;
				}

				// If the action buttons are enabled and this clause group should be limited but we haven't reached the limit
				if ( Conductor_Query_Builder_Actions_View.flags.action_buttons_enabled && limit && this.getCurrentCount() <= limit ) {
					// Enable this button
					Conductor_Query_Builder_Actions_View.enableActionButton( selector );

					// Loop through clause group action Backbone Views
					_.each( conductor_query_builder.Backbone.instances.views.clause_action_buttons, function ( view ) {
						// Enable this button
						view.enableActionButton( selector );
					} );
				}

				// Remove clause group
				this.removeClauseGroup( event );
			},
			// TODO: Future: If we only have one possible parameter, we need to disable this button and ensure this logic doesn't work
			// TODO: ^ See enable action buttons logic
			'click .conductor-qb-add-action-button': 'addSubClauseGroup' // Add sub-clause group
		},
		/**
		 * This function runs on initialization of the view.
		 */
		initialize: function( options ) {
			// Bind "this" to all functions
			_.bindAll(
				this,
				'render',
				'addSubClauseGroup',
				'removeSubClauseGroup',
				'removeClauseGroup',
				'select2Change',
				'getCurrentCount'
			);

			// Create the flag property if we don't already have one
			if ( ! options.flags ) {
				options.flags = {};
			}

			// If we have a type
			if ( options.type ) {
				// Set the el_selector
				this.el_selector = this.el_selector_prefix + options.type + this.el_selector_suffix;

				// If we don't have a valid selector, try to replace underscores with hyphens
				if ( ! $( this.el_selector ).length ) {
					// Set the el_selector (replace underscores with hyphens)
					this.el_selector = this.el_selector_prefix + options.type.replace( '_', '-' ) + this.el_selector_suffix;
				}

				// Set the el_selector on the model
				this.model.set( 'el_selector', this.el_selector );
			}

			// Set the count
			this.options.count = this.getCurrentCount( true );

			// If we don't have the sub_clause_groups flag set, set it to true now
			if ( _.isUndefined( this.options.flags.sub_clause_groups ) ) {
				this.options.flags.sub_clause_groups = true;
			}

			// If we don't have the remove flag set, set it to true now
			if ( _.isUndefined( this.options.flags.remove ) ) {
				this.options.flags.remove = true;
			}

			// If we don't have a re_init_values flag set, set it to false now
			if ( _.isUndefined( this.options.flags.re_init_values ) ) {
				this.options.flags.re_init_values = false;
			}

			// If we have a template, use that template instead of the default
			if ( options.template ) {
				this.template = options.template;
			}

			/*
			 * Event Listeners
			 */
			this.listenTo( this, 'ready', this.addSubClauseGroup ); // Add an initial sub-clause group view
		},
		/**
		 * This function adds a sub-clause group view to this view.
		 *
		 * If there is no event data, it is expected that this is the default sub-clause group.
		 */
		addSubClauseGroup: function( event ) {
			// Defaults
			event = event || false;

			var has_event = event && !_.isEmpty( event ),
				$this = ( has_event && event.currentTarget ) ? $( event.currentTarget ) : false,
				model_arguments,
				Conductor_Query_Builder_Sub_Clause_Group_Model,
				view_arguments,
				Conductor_Query_Builder_Sub_Clause_Group_View;

			// Bail if we have an event and this view does not allow sub-clauses (this allows the initial sub-clause view to be added)
			if ( has_event && ! this.options.flags.sub_clause_groups ) {
				return;
			}

			// If we have an event, prevent default
			if ( has_event ) {
				event.preventDefault();
			}

			// Setup the Backbone Model arguments
			model_arguments = {
				columns: this.options.columns,
				flags: this.options.flags,
				operators: this.options.operators,
				parameters: this.options.parameters,
				type: this.options.type,
				shortcode: this.options.shortcode
				//values: this.options.values
			};

			// Create a new instance of the Conductor Query Builder Sub-Clause Group Backbone Model
			Conductor_Query_Builder_Sub_Clause_Group_Model = new conductor_query_builder.Backbone.Models.Query_Builder_Sub_Clause_Group( model_arguments );
			Conductor_Query_Builder_Sub_Clause_Group_Collection.add( Conductor_Query_Builder_Sub_Clause_Group_Model );
			conductor_query_builder.Backbone.instances.models.sub_clauses.push( Conductor_Query_Builder_Sub_Clause_Group_Model );

			// Setup the Backbone View arguments
			view_arguments = _.clone( Conductor_Query_Builder_Sub_Clause_Group_Model.attributes );
			view_arguments.model = Conductor_Query_Builder_Sub_Clause_Group_Model;

			// Create a new instance of the Conductor Query Builder Sub-Clause Group Backbone View
			Conductor_Query_Builder_Sub_Clause_Group_View = new conductor_query_builder.Backbone.Views.Query_Builder_Sub_Clause_Group( view_arguments );
			conductor_query_builder.Backbone.instances.views.sub_clauses.push( Conductor_Query_Builder_Sub_Clause_Group_View );

			// Setup the model ID
			Conductor_Query_Builder_Sub_Clause_Group_Model.setID( Conductor_Query_Builder_Sub_Clause_Group_View );

			// Attach the Conductor Query Builder Sub-Clause Group Backbone View to this view
			this.views.add( Conductor_Query_Builder_Sub_Clause_Group_View.el_selector, Conductor_Query_Builder_Sub_Clause_Group_View );

			// Add the sub-clause group el_selector to this clause group view if it's not set or it' different
			if ( ! this.options.sub_clause_el_selector || this.options.sub_clause_el_selector !== Conductor_Query_Builder_Sub_Clause_Group_View.el_selector ) {
				this.options.sub_clause_el_selector = Conductor_Query_Builder_Sub_Clause_Group_View.el_selector;
			}
		},
		/**
		 * This function removes a sub-clause group view from this view.
		 */
		removeSubClauseGroup: function( event, sub_clause ) {
			// Prevent default
			event.preventDefault();

			// Bail if this view does not allow sub-clauses or this is the default sub-clause view
			if ( ! this.options.flags.sub_clause_groups || sub_clause.options.default ) {
				return;
			}

			// Remove the sub-clause view
			sub_clause.remove();

			// Preview query
			Conductor_Query_Builder_Wrapper_View.previewQuery();
		},
		/**
		 * This function renders the view.
		 */
		render: function( re_render ) {
			// Defaults
			re_render = re_render || false;

			// Set the count
			this.options.count = ( re_render ) ? ( this.options.count - 1 ) : this.options.count;

			// If we're re-rendering this clause group
			if ( re_render ) {
				// Set the model ID
				this.model.setID( this );
			}

			// Call (apply) the default wp.Backbone.View render function
			wp.Backbone.View.prototype.render.apply( this, arguments );

			return this;
		},
		/**
		 * This function removes the clause group view from the Conductor Query Builder Backbone View.
		 */
		removeClauseGroup: function( event, force_remove ) {
			// Defaults
			force_remove = force_remove || false;

			var has_event = event && ! _.isEmpty( event );

			// Bail if this view should does not allow for removal
			if ( ! this.options.flags.remove && ! force_remove ) {
				return;
			}

			// If we have an event, prevent default
			if ( has_event ) {
				event.preventDefault();
			}

			// Remove this view
			this.remove( force_remove );

			// Preview query
			Conductor_Query_Builder_Wrapper_View.previewQuery();
		},
		/**
		 * This function removes the view.
		 */
		remove: function( force_remove ) {
			// Defaults
			force_remove = force_remove || false;

			var result,
				clause_group_view_instances = conductor_query_builder.Backbone.instances.views.clauses,
				clause_action_buttons_view_instances = conductor_query_builder.Backbone.instances.views.clause_action_buttons,
				view_index,
				clause_group_type = this.model.get( 'type' ),
				clause_group_views,
				count = this.options.count,
				sub_views;

			// Bail if this view should does not allow for removal
			if ( ! this.options.flags.remove && ! force_remove ) {
				return;
			}

			// Set the removing flag
			if ( ! this.options.flags.removing ) {
				this.options.flags.removing = true;
			}

			// Grab the sub-views
			sub_views = this.views.all();

			// If we have sub-views
			if ( sub_views ) {
				// Loop through the sub-views
				_.each( sub_views, function( sub_view ) {
					var sub_view_index;

					// If this is a query builder actions view
					if ( sub_view.options.type === 'query-builder-actions' ) {
						// Grab the view index from instances
						sub_view_index = clause_action_buttons_view_instances.map( function( the_sub_view ) { return the_sub_view.cid; } ).indexOf( sub_view.cid );

						// If we have a view index
						if ( sub_view_index !== -1 ) {
							// Remove this view from instances
							clause_action_buttons_view_instances.splice( sub_view_index, 1 );
						}
					}
				} )
			}

			// Remove the model from the collection
			Conductor_Query_Builder_Clause_Group_Collection.remove( this.model );

			// Call (apply) the default wp.Backbone.View remove function
			result = wp.Backbone.View.prototype.remove.apply( this, arguments );

			// Grab the view index from instances
			view_index = clause_group_view_instances.map( function( view ) { return view.cid; } ).indexOf( this.cid );

			// If we have a view index
			if ( view_index !== -1 ) {
				// Remove this view from instances
				clause_group_view_instances.splice( view_index, 1 );
			}

			// If we have valid counts
			if ( count !== -1 && conductor_query_builder.meta[clause_group_type][count] ) {
				// Remove meta
				conductor_query_builder.meta[clause_group_type].splice( count, 1 );
			}

			// Grab the clause group views associated with this clause group type
			clause_group_views = clause_group_view_instances.filter( function( view ) { return view.options.type === clause_group_type; } );

			// If we have clause group views
			if ( clause_group_views.length ) {
				// Loop through all of the clause groups
				_.each( clause_group_views, function ( clause_group_view ) {
					// If this clause group view isn't currently being removed and it's after the removed clause group
					if ( ! clause_group_view.options.flags.removing && clause_group_view.options.count > count ) {
						// Re-render the clause group view
						clause_group_view.render( true );

						// Set the re-rendering flag
						clause_group_view.options.flags.re_rendering = true;

						// If we have a sub-clause group el_selector
						if ( clause_group_view.options.sub_clause_el_selector ) {
							// Loop through all of the sub-clause groups in the parent clause group
							_.each( clause_group_view.views.get( clause_group_view.options.sub_clause_el_selector ), function ( sub_clause_group_view ) {
								// Re-render this view
								sub_clause_group_view.render( true );
							} );
						}

						// Reset the re-rendering flag
						clause_group_view.options.flags.re_rendering = false;
					}
				} );
			}

			return result;
		},
		/**
		 * This function is triggered on Select2 change events.
		 */
		select2Change: function( event, sub_clause_group_view ) {
			var $this = $( event.currentTarget ),
				$parameters_select = sub_clause_group_view.$select2.filter( '[data-type="parameters"]' ),
				$parameters_select_options = $parameters_select.find( 'option' ),
				$parameters_selected_option = $parameters_select_options.filter( ':selected' ),
				selected_parameter = $parameters_selected_option.data( 'parameter' ),
				selected_parameter_field = $parameters_selected_option.data( 'field' ),
				$operators_select = sub_clause_group_view.$select2.filter( '[data-type="operators"]' ),
				$operators_select_options = $operators_select.find( 'option' ),
				operators_select_value = $operators_select.val(),
				$values_select = sub_clause_group_view.$select2.filter( '[data-type="values"]' ),
				$values_select_options = $values_select.find( 'option' ),
				values_select_value = $values_select.val(),
				value = $this.val(),
				type = $this.data( 'type' ),
				select_type = $this.data( 'select-type' ),
				parameters,
				operators,
				values,
				columns = this.model.get( 'columns' ),
				clause_type = sub_clause_group_view.options.type,
				sub_clause_group_view_meta = sub_clause_group_view.getMeta(),
				sub_clause_group_view_config = sub_clause_group_view.getConfig();

			// Switch based on type
			switch ( type ) {
				// Parameters
				case 'parameters':
					// Reset the re_init_values flag
					this.options.flags.re_init_values = false;

					// Grab the parameters
					parameters = this.getParameterData( value, selected_parameter, selected_parameter_field, select_type );

					// Grab the operators
					operators = this.getOperators( parameters );

					// Bail if we don't have operators, an operators select element, or a value
					if ( ! operators || ! operators.length || ! $operators_select.length || ! value ) {
						return;
					}

					// Update the operators select element
					this.updateOperatorsSelect( $operators_select, $operators_select_options, operators_select_value, operators );

					// Enable the operators select element
					if ( $operators_select.prop( 'disabled' ) ) {
						$operators_select.prop( 'disabled', false );
					}

					// Re-initialize Select2 on the operators select element
					sub_clause_group_view.initializeSelect2( [ 'operators' ] );

					// Grab the values
					values = this.getValues( value, select_type, $parameters_selected_option );

					// If we have values
					if ( columns && columns.values && values ) {
						// Update the values select element
						this.options.flags.re_init_values = this.updateValuesSelect( $values_select, $values_select_options, values_select_value, values );

						// Re-initialize Select2 on the values select element
						sub_clause_group_view.initializeSelect2( [ 'values' ] );
					}
				break;

				// Operators
				case 'operators':
					// If this clause group supports values
					if ( columns && columns.values ) {
						// If the operator isn't a Boolean operator
						if ( $values_select.prop( 'disabled' ) && conductor_query_builder.operators[value] && ( typeof conductor_query_builder.operators[value] === 'string' || ( conductor_query_builder.operators[value].type && conductor_query_builder.operators[value].type !== 'bool' ) ) ) {
							// Enable the values select element
							$values_select.prop( 'disabled', false );
						}
						// Otherwise if the operator is a Boolean operator
						else if ( ! $values_select.prop( 'disabled' ) && conductor_query_builder.operators[value] && typeof conductor_query_builder.operators[value] !== 'string' && conductor_query_builder.operators[value].type && conductor_query_builder.operators[value].type === 'bool' ) {
							// Reset the values select element value
							$values_select.val( null ).trigger( 'change' );

							// Disable the values select element
							$values_select.prop( 'disabled', true );
						}
					}

					// TODO: In a future version, value select elements should be adjusted based on operator
					// TODO: For BETWEEN/NOT BETWEEN we need to set maximumSelectionLength to the 'limit' value

					// If this operator allows for multiple values but the values select doesn't currently
					/*if ( operators_selected_option_multiple && ( ! $values_select.prop( 'multiple' ) || ! values_Select2.options.get( 'multiple' ) ) ) {
						// Set the multiple property on the values select
						$values_select.prop( 'multiple', true );

						// Append [] to the name attribute on the values select
						if ( values_select_name.lastIndexOf( multiple_attr ) === -1 || ( values_select_name.lastIndexOf( multiple_attr ) + multiple_attr.length ) !== values_select_name.length ) {
							$values_select.attr( 'name', values_select_name + multiple_attr );
						}

						// Re-initialize Select2 (@see https://github.com/select2/select2/issues/3347)
						sub_clause_group_view.initializeSelect2( [ 'values' ] );
					}
					// Otherwise if this operator doesn't allow for multiple values but the values select does currently
					else if ( ! operators_selected_option_multiple && ( $values_select.prop( 'multiple' ) || values_Select2.options.get( 'multiple' ) ) ) {
						// Reset the multiple property on the values select
						$values_select.prop( 'multiple', false );

						// Remove [] from the name attribute on the values select
						if ( values_select_name.lastIndexOf( multiple_attr ) !== -1 && ( values_select_name.lastIndexOf( multiple_attr ) + multiple_attr.length ) === values_select_name.length ) {
							$values_select.attr( 'name', values_select_name.substring( 0, ( values_select_name.length - multiple_attr.length ) ) );
						}

						// Re-initialize Select2 (@see https://github.com/select2/select2/issues/3347)
						sub_clause_group_view.initializeSelect2( [ 'values' ] );
					}*/
				break;
			}
		},
		/**
		 * This function grabs the current view count for this clause group type.
		 */
		getCurrentCount: function( zero_index ) {
			// Defaults
			zero_index = zero_index || false;

			var count;

			// Grab the number of existing clause groups of this type
			count = Conductor_Query_Builder_Clause_Group_Collection.where( { type: this.options.type } ).length;

			// Account for the zero index
			count = ( zero_index && count !== 0 ) ? ( count - 1 ) : count;

			return count;
		},
		/**
		 * This function returns the FROM sub-clause parameters based on the FROM sub-clause config.
		 */
		getFromSubClauseParameters: function( config, meta, clause_type, clause_type_field, select_type ) {
			var parameters = false;
			
			// Bail if we don't have config data or we don't have meta data
			if ( ! config || ! meta ) {
				return;
			}

			// Loop through the sub-clause group view config
			_.find( config, function( config, parameter ) {
				// If the sub-clause group view config has clauses and the sub-clause group view meta value contains this parameter
				if ( config.clauses && meta['parameters'] && ( ( _.isArray( meta['parameters'] ) && meta['parameters'].indexOf( parameter ) !== -1 ) || meta['parameters'] === parameter ) ) {
					// Loop through the sub-clause group view clauses config data
					_.find( config.clauses, function ( clause_config, clause ) {
						// If this clause matches the select type and we have parameters
						if ( clause === select_type && clause_config['columns'] && clause_config['columns']['parameters'] ) {
							// Set the parameters
							parameters = clause_config['columns']['parameters'];

							return true;
						}

						return false;
					} );
				}

				return false;
			} );

			return parameters;
		},
		/**
		 * This function grabs parameters based on parameters.
		 */
		getParameterData: function( value, clause_type, clause_type_field, select_type ) {
			var parameters = ( value && typeof value === 'string' && conductor_query_builder.parameters[value] ) ? conductor_query_builder.parameters[value] : false,
				has_possible_field_parameters = ( typeof conductor_query_builder.parameters[select_type] !== 'undefined' ),
				possible_parameters;

			// If we don't have parameters, check the selected parameter
			if ( ! parameters || has_possible_field_parameters || ( ! parameters.operators && clause_type && select_type && ( conductor_query_builder.parameters[clause_type] || conductor_query_builder.parameters[select_type] ) ) ) {
				// Grab the parameters for the selected parameter
				parameters = ( ! parameters && conductor_query_builder.parameters[clause_type] ) ? conductor_query_builder.parameters[clause_type] : parameters;

				// If we don't have parameters, check the select type field
				if ( ! parameters || has_possible_field_parameters || ( ! parameters.operators && clause_type !== select_type && conductor_query_builder.parameters[select_type] ) ) {
					possible_parameters = conductor_query_builder.parameters[select_type];
				}

				// If we have possible parameters but no operators
				if ( possible_parameters && _.isObject( possible_parameters ) && ! possible_parameters.operators ) {
					// If the field was found in the parameters
					if ( possible_parameters.fields && possible_parameters.fields[clause_type_field] ) {
						// Grab the parameters for the selected parameter field
						parameters = possible_parameters.fields[clause_type_field];
					}
				}
			}

			return parameters;
		},
		/**
		 * This function returns parameters.
		 */
		getParameters: function( parameters ) {
			return _.isObject( parameters ) ? _.keys( parameters ) : [ parameters ];
		},
		/**
		 * This function grabs operators from parameters.
		 */
		getOperators: function( parameters ) {
			var operators;

			// If parameters is an object, grab the operators, otherwise we'll assume it's a string (convert it to an array)
			operators = ( _.isObject( parameters ) && parameters.operators ) ? parameters.operators: [ parameters ];
			operators = ( _.isObject( operators ) ) ? _.values( operators ) : operators;

			return operators;
		},
		/**
		 * This function gets values.
		 */
		getValues: function( parameter, select_type, $parameters_selected_option ) {
			// Grab the values for the selected parameter
			var values = ( typeof parameter === 'string' ) ? conductor_query_builder.values[parameter] : false,
				parameters_selected_option_values = ( $parameters_selected_option && $parameters_selected_option.length && $parameters_selected_option.data( 'values' ) ) ? JSON.parse( JSON.stringify( $parameters_selected_option.data( 'values' ) ) ) : false,
				select_type_all_values;

			// If we don't have values and we have parameters selected option values
			if ( ! values && parameters_selected_option_values ) {
				// Set the values
				values = parameters_selected_option_values;
			}

			// If we don't have values, check the values data
			if ( ! values && select_type && conductor_query_builder.values[select_type] ) {
				// Grab the values for the selected type
				values = conductor_query_builder.values[select_type];

				// If we have values, values is an object, and
				if ( values && _.isObject( values ) ) {
					// If values has the parameter property
					if ( values.hasOwnProperty( parameter ) ) {
						// Grab the values from the selected type data
						values = values[parameter];
					}
					// Otherwise values doesn't have the parameter property
					else {
						// Reset values
						values = [];
					}
				}
			}

			// If we have "all" values
			if ( conductor_query_builder.values.all && conductor_query_builder.values.all.length ) {
				// Merge the "all" values with the values
				values = values.concat( conductor_query_builder.values.all );
			}

			// Grab the "all" values for this select type
			select_type_all_values = conductor_query_builder.values[select_type] && conductor_query_builder.values[select_type]['all'];

			// If we have "all" values for this select type
			if ( select_type_all_values && select_type_all_values.length ) {
				// Merge the "all" select type values with the values
				values = values.concat( select_type_all_values );
			}

			return values;
		},
		/**
		 * This function updates the parameters select element based on parameters.
		 */
		// TODO: Future: Adjust this logic to match updateOperatorsSelect() (for disabled selected value)
		updateParametersSelect: function( $parameters_select, $parameters_select_options, parameters_select_value, parameters, silent ) {
			// Defaults
			silent = silent || false;
			parameters = ( ! parameters ) ? [] : parameters;

			var is_selected_parameter_disabled = ( parameters_select_value && typeof parameters_select_value === 'string' && parameters.length && parameters.indexOf( parameters_select_value ) === -1 ),
				is_all_selected_parameters_disabled = ( parameters_select_value && typeof parameters_select_value === 'string' && is_selected_parameter_disabled ),
				disabled_selected_parameters = [],
				clause_type = this.options.type,
				clause_type_parameters = ( conductor_query_builder.clauses[clause_type] && conductor_query_builder.clauses[clause_type].parameters ) ? conductor_query_builder.clauses[clause_type].parameters : false,
				sub_clause_group_view_instances = conductor_query_builder.Backbone.instances.views.sub_clauses,
				from_sub_clause_group_view_index = sub_clause_group_view_instances.map( function( view ) { return view.options.type; } ).indexOf( 'from' ),
				from_sub_clause_group_view = ( from_sub_clause_group_view_index !== -1 ) ? sub_clause_group_view_instances[from_sub_clause_group_view_index] : false,
				from_sub_clause_group_view_meta = from_sub_clause_group_view && from_sub_clause_group_view.getMeta(),
				from_clause_type_parameters = ( conductor_query_builder.clauses['from'] && conductor_query_builder.clauses['from'].parameters ) ? conductor_query_builder.clauses['from'].parameters : false;

			// If we we have a selected value, the selected value is an array, and not all of the current selected values are disabled
			if ( parameters_select_value && _.isArray( parameters_select_value ) && ! is_all_selected_parameters_disabled ) {
				// Loop through the selected value
				_.each( parameters_select_value, function( parameter ) {
					// If this parameter is disabled
					if ( parameters.length && parameters.indexOf( parameter ) === -1 ) {
						// Add this parameter to the list of disabled selected parameters
						disabled_selected_parameters.push( parameter );
					}
				} );

				// If the all of the selected values are disabled
				if ( disabled_selected_parameters.length === parameters_select_value.length ) {
					// Set the all selected parameters disabled flag
					is_all_selected_parameters_disabled = true;
				}
			}

			// If we have a selected value and all of the current selected values are disabled
			if ( parameters_select_value && is_all_selected_parameters_disabled ) {
				// Set the value
				$parameters_select.val( '' );
			}

			// Loop through all of the parameter option elements
			$parameters_select_options.each( function() {
				var $this = $( this ),
					multiple = $this.prop( 'multiple' ),
					value = $this.val(),
					disabled = ( parameters.length && parameters.indexOf( value ) === -1 );

				// Set the disabled property (if this value isn't found in the list of parameters)
				$this.prop( 'disabled', disabled );
			} );

			// If we have a selected value and all of the current selected values are disabled
			if ( parameters_select_value && is_all_selected_parameters_disabled ) {
				// Select the first enabled option
				$parameters_select.val( $parameters_select_options.not( ':disabled' ).first().val() );

				// If this isn't a silent event
				if ( ! silent ) {
					// Trigger the change event
					$parameters_select.trigger( 'change' );
				}
			}
		},
		/**
		 * This function updates the operators select element based on operators.
		 */
		updateOperatorsSelect: function( $operators_select, $operators_select_options, operators_select_value, operators, silent ) {
			// Defaults
			silent = silent || false;

			// Loop through all of the operator option elements
			$operators_select_options.each( function() {
				var $this = $( this ),
					multiple = $this.prop( 'multiple' ),
					value = $this.val();

				// Set the disabled property (if this value isn't found in the list of operators)
				$this.prop( 'disabled', ( operators.indexOf( value ) === -1 ) );
			} );

			// If we have a selected value and the current selected value is disabled
			if ( operators_select_value && $operators_select_options.filter( '[value="' + operators_select_value + '"]' ).prop( 'disabled' ) ) {
				// Select the first valid option
				$operators_select.val( $operators_select_options.not( ':disabled' ).first().val() );

				// If this isn't a silent event
				if ( ! silent ) {
					// Trigger the change event
					$operators_select.trigger( 'change' );
				}
			}
		},
		/**
		 * This function updates the operators select element based on operators.
		 */
		updateValuesSelect: function( $values_select, $values_select_options, value, values, silent ) {
			// TODO: For BETWEEN/NOT BETWEEN we need to set maximumSelectionLength to the 'limit' value

			// Defaults
			silent = silent || false;

			var the_value = $values_select.val(),
				re_init_values = false;

			// Loop through values and add options
			_.each( values, function ( value_data, value ) {
				var option,
					$option_for_value,
					option_for_value_label,
					value_data_option_label;

				// Grab the value
				value = ( ! _.isObject( value_data ) ) ? value_data : value;
				value = ( _.isObject( value_data ) && value_data.hasOwnProperty( 'value' ) ) ? value_data.value : value;

				// Grab the option for the value
				$option_for_value = $values_select_options.filter( '[value="' + value + '"]' );

				// Grab the label from the option
				option_for_value_label = ( $option_for_value.length ) ? $option_for_value.text() : '';

				// If we don't have an option for this value
				if ( ! $option_for_value.length ) {
					// TODO: UnderscoreJS template here

					// Build the option
					option = '<option value="' + value + '">';
					option += ( ! _.isObject( value_data ) || ! value_data.hasOwnProperty( 'label' ) ) ? value : value_data.label;
					option += '</option>';

					// Append the new option
					$values_select.append( option );
				}
				// Otherwise we have an option for this value
				else {
					// Grab the value data option label
					value_data_option_label = ( ! _.isObject( value_data ) || ! value_data.hasOwnProperty( 'label' ) ) ? value : value_data.label;

					// If the option label doesn't match this value
					if ( option_for_value_label !== value_data_option_label ) {
						// Update the label
						$option_for_value.text( value_data_option_label );

						// Set the re-init flag
						if ( ! re_init_values ) {
							re_init_values = true;
						}
					}
				}
			} );

			// Loop through options (options that existed prior to appending new options above)
			$values_select_options.each( function() {
				var $this = $( this ),
					option_value = $this.val(),
					index;

				// Determine the index of this option value
				index = _.findIndex( values, function ( value ) {
					return ( typeof value === 'string' ) ? value === option_value : value.value === option_value;
				} );

				// If this option shouldn't exist
				if ( index === -1 ) {
					// Remove this element
					$this.remove();

					// If this option value matches a selected value
					if ( value && ( ( typeof value === 'string' && option_value === value ) || ( typeof value !== 'string' && value.length && value.indexOf( option_value ) !== -1 ) ) ) {
						// String
						if ( typeof value === 'string' ) {
							value = '';
						}
						// Array
						else {
							value.splice( value.indexOf( option_value ), 1 )
						}
					}
				}
			} );

			// If the current value does not match the actual value
			if ( ( ! the_value && value ) || ( value && ( ( typeof the_value === 'string' && typeof value === 'string' && the_value !== value ) || ( typeof value !== 'string' && value.length && ! _.isEqual( the_value, value ) ) ) ) ) {
				// Update the select element
				$values_select.val( value );

				// If this isn't a silent event
				if ( ! silent ) {
					// Trigger the change event
					$values_select.trigger( 'change' );
				}
			}

			return re_init_values;
		}
	} );

	/**
	 * Conductor Query Builder Sub-Clause Group Backbone View
	 */
	conductor_query_builder.Backbone.Views.Query_Builder_Sub_Clause_Group = wp.Backbone.View.extend( {
		className: 'conductor-qb-meta-box-query-builder-sub-clause-group-inner',
		el_selector: '',
		el_selector_prefix: '.conductor-qb-meta-box-query-builder-',
		el_selector_suffix: '-sub-clause-groups',
		select2_selector: '.conductor-qb-select2',
		$select2: false,
		shortcode_select2_re_init: false,
		values: {},
		template: wp.template( 'conductor-qb-meta-box-query-builder-sub-clause-group' ),
		// Events
		events: {
			'click .conductor-qb-remove-action-button': 'removeSubClauseGroup', // Remove sub-clause group
			'change .conductor-qb-select2': 'select2Change', // Select2 change
			'select2:select .conductor-qb-select2': 'select2SelectUnselect', // Select2 select
			'select2:unselect .conductor-qb-select2': 'select2SelectUnselect', // Select2 unselect
			'select2:close .conductor-qb-select2': function( event ) {
				var $this = $( event.currentTarget );

				// Select2 close
				this.select2Close( event );

				// Update Select2 Options
				this.updateSelect2Options( $this );
			},
			'keypress .select2-search__field' : function( event ) {
				var $this = $( event.currentTarget ),
					$select2_el = $this.parents( '.select2-container' ).prev( '.conductor-qb-select2' );

				// Update Select2 Options
				this.updateSelect2Options( $select2_el );
			},
			'keyup .select2-search__field' : function( event ) {
				var $this = $( event.currentTarget ),
					$select2_el = $this.parents( '.select2-container' ).prev( '.conductor-qb-select2' );

				// Update Select2 Options
				this.updateSelect2Options( $select2_el );
			},
			'change .select2-search__field' : function( event ) {
				var $this = $( event.currentTarget ),
					$select2_el = $this.parents( '.select2-container' ).prev( '.conductor-qb-select2' );

				// Update Select2 Options
				this.updateSelect2Options( $select2_el );
			},
			'input .select2-search__field' : function( event ) {
				var $this = $( event.currentTarget ),
					$select2_el = $this.parents( '.select2-container' ).prev( '.conductor-qb-select2' );

				// Update Select2 Options
				this.updateSelect2Options( $select2_el );
			},
			'select2:opening .conductor-qb-select2': function( event ) {
				var self = this,
					$this = $( event.currentTarget );

				// Update Select2 Options
				self.updateSelect2Options( $this );
			},
			'select2:open .conductor-qb-select2': function( event ) {
				var self = this,
					$this = $( event.currentTarget );

				// Update Select2 Options
				self.updateSelect2Options( $this );

				// Delay 1ms; new thread
				setTimeout( function() {
					// Update Select2 Options
					self.updateSelect2Options( $this );
				}, 1 );
			},
			'select2:closing .conductor-qb-select2': function( event ) {
				var self = this,
					$this = $( event.currentTarget );

				// Trigger a query for all elements
				$this.data( 'select2' ).trigger( 'query', {} );

				// Update Select2 Options
				self.updateSelect2Options( $this );

				// Delay 1ms; new thread
				setTimeout( function() {
					// Update Select2 Options
					self.updateSelect2Options( $this );
				}, 1 );
			}
		},
		/**
		 * This function runs on initialization of the view.
		 */
		initialize: function( options ) {
			// Bind "this" to all functions
			_.bindAll(
				this,
				'render',
				'removeSubClauseGroup',
				'select2Change',
				'select2SelectUnselect',
				'select2Close',
				'getMeta',
				'setMetaValue',
				'getConfig',
				'setConfig',
				'getCurrentCount'
			);

			// Create the flag property if we don't already have one
			if ( ! options.flags ) {
				options.flags = {};
			}

			// If we have a type
			if ( options.type ) {
				// Set the el_selector
				this.el_selector = this.el_selector_prefix + options.type + this.el_selector_suffix;

				// If we don't have a valid selector, try to replace underscores with hyphens
				if ( ! $( this.el_selector ).length ) {
					// Set the el_selector (replace underscores with hyphens)
					this.el_selector = this.el_selector_prefix + options.type.replace( '_', '-' ) + this.el_selector_suffix;
				}
			}

			// Set the count (may not be accurate due to view/model references; @see conductor_query_builder.Backbone.Views.Query_Builder_Sub_Clause_Group.render())
			this.options.count = this.getCurrentCount( true );
			this.options.count = ( this.options.count !== -1 ) ? this.options.count : 0;// Default to 0

			// Set the parent object if it doesn't exist
			if ( ! this.options.parent ) {
				this.options.parent = {};
			}

			// Set the parent count (may not be accurate due to view/model references; @see conductor_query_builder.Backbone.Views.Query_Builder_Sub_Clause_Group.render())
			this.options.parent.count = ( this.views.parent && this.views.parent.options.hasOwnProperty( 'count' ) && this.views.parent.options.count !== -1 ) ? this.views.parent.options.count : -1;

			// Set the parent limit (may not be accurate due to view/model references; @see conductor_query_builder.Backbone.Views.Query_Builder_Sub_Clause_Group.render())
			this.options.parent.limit = ( this.views.parent && this.views.parent.options.hasOwnProperty( 'limit' ) && this.views.parent.options.limit !== -1 ) ? this.views.parent.options.limit : -1;

			// Set the default flag (may not be accurate due to view/model references; @see conductor_query_builder.Backbone.Views.Query_Builder_Sub_Clause_Group.render())
			this.options.flags.default = ( this.options.count === 0 );

			// If we have a template, use that template instead of the default
			if ( options.template ) {
				this.template = options.template;
			}

			/*
			 * Event Listeners
			 */
			this.listenToOnce( this, 'ready', this.initializeConfigs ); // Initialize configurations when the view is ready (once)
			this.listenToOnce( this, 'ready', this.initializeSelect2 ); // Initialize Select2 when the view is ready (once)
			this.listenToOnce( this, 'ready', this.maybeEnableActionButtons ); // After initializing Select2, maybe toggle action buttons when the view is ready (once)
			this.listenToOnce( this, 'ready', this.maybeSetModelID ); // Maybe set the model ID when the view is ready (once)
			this.listenToOnce( this, 'ready', this.maybeSetModelParentData ); // Maybe set the model parent data when the view is ready (once)
		},
		/**
		 * This function initializes configurations.
		 */
		initializeConfigs: function() {
			var self = this,
				$select2 = this.$el.find( this.select2_selector );

			// Loop through Select2 elements
			$select2.each( function() {
				var $this = $( this ),
					$current_option = $this.find( 'option:selected:first' ),
					id = $current_option.val(),
					current_option_config = $current_option.data( 'config' );

				// If the current option has a config
				if ( current_option_config ) {
					// Set the config for the current option
					self.setConfig( id, current_option_config );
				}
			} );
		},
		/**
		 * This function initializes Select2.
		 */
		initializeSelect2: function( types, silent ) {
			// Defaults
			types = types || [];
			silent = ( silent === false ) ? silent : ( ! silent );

			var self = this,
				$select2 = this.$el.find( this.select2_selector ),
				selected_parameter,
				selected_parameter_field,
				$parameters_select = $select2.filter( '[data-type="parameters"]' ),
				$parameters_select_options = $parameters_select.find( 'option' ),
				$operators_select = $select2.filter( '[data-type="operators"]' ),
				$operators_select_options = $operators_select.find( 'option' ),
				$values_select = $select2.filter( '[data-type="values"]' ),
				$values_select_options = $values_select.find( 'option' ),
				parameters,
				operators,
				values,
				columns = this.views.parent.model.get( 'columns' ),
				from_sub_clause_parameters,
				parameters_for_parameters_select,
				sub_clause_group_view_instances = conductor_query_builder.Backbone.instances.views.sub_clauses,
				sub_clause_group_view_index = sub_clause_group_view_instances.map( function( view ) { return view.options.type; } ).indexOf( 'from' ),
				sub_clause_group_view = ( sub_clause_group_view_index !== -1 ) ? sub_clause_group_view_instances[sub_clause_group_view_index] : false,
				sub_clause_group_view_meta = sub_clause_group_view && sub_clause_group_view.getMeta(),
				sub_clause_group_view_type = sub_clause_group_view && sub_clause_group_view.options.type,
				sub_clause_group_view_config = sub_clause_group_view && sub_clause_group_view.getConfig();

			// Store a reference to all Select2 elements on this view
			this.$select2 = $select2;

			// Loop through Select2 elements
			$select2.each( function() {
				var $this = $( this ),
					select2_args = {},
					Select2 = $this.data( 'select2' ),
					el_css_classes,
					el_css_classes_container = [],
					el_css_classes_results = [],
					select_type = $this.data( 'select-type' ),
					type = $this.data( 'type' ),
					value = $this.val(),
					meta = self.getMeta(),
					multiple = $this.prop( 'multiple' ),
					selected_options = $this.data( 'selected-options' );

				// Bail if we have types and this type was not found
				if ( types.length && types.indexOf( type ) === -1 ) {
					return;
				}

				// If we have configuration options for this select element
				if ( self.options.columns[type] && self.options.columns[type].select2 ) {
					// Select2 tags
					if ( self.options.columns[type].select2.tags ) {
						// TODO: Since data-tags will exist in the DOM element, setting the tags argument here may not be necessary
						// Tags flag
						select2_args.tags = true;

						// If we don't already have a Select2 instance
						if ( ! Select2 ) {
							// Setup the value
							value = meta.values;

							// Setup the selected parameter field data (single parameters only; grabbing the first parameter from multiple values)
							selected_parameter_field = ( meta.parameters && typeof meta.parameters === 'string' && conductor_query_builder.clauses[select_type] && conductor_query_builder.clauses[select_type].parameters ) ? conductor_query_builder.clauses[select_type].parameters : false;
							selected_parameter_field = ( selected_parameter_field && typeof meta.parameters === 'string' && selected_parameter_field[meta.parameters] && selected_parameter_field[meta.parameters].field ) ? selected_parameter_field[meta.parameters].field : ( ( typeof meta.parameters === 'string' ) ? meta.parameters : false );

							// If we have parameters but not a selected parameter field yet
							if ( meta.parameters && ! selected_parameter_field ) {
								// Grab the first selected parameter
								selected_parameter = meta.parameters.slice( 0 );

								selected_parameter_field = ( selected_parameter && conductor_query_builder.clauses[select_type] && conductor_query_builder.clauses[select_type].parameters && conductor_query_builder.clauses[select_type].parameters ) ? conductor_query_builder.clauses[select_type].parameters : false;
								selected_parameter_field = ( selected_parameter_field && selected_parameter_field[selected_parameter] && selected_parameter_field[selected_parameter].field ) ? selected_parameter_field[selected_parameter].field : false;
							}

							// Switch based on type
							switch ( type ) {
								// Values
								case 'values':
									// Grab the parameters
									parameters = self.views.parent.getParameterData( value, ( selected_parameter ) ? selected_parameter : meta.parameters, selected_parameter_field, select_type );

									// Grab the operators
									operators = self.views.parent.getOperators( parameters );

									// If we have operators and an operators select element
									if ( operators && operators.length && $operators_select.length ) {
										// Update the operators select element
										self.views.parent.updateOperatorsSelect( $operators_select, $operators_select_options, ( meta.operators && typeof meta.operators === 'string' ) ? meta.operators : meta.operators.slice( 0 ), operators, true );

										// Grab the values
										values = self.views.parent.getValues( ( selected_parameter ) ? selected_parameter : meta.parameters, select_type, $parameters_select_options.filter( ':selected' ) );

										// If we have values
										if ( columns && columns.values && values ) {
											// Update the values select element
											self.views.parent.updateValuesSelect( $values_select, $values_select_options, value, values, true );

											// Update the values select options
											$values_select_options = $values_select.find( 'option' );
										}

										// If we have an array of meta values and it's not equal to the values for this parameter
										if ( value && value.length && ! _.isEqual( value, values ) ) {
											// Loop through the meta values
											_.each( value, function ( meta_value ) {
												// If this meta value doesn't exist
												if ( ! $values_select_options.filter( '[value="' + meta_value + '"]' ).length ) {
													// Create the data array if it doesn't exist
													if ( ! select2_args.hasOwnProperty( 'data' ) ) {
														select2_args.data = [];
													}

													// Add this meta value as a tag
													select2_args.data.push( meta_value );
												}
											} );
										}
									}
								break;
							}
						}

						// Token separators
						select2_args.tokenSeparators = self.options.columns[type].select2.tags;
					}
				}

				// If the shortcode UI is visible
				if ( self.options.shortcode ) {
					// Ensure Select2 elements are appended to the thickbox content element
					select2_args.dropdownParent = $( '#TB_ajaxContent' )
				}

				// If we have a locale set for Select2
				if ( conductor_query_builder.select2.language ) {
					// Set the Select2 language property
					select2_args.language = conductor_query_builder.select2.language;
				}

				// If we have a direction set for Select2
				if ( conductor_query_builder.select2.dir ) {
					// Set the Select2 direction property
					select2_args.dir = conductor_query_builder.select2.dir;
				}

				// If this Select2 element is open
				if ( Select2 && Select2.isOpen() ) {
					// Close this Select2 element
					$this.select2( 'close' );
				}

				// Initialize Select2 using the Conductor Query Builder Select2 library
				$this.conductor_qb_select2( select2_args );

				// Switch based on type
				switch ( type ) {
					// Parameters
					case 'parameters':
						// Enable the operators select element if we have parameters
						if ( $operators_select.prop( 'disabled' ) && meta.parameters ) {
							$operators_select.prop( 'disabled', false );
						}

						// Setup the selected parameter field data (single parameters only; grabbing the first parameter from multiple values)
						selected_parameter_field = ( meta.parameters && typeof meta.parameters === 'string' && conductor_query_builder.clauses[select_type] && conductor_query_builder.clauses[select_type].parameters ) ? conductor_query_builder.clauses[select_type].parameters : false;
						selected_parameter_field = ( selected_parameter_field && typeof meta.parameters === 'string' && selected_parameter_field[meta.parameters] && selected_parameter_field[meta.parameters].field ) ? selected_parameter_field[meta.parameters].field : ( ( typeof meta.parameters === 'string' ) ? meta.parameters : false );

						// If we have parameters but not a selected parameter field yet
						if ( meta.parameters && ! selected_parameter_field ) {
							// Grab the first selected parameter
							selected_parameter = meta.parameters.slice( 0 );

							selected_parameter_field = ( selected_parameter && conductor_query_builder.clauses[select_type] && conductor_query_builder.clauses[select_type].parameters && conductor_query_builder.clauses[select_type].parameters ) ? conductor_query_builder.clauses[select_type].parameters : false;
							selected_parameter_field = ( selected_parameter_field && selected_parameter_field[selected_parameter] && selected_parameter_field[selected_parameter].field ) ? selected_parameter_field[selected_parameter].field : false;
						}

						// Grab the FROM sub-clause parameters
						from_sub_clause_parameters = self.views.parent.getFromSubClauseParameters( sub_clause_group_view_config, sub_clause_group_view_meta, ( selected_parameter ) ? selected_parameter : meta.parameters, selected_parameter_field, select_type );

						// Set the parameters for the parameters select element
						parameters_for_parameters_select = ( from_sub_clause_parameters ) ? from_sub_clause_parameters : ( ( conductor_query_builder.clauses[select_type] && conductor_query_builder.clauses[select_type].parameters ) ? self.views.parent.getParameters( conductor_query_builder.clauses[select_type].parameters ) : false );

						// If we have the parameters for the parameters select element
						if ( parameters_for_parameters_select ) {
							// Grab the meta
							meta = self.getMeta();

							// If we have parameters but not a selected parameter field yet
							if ( meta.parameters ) {
								// Grab the first selected parameter
								selected_parameter = meta.parameters.slice( 0 );

								selected_parameter_field = ( selected_parameter && conductor_query_builder.clauses[select_type] && conductor_query_builder.clauses[select_type].parameters && conductor_query_builder.clauses[select_type].parameters ) ? conductor_query_builder.clauses[select_type].parameters : false;
								selected_parameter_field = ( selected_parameter_field && selected_parameter_field[selected_parameter] && selected_parameter_field[selected_parameter].field ) ? selected_parameter_field[selected_parameter].field : false;
							}
						}

						// Update the parameters select element
						self.views.parent.updateParametersSelect( $parameters_select, $parameters_select_options, value, parameters_for_parameters_select, silent );

						// Update the value
						value = $this.val();

						// Grab the parameters
						parameters = ( ! parameters ) ? self.views.parent.getParameterData( value, ( selected_parameter ) ? selected_parameter : meta.parameters, selected_parameter_field, select_type ) : parameters;

						// Grab the operators
						operators = ( ! operators && parameters ) ? self.views.parent.getOperators( parameters ) : operators;

						// If we have operators and an operators select element
						if ( operators && operators.length && $operators_select.length ) {
							// Update the operators select element
							self.views.parent.updateOperatorsSelect( $operators_select, $operators_select_options, ( meta.operators ) ? ( ( meta.operators && typeof meta.operators === 'string' ) ? meta.operators : meta.operators.slice( 0 ) ) : '', operators, silent );
						}
					break;

					// Operators
					case 'operators':
						// Update the meta
						meta = self.getMeta();

						// Grab the parameters
						parameters = ( ! parameters ) ? self.views.parent.getParameterData( meta.parameters, ( selected_parameter ) ? selected_parameter : meta.parameters, selected_parameter_field, select_type ) : parameters;

						// Grab the operators
						operators = ( ! operators && parameters ) ? self.views.parent.getOperators( parameters ) : operators;

						// If we have operators and an operators select element
						if ( operators && operators.length && $operators_select.length ) {
							// Update the operators select element
							self.views.parent.updateOperatorsSelect( $operators_select, $operators_select_options, ( meta.operators ) ? ( ( meta.operators && typeof meta.operators === 'string' ) ? meta.operators : meta.operators.slice( 0 ) ) : '', operators, silent );
						}

						// If this clause group supports values
						if ( columns && columns.values ) {
							// If the operator isn't a Boolean operator
							if ( $values_select.prop( 'disabled' ) && conductor_query_builder.operators[value] && ( typeof conductor_query_builder.operators[value] === 'string' || ( conductor_query_builder.operators[value].type && conductor_query_builder.operators[value].type !== 'bool' ) ) ) {
								// Enable the values select element
								$values_select.prop( 'disabled', false );
							}
							// Otherwise if the operator is a Boolean operator
							else if ( ! $values_select.prop( 'disabled' ) && conductor_query_builder.operators[value] && typeof conductor_query_builder.operators[value] !== 'string' && conductor_query_builder.operators[value].type && conductor_query_builder.operators[value].type === 'bool' ) {
								// Reset the values select element value
								$values_select.val( null ).trigger( 'change' );

								// Disable the values select element
								$values_select.prop( 'disabled', true );
							}
						}

						// TODO: For BETWEEN/NOT BETWEEN we need to set maximumSelectionLength to the 'limit' value
					break;

					// Values
					case 'values':
						// If we have meta values
						if ( meta.values ) {
							// Grab the updated value
							value = $this.val();

							// If the current value doesn't equal the meta value
							if ( ! _.isEqual( value, meta.values ) ) {
								// Set the value equal to the meta value (trigger the change event)
								$this.val( meta.values ).trigger( 'change' );

								// Grab the updated value
								value = $this.val();
							}
						}

						// If this values select element supports multiple values and we have selected options
						if ( multiple && selected_options ) {
							self.updateSelect2Options( $this );
						}
					break;
				}

				/*
				 * Initialize Select2 using the Conductor Query Builder Select2 library.
				 *
				 * We're calling this again to ensure previously selected items that are now disabled
				 * are removed from available Select2 options.
				 */
				$this.conductor_qb_select2( select2_args );

				// Grab the Select2 instance
				Select2 = $this.data( 'select2' );

				// Grab the Select2 element classes
				el_css_classes = Select2.$element.attr( 'class' ).split( /\s+/ ); // All whitespace characters

				// Remove the CSS classes with "select2" in the name (remove empty results after)
				el_css_classes = el_css_classes.map( function( css_class ) { return ( css_class.indexOf( 'select2' ) === -1 ) ? css_class : ''; } ).filter( function( css_class ) { return ( css_class ) } );

				// Loop through the CSS classes
				_.each( el_css_classes, function ( css_class ) {
					// Create the container CSS classes
					el_css_classes_container.push( css_class + '-select2' );
					el_css_classes_container.push( css_class + '-select2-container' );

					// Create the results CSS classes
					el_css_classes_results.push( css_class + '-select2' );
					el_css_classes_results.push( css_class + '-select2-results' );
				} );

				// Join the CSS classes
				el_css_classes_container = el_css_classes_container.join( ' ' );
				el_css_classes_results = el_css_classes_results.join( ' ' );

				// Add the CSS classes to the Select2 container
				Select2.$container.addClass( el_css_classes_container );

				// Add the CSS classes to the Select2 container
				Select2.$results.addClass( el_css_classes_results );

				// Store the value on this view
				self.setMetaValue( type, value );
			} );
		},
		/**
		 * This function enables action buttons after the initial Select2 initialization if the
		 * select elements support toggling action buttons and a value exists.
		 */
		maybeEnableActionButtons: function() {
			var $select2 = this.$el.find( this.select2_selector );

			// Loop through Select2 elements
			$select2.each( function() {
				var $this = $( this ),
					value = $this.val(),
					toggle_action_buttons = $this.data( 'toggle-action-buttons' );

				// If we should toggle the action buttons
				if ( toggle_action_buttons ) {
					// If we have a value
					if ( value || ( _.isArray( value ) && value.length ) ) {
						// TODO: Transition to "conductor-query-builder-{event}" event name
						// Trigger the "conductor-qb-enable-action-buttons" event on the Conductor Query Builder Actions View
						Conductor_Query_Builder_Actions_View.trigger( 'conductor-qb-enable-action-buttons' );

						// Loop through clause group action Backbone Views
						_.each( conductor_query_builder.Backbone.instances.views.clause_action_buttons, function ( view ) {
							// TODO: Transition to "conductor-query-builder-{event}" event name
							// Trigger the "conductor-qb-enable-action-buttons" event on this view
							view.trigger( 'conductor-qb-enable-action-buttons' );
						} );

						// Break from the loop
						return false;
					}
				}
			} );
		},
		/**
		 * This function sets up the model ID value only if it has not already be set.
		 */
		maybeSetModelID: function( re_render ) {
			// Defaults
			re_render = re_render || false;

			var id = this.model.get( 'id' ),
				view_id = this.model.getIDFromView( this );

			// If we don't have a model ID or this is a re-render
			if ( ! id || ( view_id && id !== view_id ) || re_render ) {
				// Set the model ID now
				this.model.setID( this, view_id );
			}
		},
		/**
		 * This function sets up the model parent data only if it has not already be set.
		 */
		maybeSetModelParentData: function( re_render ) {
			// Defaults
			re_render = re_render || false;

			var parent_data = this.model.get( 'parent' );

			// Bail if we don't have a parent view or a valid count
			if ( ! this.views.parent || ! this.views.parent.model || ( parent_data.count === -1 && this.options.parent.count !== -1 ) ) {
				return;
			}

			// If we don't have the correct parent data or this is a re-render
			if ( parent_data.id !== this.model.get( 'id' ) || parent_data.count !== this.views.parent.options.count || parent_data.limit !== this.views.parent.model.get( 'limit' ) || re_render ) {
				// Set the parent data now
				this.model.setParentData( this );
			}
		},
		/**
		 * This function sets up the meta values references on the model and the view.
		 */
		setupMetaValues: function() {
			var parent_data = this.model.get( 'parent' ),
				parent_count = ( parent_data.count !== -1 ) ? parent_data.count : -1,
				count = this.options.count,
				meta = this.getMeta();

			// If we have meta data and valid counts
			if ( this.views.parent && this.views.parent.options.meta && parent_count !== -1 && count !== -1 ) {
				// If there is meta for this sub-clause group
				if ( this.views.parent.options.meta[parent_count] && this.views.parent.options.meta[parent_count][count] ) {
					// Reset meta (may contain other data due to count references not being completely setup until rendering)
					meta = _.clone( conductor_query_builder.Backbone.defaults.meta );

					// Loop through meta
					_.each( this.views.parent.options.meta[parent_count][count], function ( meta_value, meta_key ) {
						// Update this meta value
						meta[meta_key] = meta_value;
					} );

					// Set the meta data on the model
					this.model.set( 'meta', meta );

					// Set the meta data on this view
					this.options.meta = meta;
				}
				// Otherwise, if we have meta set we need to remove it now
				else {
					// Reset meta
					meta = _.clone( conductor_query_builder.Backbone.defaults.meta );

					// Set the meta data on the model
					this.model.set( 'meta', meta );

					// Set the meta data on this view
					this.options.meta = meta;
				}
			}
		},
		/**
		 * This function destroys all Select2 instances.
		 */
		destroySelect2: function() {
			var $select2 = this.$el.find( this.select2_selector );

			// Loop through possible Select2 instances
			$select2.each( function() {
				var $this = $( this ),
					Select2 = $this.data( 'select2' );

				// If we have a Select2 instance
				if ( Select2 ) {
					// Destroy Select2
					$select2.conductor_qb_select2( 'destroy' );
				}
			} );
		},
		/**
		 * This function removes a sub-clause group view from this view.
		 */
		removeSubClauseGroup: function( event ) {
			//var $this = $( event.currentTarget );

			// Prevent default
			event.preventDefault();

			// Bail if this is the default view
			if ( this.options.default ) {
				return;
			}

			// Call removeSubClauseGroup() on the parent view
			this.views.parent.removeSubClauseGroup( event, this );
		},
		/**
		 * This function renders the view.
		 */
		render: function( re_render ) {
			// Defaults
			re_render = re_render || this.views.parent.options.flags.re_rendering || false;

			// Maybe set the model ID
			this.maybeSetModelID( re_render );

			// Maybe set the model parent data
			this.maybeSetModelParentData( re_render );

			/*
			 * We likely have the model ID set now due to the view references being setup (parent views
			 * have been add()ed and render()ed). We'll set the count, parent count, and default flag
			 * again here just to be sure that we have more accurate values.
			 */

			// Set the count
			this.options.count = ( re_render && ! this.views.parent.options.flags.re_rendering ) ? ( this.options.count - 1 ) : this.getCurrentCount( true );
			this.options.count = ( this.options.count !== -1 ) ? this.options.count : 0; // Default to 0

			// Set the parent count
			this.options.parent.count = ( this.views.parent && this.views.parent.options.hasOwnProperty( 'count' ) ) ? this.views.parent.options.count : -1;

			// Set the parent limit
			this.options.parent.limit = ( this.views.parent && this.views.parent.options.hasOwnProperty( 'limit' ) ) ? this.views.parent.options.limit : -1;

			// Set the parent ID
			this.options.parent_id = this.model.get( 'parent_id' );

			// Set the default flag
			this.options.flags.default = ( this.options.count === 0 );

			// If we don't have the sub_clause_groups flag set, set it to true now
			if ( this.views.parent && _.isUndefined( this.views.parent.options.flags.sub_clause_groups ) ) {
				this.options.flags.sub_clause_groups = true;
			}

			// Set meta data
			this.setupMetaValues();

			// Call (apply) the default wp.Backbone.View render function
			wp.Backbone.View.prototype.render.apply( this, arguments );

			// If this is a re-render
			if ( re_render ) {
				this.listenToOnce( this, 'ready', this.initializeSelect2 ); // Initialize Select2 when the view is ready (once)
				this.listenToOnce( this, 'ready', this.maybeEnableActionButtons ); // After initializing Select2, maybe toggle action buttons when the view is ready (once)

				// Trigger the ready event
				this.trigger( 'ready' );
			}

			return this;
		},
		/**
		 * This function removes the view.
		 */
		remove: function() {
			var result,
				sub_clause_group_view_instances = conductor_query_builder.Backbone.instances.views.sub_clauses,
				view_index,
				clause_group_type = this.model.get( 'type' ),
				clause_group_view = this.views.parent,
				parent_data = this.model.get( 'parent' ),
				parent_count = ( parent_data.count !== -1 ) ? parent_data.count : -1,
				count = this.options.count,
				el_selector = this.el_selector;

			// Destroy Select2
			this.destroySelect2();

			// Remove the model from the collection
			Conductor_Query_Builder_Sub_Clause_Group_Collection.remove( this.model );

			// Call (apply) the default wp.Backbone.View remove function
			result = wp.Backbone.View.prototype.remove.apply( this, arguments );

			// Grab the model index from instances
			view_index = sub_clause_group_view_instances.map( function( view ) { return view.cid; } ).indexOf( this.cid );

			// If we have a model index
			if ( view_index !== -1 ) {
				// Remove this view from instances
				sub_clause_group_view_instances.splice( view_index, 1 );
			}

			// If we have valid counts
			if ( parent_count !== -1 && count !== -1 && conductor_query_builder.meta[clause_group_type][parent_count] && conductor_query_builder.meta[clause_group_type][parent_count] && conductor_query_builder.meta[clause_group_type][parent_count][count] ) {
				// Remove meta
				conductor_query_builder.meta[clause_group_type][parent_count].splice( count, 1 );
			}

			// If this clause group view isn't currently being removed
			if ( ! clause_group_view.options.flags.removing ) {
				// Loop through all of the sub-clause groups in the parent clause group
				_.each( clause_group_view.views.get( el_selector ), function ( sub_clause_group_view ) {
					// If this sub-clause group is after the sub-clause group that was removed
					if ( sub_clause_group_view.options.count !== -1 && ( ( sub_clause_group_view.options.parent.count !== parent_count && sub_clause_group_view.options.parent.count > parent_count ) || sub_clause_group_view.options.count > count ) ) {
						// Re-render this view
						sub_clause_group_view.render( true );
					}
				} );
			}

			return result;
		},
		/**
		 * This function is triggered on Select2 change events.
		 */
		select2Change: function( event ) {
			var $this = $( event.currentTarget ),
				Select2 = $this.data( 'select2' ),
				value = $this.val(),
				type = $this.data( 'type' );

			// Store the value on the model and view
			this.setMetaValue( type, value );

			// Call select2Change() on the parent view
			this.views.parent.select2Change( event, this );
		},
		/**
		 * This function runs when a Select2 option is selected or unselected.
		 */
		select2SelectUnselect: function( event ) {
			var $this = $( event.currentTarget ),
				$widget_parent = $this.parents( '.widget' ),
				value = $this.val(),
				$selected_options = $this.find( 'option:selected' ),
				index = -1,
				multiple = $this.prop( 'multiple' ),
				selected_options = $this.data( 'selected-options' ) || [],
				type = $this.data( 'type' ),
				id = event.params.data.id,
				$current_option = $this.find( 'option[value="' + id + '"]' ),
				current_option_config = $current_option.data( 'config' ),
				selected_options_config_for_type = {},
				default_select2_config = this.options.columns && this.options.columns[type] && this.options.columns[type].select2,
				toggle_action_buttons = $this.data( 'toggle-action-buttons' ),
				clause_type = this.options.type,
				sub_clause_group_view_instances = conductor_query_builder.Backbone.instances.views.sub_clauses,
				$conductor_select_content_type,
				conductor_select_content_type_value,
				self = this;

			// Loop through the selected options
			_.each( $selected_options, function( selected_option_el ) {
				var $selected_option = $( selected_option_el ),
					selected_option_config = $selected_option.data( 'config' ),
					selected_option_value = $selected_option.attr( 'value' );

				// If we have the selected option config
				if ( selected_option_config ) {
					// Add this selected option config to the selected options config data
					selected_options_config_for_type[selected_option_value] = selected_option_config.columns && selected_option_config.columns[type] && selected_option_config.columns[type].select2;
				}
			} );

			// If the current option has a config
			if ( current_option_config ) {
				// Add the current option config to the selected options config
				selected_options_config_for_type[id] = current_option_config.columns && current_option_config.columns[type] && current_option_config.columns[type].select2;

				// Set the config for the current option
				this.setConfig( id, current_option_config );
			}

			// If we have a selected options config for this ID
			if ( selected_options_config_for_type[id] ) {
				// Loop through the selected option config for this ID
				_.each( selected_options_config_for_type[id], function( the_value, property ) {
					var can_trigger_change_event = false;

					// Switch based on the property
					switch ( property ) {
						// Multiple
						case 'multiple':
							// If this select element currently allows multiple options to be selected and the multiple config data isn't set
							if ( multiple && ! the_value ) {
								// Loop through the selected options
								$selected_options.each( function() {
									var $the_selected_option = $( this );

									// If this isn't the selected option
									if ( $the_selected_option[0] !== $current_option[0] ) {
										// Reset the selected property on this selected option
										$the_selected_option.prop( 'selected', false );

										// If the can trigger change event flag isn't set
										if ( ! can_trigger_change_event ) {
											// Set the can trigger change event flag
											can_trigger_change_event = true;
										}
									}
								} );

								// Reset the multiple property on this select element
								$this.prop( 'multiple', the_value );

								// If we can trigger the change event
								if ( can_trigger_change_event ) {
									// Trigger the "change" event on this select element
									$this.trigger( 'change' );
								}
							}
							// Otherwise if this select element doesn't currently allow multiple options to be selected and the multiple config data is set
							else if ( ! multiple && the_value ) {
								// Set the multiple property on this select element
								$this.prop( 'multiple', the_value );
							}

							// If this is a Select2 unselect event
							if ( event.type === 'select2:unselect' ) {
								// Reset the selected property on the selected option
								$current_option.prop( 'selected', false );

								// Reset the config for the current option
								self.setConfig( id, false );

								// If we have default Select2 config data and this value doesn't match the default Select2 config value
								if ( default_select2_config && typeof default_select2_config[property] !== 'undefined' && the_value !== default_select2_config[property] ) {
									// Reset the multiple property on this select element
									$this.prop( 'multiple', default_select2_config[property] );
								}

								// If the selected option doesn't allow for multiple values
								if ( ! the_value ) {
									// Reset the value on this select element
									$this.val( [] );

									// Set the value
									value = $this.val();
								}

								// Trigger the "change" event on this select element
								$this.trigger( 'change' );
							}
						break;
					}
				} );
			}
			// Otherwise if we have a default Select2 config
			else if ( default_select2_config ) {
				// Loop through the default Select2 config
				_.each( default_select2_config, function( value, property ) {
					// Switch based on the property
					switch ( property ) {
						// Multiple
						case 'multiple':
							// If this select element currently allows multiple options to be selected and the multiple config data isn't set
							if ( multiple && ! value ) {
								// Reset the multiple property on this select element
								$this.prop( 'multiple', value );

								// TODO: Future: Only allow the selected option to be set here (remove selected from other selected options)?
							}
							// Otherwise if this select element doesn't currently allow multiple options to be selected and the multiple config data is set
							else if ( ! multiple && value ) {
								// Set the multiple property on this select element
								$this.prop( 'multiple', value );
							}
							break;
					}
				} );
			}

			// If we should toggle the action buttons
			if ( toggle_action_buttons ) {
				// If we have a value
				if ( value || ( _.isArray( value ) && value.length ) ) {
					// TODO: Transition to "conductor-query-builder-{event}" event name
					// Trigger the conductor-qb-enable-action-buttons event on the Conductor Query Builder Actions View
					Conductor_Query_Builder_Actions_View.trigger( 'conductor-qb-enable-action-buttons' );

					// Loop through clause group action Backbone Views
					_.each( conductor_query_builder.Backbone.instances.views.clause_action_buttons, function ( view ) {
						// TODO: Transition to "conductor-query-builder-{event}" event name
						// Trigger the conductor-qb-enable-action-buttons event on this view
						view.trigger( 'conductor-qb-enable-action-buttons' );
					} );
				}
				// Otherwise we don't have a selection
				else {
					// TODO: Transition to "conductor-query-builder-{event}" event name
					// Trigger the conductor-qb-disable-action-buttons event on the Conductor Query Builder Actions View
					Conductor_Query_Builder_Actions_View.trigger( 'conductor-qb-disable-action-buttons' );

					// Loop through clause group action Backbone Views
					_.each( conductor_query_builder.Backbone.instances.views.clause_action_buttons, function ( view ) {
						// TODO: Transition to "conductor-query-builder-{event}" event name
						// Trigger the conductor-qb-disable-action-buttons event on this view
						view.trigger( 'conductor-qb-disable-action-buttons' );
					} );
				}
			}

			// If this is the FROM clause type
			if ( clause_type === 'from' ) {
				// Grab the Conductor select content type element
				$conductor_select_content_type = Conductor_Query_Builder_View.$el.find( '.conductor-select-content-type' );

				// Grab the Conductor select content type value
				conductor_select_content_type_value = ( multiple && value ) ? value[( value.length - 1 )]: value;

				// If we have a Conductor select content type value
				if ( conductor_select_content_type_value ) {
					// Set the Conductor select content type value
					$conductor_select_content_type.val( conductor_select_content_type_value ).trigger( 'change' );
				}
				// Otherwise we don't have a Conductor select content type value
				else {
					// Set the Conductor select content type value to the first option value
					$conductor_select_content_type.val( $conductor_select_content_type.find( 'option:first' ).val() ).trigger( 'change' );
				}

				// If this a Select2 select event
				if ( event.type === 'select2:select' ) {
					// If we have current option config data
					if ( current_option_config && event.type === 'select2:select' ) {
						// Loop through the current option config
						_.each( current_option_config, function( clauses ) {
							// Loop through the current option config clauses
							_.each( clauses, function( clause_group_config, clause_group ) {
								// If this clause group is disabled
								if ( clause_group_config.disabled ) {
									// If we have models in the clause group collection
									if ( Conductor_Query_Builder_Clause_Group_Collection.length ) {
										// Loop through the clause group collection models
										Conductor_Query_Builder_Clause_Group_Collection.each( function( model ) {
											var views;

											// If we have a model and this model type matches this clause group
											if ( model && model.get( 'type' ) === clause_group ) {
												// Grab all of the clause group views associated with this model's element selector
												views = Conductor_Query_Builder_View.views.get( model.get( 'el_selector' ) );

												// If we have views
												if ( views.length ) {
													// Loop through views
													_.each( views, function ( view ) {
														// If this view model ID matches this model ID
														if ( view.model.get( 'id' ) === model.get( 'id' ) ) {
															// Force remove this clause group
															view.removeClauseGroup( false, true );
														}
													} );
												}
											}
										} );
									}
								}
							} );
						} );

						// Grab the sub-clause group view instances
						sub_clause_group_view_instances = conductor_query_builder.Backbone.instances.views.sub_clauses;
					}
				}

				// If this is the parameters select element
				if ( type === 'parameters' ) {
					// Loop through all of the sub-clause group vew
					_.each( sub_clause_group_view_instances, function ( the_sub_clause_group_view ) {
						// If this isn't the FROM sub-clause group view
						if ( the_sub_clause_group_view.options.type !== clause_type ) {
							// Re-initialize Select2 on the parameters select element (not silent)
							the_sub_clause_group_view.initializeSelect2( [ 'parameters', 'operators' ], false );
						}
					} );
				}
			}

			// If this is a values select element and this values select element supports multiple selections
			if ( type === 'values' && multiple ) {
				// Switch based on event type
				switch ( event.type ) {
					// Select2 Select
					case 'select2:select':
						// Add the selected value to the selected data
						selected_options.push( id );
					break;

					// Select2 Un-Select
					case 'select2:unselect':
						// Grab the index for the un-selected element
						index = selected_options.indexOf( id );

						// If we have a valid index
						if ( index !== -1 ) {
							// Splice (remove) the un-selected element from the selected data
							selected_options.splice( index , 1 );
						}
					break;
				}

				// Set the selected data on the select element
				$this.data( 'selected', selected_options ).attr( 'selected-options', JSON.stringify( selected_options ) );

				// Update the Select2 options
				this.updateSelect2Options( $this );
			}

			// Trigger the "conductor-query-builder:select2-select-unselect" event on the widget parent element
			$widget_parent.trigger( 'conductor-query-builder:select2-select-unselect', [
				$this,
				event,
				clause_type,
				type,
				value,
				id,
				multiple,
				selected_options_config_for_type,
				self
			] );

			// Trigger the "conductor-query-builder:select2-select-unselect" this view
			self.trigger( 'conductor-query-builder:select2-select-unselect', [
				$this,
				event,
				value,
				id,
				multiple,
				clause_type,
				selected_options_config_for_type,
				self
			] );
		},
		/**
		 * This function is triggered when the Select2 dropdown closes.
		 *
		 * Re-initialize Select2 after the Select2 dropdown has closed
		 * @see https://github.com/select2/select2/issues/3347
		 *
		 * This has to occur after the Select2 dropdown closes due to
		 * event handlers which are attached by Select2 to prevent scrolling
		 * in elements with overflow scroll/auto while the Select2 dropdown
		 * is open.
		 *
		 * If the re-initialization doesn't happen after the Select2 dropdown
		 * is closed, Select2 cannot remove the correct event handlers and scrolling
		 * is prevented in elements with overflow scroll/auto.
		 */
		select2Close: function( event ) {
			var $this = $( event.currentTarget );

			// If the shortcode UI is visible and we're not already doing a shortcode Select2 re-initialization
			if ( this.options.shortcode && ! this.shortcode_select2_re_init ) {
				// Set the shortcode Select2 re-initialization flag
				this.shortcode_select2_re_init = true;

				// Delay 1ms; new thread
				( function ( $current_select2_el, self ) {
					setTimeout( function() {
						var $conductor_qb_select2 = self.$el.find( self.select2_selector ).not( $current_select2_el ),
							$open_conductor_qb_select2 = $conductor_qb_select2.filter( function() {
								var $this = $( this ),
									Select2 = $this.data( 'select2' ),
									$select2_container = Select2.$container;

								return $select2_container.hasClass( 'select2-container--open' );
							} );

						// Initialize Select2
						self.initializeSelect2( ( ! self.views.parent.options.flags.re_init_values ) ? [ 'parameters', 'operators' ] : [] );

						// If we have any open Select2 elements
						if ( $open_conductor_qb_select2.length ) {
							// Loop through the open Select2 elements
							$open_conductor_qb_select2.each( function() {
								// Open the Select2 element
								$( this ).select2( 'open' );
							} );
						}

						// Reset the shortcode Select2 re-initialization flag
						self.shortcode_select2_re_init = false;
					}, 1 )
				} ( $this, this ) );
			}
		},
		/**
		 * This function gets meta associated with this sub-clause group.
		 */
		getMeta: function() {
			return this.model.get( 'meta' );
		},
		/**
		 * This function sets a value on the view and model based on type.
		 */
		setMetaValue: function( type, value ) {
			var meta = this.getMeta(),
				clause_group_type = this.model.get( 'type' ),
				parent_data = this.model.get( 'parent' ),
				parent_count = ( parent_data.count !== -1 ) ? parent_data.count : -1,
				count = this.options.count;

			// Update the value
			meta[type] = value;

			// Set the value on the model
			this.model.set( 'meta', meta );

			// Set the value on the view
			this.options.meta = meta;

			// If we have valid counts
			if ( parent_count !== -1 && count !== -1 ) {
				// If this clause group doesn't exist, create it now
				if ( ! conductor_query_builder.meta[clause_group_type][parent_count] ) {
					conductor_query_builder.meta[clause_group_type][parent_count] = [];
				}

				// If this sub-clause group doesn't exist, create it now
				if ( ! conductor_query_builder.meta[clause_group_type][parent_count][count] ) {
					conductor_query_builder.meta[clause_group_type][parent_count][count] = _.clone( conductor_query_builder.Backbone.defaults.meta );
				}

				// If the global meta value is not equal to the new value
				if ( ! conductor_query_builder.meta[clause_group_type][parent_count][count][type] || ! _.isEqual( conductor_query_builder.meta[clause_group_type][parent_count][count][type], value ) ) {
					// Set the value now
					conductor_query_builder.meta[clause_group_type][parent_count][count][type] = value;
				}
			}
		},
		/**
		 * This function returns the config associated with this sub-clause group.
		 */
		getConfig: function() {
			return this.model.get( 'config' );
		},
		/**
		 * This function sets the config on this sub-claus group.
		 */
		setConfig: function( id, config_data ) {
			var config = this.getConfig();

			// Update the value
			config[id] = config_data;

			// Set the config on the model
			this.model.set( 'config', config );

			// Set the config on the view
			this.options.config = config;
		},
		/**
		 * This function grabs the current view count for this sub-clause group type.
		 */
		getCurrentCount: function( zero_index ) {
			// Defaults
			zero_index = zero_index || false;

			var count;

			// Bail if we don't have a parent ID value as we can't get an accurate count
			if ( ! this.model.get( 'parent_id' ) ) {
				return -1;
			}

			// Grab the count of sub-clause groups with the same parent ID
			count = Conductor_Query_Builder_Sub_Clause_Group_Collection.where( { parent_id: this.model.get( 'parent_id' ) } ).length;

			// Account for the zero index
			count = ( zero_index && count !== 0 ) ? ( count - 1 ) : count;

			return count;
		},
		/**
		 * This function updates the displayed Select2 options to ensure the selection order is preserved.
		 */
		updateSelect2Options: function( $select2_el ) {
			var Select2 = $select2_el.data( 'select2' ),
				$select2_container = Select2.$container,
				$select2_tags = $select2_container.find( '.select2-selection__choice' ),
				$select2_search = $select2_container.find( '.select2-search' ),
				selected_options = $select2_el.data( 'selected-options' );

			// If we have selected options
			if ( selected_options && selected_options.length ) {
				// Loop through the selected values
				_.each( selected_options, function( value ) {
					// Move the tag the corresponds with this meta value
					$select2_search.before( $select2_tags.filter( function() {
						return $( this ).data( 'data' ).id === value.toString();
					} ) );
				} );
			}
		}
	} );


	/**************
	 * Shortcodes *
	 **************/

	/**
	 * Conductor Query Builder Shortcode Backbone Model
	 */
	conductor_query_builder.Backbone.Models.Shortcode = Backbone.Model.extend( {
		defaults: {
			create_title: '',
			insert_query: '',
			insert_title: ''
		},
		id_prefix: 'conductor-qb-',
		initialize: function() {
			// Bind "this" to all functions
			_.bindAll(
				this,
				'setID',
				'removeFromInstances'
			);

			// Set the ID
			this.setID();

			// Remove the model from the instances
			this.listenTo( this, 'remove', this.removeFromInstances );

			// Stop listening to events on the model when it's removed
			this.listenTo( this, 'remove', this.stopListening );
		},
		/**
		 * This function sets the ID value of this model based on the number of existing models of this model's type.
		 */
		setID: function() {
			// Set the ID
			this.set( 'id', this.id_prefix + 'shortcode' );
		},
		/**
		 * This function removes the model from instances.
		 */
		removeFromInstances: function() {
			var model_clause_instances = conductor_query_builder.Backbone.instances.models.shortcode,
				model_index,
				self = this;

			// Grab the model index from instances
			model_index = model_clause_instances.map( function( model ) { return model.get( 'id' ); } ).indexOf( self.get( 'id' ) );

			// If we have a model index
			if ( model_index !== -1 ) {
				// Remove this model from instances
				model_clause_instances.splice( model_index, 1 );
			}
		},
		/**
		 * This function returns a shortcode based on model parameters.
		 */
		getShortcode: function( attributes ) {
			// Defaults
			attributes = attributes || {
				id: this.get( 'insert_query' )
			};

			var shortcode = '';

			// Bail if we don't have at least an id
			if ( ! attributes.id ) {
				return shortcode;
			}

			/*
			 * Build the shortcode string.
			 */
			shortcode += '[' + conductor_query_builder.shortcode;

			// Loop through the attributes
			_.each( attributes, function ( value, attribute ) {
				// If we have an attribute value
				if ( value ) {
					// Append the attribute to the shortcode
					// TODO: UnderscoreJS template?
					shortcode += ' ' + attribute + '="' + value + '"';
				}
			} );

			shortcode += ']';

			return shortcode;
		}
	} );

	/**
	 * Conductor Query Builder Shortcode Backbone Collection
	 */
	conductor_query_builder.Backbone.Collections.Shortcode = Backbone.Collection.extend( {} );

	/**
	 * Conductor Query Builder Shortcode Backbone View
	 */
	conductor_query_builder.Backbone.Views.Shortcode = wp.Backbone.View.extend( {
		el: '#conductor-qb-shortcode-wrapper',
		has_user_changed_feature_type: false,
		feature_type_value: '',
		// Events
		events: {
			'change .conductor-select-feature-type': 'setHasUserChangedFeatureTypeFlag',
			'click .conductor-qb-shortcode-tabs .nav-tab': 'switchShortcodeActionButton',
			'click .conductor-qb-shortcode-action-button': 'shortcodeActionButton',
			'change #conductor-qb-shortcode-insert-query': 'setInsertQuery',
			'keyup #conductor-qb-shortcode-insert-title': 'setInsertTitle',
			'change #conductor-qb-shortcode-insert-title': 'setInsertTitle',
			'keyup #conductor-qb-shortcode-create-title': 'setCreateTitle',
			'change #conductor-qb-shortcode-create-title': 'setCreateTitle'
		},
		/**
		 * This function runs on initialization of the view.
		 */
		initialize: function( options ) {
			// Bind "this" to all functions
			_.bindAll(
				this,
				'render',
				'setHasUserChangedFeatureTypeFlag',
				'switchShortcodeActionButton',
				'shortcodeActionButton',
				'setInsertQuery',
				'setCreateTitle'
			);
		},
		/**
		 * This function renders the view.
		 */
		render: function() {
			// Call (apply) the default wp.Backbone.View render function
			wp.Backbone.View.prototype.render.apply( this, arguments );

			return this;
		},
		/**
		 * This function resets the fields within this view
		 */
		reset: function() {
			// Reset the insert title value
			this.$el.find( '#conductor-qb-shortcode-insert-title' ).val( '' ).change();

			// Reset the insert query value
			this.$el.find( '#conductor-qb-shortcode-insert-query' ).val( '' ).change();

			// Reset the create title value
			this.$el.find( '#conductor-qb-shortcode-create-title' ).val( '' ).change();
		},
		/**
		 * This function sets the has user changed feature type flag.
		 */
		setHasUserChangedFeatureTypeFlag: function( event ) {
			// Store the current feature type value
			this.feature_type_value = $( event.currentTarget ).val();

			// Bail if the has user changed feature type flag is set
			if ( this.has_user_changed_feature_type ) {
				return;
			}

			// Set the has user changed feature type flag based on the feature type focused flag
			this.has_user_changed_feature_type = true;
		},
		/**
		 * This function adjusts the shortcode action button data.
		 */
		switchShortcodeActionButton: function ( event ) {
			var $this = $( event.currentTarget ),
				button_type = $this.data( 'shortcode-action' );

			// Adjust the button type
			Conductor_Query_Builder_Shortcode_Actions_View.options.button_type = button_type;

			// Adjust the label
			Conductor_Query_Builder_Shortcode_Actions_View.options.label = conductor_query_builder.l10n.shortcode[button_type];

			// Re-render the view
			Conductor_Query_Builder_Shortcode_Actions_View.render();

			// Maybe toggle action button
			this.maybeToggleActionButton( button_type );
		},
		/**
		 * This function toggles the action button if required fields are not entered.
		 */
		maybeToggleActionButton: function( type ) {
			var $button = Conductor_Query_Builder_Shortcode_Actions_View.$el.find( '.conductor-qb-shortcode-action-button' ),
				value;

			// Defaults
			type = type || $button.data( 'type' );

			// Switch based on type
			switch ( type ) {
				// Insert
				case 'insert':
					// Grab the insert query value
					value = this.model.get( 'insert_query' );

					// If the insert query isn't set and the button is enabled
					if ( ! value && ! $button.prop( 'disabled' ) ) {
						// Disable the button
						Conductor_Query_Builder_Shortcode_Actions_View.disableActionButton();
					}
					// Otherwise if we have an insert query and the button is disabled
					else if ( value && $button.prop( 'disabled' ) ) {
						// Enable the button
						Conductor_Query_Builder_Shortcode_Actions_View.enableActionButton();
					}
				break;

				// Create
				case 'create':
					// Grab the create title value
					value = this.model.get( 'create_title' );

					// If the create title isn't set and the button is enabled
					if ( ! value && ! $button.prop( 'disabled' ) ) {
						// Disable the button
						Conductor_Query_Builder_Shortcode_Actions_View.disableActionButton();
					}
					// Otherwise if we have a create title and the button is disabled
					else if ( value && $button.prop( 'disabled' ) ) {
						// Enable the button
						Conductor_Query_Builder_Shortcode_Actions_View.enableActionButton();
					}
				break;
			}
		},
		/**
		 * This function performs the shortcode action button action.
		 */
		shortcodeActionButton: function ( event ) {
			var $this = $( event.currentTarget ),
				type = $this.data( 'type' ),
				shortcode;

			// Prevent default
			event.preventDefault();

			// Add the active CSS classes to the spinner
			Conductor_Query_Builder_Shortcode_Actions_View.$el.find( '.conductor-qb-loading' ).addClass( 'is-active conductor-qb-spinner-is-active' );

			// Switch based on type
			switch ( type ) {
				// Insert
				case 'insert':
					// Grab the shortcode
					shortcode = this.model.getShortcode( {
						id: this.model.get( 'insert_query' ),
						title: this.model.get( 'insert_title' )
					} );

					// If we have a shortcode
					if ( shortcode ) {
						// Set the shortcode to the editor
						send_to_editor( shortcode );
					}
					// Otherwise close the thickbox
					else {
						tb_remove();
					}

					// Remove the active CSS classes from the spinner
					Conductor_Query_Builder_Shortcode_Actions_View.$el.find( '.conductor-qb-loading' ).removeClass( 'is-active conductor-qb-spinner-is-active' );
				break;

				// Create
				case 'create':
					// Make the AJAX request (POST)
					wp.ajax.post( conductor_query_builder.ajax.shortcode.action, this.ajax.setupAJAXData( conductor_query_builder.ajax.shortcode.nonce, conductor_query_builder.ajax.shortcode.action ) ).done( this.ajax.success ).fail( this.ajax.fail );

					// Disable the button
					Conductor_Query_Builder_Shortcode_Actions_View.disableActionButton();
				break;
			}
		},
		/**
		 * This function sets the insert query on the model.
		 */
		setInsertQuery: function( event ) {
			var $this = $( event.currentTarget ),
				value = $this.val();

			// Set the insert query on the model
			this.model.set( 'insert_query', value );

			// Maybe toggle action button
			this.maybeToggleActionButton();
		},
		/**
		 * This function sets the insert title on the model.
		 */
		setInsertTitle: function( event ) {
			var $this = $( event.currentTarget ),
				value = $this.val();

			// Set the insert title on the model
			this.model.set( 'insert_title', value );
		},
		/**
		 * This function sets the create title on the model.
		 */
		setCreateTitle: function( event ) {
			var $this = $( event.currentTarget ),
				value = $this.val();

			// Set the insert query on the model
			this.model.set( 'create_title', value );

			// Maybe toggle action button
			this.maybeToggleActionButton();
		},
		/**
		 * AJAX
		 *
		 * AJAX data and functions.
		 */
		ajax: {
			// Default AJAX data
			data: {
				conductor_query_builder: 1
			},
			/**
			 * This function sets up AJAX data.
			 */
			setupAJAXData: function( nonce, nonce_action ) {
				return Conductor_Query_Builder_Wrapper_View.ajax.setupAJAXData( -1, nonce, nonce_action, Conductor_Query_Builder_Shortcode_View.$el.find( '.conductor-qb-shortcode-create-form' ) );
			},
			/**
			 * This function runs on a successful AJAX request.
			 */
			success: function( response ) {
				var shortcode = Conductor_Query_Builder_Shortcode_View.model.getShortcode( {
					id: response.ID,
					title: response.title
				} );

				// If we have a shortcode
				if ( shortcode ) {
					// Send the shortcode to the editor
					send_to_editor( shortcode );

					// Add this query to the global queries
					conductor_query_builder.queries.push( {
						ID: response.ID,
						post_title: response.title
					} );

					// Re-render the shortcode insert Backbone view
					Conductor_Query_Builder_Shortcode_Insert_View.render();
				}
				// Otherwise close the thickbox
				else {
					tb_remove();
				}

				// Remove the active CSS classes from the spinner
				Conductor_Query_Builder_Shortcode_Actions_View.$el.find( '.conductor-qb-loading' ).removeClass( 'is-active conductor-qb-spinner-is-active' );
			},
			/**
			 * This function runs on a failed AJAX request.
			 */
			fail: function( response ) {
				// Enable the button
				Conductor_Query_Builder_Shortcode_Actions_View.enableActionButton();

				// TODO: Utilize fail l10n message
			}
		},
		/**
		 * This function determines if the simple query builder query arguments are empty.
		 */
		isSimpleQueryArgsEmpty: function() {
			var $conductor_widget = this.$el.find( '.widget' ),
				$conductor_section_general = $conductor_widget.find( '.conductor-section-general' ),
				is_query_args_empty = true;

			// Bail if we're not in the simple query builder mode
			if ( window.getUserSetting( conductor_query_builder.user.settings['query-builder'].mode.name ) !== 'simple' ) {
				return is_query_args_empty;
			}

			// Loop through all input elements
			$conductor_section_general.find( ':input' ).each( function() {
				var $this = $( this ),
					value = $this.val(),
					$option;

				// Bail if we don't have empty query arguments
				if ( ! is_query_args_empty ) {
					return;
				}

				// Switch based on element node name
				switch ( this.nodeName.toLowerCase() ) {
					// Input
					case 'input':
						// If the current value doesn't equal the default value
						if ( value !== this.defaultValue ) {
							// Set the query arguments flag to false
							is_query_args_empty = false;
						}
					break;

					// Select
					case 'select':
						// Grab the option for the current value
						$option = $this.find( 'option[value="' + value + '"]' );

						// If the current selected option isn't the default option
						if ( ! $this.find( 'option[value="' + value + '"]' )[0].defaultSelected && $option.index() !== 0 ) {
							// Set the query arguments flag to false
							is_query_args_empty = false;
						}
					break;
				}
			} );

			return is_query_args_empty;
		}
	} );

	/**
	 * Conductor Query Builder Shortcode Actions Backbone View
	 */
	conductor_query_builder.Backbone.Views.Shortcode_Actions = wp.Backbone.View.extend( {
		id: 'conductor-qb-shortcode-actions-inner',
		el_selector: '#conductor-qb-shortcode-actions',
		template: wp.template( 'conductor-qb-shortcode-query-builder-actions' ),
		/**
		 * This function runs on initialization of the view.
		 */
		initialize: function( options ) {
			// Bind "this" to all functions
			_.bindAll(
				this,
				'render'
			);
		},
		/**
		 * This function renders the view.
		 */
		render: function() {
			// Call (apply) the default wp.Backbone.View render function
			wp.Backbone.View.prototype.render.apply( this, arguments );

			return this;
		},
		/**
		 * This function disables the action button.
		 */
		disableActionButton: function() {
			var $button = this.$el.find( '.conductor-qb-shortcode-action-button' );

			// Disable the button
			$button.prop( 'disabled', true );
		},
		/**
		 * This function enables the action button.
		 */
		enableActionButton: function() {
			var $button = this.$el.find( '.conductor-qb-shortcode-action-button' );

			// Enable the button
			$button.prop( 'disabled', false );
		}
	} );

	/**
	 * Conductor Query Builder Shortcode Insert Backbone View
	 */
	conductor_query_builder.Backbone.Views.Shortcode_Insert = wp.Backbone.View.extend( {
		id: 'conductor-qb-shortcode-insert-inner',
		el_selector: '#conductor-qb-shortcode-insert',
		select2_selector: '.conductor-qb-select2',
		template: wp.template( 'conductor-qb-shortcode-query-builder-insert' ),
		/**
		 * This function runs on initialization of the view.
		 */
		initialize: function( options ) {
			// Bind "this" to all functions
			_.bindAll(
				this,
				'render'
			);
		},
		/**
		 * This function renders the view.
		 */
		render: function() {
			// Call (apply) the default wp.Backbone.View render function
			wp.Backbone.View.prototype.render.apply( this, arguments );

			// Initialize Select2
			this.initializeSelect2();

			return this;
		},
		/**
		 * This function resets the fields within this view
		 */
		reset: function() {
			// Reset the insert title value
			this.$el.find( '#conductor-qb-shortcode-insert-title' ).val( '' ).change();

			// Reset the insert query value
			this.$el.find( '#conductor-qb-shortcode-insert-query' ).val( '' ).change();

			// Reset the create title value
			this.$el.find( '#conductor-qb-shortcode-create-title' ).val( '' ).change();

			// Destroy Select2
			this.destroySelect2();
		},
		/**
		 * This function initializes Select2.
		 */
		initializeSelect2: function() {
			var self = this;

			// Initialize Select2 (new thread)
			setTimeout( function() {
				self.$el.find( self.select2_selector ).conductor_qb_select2( {
					dropdownParent: $( '#TB_ajaxContent' )
				} );
			}, 1 );
		},
		/**
		 * This function destroys all Select2 instances.
		 */
		destroySelect2: function() {
			var $select2 = this.$el.find( this.select2_selector );

			// Loop through possible Select2 instances
			$select2.each( function() {
				var $this = $( this ),
					Select2 = $this.data( 'select2' );

				// If we have a Select2 instance
				if ( Select2 ) {
					// Destroy Select2
					$select2.conductor_qb_select2( 'destroy' );
				}
			} );
		},
	} );

	/**
	 * Conductor Query Builder Shortcode Create Title Backbone View
	 */
	conductor_query_builder.Backbone.Views.Shortcode_Create_Title = wp.Backbone.View.extend( {
		id: 'conductor-qb-shortcode-create-title-inner',
		el_selector: '#conductor-qb-shortcode-create-title-content',
		template: wp.template( 'conductor-qb-shortcode-query-builder-create-title' ),
		/**
		 * This function runs on initialization of the view.
		 */
		initialize: function( options ) {
			// Bind "this" to all functions
			_.bindAll(
				this,
				'render'
			);
		},
		/**
		 * This function renders the view.
		 */
		render: function() {
			// Call (apply) the default wp.Backbone.View render function
			wp.Backbone.View.prototype.render.apply( this, arguments );

			return this;
		}
	} );


	/**
	 * Document Ready
	 */
	$( function() {
		var bodyMutationObserver,
			$form = $( '#post' ),
			$tabs = $( '.conductor-qb-tabs a' ),
			$add_shortcode = $( '.conductor-qb-add-shortcode' );

		// Setup the document element
		$document = $( document );

		// Setup the body element
		$body = $( 'body' );

		// Setup the Conductor Query Builder preview element
		$conductor_qb_preview = $( '#conductor-query-builder-preview' );

		/**
		 * Navigation Tabs
		 */
		$tabs.on( 'click.conductor-qb', function ( event ) {
			var $this = $( this ),
				$sibling_tabs = $this.siblings(),
				$tabs_wrapper = $this.parents( '.conductor-qb-tabs-wrapper' ),
				$tab_content = $tabs_wrapper.next( '.conductor-qb-tab-content-wrapper' ).find( '.conductor-qb-tab' ),
				tab_id = $this.attr( 'href' );

			// Filter the tabs to be sure we only have the tabs we're looking for
			$tab_content = $tab_content.filter( '[data-type="' + $this.data( 'type' )+ '"]' );

			// Prevent default
			event.preventDefault();

			// Remove active classes
			$sibling_tabs.removeClass( 'nav-tab-active' );
			$tab_content.removeClass( 'active' );

			// Activate new tab
			$this.addClass( 'nav-tab-active' );
			$( tab_id ).addClass( 'active' );
		} );


		/**
		 * Query Builder
		 */

		// If we're on the query builder post type
		if ( conductor_query_builder.flags.is_query_builder_post_type ) {
			// Initialize the query builder Backbone components
			conductor_query_builder.fn.query_builder.init();
		}


		/**
		 * Shortcodes
		 */

		// Add shortcode
		$add_shortcode.on( 'click.conductor-qb', function( event ) {
			// Add our custom CSS class to the body element
			$body.addClass( 'conductor-shortcode-ui-visible' );

			// If we don't already have the Backbone views setup
			if ( ! Conductor_Query_Builder_Shortcode_View ) {
				// Add our custom CSS class to the body element
				$body.addClass( 'conductor-shortcode-ui-initialized' );

				// Initialize the shortcode Backbone components
				conductor_query_builder.fn.shortcode.init();

				// Initialize the query builder Backbone components
				conductor_query_builder.fn.query_builder.init( true );

				// TODO: Transition to "conductor-query-builder-{event}" event name
				// Trigger the "conductor-qb-shortcode-init" event on the query builder view
				Conductor_Query_Builder_View.trigger( 'conductor-qb-shortcode-init' );

				// TODO: Transition to "conductor-query-builder-{event}" event name
				// Trigger the "conductor-qb-shortcode-init" event on the query builder view element
				Conductor_Query_Builder_View.$el.trigger( 'conductor-qb-shortcode-init' );
			}
			// Otherwise if we have the Backbone views setup
			else {
				// Reset the shortcode Backbone components
				conductor_query_builder.fn.shortcode.reset();

				// Re-render the shortcode insert Backbone view
				Conductor_Query_Builder_Shortcode_Insert_View.render();

				// Reset the query builder Backbone components
				conductor_query_builder.fn.query_builder.reset();

				// Call the initialize function on the query builder view
				Conductor_Query_Builder_View.initialize();

				// TODO: Transition to "conductor-query-builder-{event}" event name
				// Trigger the "conductor-qb-shortcode-reset" event on the query builder view
				Conductor_Query_Builder_View.trigger( 'conductor-qb-shortcode-reset' );

				// TODO: Transition to "conductor-query-builder-{event}" event name
				// Trigger the "conductor-qb-shortcode-reset" event on the query builder view element
				Conductor_Query_Builder_View.$el.trigger( 'conductor-qb-shortcode-reset' );
			}

			// Trigger an event on the document
			$document.trigger( 'conductor-query-builder-thickbox-init', [ Conductor_Query_Builder_Shortcode_View, Conductor_Query_Builder_View ] );

			// Show the Thickbox
			tb_show( conductor_query_builder.l10n.shortcode.title, '#TB_inline?inlineId=conductor-qb-shortcode-wrapper-container&width=753&height=480', false );

			// If the query builder mode is set to advanced
			if ( window.getUserSetting( conductor_query_builder.user.settings['query-builder'].mode.name ) === 'advanced' ) {
				// Start a new thread; delay 1ms
				setTimeout( function() {
					// Loop through clause group action Backbone Views
					_.each( conductor_query_builder.Backbone.instances.views.clause_action_buttons, function ( view ) {
						// Show all query builder action button elements
						view.$el.find( view.action_button_selector ).removeClass( 'hide hidden conductor-qb-hide conductor-qb-hidden' );
					} );
				}, 1 );
			}
		} );

		// Add an event listener to the thickbox remove event
		$body.on( 'thickbox:removed', function() {
			// If the body element contains our custom CSS class
			if ( $body.hasClass( 'conductor-shortcode-ui-visible' ) ) {
				// Reset the shortcode Backbone components
				conductor_query_builder.fn.shortcode.reset();

				// Reset the query builder Backbone components
				conductor_query_builder.fn.query_builder.reset();

				// Switch to the first tab (all tabs; trigger the click event)
				Conductor_Query_Builder_Shortcode_View.$el.find( '.conductor-qb-tabs .nav-tab:first-child' ).trigger( 'click' );

				// Reset the query builder Conductor widget components
				conductor_query_builder.fn.conductor.widget.reset();

				// Trigger an event on the document
				$document.trigger( 'conductor-query-builder-thickbox-removed', [ Conductor_Query_Builder_Shortcode_View, Conductor_Query_Builder_View ] );

				// Remove our custom CSS class to the body element (delay 1ms; new thread)
				setTimeout( function() {
					$body.removeClass( 'conductor-shortcode-ui-visible' );
				}, 1 );
			}
		} );

		// Add an event listener to the Select2 search fields keydown event
		$document.on( 'keydown.conductor-qb', '.select2-search__field, .select2-search--inline', function( event ) {
			// Bail if the shortcode UI is not visible
			if ( ! $body.hasClass( 'conductor-shortcode-ui-visible' ) ) {
				return;
			}

			// If the escape key was pressed
			if ( event.which === 27 ) {
				// Stop propagation
				event.stopImmediatePropagation();
			}
		} );

		// Add an event listener to the document keydown event
		$document.on( 'keydown.conductor-qb', function( event ) {
			// Bail if the shortcode UI is not visible
			if ( ! $body.hasClass( 'conductor-shortcode-ui-visible' ) ) {
				return;
			}

			// If the escape key was pressed
			if ( event.which === 27 ) {
				// If we have meta and the confirmation was cancelled
				if ( ( ! Conductor_Query_Builder_Sub_Clause_Group_Collection.isMetaEmpty() || ! Conductor_Query_Builder_Shortcode_View.isSimpleQueryArgsEmpty() ) && ! window.confirm( conductor_query_builder.l10n.shortcode.confirm ) ) {
					// Stop propagation
					event.stopImmediatePropagation();
				}
			}
		} );


		/*
		 * Create a MutationObserver to listen for when the thickbox nodes
		 * are added to the body and add an event listener to the close button
		 * since we can't listen for this element dynamically as it is added and removed
		 * each time thickbox is opened and closed.
		 *
		 * This ensures that our event listener is added before thickbox's.
		 */
		bodyMutationObserver = new MutationObserver( function( mutations ) {
			// Bail if the shortcode UI is not visible
			if ( ! $body.hasClass( 'conductor-shortcode-ui-visible' ) ) {
				return;
			}

			// Loop through mutations
			_.each( mutations, function( mutation ) {
				// If we have added nodes
				if ( mutation.addedNodes && mutation.addedNodes.length ) {
					// Loop through added nodes
					_.each( mutation.addedNodes, function ( node ) {
						// If this is the thickbox window
						if ( node.id === 'TB_window' ) {
							var $tb_close_els = $( '#TB_overlay, #TB_closeWindowButton' );

							// Add an event listener to the click event on thickbox close elements
							$tb_close_els.on( 'click.conductor-qb', function( event ) {
								// Bail if the shortcode UI is not visible
								if ( ! $body.hasClass( 'conductor-shortcode-ui-visible' ) ) {
									return;
								}

								// If we have meta and the confirmation was cancelled
								if ( ( ! Conductor_Query_Builder_Sub_Clause_Group_Collection.isMetaEmpty() || ! Conductor_Query_Builder_Shortcode_View.isSimpleQueryArgsEmpty() ) && ! window.confirm( conductor_query_builder.l10n.shortcode.confirm ) ) {
									// Stop propagation
									event.stopImmediatePropagation();
								}
							} );

							// Ensure our event is at the front of the queue
							$tb_close_els.each( function() {
								var $this = $( this ),
									events = jQuery._data( this, 'events' ),
									the_index = -1,
									the_event;

								// If we have click event data
								if ( events && events.click && events.click.length ) {
									// Loop through the click events
									_.each( events.click, function ( event, index ) {
										// If the namespace of this event matches ours
										if ( event.namespace === 'conductor-qb' ) {
											the_index = index;
										}
									} );

									// If we have an index for our event
									if ( the_index !== -1 ) {
										// Remove our event
										the_event = events.click.splice( the_index, 1 );

										// If we have an event
										if ( the_event.length ) {
											// Splice our event at the beginning of the array
											events.click.splice( 0, 0, the_event[0] );
										}
									}

									// Save the updated events data on the elements
									jQuery._data( this, 'events', events );
								}
							} );
						}
					} );
				}
			} );
		});

		// Observe
		bodyMutationObserver.observe( $body[0], {
			childList: true // Listen for elements added or removed to the body element
		} );


		/**
		 * Post Form Submission
		 */
		$form.submit( function( event ) {
			var $conductor_qb_select2 = $( '.conductor-qb-select2' );

			// Loop through all Conductor Query Builder Select2 elements
			$conductor_qb_select2.each( function() {
				var $this = $( this ),
					type = $this.data( 'type' ),
					multiple = $this.prop( 'multiple' ),
					selected_options = $this.data( 'selected-options' );

				// Bail if this isn't a values select element, this isn't a multiple select element, or we don't have any selected options
				if ( type !== 'values' || ! multiple || ! selected_options.length ) {
					return;
				}

				// Loop through the selected values
				_.each( selected_options.reverse(), function( value ) {
					var $option = $this.find( 'option[value="' + value + '"]' );

					// If we have an option for this value
					if ( $option.length ) {
						// Move this option to the top of the list
						$this.prepend( $option );
					}
				} );
			} );
		} );
	} );
}( wp, jQuery ) );