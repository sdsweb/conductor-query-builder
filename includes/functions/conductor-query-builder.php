<?php
/**
 * Conductor Query Builder Functions
 *
 * @author Slocum Studio
 * @version 1.0.3
 * @since 1.0.3
 */

/**
 * This function renders a Conductor Query.
 */
if ( ! function_exists( 'conductor_query' ) ) {
	function conductor_query( $id, $title = '' ) {
		// Grab the Conductor Query Builder instance
		$conductor_query_builder = Conduct_Query_Builder();

		// Render this Conductor Query
		$conductor_query_builder->render( ( int ) $id, $title, 'function' );
	}
}