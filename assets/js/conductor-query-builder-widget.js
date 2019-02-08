/**
 * Conductor Query Builder Widget
 */

( function ( $, conductor_query_builder, conductor_widget ) {
	"use strict";

	// Defaults
	if ( ! conductor_query_builder.hasOwnProperty( 'Backbone' ) ) {
		conductor_query_builder.Backbone = {
			Views: {},
			Models: {},
			Collections: {},
			instances: {
				current_view_for_ajax: false,
				models: {},
				collections: {},
				views: {}
			}
		};
	}

	// Default functions
	if ( ! conductor_query_builder.hasOwnProperty( 'fn' ) ) {
		conductor_query_builder.fn = {
			/**
			 * Backbone
			 */
			Backbone: {
				/**
				 * Views
				 */
				views: {
					/**
					 * This function gets a view instance based on the widget ID.
					 */
					get: function( widget_id ) {
						return conductor_query_builder.Backbone.instances.views[widget_id];
					}
				}
			}
		};
	}


	/************
	 * Backbone *
	 ************/

	/**
	 * Conductor Query Builder Backbone View
	 */
	conductor_query_builder.Backbone.Views.Conductor_Widget_Query_Builder = wp.Backbone.View.extend( {
		/*
		 * Events
		 */
		events: {
			// TODO
		},
		/**
		 * AJAX
		 *
		 * AJAX data and functions.
		 */
		ajax: {
			/*
			 * Data
			 */
			data: {
				/*
				 * Default AJAX data
				 */
			default: {
					conductor_query_builder: true
				}
			}
		},
		/**
		 * This function runs on initialization of the view.
		 */
		initialize: function( options ) {
			var widget_id = this.$el.data( 'widget-id' ),
				conductor_widget_view = conductor_widget.fn.Backbone.views.get( widget_id );

			// Bind "this" to all functions
			_.bindAll(
				this,
				'conductorWidgetAJAXSetupData'
			);

			/*
			 * listenTo events
			 */
			// "conductor-widget-ajax-setup-data" event
			this.listenTo( conductor_widget_view, 'conductor-widget-ajax-setup-data', this.conductorWidgetAJAXSetupData );
			// "conductor-widget-ajax-processing" event
			this.listenTo( conductor_widget_view, 'conductor-widget-ajax-processing', this.conductorWidgetAJAXProcessing );
		},
		/**
		 * This function adjusts Conductor Widget AJAX data.
		 */
		conductorWidgetAJAXSetupData: function( data, action, event, view ) {
			// Add the Conductor Query Builder default AJAX data to the AJAX data
			$.extend( data, this.ajax.data.default );

			// If the query builder data doesn't exist
			if ( ! data.query_builder ) {
				// Add the query builder data
				data.query_builder = {
					post_id: this.$el.data( 'query-builder-post-id' ),
					type: this.$el.data( 'query-builder-type' ),
					number: this.$el.data( 'query-builder-number' ),
					widget_number: this.$el.data( 'query-builder-widget-number' )
				}
			}
		},
		/**
		 * This function runs when the Conductor Widget is processing AJAX.
		 */
		conductorWidgetAJAXProcessing: function( is_processing, view ) {
			// If we're processing
			if ( is_processing ) {
				// Set the current view reference
				conductor_query_builder.Backbone.instances.current_view_for_ajax = this;
			}
			// Otherwise we're not processing
			else {
				// Reset the current view reference
				conductor_query_builder.Backbone.instances.current_view_for_ajax = false;
			}
		}
	} );


	/**
	 * Document Ready
	 */
	$( function() {
		var $body = $( 'body' ),
			$conductor_widgets = $( conductor_widget.css_selectors.widget_wrap );

		// If we have Conductor Widgets
		if ( $conductor_widgets.length ) {
			// Loop through the Conductor Widgets
			$conductor_widgets.each( function() {
				var $this = $( this ),
					id = $this.attr( 'id' ),
					widget_id = $this.data( 'widget-id' );

				// If this Conductor Widget has the query builder CSS class
				if ( $this.hasClass( conductor_query_builder.css_classes.widget_wrap ) ) {
					// Create a new Conductor Widget Query Builder view
					conductor_query_builder.Backbone.instances.views[widget_id] = new conductor_query_builder.Backbone.Views.Conductor_Widget_Query_Builder( {
						el: '#' + id
					} );
				}
			} );
		}

		/**
		 * This function runs when the Conductor Widget initialize event is triggered on the body element.
		 */
		// TODO: Future: If we have a view, destroy it first if we're forcing a new init
		$body.on( 'conductor-widget-init', function( event, $widgets, force ) {
			// Defaults
			$widgets = $widgets || $( conductor_widget.css_selectors.widget_wrap );
			force = force || false;

			// If we have widgets
			if ( $widgets.length ) {
				// Loop through the widgets
				$widgets.each( function() {
					var $this = $( this ),
						id = $this.attr( 'id' ),
						widget_id = $this.data( 'widget-id' ),
						view = conductor_query_builder.fn.Backbone.views.get( widget_id );

					// If we're forcing or don't have a view and this widget has the query builder CSS class
					if ( force || ( ! view && $this.hasClass( conductor_query_builder.css_classes.widget_wrap ) ) ) {
						// Create a new Conductor Widget Query Builder view
						conductor_query_builder.Backbone.instances.views[widget_id] = new conductor_query_builder.Backbone.Views.Conductor_Widget_Query_Builder( {
							el: '#' + id
						} );
					}
				} );
			}
		} );
	} );
}( jQuery, window.conductor_query_builder, window.conductor_widget ) );