/**
 * Conductor Query Builder - Beaver Builder Module Settings
 */

( function( $, FLBuilder, FLBuilderSettingsForms ) {
	"use strict";


	/**
	 * Conductor Query Builder - Beaver Builder Module
	 */
	FLBuilder._registerModuleHelper( 'class-conductor-query-builder-beaver-builder-module', {
		$body: $( 'body' ),
		$conductor_widget: false,
		$conductor_widget_accordion_sections: false,
		$conductor_query_builder_beaver_builder_post_meta: false,
		$conductor_query_builder_beaver_builder_title: false,
		$conductor_query_builder_beaver_builder_conductor_widget: false,
		$conductor_query_builder_beaver_builder_update_count: false,
		$document: $( document ),
		$fl_lightbox_header: false,
		$tabs: false,
		observer: false,
		/**
		 * This function runs when the module has been initialized.
		 */
		init: function() {
			var self = this,
				meta;

			// Initialize properties on the class
			this.$conductor_widget = $( '#conductor-query-builder-conductor-widget' );
			this.$conductor_widget_accordion_sections = this.$conductor_widget.find( '.conductor-accordion-section' );
			this.$conductor_query_builder_beaver_builder_post_meta = $( '#conductor-query-builder-beaver-builder-post-meta' );
			this.$conductor_query_builder_beaver_builder_title = $( '#conductor-query-builder-beaver-builder-title' );
			this.$conductor_query_builder_beaver_builder_conductor_widget = $( '#conductor-query-builder-beaver-builder-conductor-widget' );
			this.$conductor_query_builder_beaver_builder_update_count = $( '.conductor-query-builder-beaver-builder-update-count' );
			this.$fl_lightbox_header = $( '.fl-lightbox-header' );
			this.$tabs = this.$body.find( '.conductor-qb-tabs a' );

			// Add our custom CSS class to the body element
			this.$body.addClass( 'conductor-shortcode-ui-visible' );

			// Remove the "change" events on the document
			this.$document.off( 'conductor-query-builder-preview-query.conductor-qb keyup.conductor-qb input.conductor-qb change.conductor-qb' );

			// Grab the Conductor Query Builder meta
			meta = JSON.parse( this.$conductor_query_builder_beaver_builder_post_meta.val() );

			// Set the Conductor Query Builder title
			conductor_query_builder.title = this.$conductor_query_builder_beaver_builder_title.val();

			// Set the Conductor Query Builder meta
			conductor_query_builder.meta = meta;

			// Initialize the shortcode Backbone components
			conductor_query_builder.fn.shortcode.init();

			// Initialize the query builder Backbone components
			conductor_query_builder.fn.query_builder.init( true );

			// If we don't have a MutationObserver
			if ( ! this.observer ) {
				// Create the MutationObserver
				this.observer = new MutationObserver( function( mutations ) {
					mutations.forEach( function( mutation ) {
						// If we have added nodes
						if ( mutation.addedNodes && mutation.addedNodes.length ) {
							// Loop through the added nodes
							mutation.addedNodes.forEach( function( node ) {
								// If this is the Beaver Builder preview loaded
								if ( node.className.indexOf( 'fl-builder-preview-loader' ) !== -1 && self.$fl_lightbox_header.find( '.fl-builder-preview-loader' ).length === 0 ) {
									// Move the Beaver Builder preview loader to header
									self.$fl_lightbox_header.append( node );
								}
							} );
						}
					} );
				} );
			}

			/**
			 * Navigation Tabs
			 */
			this.$tabs.on( 'click.conductor-qb', function ( event ) {
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

			// Find the "create" tab and set that to the active tab
			this.$tabs.filter( '[data-shortcode-action="create"]' ).click();



			/**
			 * Conductor Widgets
			 */

			// Trigger the "widget-added" event to ensure the Conductor Widget output logic is initialized
			this.$document.trigger( 'widget-added', [ this.$conductor_widget ] );

			// Trigger the "change" event on the Conductor Widget feature type to ensure the correct elements are displayed
			this.$conductor_widget.find( '.conductor-select-feature-type' ).trigger( 'change' );

			// Delay 1ms; new thread
			setTimeout( function() {
				/**
				 * Conductor Widgets
				 */
				// Loop through Conductor Widgets accordion sections
				self.$conductor_widget_accordion_sections.each( function() {
					var $this = $( this ),
						$accordion_title = $this.find( '.conductor-accordion-section-title' ),
						$accordion_content = $this.find( '.conductor-accordion-section-content' );

					// If this accordion section isn't open
					if ( $accordion_title.length && ! $this.hasClass( 'open' ) ) {
						// Toggle it open now (no delay)
						$accordion_content.slideToggle( 0 );
					}
				} );
			}, 1 );

			/**
			 * This function runs when change events occur on all input elements within the Conductor
			 * Query Builder UI.
			 */
			// Delay 250ms; new thread
			setTimeout( function() {
				self.$document.on( 'keyup.conductor-qb input.conductor-qb change.conductor-qb', '#' + conductor_query_builder.Backbone.instances.views.shortcode.$el.attr( 'id' ) + ' :input', function() {
					// Set the update count value and trigger the "keyup" event (Beaver Builder uses this event for previewing)
					self.$conductor_query_builder_beaver_builder_update_count.val( ( parseInt( self.$conductor_query_builder_beaver_builder_update_count.val(), 10 ) + 1 ) ).trigger( 'keyup' );
				} );

				self.$document.on( 'conductor-query-builder-preview-query.conductor-qb', function() {
					// Set the update count value and trigger the "keyup" event (Beaver Builder uses this event for previewing)
					self.$conductor_query_builder_beaver_builder_update_count.val( ( parseInt( self.$conductor_query_builder_beaver_builder_update_count.val(), 10 ) + 1 ) ).trigger( 'keyup' );
				} );

				// Loop through Beaver Builder field labels
				$( '.fl-field-label' ).each( function() {
					// Initialize the MutationObserver on this field label
					self.observer.observe( this, {
						childList: true
					} );
				} );
			}, 250 );
		},
		/**
		 * This function runs when the module has been submitted.
		 */
		submit: function() {
			// Disconnect the MutationObserver
			this.observer.disconnect();

			return true;
		}
	} );


	/**
	 * This function runs when the Beaver Builder layout rendering is complete.
	 */
	FLBuilder.addHook( 'didRenderLayoutComplete', function() {
		// If this is a Conductor Query Builder Beaver Builder Module
		if ( FLBuilderSettingsForms && FLBuilderSettingsForms.config && FLBuilderSettingsForms.config.id && FLBuilderSettingsForms.config.id === 'class-conductor-query-builder-beaver-builder-module' ) {
			// Start a new thread; delay 1ms
			setTimeout( function() {
				// Trigger the Conductor Widget initialize event on the body element (force)
				$( 'body' ).trigger( 'conductor-widget-init', [ null, true ] );
			}, 1 );
		}
	} );

	/**
	 * This function runs when the Beaver Builder layout is published.
	 */
	FLBuilder.addHook( 'didPublishLayout', function() {
		// Trigger the Conductor Widget initialize event on the body element (force)
		$( 'body' ).trigger( 'conductor-widget-init', [ null, true ] );
	} );
} )( jQuery, FLBuilder, FLBuilderSettingsForms );