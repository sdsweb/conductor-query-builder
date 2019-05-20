<?php
/**
 * Conductor Query Builder
 *
 * @class Conductor_Query_Builder
 * @author Slocum Studio
 * @version 1.0.6
 * @since 1.0.0
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'Conductor_Query_Builder' ) ) {
	class Conductor_Query_Builder {
		/**
		 * @var string
		 */
		public $version = '1.0.6';

		/**
		 * @var string
		 */
		public $post_type_name = 'conductor_qb_queries';

		/**
		 * @var string
		 */
		public $meta_key_prefix = 'conductor_query_builder_';

		/**
		 * @var string
		 */
		public $query_args_meta_key_suffix = '_query_args';

		/**
		 * @var string
		 */
		public $conductor_widget_meta_key_suffix = 'conductor_widget';

		/**
		 * @var string
		 */
		public $query_builder_mode_meta_key_suffix = 'query_builder_mode';

		/**
		 * @var array
		 */
		public $post_type_args = array();

		/**
		 * @var WP_Post_Type|stdClass
		 */
		public $post_type_object = false;

		/**
		 * @var array
		 */
		public $operators = array();

		/**
		 * @var array
		 */
		public $query_args_operators = array();

		/**
		 * @var array
		 */
		public $parameters = array();

		/**
		 * @var array
		 */
		public $values = array();

		/**
		 * @var array
		 */
		public $clauses = array();

		/**
		 * @var array
		 */
		public $query_builder_modes = array( 'simple', 'advanced' );

		/**
		 * @var array
		 */
		public $current_query_args = array();

		/**
		 * @var string
		 */
		public $current_query_builder_mode = false;

		/**
		 * @var array
		 */
		public $current_conductor_widget_instance = array();

		/**
		 * @var Boolean
		 */
		public $doing_conductor_query = false;

		/**
		 * @var Boolean
		 */
		public $doing_preview = false;

		/**
		 * @var array
		 */
		public $preview_data = array(
			'post_meta' => array(),
			'query_args' => array(),
			'query_builder_mode' => 'simple',
			'conductor_widget_instance' => array()
		);

		/**
		 * @var int, Number used for widget when calling the_widget()
		 */
		public $the_widget_number = -1;

		/**
		 * @var string, Temporary Query Builder sidebar ID
		 */
		public $temporary_sidebar_id = 'conductor-qb-temporary-sidebar';

		/**
		 * @var WP_Post, Reference to the global $post object
		 */
		public $global_post = null;

		/**
		 * @var array
		 */
		public $rendered = array(
			'shortcode' => array(),
			'widget' => array(),
			'preview' => array()
		);

		/**
		 * @var string
		 */
		public $shortcode = 'conductor';

		/**
		 * @var string
		 */
		public $function = 'conductor_query';

		/**
		 * @var Boolean
		 */
		public $is_active_widget = false;

		/**
		 * @var Conductor_Query_Builder, Instance of the class
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
		function __construct( $args = array() ) {
			// Load required assets
			$this->includes();

			// Hooks
			add_action( 'init', array( $this, 'init' ), 9999 ); // Init (Late)
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) ); // WordPress Enqueue Scripts
			add_filter( 'sidebars_widgets', array( $this, 'sidebars_widgets' ) ); // Sidebars Widgets
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 0 ); // Admin Enqueue Scripts (Very Early)
			add_action( 'media_buttons', array( $this, 'media_buttons' ) ); // Media Buttons
			add_action( 'save_post', array( $this, 'save_post' ) ); // Save Post
			add_action( 'admin_print_footer_scripts', array( $this, 'admin_print_footer_scripts' ) ); // Admin Print Footer Scripts

			// Hooks - Revisions
			// TODO: WordPress causes a trim() PHP warning to be displayed likely because our values are arrays
			//add_filter( 'wp_save_post_revision_post_has_changed', array( $this, 'wp_save_post_revision_post_has_changed' ), 10, 3 ); // WordPress Save Post Revision Post Has Changed
			//add_filter( '_wp_post_revision_fields', array( $this, '_wp_post_revision_fields' ), 10, 2 ); // WordPress Post Revision Fields
			//add_filter( '_wp_post_revision_field_' . $this->meta_key_prefix . $this->conductor_widget_meta_key_suffix, array( $this, '_wp_post_revision_field' ), 10, 4 ); // WordPress Post Revision Field - Conductor Query Builder Conductor Widget
			//add_filter( '_wp_post_revision_field_' . $this->meta_key_prefix . $this->conductor_widget_meta_key_suffix, array( $this, '_wp_post_revision_field' ), 10, 4 ); // WordPress Post Revision Field - Conductor Query Builder Query Builder Mode
			//foreach ( $this->get_clause_types() as $clause_type ) {
			//	add_filter( '_wp_post_revision_field_' . $this->meta_key_prefix . $clause_type, array( $this, '_wp_post_revision_field' ), 10, 4 ); // WordPress Post Revision Field - Conductor Query Builder Clause Type
			//	add_filter( '_wp_post_revision_field_' . $this->meta_key_prefix . $clause_type . $this->query_args_meta_key_suffix, array( $this, '_wp_post_revision_field' ), 10, 4 ); // WordPress Post Revision Field - Conductor Query Builder Clause Type Query Arguments
			//}
			//add_action( 'wp_restore_post_revision', array( $this, 'wp_restore_post_revision' ), 10, 2 ); // WordPress Restore Post Revision

			// Shortcodes
			add_shortcode( 'conductor', array( $this, 'conductor' ) ); // Conductor Query Builder Shortcode - [conductor]

			// AJAX Hooks
			add_action( 'wp_ajax_conductor-query-builder-create-query', array( $this, 'wp_ajax_conductor_query_builder_create_query' ) ); // Conductor Query Builder Create Query
			add_action( 'wp_ajax_conductor-query-builder-preview-query', array( $this, 'wp_ajax_conductor_query_builder_preview_query' ) ); // Conductor Query Builder Preview Query
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		private function includes() {
		}


		/**
		 * This function runs on initialization, sets up properties on this class, and allows other
		 * plugins and themes to adjust those properties via filters.
		 */
		public function init() {
			global $wpdb, $post, $pagenow;

			// Grab the post types
			// TODO: Future: Hook into filter in add-ons to add post types (possibly call Conductor Widget post types filter name instead)
			$post_types = $this->get_post_types();

			// If the Conductor Query Builder Widget exists
			if ( function_exists( 'Conduct_Query_Builder_Widget' ) ) {
				// Grab the Conductor Query Builder Widget instance
				$conductor_qb_widget = Conduct_Query_Builder_Widget();

				// Set the Conductor Query Builder Widget active flag
				$this->is_active_widget = is_active_widget( false, false, $conductor_qb_widget->id_base );
			}


			/*
			 * Operators
			 */
			// TODO: _x()?
			$this->operators = apply_filters( 'conductor_query_builder_operators', array(
				// Equals
				'IS' => __( '=', 'conductor-query-builder' ),
				// Not Equals
				'NOT' => __( '!=', 'conductor-query-builder' ),
				// Greater Than
				'GREATER_THAN' => __( '&gt;', 'conductor-query-builder' ),
				// Greater Than or Equal
				'GREATER_THAN_EQUALS' => __('&gt;=', 'conductor-query-builder' ),
				// Less Than
				'LESS_THAN' => __( '&lt;', 'conductor-query-builder' ),
				// Less Than or Equal
				'LESS_THAN_EQUALS' => __( '&lt;=', 'conductor-query-builder' ),
				// AND
				'AND' => array(
					'label' => __( 'AND', 'conductor-query-builder' ),
					'multiple' => true,
					'type' => 'multiple'
				),
				// EXISTS
				'EXISTS' => array(
					'label' => __( 'EXISTS', 'conductor-query-builder' )
					// TODO: 'type' => 'bool'?
				),
				// NOT EXISTS
				'NOT EXISTS' => array(
					'label' => __( 'NOT EXISTS', 'conductor-query-builder' )
					// TODO: 'type' => 'bool'?
				),
				// LIKE
				'LIKE' => __( 'LIKE', 'conductor-query-builder' ),
				// NOT LIKE
				'NOT LIKE' => __( 'NOT LIKE', 'conductor-query-builder' ),
				// IN
				'IN' => array(
					'label' => __( 'IN', 'conductor-query-builder' ),
					'multiple' => true,
					'type' => 'multiple'
				),
				// NOT IN
				'NOT IN' => array(
					'label' => __( 'NOT IN', 'conductor-query-builder' ),
					'multiple' => true,
					'type' => 'multiple'
				),
				// TRUE
				'TRUE' => array(
					'label' => __( 'TRUE', 'conductor-query-builder' ),
					'type' => 'bool',
					'value' => true
				),
				// FALSE
				'FALSE' => array(
					'label' => __( 'FALSE', 'conductor-query-builder' ),
					'type' => 'bool',
					'value' => false
				),
				// ASCENDING
				'ASC' => __( 'Ascending', 'conductor-query-builder' ),
				// DESCENDING
				'DESC' => __( 'Descending', 'conductor-query-builder' ),
				// BETWEEN
				'BETWEEN' => array(
					'label' => __( 'BETWEEN', 'conductor-query-builder' ),
					'multiple' => true,
					'limit' => 2, // TODO: Future: Ensure this is used in sanitize logic and JS logic via maximumSelectionLength
					'type' => 'multiple'
				),
				// NOT BETWEEN
				'NOT BETWEEN' => array(
					'label' => __( 'NOT BETWEEN', 'conductor-query-builder' ),
					'multiple' => true,
					'limit' => 2, // TODO: Future: Ensure this is used in sanitize logic and JS logic via maximumSelectionLength
					'type' => 'multiple'
				)
			), $this );


			/*
			 * Query Argument Operators
			 */
			// TODO: Future: Depreciate; move this mapping of operators to $this->operators inside of an "operator" parameter
			$this->query_args_operators = apply_filters( 'conductor_query_builder_query_args_operators', array(
				// Equals
				'IS' => '=',
				// Not Equals
				'NOT' => '!=',
				// Greater Than
				'GREATER_THAN' => '>',
				// Greater Than or Equal
				'GREATER_THAN_EQUALS' => '>=',
				// Less Than
				'LESS_THAN' => '<',
				// Less Than or Equal
				'LESS_THAN_EQUALS' => '<=',
				// AND
				'AND' => 'AND',
				// EXISTS
				'EXISTS' => 'EXISTS',
				// NOT EXISTS
				'NOT EXISTS' => 'NOT EXISTS',
				// LIKE
				'LIKE' => 'LIKE',
				// NOT LIKE
				'NOT LIKE' => 'NOT LIKE',
				// IN
				'IN' => 'IN',
				// NOT IN
				'NOT IN' => 'NOT IN',
				// TRUE
				'TRUE' => true,
				// FALSE
				'FALSE' => false,
				// ASCENDING
				'ASC' => 'ASC',
				// DESCENDING
				'DESC' => 'DESC',
				// BETWEEN
				'BETWEEN' => 'BETWEEN',
				// NOT BETWEEN
				'NOT BETWEEN' => 'NOT BETWEEN'
			) );


			/*
			 * Parameters
			 *
			 * This section contains the parameters configuration data for each clause and parameter.
			 *
			 * Note: The array values must match an operator KEY present in the list above - the query
			 * argument operators. TODO: Future: Depreciate this requirement, see TODO above
			 *
			 * Parameters are specified via array keys. Parameters can be specified in any of the
			 * following formats:
			 *
			 * - 1. Simple (the WP_Query parameter is the array key, the operator is the array value):
			 *
			 *		'author_name' => 'IS'
			 *
			 * - 2. Configuration Array (the WP_Query parameter is the array key, the configuration is the
			 *   array value):
			 *
			 *		'author' => array(
			 *			'operators' => array(
			 *				'IS',
			 *				'author__in' => 'IN',
			 *				'author__not_in' => 'NOT IN'
			 *			),
			 *			'type' => 'int',
			 *			'multiple' => array(
			 *				'IN',
			 *				'NOT IN'
			 *			),
			 *			'unique' => array(
			 *				'IN',
			 *				'NOT IN'
			 *			)
			 *		)
			 *
			 *		- The 'operators' parameter is configured as an array - array values are valid operators and
			 * 		  optional array keys are specified for use in WP_Query when specific operators are selected.
			 * 		  For example, when the NOT IN operator is selected for the author, the author__not_in WP_Query
			 * 		  query argument is used instead of the author query argument.
			 * 		- The 'type' parameter is specified and will ensure that the value entered by the user is
			 * 		  sanitized to either an integer ('int') or a Boolean ('bool'; operators only) value.
			 * 		- The 'multiple' parameter is configured as an array - array values are valid operators. This
			 * 		  parameter specifies which operators allow multiple values to be selected.
			 * 		- The 'unique' parameter is configured as an array - array values are valid operators. This
			 * 		  parameter specifies which operators allow unique values to be selected. For example, if
			 * 		  multiple sub-clause groups exist for a particular parameter (e.g. 'author') one of the
			 * 		  sub-clause groups contains a unique operator, the first sub-clause group is kept and the
			 * 		  other sub-clause groups are removed during sanitization.
			 *
			 * - 3. Advanced Configuration Array (the WP_Query parameter is the array key, the configuration is the
			 *   array value with fields specified):
			 *
			 *		'fields' => array( ... )
			 *
			 *		- In special cases ('meta_query' and 'tax_query'), the 'fields' array is specified in the
			 * 		  configuration array. This key specifies the nested field in which the configuration array
			 * 		  pertains to. For example, in the 'meta_query' configuration array, the 'compare' field is
			 * 		  specified which specifies that the configuration array data pertains to the nested 'compare'
			 * 		  field within the 'meta_query' argument.
			 *
			 * Note: If the 'type' parameter is not specified, all data is sanitized via sanitize_text_field().
			 */
			// TODO: Future: Group these by query type (e.g. WP_Query or similar)?
			// TODO: Future: Comment all relevant properties below
			$this->parameters = apply_filters( 'conductor_query_builder_parameters', array(
				// WHERE
				'author' => array(
					'operators' => array(
						'IS',
						'author__in' => 'IN',
						'author__not_in' => 'NOT IN'
					),
					'type' => 'int',
					'multiple' => array(
						'IN',
						'NOT IN'
					),
					'unique' => array(
						'IN',
						'NOT IN'
					)
				),
				'author_name' => 'IS',
				'has_password' => array(
					'operators' => array(
						'TRUE',
						'FALSE'
					),
					'type' => 'bool'
				),
				'name' => array(
					'operators' => array(
						'IS',
						'post_name__in' => 'IN'
					),
					'multiple' => array(
						'IN'
					)
				),
				'p' => array(
					'operators' => array(
						'IS',
						'post__in' => 'IN',
						'post__not_in' => 'NOT IN',
					),
					'type' => 'int',
					'multiple' => array(
						'IN',
						'NOT IN'
					),
					'unique' => array(
						'IN',
						'NOT IN'
					)
				),
				'pagename' => array(
					'operators' => array(
						'IS'
					),
					'post_type' => array(
						'page'
					)
				),
				'perm' => 'IS',
				// TODO: Hierarchical post types only?
				'post_parent' => array(
					'operators' => array(
						'IS',
						'post_parent__in' => 'IN',
						'post_parent__not_in' => 'NOT IN',
					),
					'type' => 'int',
					'multiple' => array(
						'IN',
						'NOT IN'
					),
					'unique' => array(
						'IN',
						'NOT IN'
					)
				),
				'post_password' => 'IS',
				'post_status' => array(
					'operators' => array(
						'IN'
					),
					'multiple' => array(
						'IN'
					)
				),
				's' => 'IS',

				// WHERE Meta (Custom Field)
				'meta_query' => array(
					// Fields
					'fields' => array(
						// Compare (field type/name)
						'compare' => array(
							// Operators
							'operators' => array(
								'IS',
								'NOT',
								'GREATER_THAN',
								'GREATER_THAN_EQUALS',
								'LESS_THAN',
								'LESS_THAN_EQUALS',
								'EXISTS',
								'NOT EXISTS',
								'LIKE',
								'NOT LIKE',
								'IN',
								'NOT IN',
								'BETWEEN',
								'NOT BETWEEN'
							),
							// Multiple operators (operators that allow for multiple values to be selected)
							'multiple' => array(
								'IN',
								'NOT IN',
								'BETWEEN',
								'NOT BETWEEN'
							),
							// Unique operators (operators that allow for values to be chosen once)
							'unique' => array(
								'IN',
								'NOT IN',
								'BETWEEN',
								'NOT BETWEEN'
							)
						)
					)
				),

				// WHERE Taxonomy
				'cat' => array(
					'operators' => array(
						'IS',
						'category__and' => 'AND',
						'category__in' => 'IN',
						'category_not__in' => 'NOT IN'
					),
					'taxonomies' => array(
						'category'
					),
					'type' => 'int',
					'multiple' => array(
						'AND',
						'IN',
						'NOT IN'
					),
					'unique' => array(
						'AND',
						'IN',
						'NOT IN'
					)
				),
				'category_name' => array(
					'operators' => array(
						'IS'
					),
					'taxonomies' => array(
						'category'
					)
				),
				'tag' => array(
					'operators' => array(
						'tag_slug__and' => 'AND',
						'tag_slug__in' => 'IN'
					),
					'taxonomies' => array(
						'post_tag'
					),
					'multiple' => array(
						'AND',
						'IN'
					),
					'unique' => array(
						'AND',
						'IN'
					)
				),
				'tag_id' => array(
					'operators' => array(
						'IS',
						'tag__and' => 'AND',
						'tag__in' => 'IN',
						'tag_not__in' => 'NOT IN',
					),
					'taxonomies' => array(
						'post_tag'
					),
					'type' => 'int',
					'multiple' => array(
						'AND',
						'IN',
						'NOT IN'
					),
					'unique' => array(
						'AND',
						'IN',
						'NOT IN'
					)
				),
				'tax_query' => array(
					// Fields
					'fields' => array(
						// Field type/name
						'operator' => array(
							'operators' => array(
								'AND',
								'EXISTS',
								'NOT EXISTS',
								'IN',
								'NOT IN',
							),
							'multiple' => array(
								'AND',
								'IN',
								'NOT IN'
							),
							'unique' => array(
								'AND',
								'IN',
								'NOT IN'
							)
						),
					)
				),

				// ORDER BY
				'order_by' => array(
					// Fields
					'fields' => array(
						// Field type/name
						'order' => array(
							'operators' => array(
								'ASC',
								'DESC'
							)
						)
					)
				),

				// LIMIT
				'offset' => array(
					'operators' => array(
						'IS'
					),
					'type' => 'int'
				),
				// TODO:
				/*'paged' => array(
					'operators' => array(
						'IS'
					),
					'type' => 'int'
				),*/
				'posts_per_page' => array(
					'operators' => array(
						'IS'
					),
					'type' => 'int'
				),
				// Conductor specific parameter
				'max_num_posts' => array(
					'operators' => array(
						'IS'
					),
					'type' => 'int',
					'conductor' => true
				),
				'ignore_sticky_posts' => array(
					'operators' => array(
						'TRUE',
						'FALSE'
					),
					'type' => 'bool'
				)
			), $this );


			/*
			 * Values
			 *
			 * Note: Most values are populated dynamically based on database entries.
			 *
			 * Note: Array keys must match an parameter key in the list above.
			 */

			// If we're in the admin on a page that supports the Conductor Query Builder or doing an AJAX request
			if ( apply_filters( 'conductor_query_builder_populate_values_data', ( ( is_admin() && in_array( $pagenow, array( 'post.php', 'post-new.php', 'page.php', 'page-new.php' ) ) ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ), $this ) ) {
				$this->values = array(
					// All - All parameters
					'all' => array(),
					// WHERE
					'author' => array(),
					'author_name' => array(),
					'p' => array(),
					'name' => array(),
					'pagename' => array(),
					'post_parent' => array(
						// Allow for top level entries
						array(
							'label' => __( 'All Top Level Pages', 'conductor-query-builder' ),
							'value' => 0
						)
					),
					'post_status' => array_values( get_post_stati() ),
					// TODO: What permissions do we list here? Are 'editable' and 'readable' the only valid permissions?
					'perm' => array(
						'editable',
						'readable'
					),

					// TODO: WHERE Meta (Custom Field)?

					// WHERE Taxonomy (see below)
					'tax_query' => array(
						'cat' => array(),
						'category_name' => array(),
						'tag_id' => array(),
						'tag' => array()
					)
				);

				// Add the "any" post status to the post status
				$this->values['post_status'][] = 'any';

				// Add the "inherit" post status to the post status
				$this->values['post_status'][] = 'inherit';

				// Sort post status by natural order
				natcasesort( $this->values['post_status'] );

				// Reset post status array keys
				$this->values['post_status'] = array_values( $this->values['post_status'] );

				// Query authors
				$authors = get_users( apply_filters( 'conductor_query_builder_get_users_args', array(
					'fields' => array( 'ID', 'display_name', 'user_nicename' ),
					'orderby' => 'ID',
					'who' => 'authors'
				), $this ) );

				// If we have authors
				if ( ! empty( $authors ) ) {
					// Loop through them
					foreach ( $authors as $author ) {
						// Author ID
						$this->values['author'][] = array(
							'label' => sprintf( _x( '%1$s (%2$s)', 'label for author ID value', 'conductor-query-builder' ), $author->display_name, $author->ID ),
							'value' => $author->ID
						);

						// Author Name
						$this->values['author_name'][] = array(
							'label' => sprintf( _x( '%1$s (%2$s)', 'label for author name value', 'conductor-query-builder' ), $author->display_name, $author->user_nicename ),
							'value' => $author->ID
						);
					}

					// Sort author name by natural order
					array_multisort( array_column( $this->values['author_name'], 'label' ), SORT_NATURAL|SORT_FLAG_CASE, $this->values['author_name'] );

					// Reset author name array keys
					$this->values['author_name'] = array_values( $this->values['author_name'] );
				}

				// Query all content types
				// TODO: We only need ID, title, post_name, and post_parent here; maybe a custom query would be more efficient
				$posts = new WP_Query( array(
					'orderby' => 'ID',
					'order' => 'ASC',
					'post_type' => array_keys( $post_types ),
					'posts_per_page' => -1
				) );

				// If we have content
				if ( $posts->have_posts() ) {
					// Loop through the content
					while ( $posts->have_posts() ) {
						// Move to the next post
						$the_post = $posts->next_post();

						// Grab the post ID
						$post_id = get_post_field( 'ID', $the_post );

						// Grab the post title
						$post_title = get_the_title( $the_post );

						$this->values['p'][] = array(
							'label' => sprintf( _x( '%1$s (%2$s)', 'label for post ID value', 'conductor-query-builder' ), ( $post_title ) ? $post_title : __( '(no title)', 'conductor-query-builder' ), $post_id ),
							'value' => $post_id
						);

						// If we have a post name
						if ( ( $post_name = get_post_field( 'post_name', $the_post ) ) ) {
							// Name
							$this->values['name'][] = array(
								'label' => sprintf( _x( '%1$s (%2$s)', 'label for post name value', 'conductor-query-builder' ), ( empty( $post_title ) ) ? sprintf( __( '#%d', 'conductor-query-builder' ), $post_id ) : $post_title, $post_name ),
								'value' => $post_name
							);

							// Page Name
							if ( get_post_type( $the_post ) === 'page' )
								$this->values['pagename'][] = array(
									'label' => sprintf( _x( '%1$s (%2$s)', 'label for page name value', 'conductor-query-builder' ), ( empty( $post_title ) ) ? sprintf( __( '#%d', 'conductor-query-builder' ), $post_id ) : $post_title, $post_name ),
									'value' => $post_name
								);
						}

						// Post Parent
						if ( ( $post_parent_id = wp_get_post_parent_id( $the_post ) ) && ! in_array( $post_parent_id, $this->values['post_parent'] ) )
							$this->values['post_parent'][] = array(
								'label' => sprintf( _x( '%1$s (%2$s)', 'label for post parent ID value', 'conductor-query-builder' ), ( $post_title ) ? $post_title : __( '(no title)', 'conductor-query-builder' ), $post_parent_id ),
								'value' => $post_parent_id
							);
					}

					// Sort name by natural order
					array_multisort( array_column( $this->values['name'], 'label' ), SORT_NATURAL|SORT_FLAG_CASE, $this->values['name'] );

					// Reset name array keys
					$this->values['name'] = array_values( $this->values['name'] );

					// Sort page name by natural order
					array_multisort( array_column( $this->values['pagename'], 'label' ), SORT_NATURAL|SORT_FLAG_CASE, $this->values['pagename'] );

					// Reset page name array keys
					$this->values['pagename'] = array_values( $this->values['pagename'] );
				}

				// Query all taxonomies
				$taxonomies = get_taxonomies();

				// Taxonomies to skip
				$taxonomies_to_skip = apply_filters( 'conductor_query_builder_values_taxonomies_to_skip', array(
					'nav_menu',
					'link_category',
					'post_format'
				), $this );

				// If we have taxonomies
				if ( ! empty( $taxonomies ) ) {
					// Loop through them
					foreach ( $taxonomies as $taxonomy ) {
						// If this taxonomy shouldn't be skipped
						if ( ! in_array( $taxonomy, $taxonomies_to_skip ) ) {
							// Grab the terms within this taxonomy
							$terms = get_terms( array(
								'taxonomy' => $taxonomy,
								'orderby' => 'term_id'
							) );

							// If we have terms
							if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
								// Loop through them
								foreach ( $terms as $term_obj ) {
									// Switch based on taxonomy name
									switch ( $taxonomy ) {
										// Category
										case 'category':
											// cat
											$this->values['tax_query']['cat'][] = array(
												'label' => sprintf( _x( '%1$s (%2$s)', 'label for category ID value', 'conductor-query-builder' ), $term_obj->name, $term_obj->term_id ),
												'value' => $term_obj->term_id
											);

											// category_name
											$this->values['tax_query']['category_name'][] = array(
												'label' => sprintf( _x( '%1$s (%2$s)', 'label for category name value', 'conductor-query-builder' ), $term_obj->name, $term_obj->slug ),
												'value' => $term_obj->slug
											);
										break;

										// Tag
										case 'post_tag':
											// tag_id
											$this->values['tax_query']['tag_id'][] = array(
												'label' => sprintf( _x( '%1$s (%2$s)', 'label for tag ID value', 'conductor-query-builder' ), $term_obj->name, $term_obj->term_id ),
												'value' => $term_obj->term_id
											);

											// tag
											$this->values['tax_query']['tag'][] = array(
												'label' => sprintf( _x( '%1$s (%2$s)', 'label for tag name value', 'conductor-query-builder' ), $term_obj->name, $term_obj->slug ),
												'value' => $term_obj->slug
											);
										break;

										// Default
										default:
											// Create the config arrays if they don't exist
											if ( ! isset( $this->values['tax_query'][$taxonomy . ':name'] ) )
												$this->values['tax_query'][$taxonomy . ':name'] = array();

											if ( ! isset( $this->values['tax_query'][$taxonomy . ':slug'] ) )
												$this->values['tax_query'][$taxonomy . ':slug'] = array();

											if ( ! isset( $this->values['tax_query'][$taxonomy . ':term_id'] ) )
												$this->values['tax_query'][$taxonomy . ':term_id'] = array();

											// Add this taxonomy to the tax_query values
											$this->values['tax_query'][$taxonomy . ':name'][] = $term_obj->name;

											$this->values['tax_query'][$taxonomy . ':slug'][] = array(
												'label' => sprintf( _x( '%1$s (%2$s)', 'label for taxonomy term slug value', 'conductor-query-builder' ), $term_obj->name, $term_obj->slug ),
												'value' => $term_obj->slug
											);

											$this->values['tax_query'][$taxonomy . ':term_id'][] = array(
												'label' => sprintf( _x( '%1$s (%2$s)', 'label for taxonomy term ID value', 'conductor-query-builder' ), $term_obj->name, $term_obj->term_id ),
												'value' => $term_obj->term_id
											);
										break;
									}
								}

								// Switch based on taxonomy name
								switch ( $taxonomy ) {
									// Category
									case 'category':
										// Sort category name by natural order
										array_multisort( array_column( $this->values['tax_query']['category_name'], 'label' ), SORT_NATURAL|SORT_FLAG_CASE, $this->values['tax_query']['category_name'] );

										// Reset category name array keys
										$this->values['tax_query']['category_name'] = array_values( $this->values['tax_query']['category_name'] );
									break;

									// Tag
									case 'post_tag':
										// Sort tag by natural order
										array_multisort( array_column( $this->values['tax_query']['tag'], 'label' ), SORT_NATURAL|SORT_FLAG_CASE, $this->values['tax_query']['tag'] );

										// Reset tag array keys
										$this->values['tax_query']['tag'] = array_values( $this->values['tax_query']['tag'] );
									break;

									// Default
									default:
										// Sort term names by natural order
										natcasesort( $this->values['tax_query'][$taxonomy . ':name'] );

										// Reset term names array keys
										$this->values['tax_query'][$taxonomy . ':name'] = array_values( $this->values['tax_query'][$taxonomy . ':name'] );

										// Sort term slugs by natural order
										array_multisort( array_column( $this->values['tax_query'][$taxonomy . ':slug'], 'label' ), SORT_NATURAL|SORT_FLAG_CASE, $this->values['tax_query'][$taxonomy . ':slug'] );

										// Reset term slugs array keys
										$this->values['tax_query'][$taxonomy . ':slug'] = array_values( $this->values['tax_query'][$taxonomy . ':slug'] );
									break;
								}
							}
						}
					}
				}

				$this->values = apply_filters( 'conductor_query_builder_values', $this->values, $this );
			}


			/*
			 * Clauses
			 */

			// If we're in the admin on a page that supports the Conductor Query Builder or doing an AJAX request
			if ( apply_filters( 'conductor_query_builder_populate_clauses_data', apply_filters( 'conductor_query_builder_populate_clause_data', ( ( is_admin() && in_array( $pagenow, array( 'post.php', 'post-new.php', 'page.php', 'page-new.php' ) ) ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ), $this ), $this ) ) {
				$this->clauses = array(
					// FROM
					'from' => array(
						// Config
						'config' => array(
							// Columns
							'columns' => array(
								// Parameters Column (1st Column)
								'parameters' => array(
									// Select2 Configuration
									'select2' => array(
										// Allow multiple selections
										'multiple' => true,
										// Placeholder
										'placeholder' => __( 'Select a content type...', 'conductor-query-builder' ),
										// Toggle Action Buttons
										'toggle-action-buttons' => true
									)
								)
							),
							// Flags
							'flags' => array(
								// Allow sub-clause groups
								'sub_clause_groups' => false,
								// Allow removal
								'remove' => false,
								// Ignore descriptive labels (e.g. AND and OR labels)
								'ignore_descriptive_labels' => true,
								// Allow actions
								'actions' => true
							),
							// Title
							'title' => __( 'From', 'conductor-query-builder' ),
							// Limit
							'limit' => 1,
							// Default
							'default' => true
						),
						// Parameters
						'parameters' => $post_types,
						// Operators
						'operators' => $this->operators,
						// Query argument
						'query_arg' => 'post_type'
					),
					// WHERE
					'where' => array(
						// Config
						'config' => array(
							// Columns
							'columns' => array(
								// Parameters Column (1st Column)
								'parameters' => array(
									// Select2 Configuration
									'select2' => array(
										// Placeholder
										'placeholder' => __( 'Select a parameter...', 'conductor-query-builder' )
									)
								),
								// Operators Column (2nd Column)
								'operators' => array(
									// Select2 Configuration
									'select2' => array(
										// Placeholder
										'placeholder' => __( 'Select an operator...', 'conductor-query-builder' )
									)
								),
								// Values Column (3rd Column)
								'values' => array(
									// Select2 Configuration
									'select2' => array(
										// Allow multiple selections
										'multiple' => true,
										// Placeholder
										'placeholder' => __( 'Enter a value...', 'conductor-query-builder' ),
										// Allow tags (custom values; pass an array of separator tokens)
										'tags' => array(
											','
										)
									)
								)
							),
							// Flags
							'flags' => array(
								// Ignore descriptive labels (e.g. AND and OR labels)
								'ignore_descriptive_labels' => true
							),
							// Title
							'title' => __( 'Where', 'conductor-query-builder' ),
							// Limit
							'limit' => 1
						),
						// Parameters
						'parameters' => array(
							'author' => __( 'Author ID', 'conductor-query-builder' ),
							'author_name' => __( 'Author Name (slug; user_nicename)', 'conductor-query-builder' ),
							'has_password' => __( 'Has Password', 'conductor-query-builder' ),
							'p' => __( 'ID', 'conductor-query-builder' ),
							's' => __( 'Keyword', 'conductor-query-builder' ),
							'name' => __( 'Name (slug)', 'conductor-query-builder' ),
							'pagename' => __( 'Page Name (slug)', 'conductor-query-builder' ),
							'post_parent' => __( 'Parent (IDs)', 'conductor-query-builder' ),
							'post_password' => __( 'Password', 'conductor-query-builder' ),
							'post_status' => __( 'Post Status', 'conductor-query-builder' ),
							'perm' => __( 'User Permission', 'conductor-query-builder' ),
						),
						// Operators
						'operators' => $this->operators,
						// Values
						'values' => array(
							'author' => $this->values['author'],
							'author_name' => $this->values['author_name'],
							'p' => $this->values['p'],
							'name' => $this->values['name'],
							'pagename' => $this->values['pagename'],
							'post_parent' => $this->values['post_parent'],
							'post_status' => $this->values['post_status'],
							'perm' => $this->values['perm']
						)
					),
					// WHERE Meta (Custom Field)
					'meta_query' => array(
						// Config
						'config' => array(
							// Columns
							'columns' => array(
								// Parameters Column (1st Column)
								'parameters' => array(
									// Select2 Configuration
									'select2' => array(
										// Placeholder
										'placeholder' => __( 'Select a parameter...', 'conductor-query-builder' )
									)
								),
								// Operators Column (2nd Column)
								'operators' => array(
									// Select2 Configuration
									'select2' => array(
										// Placeholder
										'placeholder' => __( 'Select an operator...', 'conductor-query-builder' )
									)
								),
								// Values Column (3rd Column)
								'values' => array(
									// Select2 Configuration
									'select2' => array(
										// Allow multiple selections
										'multiple' => true,
										// Placeholder
										'placeholder' => __( 'Enter a value...', 'conductor-query-builder' ),
										// Allow tags (custom values; pass an array of separator tokens)
										'tags' => array(
											','
										)
									)
								)
							),
							// Title
							'title' => __( 'Where Meta (Custom Field)', 'conductor-query-builder' )
						),
						// Parameters (see below)
						'parameters' => array(),
						// Operators
						'operators' => $this->operators
						// TODO: Values?
					),
					// WHERE Taxonomy
					'tax_query' => array(
						// Config
						'config' => array(
							// Columns
							'columns' => array(
								// Parameters Column (1st Column)
								'parameters' => array(
									// Select2 Configuration
									'select2' => array(
										// Placeholder
										'placeholder' => __( 'Select a parameter...', 'conductor-query-builder' )
									)
								),
								// Operators Column (2nd Column)
								'operators' => array(
									// Select2 Configuration
									'select2' => array(
										// Placeholder
										'placeholder' => __( 'Select an operator...', 'conductor-query-builder' )
									)
								),
								// Values Column (3rd Column)
								'values' => array(
									// Select2 Configuration
									'select2' => array(
										// Allow multiple selections
										'multiple' => true,
										// Placeholder
										'placeholder' => __( 'Enter a value...', 'conductor-query-builder' ),
										// Allow tags (custom values; pass an array of separator tokens)
										'tags' => array(
											','
										)
									)
								)
							),
							// Title
							'title' => __( 'Where Taxonomy', 'conductor-query-builder' )
						),
						// Parameters (see below)
						'parameters' => array(
							'cat' => __( 'Category ID', 'conductor-query-builder' ),
							'category_name' => __( 'Category Name (slug)', 'conductor-query-builder' ),
							'tag_id' => __( 'Tag ID', 'conductor-query-builder' ),
							'tag' => __( 'Tag Name (slug)', 'conductor-query-builder' ),
						),
						// Operators
						'operators' => $this->operators,
						// Values
						'values' => $this->values['tax_query']
					),
					// ORDER BY
					'order_by' => array(
						// Config
						'config' => array(
							// Columns
							'columns' => array(
								// Parameters Column (1st Column)
								'parameters' => array(
									// Select2 Configuration
									'select2' => array(
										// Placeholder
										'placeholder' => __( 'Select a parameter...', 'conductor-query-builder' )
									)
								),
								// Operators Column (2nd Column)
								'operators' => array(
									// Select2 Configuration
									'select2' => array(
										// Placeholder
										'placeholder' => __( 'Select an operator...', 'conductor-query-builder' )
									)
								)
							),
							// Flags
							'flags' => array(
								// Ignore descriptive labels (e.g. AND and OR labels)
								'ignore_descriptive_labels' => true
							),
							// Title
							'title' => __( 'Order By', 'conductor-query-builder' ),
							// Limit
							'limit' => 1
						),
						// Parameters
						'parameters' => array(
							'none' => array(
								'label' => __( 'None', 'conductor-query-builder' ),
								'parameter' => 'order_by',
								'field' => 'order'
							),
							'author' => array(
								'label' => __( 'Author', 'conductor-query-builder' ),
								'parameter' => 'order_by',
								'field' => 'order'
							),
							'comment_count' => array(
								'label' => __( 'Comment Count', 'conductor-query-builder' ),
								'parameter' => 'order_by',
								'field' => 'order'
							),
							'date' => array(
								'label' => __( 'Date', 'conductor-query-builder' ),
								'parameter' => 'order_by',
								'field' => 'order'
							),
							'ID' => array(
								'label' => __( 'ID', 'conductor-query-builder' ),
								'parameter' => 'order_by',
								'field' => 'order'
							),
							// TODO: Hierarchical post types only
							'menu_order' => array(
								'label' => __( 'Menu Order', 'conductor-query-builder' ),
								'parameter' => 'order_by',
								'field' => 'order'
							),
							// TODO: Requires meta_key query argument
							'meta_value' => array(
								'label' => __( 'Meta Value', 'conductor-query-builder' ),
								'parameter' => 'order_by',
								'field' => 'order'
							),
							// TODO: Requires meta_key query argument
							'meta_value_num' => array(
								'label' => __( 'Meta Value Number', 'conductor-query-builder' ),
								'parameter' => 'order_by',
								'field' => 'order'
							),
							'modified' => array(
								'label' => __( 'Modified Date', 'conductor-query-builder' ),
								'parameter' => 'order_by',
								'field' => 'order'
							),
							'name' => array(
								'label' => __( 'Name (slug)', 'conductor-query-builder' ),
								'parameter' => 'order_by',
								'field' => 'order'
							),
							'parent' => array(
								'label' => __( 'Parent ID', 'conductor-query-builder' ),
								'parameter' => 'order_by',
								'field' => 'order'
							),
							// TODO: Requires post__in query argument
							'post__in' => array(
								'label' => __( 'post__in', 'conductor-query-builder' ),
								'parameter' => 'order_by',
								'field' => 'order'
							),
							// TODO: Requires post_parent__in query argument
							'post_parent__in' => array(
								'label' => __( 'post_parent__in', 'conductor-query-builder' ),
								'parameter' => 'order_by',
								'field' => 'order'
							),
							'rand' => array(
								'label' => __( 'Random', 'conductor-query-builder' ),
								'parameter' => 'order_by',
								'field' => 'order'
							),
							'title' => array(
								'label' => __( 'Title', 'conductor-query-builder' ),
								'parameter' => 'order_by',
								'field' => 'order'
							),
							'type' => array(
								'label' => __( 'Post Type', 'conductor-query-builder' ),
								'parameter' => 'order_by',
								'field' => 'order'
							),
						),
						// Operators
						'operators' => $this->operators,
						// Query argument
						'query_arg' => 'orderby',
						// Multiple query argument values
						'multiple_query_arg_values' => true
					),
					// LIMIT
					'limit' => array(
						// Config
						'config' => array(
							// Columns
							'columns' => array(
								// Parameters Column (1st Column)
								'parameters' => array(
									// Select2 Configuration
									'select2' => array(
										// Placeholder
										'placeholder' => __( 'Select a parameter...', 'conductor-query-builder' )
									)
								),
								// Operators Column (2nd Column)
								'operators' => array(
									// Select2 Configuration
									'select2' => array(
										// Placeholder
										'placeholder' => __( 'Select an operator...', 'conductor-query-builder' )
									)
								),
								// Values Column (3rd Column)
								'values' => array(
									// Select2 Configuration
									'select2' => array(
										// Allow multiple selections
										'multiple' => true,
										// Placeholder
										'placeholder' => __( 'Enter a value...', 'conductor-query-builder' ),
										// Allow tags (custom values; pass an array of separator tokens)
										'tags' => array(
											','
										)
									)
								)
							),
							// Flags
							'flags' => array(
								// Ignore descriptive labels (e.g. AND and OR labels)
								'ignore_descriptive_labels' => true
							),
							// Title
							'title' => __( 'Limit', 'conductor-query-builder' ),
							// Limit
							'limit' => 1
						),
						// Parameters
						'parameters' => array(
							'offset' => __( 'Offset', 'conductor-query-builder' ),
							//'paged' => __( 'Paged', 'conductor-query-builder' ), // TODO
							'max_num_posts' => __( 'Maximum Number of Posts', 'conductor-query-builder' ),
							'posts_per_page' => __( 'Posts Per Page', 'conductor-query-builder' ),
							'ignore_sticky_posts' => __( 'Ignore Sticky Posts', 'conductor-query-builder' )
						),
						// Operators
						'operators' => $this->operators
					)
				);

				// Clauses - FROM
				foreach ( $this->clauses['from']['parameters'] as $post_type => &$post_type_object )
					$post_type_object = array(
						'value' => $post_type,
						'label' => $post_type_object->label
					);

				// Clauses - WHERE Meta (Custom Field)
				$limit = apply_filters( 'conductor_query_builder_postmeta_form_limit', apply_filters( 'postmeta_form_limit', 9999, $this ), $this );
				$custom_fields = $wpdb->get_col(
					$wpdb->prepare(
						"SELECT meta_key FROM $wpdb->postmeta GROUP BY meta_key ORDER BY meta_key LIMIT %d",
						$limit
					)
					/*
						 $wpdb->prepare(
							"SELECT meta_key FROM $wpdb->postmeta GROUP BY meta_key HAVING meta_key NOT LIKE %s ORDER BY meta_key LIMIT %d",
							$wpdb->esc_like( '_' ) . '%',
							$limit
						)
					 */
				);

				// If we have custom fields
				if ( ! empty( $custom_fields ) )
					// Loop through them
					foreach ( $custom_fields as $custom_field )
						// Add this custom field to the meta_query parameters
						$this->clauses['meta_query']['parameters'][$custom_field] = array(
							'label' => $custom_field,
							'parameter' => 'meta_query',
							'field' => 'compare'
						);

				// Clauses - WHERE Taxonomy
				$taxonomies = get_taxonomies( array(
					'_builtin' => false
				), 'objects' );

				// If we have non-built-in taxonomies
				if ( ! empty( $taxonomies ) )
					// TODO: Future: Sort taxonomies naturally here before adding taxonomies
					// Loop through them
					foreach ( $taxonomies as $taxonomy => $taxonomy_obj ) {
						// TODO: _x()

						// Add this taxonomy to the tax_query parameters
						$this->clauses['tax_query']['parameters'][$taxonomy . ':name'] = array(
							'label' => sprintf( __( '%1$s: Name', 'conductor-query-builder' ), $taxonomy_obj->label ),
							'parameter' => 'tax_query',
							'field' => 'operator'
						);
						$this->clauses['tax_query']['parameters'][$taxonomy . ':slug'] = array(
							'label' => sprintf( __( '%1$s: Slug', 'conductor-query-builder' ), $taxonomy_obj->label ),
							'parameter' => 'tax_query',
							'field' => 'operator'
						);
						$this->clauses['tax_query']['parameters'][$taxonomy . ':term_id'] = array(
							'label' => sprintf( __( '%1$s: Term ID', 'conductor-query-builder' ), $taxonomy_obj->label ),
							'parameter' => 'tax_query',
							'field' => 'operator'
						);
					}

				$this->clauses = apply_filters( 'conductor_query_builder_clauses', $this->clauses, $post, $this );
			}


			/**
			 * Conductor Query Builder Post Type
			 */
			$this->post_type_args = register_post_type( $this->post_type_name, array(
				'label' => __( 'Queries', 'conductor-query-builder' ),
				'labels' => array(
					'name' => _x( 'Queries', 'post type general name', 'conductor-query-builder' ),
					'singular_name' => _x( 'Query', 'post type singular name', 'conductor-query-builder' ),
					'menu_name' => __( 'Queries', 'conductor-query-builder' ),
					'name_admin_bar' => __( 'Conductor Query', 'conductor-query-builder' ),
					'parent_item_colon' => __( 'Parent:', 'conductor-query-builder' ),
					'all_items' => __( 'All Queries', 'conductor-query-builder' ),
					'add_new_item' => __( 'Add New Query', 'conductor-query-builder' ),
					'add_new' => __( 'Add New', 'conductor-query-builder' ),
					'new_item' => __( 'New Query', 'conductor-query-builder' ),
					'edit_item' => __( 'Edit Query', 'conductor-query-builder' ),
					'update_item' => __( 'Update Query', 'conductor-query-builder' ),
					'view_item' => __( 'View Query', 'conductor-query-builder' ),
					'not_found' => __( 'No Conductor Queries found', 'conductor-query-builder' ),
					'not_found_in_trash' => __( 'Not Conductor Queries found in Trash', 'conductor-query-builder' ),
					'insert_into_item' => __( 'Insert into Query', 'conductor-query-builder' ),
					'uploaded_to_this_item' => __( 'Uploaded to this Query', 'conductor-query-builder' ),
					'items_list' => __( 'Conductor Queries list', 'conductor-query-builder' ),
					'items_list_navigation' => __( 'Conductor Queries list navigation', 'conductor-query-builder' ),
					'filter_items_list' => __( 'Filter Conductor Queries list', 'conductor-query-builder' ),
				),
				'description' => __( 'Conductor Query Builder queries; create queries for your Conductor Widgets', 'conductor-query-builder' ),
				'public' => false,
				'exclude_from_search' => true,
				'show_ui' => true,
				'show_in_menu' => false, // Don't use WordPress' logic to add as a menu/sub-menu item since the init action is triggered before admin_menu where the Conductor Admin Options are added and this will over-ride that logic
				'show_in_admin_bar' => false,
				'capability_type' => 'page', // TODO: Pass capabilities array and map_meta_cap for admins only? Conductor requires manage_options capability
				'supports' => array(
					'title',
					'revisions'
				),
				'register_meta_box_cb' => array( $this, 'register_meta_box_cb' )
			) );

			// Store a reference to the post type object
			$this->post_type_object = get_post_type_object( $this->post_type_name );
		}

		/**
		 * This function registers meta boxes for the query builder post type
		 */
		public function register_meta_box_cb( $post ) {
			// Grab the post status
			$post_status = get_post_status( $post );

			/*
			 * Side
			 */

			// Notes (excerpt)
			add_meta_box( 'conductor-qb-excerpt', __( 'Notes', 'conductor-query-builder' ), array( $this, 'meta_box_notes' ), null, 'side', 'core' );

			// If this isn't an auto draft
			if ( $post_status !== 'auto-draft' ) {
				// Shortcode
				add_meta_box( 'conductor-qb-shortcode', __( 'Shortcode', 'conductor-query-builder' ), array( $this, 'meta_box_shortcode' ), null, 'side', 'core' );

				// Function
				add_meta_box( 'conductor-qb-function', __( 'Function', 'conductor-query-builder' ), array( $this, 'meta_box_function' ), null, 'side', 'core' );
			}

			// Preview
			add_meta_box( 'conductor-qb-preview', __( 'Front-End Preview', 'conductor-query-builder' ), array( $this, 'meta_box_preview' ), null, 'side', 'core' );


			/*
			 * Normal
			 */

			// Query Builder
			add_meta_box( 'conductor-qb-query-builder', __( 'Query Builder', 'conductor-query-builder' ), array( $this, 'meta_box_query_builder' ), null, 'normal', 'core' );
		}

		/**
		 * This function enqueues scripts and styles on the front-end.
		 */
		public function wp_enqueue_scripts() {
			// If the Conductor Query Builder Widget exists
			if ( function_exists( 'Conduct_Query_Builder_Widget' ) ) {
				// Grab the Conductor Query Builder Widget instance
				$conductor_query_builder_widget = Conduct_Query_Builder_Widget();

				// If at least one Conductor Query Builder Widget is active
				if ( is_active_widget( false, false, $conductor_query_builder_widget->id_base ) ) {
					// Conductor Query Builder Widget Script
					wp_enqueue_script( 'conductor-query-builder-widget', Conductor_Query_Builder_Add_On::plugin_url() . '/assets/js/conductor-query-builder-widget.js', array( 'conductor-widget' ), Conductor_Query_Builder_Add_On::$version, true );
					wp_localize_script( 'conductor-query-builder-widget', 'conductor_query_builder', apply_filters( 'conductor_query_builder_widget_localize', array(
						// CSS Classes
						'css_classes' => array(
							'widget_wrap' => 'conductor-query-builder-widget'
						),
						// CSS Selectors
						'css_selectors' => array(
							'widget_wrap' => '.conductor-query-builder-widget'
						)
					) ) );
				}
			}
		}

		/**
		 * This function adjusts the sidebars widgets.
		 */
		public function sidebars_widgets( $sidebars_widgets ) {
			global $wp_the_query;

			// Bail if we're in the admin, we're not doing the wp_enqueue_scripts action, or our temporary sidebar already exists
			if ( is_admin() || ! doing_action( 'wp_enqueue_scripts' ) || isset( $sidebars_widgets[$this->temporary_sidebar_id] ) )
				return $sidebars_widgets;

			// Flag to determine if the [conductor] shortcode exists in any of the content in the current main query
			$has_conductor_shortcode = false;

			// If we have posts in the main query and we don't have an active Conductor Query Builder Widget
			if ( ! $this->is_active_widget && $wp_the_query->have_posts() )
				// Loop through the posts
				while ( $wp_the_query->have_posts() ) {
					// Grab the post
					$post = $wp_the_query->next_post();

					// If this post content has the [conductor] shortcode
					if ( ! $has_conductor_shortcode )
						// Determine if this post content has the [conductor] shortcode
						$has_conductor_shortcode = has_shortcode( $post->post_content, $this->shortcode );
				}

			// Bail if we don't have an active Conductor Query Builder Widget or we don't have a [conductor] shortcode
			if ( ! $this->is_active_widget && ! $has_conductor_shortcode )
				return $sidebars_widgets;

			// Grab the Conductor Widget instance
			$conductor_widget = Conduct_Widget();

			// Grab the Conductor Query Builder Widget instance
			$conductor_query_builder_widget = Conduct_Query_Builder_Widget();

			/*
			 * Add our temporary sidebar.
			 *
			 * Insert a mock Conductor Widget and a mock Conductor Query Builder Widget. This ensures
			 * that Conductor and Conductor Query Builder scripts and styles are enqueued.
			 */
			$sidebars_widgets[$this->temporary_sidebar_id] = array(
				$conductor_widget->id_base . '-0',
				$conductor_query_builder_widget->id_base . '-0'
			);

			return $sidebars_widgets;
		}

		/**
		 * This function enqueues scripts and styles in the admin.
		 */
		public function admin_enqueue_scripts( $hook ) {
			global $post;

			// Bail if we're not on a page that supports the Conductor Query Builder
			// TODO: Turn this into a function to let other add-ons utilize this logic
			if ( ! apply_filters( 'conductor_query_builder_admin_enqueue_scripts', in_array( $hook, array( 'post.php', 'post-new.php', 'page.php', 'page-new.php' ) ), $hook, $post, $this ) )
				return;

			// Grab the post ID
			$post_id = get_post_field( 'ID', $post );

			// Grab the Conductor Widget instance
			$conductor_widget = Conduct_Widget();


			// Select2 localize data
			$select2_localize_data = array();

			// Grab the current locale
			$locale = get_locale();

			// Create the Select2 internationalization file path
			$select2_i18n_file = Conductor_Query_Builder_Add_On::plugin_dir() . '/assets/js/select2/i18n/' . $locale . '.js';

			// If the Select2 internationalization file doesn't exist
			if ( ! file_exists( $select2_i18n_file ) ) {
				// If an underscore exists in the locale
				if ( strpos( $locale, '_' ) !== false ) {
					// Replace the underscore in the locale with a hyphen
					$locale = str_replace( '_', '-', $locale );

					// Create the Select2 internationalization file path again
					$select2_i18n_file = Conductor_Query_Builder_Add_On::plugin_dir() . '/assets/js/select2/i18n/' . $locale . '.js';

					// If the Select2 internationalization file doesn't exist
					if ( ! file_exists( $select2_i18n_file ) ) {
						// Explode the locale by the hyphen character
						$locale_parts = explode( '-', $locale );

						// Set the locale to the first locale part
						$locale = $locale_parts[0];
					}
				}

				// Create the Select2 internationalization file path again
				$select2_i18n_file = Conductor_Query_Builder_Add_On::plugin_dir() . '/assets/js/select2/i18n/' . $locale . '.js';

				// If the Select2 internationalization file doesn't exist
				if ( file_exists( $select2_i18n_file ) ) {
					// Set the Select2 localize language data
					$select2_localize_data['language'] = $locale;
				}
			}

			// If we're right-to-left
			if ( is_rtl() )
				$select2_localize_data['dir'] = 'rtl';

			// Thickbox
			add_thickbox();

			// Select2 Stylesheet
			wp_enqueue_style( 'conductor-query-builder-select2', Conductor_Query_Builder_Add_On::plugin_url() . '/assets/css/select2/select2.min.css', false, Conductor_Query_Builder_Add_On::$version );

			/*
			 * Select2 Script
			 * License: MIT
			 * Copyright: Kevin Brown (https://github.com/kevin-brown), Igor Vaynberg (https://github.com/ivaynberg), and contributors (https://github.com/select2/select2/graphs/contributors)
			 *
			 * Due to potential conflicts that arise when multiple Select2 versions are enqueued on a page, we have
			 * to enqueue this script in the <head> element to ensure we can capture the correct jQuery Select2 function.
			 * This is also why we are hooking into admin_enqueue_scripts with a priority of 0.
			 */
			wp_enqueue_script( 'conductor-query-builder-select2', Conductor_Query_Builder_Add_On::plugin_url() . '/assets/js/select2/select2.min.js', array( 'jquery' ), Conductor_Query_Builder_Add_On::$version );
			wp_add_inline_script( 'conductor-query-builder-select2', '( function ( $ ) { $.fn.conductor_qb_select2 = $.fn.select2; }( jQuery ) );' );

			// If we have a language set for Select2
			if ( isset( $select2_localize_data['language'] ) )
				// Select2 Language Script
				wp_enqueue_script( 'conductor-query-builder-select2-language', Conductor_Query_Builder_Add_On::plugin_url() . '/assets/js/select2/i18n/' . $locale . '.js', array( 'conductor-query-builder-select2' ), Conductor_Query_Builder_Add_On::$version );

			// Conductor Query Builder Admin Stylesheet
			wp_enqueue_style( 'conductor-query-builder-admin', Conductor_Query_Builder_Add_On::plugin_url() . '/assets/css/conductor-query-builder-admin.css', false, Conductor_Query_Builder_Add_On::$version );

			/*
			 * Clipboard.js Script
			 * License: MIT License
			 * Copyright: Zeno Rocha, http://zenorocha.com/
			 */
			// TODO: Future: Depreciate in favor of Conductor script
			// TODO: ^ When doing this: new Clipboard() changes to new ClipboardJS()
			wp_enqueue_script( 'conductor-query-builder-clipboard', Conductor_Query_Builder_Add_On::plugin_url() . '/assets/js/clipboard/clipboard.min.js', false, Conductor_Query_Builder_Add_On::$version, true );
			wp_localize_script( 'conductor-query-builder-clipboard', 'conductor_qb_clipboard', array(
				// Localization
				'l10n' => array(
					'copied' => __( 'Copied to clipboard!', 'conductor-query-builder' ),
					'error' => array(
						'no_support' => __( 'Please manually select the text and copy.', 'conductor-query-builder' ),
						'mac' => array(
							'copy' => __( 'Press Command (⌘) + C to copy.', 'conductor-query-builder' ),
							'cut' => __( 'Press Command (⌘) + X to cut.', 'conductor-query-builder' )
						),
						'windows' => array(
							'copy' => __( 'Press Ctrl + C to copy.', 'conductor-query-builder' ),
							'cut' => __( 'Press Ctrl + X to cut.', 'conductor-query-builder' )
						)
					)
				)
			) );

			// Conductor Query Builder Admin Script
			wp_enqueue_script( 'conductor-query-builder-admin', Conductor_Query_Builder_Add_On::plugin_url() . '/assets/js/conductor-query-builder-admin.min.js', array( 'conductor-query-builder-clipboard', 'wp-util', 'jquery-ui-core', 'underscore', 'wp-backbone', 'thickbox', 'conductor-query-builder-select2' ), Conductor_Query_Builder_Add_On::$version, true );
			wp_localize_script( 'conductor-query-builder-admin', 'conductor_query_builder', apply_filters( 'conductor_query_builder_admin_localize', array(
				// AJAX
				'ajax' => array(
					// Shortcode
					'shortcode' => array(
						'action' => 'conductor-query-builder-create-query',
						'nonce' => wp_create_nonce( 'conductor-query-builder-create-query' ),
					),
					// Preview
					'preview' => array(
						'action' => 'conductor-query-builder-preview-query',
						'nonce' => wp_create_nonce( 'conductor-query-builder-preview-query' ),
					)
				),
				// Clauses
				'clauses' => $this->clauses,
				// CSS
				'css' => array(
					// CSS Classes
					'classes' => array(
						'loading' => 'loading conductor-qb-loading spinner conductor-qb-spinner is-active conductor-qb-spinner-is-active'
					)
				),
				// Flags
				'flags' => array(
					'is_query_builder_post_type' => ( ! empty( $post ) && get_post_field( 'post_type', $post ) === $this->post_type_name )
				),
				// ID
				'ID' => $post_id,
				// Operators
				'operators' => $this->operators,
				// Parameters
				'parameters' => $this->parameters,
				// Post Types
				'post_types' => $this->get_post_types(),
				// Values
				'values' => $this->values,
				// Localization
				'l10n' => array(
					'add' => _x( '+', 'label for add', 'conductor-query-builder' ),
					'ajax' => array(
						'fail' => array(
							'preview' => __( 'There was a problem fetching the front-end preview for this query.', 'conductor-query-builder' ),
							'shortcode' => __( 'The shortcode for this query could not be generated at this time. Please try again.', 'conductor-query-builder' ) // TODO: Not currently utilized
						)
					),
					'and' => _x( 'And', 'label for AND clause', 'conductor-query-builder' ),
					'clause_group' => _x( 'Clause Group', 'label for clause group', 'conductor-query-builder' ),
					'or' => _x( 'Or', 'label for OR clause', 'conductor-query-builder' ),
					'query' => array(
						'select' => _x( '&mdash; Select a Query &mdash;', 'label for selecting a query', 'conductor-query-builder' ),
						'no_title' => __( '(no title)', 'conductor-query-builder' ),
						'none' => _x( 'We weren\'t able to find any existing queries. You can create a query with the query builder the "Create" tab.', 'label for no queries', 'conductor-query-builder' )
					),
					'shortcode' => array(
						'add' => _x( '+', 'label for shortcode add', 'conductor-query-builder' ),
						'confirm' => _x( 'You have unsaved changes to your query which will be lost. Are you sure you want to close this window?', 'message for shortcode confirm', 'conductor-query-builder' ),
						'insert' => _x( 'Insert', 'label for shortcode insert', 'conductor-query-builder' ),
						'create' => _x( 'Create &amp; Insert', 'label for shortcode create', 'conductor-query-builder' ),
						'title' => _x( 'Insert or Create a Conductor Query', 'label for shortcode create', 'conductor-query-builder' )
					),
					'sub_clause_group' => _x( 'Sub-Clause Group', 'label for sub-clause group', 'conductor-query-builder' )
				),
				// Meta
				'meta' => $this->get_post_meta( $post_id ),
				// Select2
				'select2' => $select2_localize_data,
				// Shortcode
				'shortcode' => $this->shortcode,
				// Queries
				'queries' => $this->get_queries(),
				// User
				'user' => array(
					// Settings
					'settings' => array(
						// Query Builder
						'query-builder' => array(
							// Query Builder Mode
							'mode' => array(
								'name' => 'conductor-qb-mode',
								'value' => $this->get_query_builder_mode(),
								'values' => $this->get_query_builder_modes()
							)
						)
					)
				),
				// Widgets
				'widgets' => array(
					// Conductor
					'conductor' => array(
						// Defaults
						'defaults' => $conductor_widget->defaults
					)
				)
			), $this ) );

			/*
			 * Enqueue the Conductor Widget scripts and styles.
			 *
			 * Force the scripts to be enqueued by passing the widgets.php hook
			 */
			Conductor_Widget::admin_enqueue_scripts( 'widgets.php', array(
				'conductor-query-builder' => true
			) );
		}

		/**
		 * This function adds a media button to insert a Conductor Query shortcode into the editor.
		 */
		public function media_buttons() {
			global $pagenow;

			// Bail if we're not on the following pages
			if ( ! apply_filters( 'conductor_query_builder_display_media_buttons', in_array( $pagenow, array( 'post.php', 'post-new.php', 'page.php', 'page-new.php' ) ), $pagenow ) )
				return;
		?>
			<a href="#" class="button conductor-qb-button conductor-qb-add-shortcode" id="conductor-qb-add-shortcode" title="<?php esc_attr_e( 'Add Conductor Query Shortcode', 'conductor-query-builder' ); ?>">
				<strong class="conductor-branding conductor-branding-c"><?php _ex( 'C', 'C is for Conductor', 'conductor-query-builder' ); ?></strong>
				<?php _e( 'Add Conductor', 'conductor-query-builder' ); ?>
			</a>
		<?php
		}

		/**
		 * This function runs when a post is saved and sanitizes/stores all meta data.
		 */
		// TODO: Filters to allow for data modification before saving
		public function save_post( $post_id ) {
			// Grab the post type
			$post_type = get_post_type( $post_id );

			// Grab the query builder mode
			$query_builder_mode = $this->get_query_builder_mode();

			// Grab the Conductor Query Builder data (default to an empty array)
			$conductor_query_builder_data = $this->get_query_builder_data( $_POST, $query_builder_mode );

			/*
			 * Bail if:
			 *
			 * - the post type isn't a Conductor Query Builder post type or a revision
			 * - we don't have any data
			 * - the current user cannot edit this post
			 * - we don't have a valid nonce
			 * - this is an autosave
			 * - this is a cron job request
			 */
			if ( ( $post_type !== $this->post_type_name && $post_type !== 'revision' ) || empty( $conductor_query_builder_data ) || ! current_user_can( 'edit_post', $post_id ) || ! wp_verify_nonce( $_POST['conductor_qb_nonce'], 'conductor_query_builder_meta_box' ) || ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_autosave( $post_id ) ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) )
				return;

			// Grab the parent ID if this is a revision
			$parent_id = wp_is_post_revision( $post_id );

			do_action( 'conductor_query_builder_save_post_before', $post_id, $post_type, $parent_id, $query_builder_mode, $conductor_query_builder_data, $this );

			// Grab the simple query builder data
			$simple_conductor_query_builder_data = ( ! $parent_id ) ? $this->get_simple_query_builder_data( ( $query_builder_mode === 'simple' ) ? $_POST : $conductor_query_builder_data, $query_builder_mode ) : array();

			// If we have simple query builder data or this is a revision
			if ( ! empty( $simple_conductor_query_builder_data ) || $parent_id ) {
				// If this is a revision and we have a post meta value from the original post
				if ( $parent_id && ( $simple_conductor_query_builder_data = get_post_meta( $parent_id, $this->meta_key_prefix . $this->conductor_widget_meta_key_suffix, true ) ) )
					// Add post meta to the revision (using add_metadata() to ensure meta is added to the revision)
					add_metadata( 'post', $post_id, $this->meta_key_prefix . $this->conductor_widget_meta_key_suffix, $simple_conductor_query_builder_data );
				// Otherwise if this is a Conductor Query Builder post and we have a post meta value
				else if ( ! $parent_id && ! empty( $simple_conductor_query_builder_data ) )
					// Update post meta
					update_post_meta( $post_id, $this->meta_key_prefix . $this->conductor_widget_meta_key_suffix, $simple_conductor_query_builder_data );
			}

			// If we have a query builder mode
			if ( $query_builder_mode ) {
				// If this is a revision and we have a post meta value from the original post
				if ( $parent_id && ( $query_builder_mode = get_post_meta( $parent_id, $this->meta_key_prefix . $this->query_builder_mode_meta_key_suffix, true ) ) )
					// Add post meta to the revision (using add_metadata() to ensure meta is added to the revision)
					add_metadata( 'post', $post_id, $this->meta_key_prefix . $this->query_builder_mode_meta_key_suffix, $query_builder_mode );
				// Otherwise if this is a Conductor Query Builder post and we have a post meta value
				else if ( ! $parent_id && ! empty( $query_builder_mode ) )
					// Update post meta
					update_post_meta( $post_id, $this->meta_key_prefix . $this->query_builder_mode_meta_key_suffix, $query_builder_mode );
			}

			// Grab the clause types
			$clause_types = $this->get_clause_types();

			// Loop through the clause types
			foreach ( $clause_types as $clause_type ) {
				// Grab the post meta for this clause type
				$post_meta = ( ! $parent_id ) ? $this->get_clause_type_post_meta( $post_id, $clause_type, $conductor_query_builder_data ) : array();

				// If we have post meta or this is a revision
				if ( ! empty( $post_meta ) || $parent_id ) {
					// If this is a revision and we have a post meta value from the original post
					if ( $parent_id && ( $original_post_meta = get_post_meta( $parent_id, $this->meta_key_prefix . $clause_type, true ) ) )
						// Add post meta to the revision (using add_metadata() to ensure meta is added to the revision)
						add_metadata( 'post', $post_id, $this->meta_key_prefix . $clause_type, $original_post_meta );
					// Otherwise if this is a Conductor Query Builder post and we have a post meta value
					else if ( ! $parent_id && ! empty( $post_meta ) )
						// Update clause type post meta
						update_post_meta( $post_id, $this->meta_key_prefix . $clause_type, $post_meta );

					// Grab the clause type query arguments
					$clause_type_query_args = $this->get_clause_type_query_args( $post_id, $clause_type, $post_meta );

					// If we have clause type query arguments or this is a revision
					if ( ! empty( $clause_type_query_args ) || $parent_id ) {
						// If this is a revision and we have a post meta value from the original post
						if ( $parent_id && ( $clause_type_query_args = get_post_meta( $parent_id, $this->meta_key_prefix . $clause_type . $this->query_args_meta_key_suffix, true ) ) )
							// Add post meta to the revision (using add_metadata() to ensure meta is added to the revision)
							add_metadata( 'post', $post_id, $this->meta_key_prefix . $clause_type . $this->query_args_meta_key_suffix, $clause_type_query_args );
						// Otherwise if this is a Conductor Query Builder post and we have a post meta value
						else if ( ! $parent_id && ! empty( $clause_type_query_args ) )
							// Update the clause type query arguments post meta
							update_post_meta( $post_id, $this->meta_key_prefix . $clause_type . $this->query_args_meta_key_suffix, $clause_type_query_args );
					}
				}
				// Otherwise if this isn't a revision
				else if ( ! $parent_id ) {
					// Delete the clause type post meta
					delete_post_meta( $post_id, $this->meta_key_prefix . $clause_type );

					// Delete the clause type query arguments post meta
					delete_post_meta( $post_id, $this->meta_key_prefix . $clause_type . $this->query_args_meta_key_suffix );
				}
			}

			do_action( 'conductor_query_builder_save_post_after', $post_id, $post_type, $parent_id, $query_builder_mode, $conductor_query_builder_data, $this );
		}

		/**
		 * This function prints scripts in the admin footer.
		 */
		public function admin_print_footer_scripts() {
			global $hook_suffix, $post;

			// Bail if we're not on a page that supports the Conductor Query Builder
			if ( ! apply_filters( 'conductor_query_builder_admin_print_footer_scripts', in_array( $hook_suffix, array( 'post.php', 'post-new.php', 'page.php', 'page-new.php' ) ), $hook_suffix, $post, $this ) )
				return;
		?>
			<script type="text/javascript">
				// <![CDATA[
				( function( navigator, $, conductor_qb_clipboard ) {
					// Create a new Clipboard instance
					var ConductorQBClipboard = new Clipboard( '.conductor-qb-clipboard' );

					// Clipboard success
					ConductorQBClipboard.on( 'success', function( event ) {
						var $el = $( event.trigger );

						// Add the tooltip class
						$el.addClass( 'conductor-qb-tooltip' );

						// Show the tooltip
						$el.attr( 'aria-label', conductor_qb_clipboard.l10n.copied );

						// Remove the tooltip after 2500ms
						( function( $el ) {
							setTimeout( function() {
								// Remove the tooltip class
								$el.removeClass( 'conductor-qb-tooltip' );

								// Remove the tooltip
								$el.removeAttr( 'aria-label' );
							}, 2500 );
						} )( $el );
					} );

					// Clipboard error
					ConductorQBClipboard.on( 'error', function( event ) {
						var $el = $( event.trigger ),
							message = '';

						// Add the tooltip class
						$el.addClass( 'conductor-qb-tooltip' );

						// iPhone/iPad (No Support)
						if ( /iPhone|iPad/i.test( navigator.userAgent ) ) {
							message = conductor_qb_clipboard.l10n.error.no_support;
						}
						// Apple/Mac
						else if ( /Mac/i.test( navigator.userAgent ) ) {
							message = conductor_qb_clipboard.l10n.error.mac[event.action];
						}
						// Windows/Other
						else {
							message = conductor_qb_clipboard.l10n.error.windows[event.action];
						}

						// Show the tooltip
						$el.attr( 'aria-label', message );

						// Remove the tooltip after 2500ms
						( function( $el ) {
							setTimeout( function() {
								// Remove the tooltip class
								$el.removeClass( 'conductor-qb-tooltip' );

								// Remove the tooltip
								$el.removeAttr( 'aria-label' );
							}, 2500 );
						} )( $el );
					} );
				} )( window.navigator, jQuery, window.conductor_qb_clipboard );
				// ]]>
			</script>
		<?php
		}


		/*************
		 * Revisions *
		 *************/

		/**
		 * This function determines if a post has changed from the last revision
		 */
		public function wp_save_post_revision_post_has_changed( $post_has_changed, $last_revision, $post ) {
			// Grab the query builder mode
			$the_query_builder_mode = $this->get_query_builder_mode();

			// Grab the Conductor Query Builder data (default to an empty array)
			$conductor_query_builder_data = $this->get_query_builder_data( $_POST, $the_query_builder_mode );

			// Bail if the revision post has already changed, this isn't a Conductor Query Builder post type or we don't have any data
			if ( $post_has_changed || get_post_type( $post ) !== $this->post_type_name || empty( $conductor_query_builder_data ) )
				return $post_has_changed;

			// Grab the post ID
			$post_id = get_post_field( 'ID', $post );

			// Grab the last revision ID
			$last_revision_id = get_post_field( 'ID', $last_revision );

			// If we have simple query data for both the post and last revision
			if ( ( $simple_conductor_query_builder_data = maybe_serialize( $this->get_simple_query_builder_data( ( $the_query_builder_mode === 'simple' ) ? $_POST : $conductor_query_builder_data, $the_query_builder_mode ) ) ) && ( $last_revision_simple_conductor_query_builder_data = maybe_serialize( get_metadata( 'post', $last_revision_id, $this->meta_key_prefix . $this->conductor_widget_meta_key_suffix, true ) ) ) )
				// If the data for the post does not match the last revision
				if ( $simple_conductor_query_builder_data !== $last_revision_simple_conductor_query_builder_data )
					// Set the post has changed flag
					$post_has_changed = true;

			// If the post has changed flag is not set and we have query builder mode data for both the post and last revision
			if ( ! $post_has_changed && ( $query_builder_mode = $this->get_query_builder_mode() ) && ( $last_revision_query_builder_mode = get_metadata( 'post', $last_revision_id, $this->meta_key_prefix . $this->query_builder_mode_meta_key_suffix, true ) ) )
				// If the data for the post does not match the last revision
				if ( $query_builder_mode !== $last_revision_query_builder_mode )
					// Set the post has changed flag
					$post_has_changed = true;

			// Grab the clause types
			$clause_types = $this->get_clause_types();

			// Loop through the clause types
			foreach ( $clause_types as $clause_type ) {
				// If the post has changed flag is not set and we have clause type data for both the post and last revision
				if ( ! $post_has_changed && ( $post_meta = maybe_serialize( $this->get_clause_type_post_meta( $post_id, $clause_type, $conductor_query_builder_data ) ) ) && ( $last_revision_post_meta = maybe_serialize( get_metadata( 'post', $last_revision_id, $this->meta_key_prefix . $clause_type, true ) ) ) )
					// If the data for the post does not match the last revision
					if ( $post_meta !== $last_revision_post_meta ) {
						// Set the post has changed flag
						$post_has_changed = true;

						// Break from the loop
						break;
					}

				// If the post has changed flag is not set, we have post meta and we have clause type query argument data for both the post and last revision
				if ( ! $post_has_changed && isset( $post_meta ) && isset( $last_revision_post_meta ) && ( $clause_type_query_args = maybe_serialize( $this->get_clause_type_query_args( $post_id, $clause_type, maybe_unserialize( $post_meta ) ) ) ) && ( $last_revision_clause_type_query_args = maybe_serialize( get_metadata( 'post', $last_revision_id, $this->meta_key_prefix . $clause_type . $this->query_args_meta_key_suffix, true ) ) ) )
					// If the data for the post does not match the last revision
					if ( $clause_type_query_args !== $last_revision_clause_type_query_args ) {
						// Set the post has changed flag
						$post_has_changed = true;

						// Break from the loop
						break;
					}
			}

			return $post_has_changed;
		}

		/**
		 * This function adjusts WordPress post revision fields.
		 */
		public function _wp_post_revision_fields( $fields, $post_arr ) {
			// Bail if this isn't a Conductor Query Builder post
			if ( get_post_type( $post_arr['ID'] ) !== $this->post_type_name )
				return $fields;

			// Add the Conductor Query Builder Conductor Widget meta key
			$fields[$this->meta_key_prefix . $this->conductor_widget_meta_key_suffix] = __( 'Conductor Query Builder: Conductor Widget Instance', 'conductor-query-builder' );

			// Add the Conductor Query Builder query builder mode meta key
			$fields[$this->meta_key_prefix . $this->query_builder_mode_meta_key_suffix] = __( 'Conductor Query Builder: Query Builder Mode', 'conductor-query-builder' );

			// Grab the clause types
			$clause_types = $this->get_clause_types();

			// Loop through the clause types
			foreach ( $clause_types as $clause_type ) {
				// Create a label for this clause type
				$clause_type_label = ucwords( str_replace( array('_', '-' ), ' ', $clause_type ) );

				// Add the Conductor Query Builder clause type meta key
				$fields[$this->meta_key_prefix . $clause_type] = sprintf( __( 'Conductor Query Builder: %1$s Clause Type', 'conductor-query-builder' ), $clause_type_label );

				// Add the Conductor Query Builder clause type query arguments meta key
				$fields[$this->meta_key_prefix . $clause_type . $this->query_args_meta_key_suffix] = sprintf( __( 'Conductor Query Builder: %1$s Clause Type Query Arguments', 'conductor-query-builder' ), $clause_type_label );
			}

			return $fields;
		}

		/**
		 * This function adjusts WordPress post revision field values.
		 */
		public function _wp_post_revision_field( $value, $field, $post, $type ) {
			// Grab the post meta value for this field (keep it serialized, using get_metadata() to ensure meta is fetched from the revision)
			return var_export( get_metadata( 'post', get_post_field( 'ID', $post ), $field, true ), true );
		}

		/**
		 * This function runs when a post revision is restored.
		 */
		public function wp_restore_post_revision( $post_id, $revision_id ) {
			// Bail if this isn't a Conductor Query Builder post
			if ( get_post_type( $post_id ) !== $this->post_type_name )
				return;

			/*
			 * Restore the Conductor Query Builder Conductor Widget meta value
			 */
			if ( ( $conductor_widget = get_metadata( 'post', $revision_id, $this->meta_key_prefix . $this->conductor_widget_meta_key_suffix, true ) ) )
				// Update post meta
				update_post_meta( $post_id, $this->meta_key_prefix . $this->conductor_widget_meta_key_suffix, $conductor_widget );
			else
				// Delete post meta
				delete_post_meta( $post_id, $this->meta_key_prefix . $this->conductor_widget_meta_key_suffix );

			/*
			 * Restore the Conductor Query Builder query builder mode meta value
			 */
			if ( ( $query_builder_mode = get_metadata( 'post', $revision_id, $this->meta_key_prefix . $this->query_builder_mode_meta_key_suffix, true ) ) )
				// Update post meta
				update_post_meta( $post_id, $this->meta_key_prefix . $this->query_builder_mode_meta_key_suffix, $query_builder_mode );
			else
				// Delete post meta
				delete_post_meta( $post_id, $this->meta_key_prefix . $this->query_builder_mode_meta_key_suffix );

			/*
			 * Restore the Conductor Query Builder Conductor Widget meta value
			 */

			// Grab the clause types
			$clause_types = $this->get_clause_types();

			// Loop through the clause types
			foreach ( $clause_types as $clause_type ) {
				/*
				 * Restore the Conductor Query Builder clause type meta value
				 */
				if ( ( $post_meta = get_metadata( 'post', $revision_id, $this->meta_key_prefix . $clause_type, true ) ) )
					// Update post meta
					update_post_meta( $post_id, $this->meta_key_prefix . $clause_type, $post_meta );
				else
					// Delete post meta
					delete_post_meta( $post_id, $this->meta_key_prefix . $clause_type );

				/*
				 * Restore the Conductor Query Builder clause type query arguments meta value
				 */
				if ( ( $clause_type_query_args = get_metadata( 'post', $revision_id, $this->meta_key_prefix . $clause_type . $this->query_args_meta_key_suffix, true ) ) )
					// Update post meta
					update_post_meta( $post_id, $this->meta_key_prefix . $clause_type . $this->query_args_meta_key_suffix, $clause_type_query_args );
				else
					// Delete post meta
					delete_post_meta( $post_id, $this->meta_key_prefix . $clause_type . $this->query_args_meta_key_suffix );
			}
		}


		/********************
		 * Helper Functions *
		 ********************/

		/**
		 * This function returns the public post types as objects.
		 */
		public function get_post_types() {
			return apply_filters( 'conductor_query_builder_post_types', get_post_types( apply_filters( 'conductor_query_builder_get_post_types_args', array(
				'public' => true
			), $this ), 'objects' ), $this );
		}

		/**
		 * This function returns the clause types associated with the query builder.
		 */
		public function get_clause_types() {
			return apply_filters( 'conductor_query_builder_clause_types', array(
				'from',
				'where',
				'meta_query',
				'tax_query',
				'order_by',
				'limit'
			), $this );
		}

		/**
		 * This function returns the field types associated with the query builder.
		 */
		public function get_field_types() {
			return apply_filters( 'conductor_query_builder_field_types', array(
				'parameters',
				'operators',
				'values'
			), $this );
		}

		/**
		 * This function returns the query builder data.
		 */
		public function get_query_builder_data( $data, $query_builder_mode = 'simple' ) {
			// Bail if the Conductor Widget doesn't exist
			if ( ! function_exists( 'Conduct_Widget' ) )
				return false;

			// Grab the Conductor Widget instance
			$conductor_widget = Conduct_Widget();

			// Grab the Conductor Query Builder data (default to an empty array)
			$query_builder_data = ( $query_builder_mode === 'advanced' && isset( $data['conductor_query_builder'] ) ) ? $data['conductor_query_builder'] : ( ( $query_builder_mode === 'simple' && isset( $data['widget-' . $conductor_widget->id_base] ) ) ? $data['widget-' . $conductor_widget->id_base] : array() );

			// If the query builder mode is simple and we have data
			if ( $query_builder_mode === 'simple' && ! empty( $query_builder_data ) ) {
				// Grab the simple query builder mode data
				$simple_conductor_query_builder_data = $this->get_simple_query_builder_data( $query_builder_data, $query_builder_mode );

				// Convert the simple query builder data to advanced query builder data
				$query_builder_data = $this->convert_simple_query_builder_data_to_advanced_data( $simple_conductor_query_builder_data );
			}

			return $query_builder_data;
		}

		/**
		 * This function returns the Conductor Query Builder meta.
		 */
		public function get_post_meta( $post_id = false ) {
			global $post;

			// Post ID
			$post_id = ( empty( $post_id ) ) ? get_post_field( 'ID', $post ) : $post_id;

			// Flag to determine if this is a Conductor Query Builder post type
			$is_conductor_query_builder = ( get_post_type( $post_id ) === $this->post_type_name );

			// Post meta
			$post_meta = array();

			// Grab the clause types
			$clause_types = $this->get_clause_types();

			// Loop through the clause types
			foreach ( $clause_types as $clause_type ) {
				// Grab the meta value for this clause type
				$post_meta[$clause_type] = ( $is_conductor_query_builder ) ? $this->get_clause_type_post_meta( $post_id, $clause_type, array(), 'query' ) : array();

				// Ensure we have an array for empty meta values (as of WordPress 4.6, an empty string is returned when meta does not exist)
				if ( empty( $post_meta[$clause_type] ) && ! is_array( $post_meta[$clause_type] ) )
					$post_meta[$clause_type] = array();
			}

			return apply_filters( 'conductor_query_builder_post_meta', $post_meta, $post_id, $clause_types, $is_conductor_query_builder, $this );
		}

		/**
		 * This function returns the query arguments.
		 */
		public function get_query_args( $post_id = false, $post_meta = array() ) {
			global $post;

			// Post ID
			$post_id = ( empty( $post_id ) ) ? get_post_field( 'ID', $post ) : $post_id;

			// Bail if the post type isn't a Conductor Query Builder post type
			if ( get_post_type( $post_id ) !== $this->post_type_name )
				return false;

			// Query arguments
			$query_args = array();

			// Post meta
			$post_meta = ( empty( $post_meta ) ) ? $this->get_post_meta( $post_id ) : $post_meta;

			// Grab the clause types
			$clause_types = $this->get_clause_types();

			// Loop through the clause types
			foreach ( $clause_types as $clause_type ) {
				// If we meta for this clause type and we have a helper function for this clause type
				if ( isset( $post_meta[$clause_type] ) && ! empty( $post_meta[$clause_type] ) ) {
					// Grab the clause type query arguments
					$clause_type_query_args = $this->get_clause_type_query_args( $post_id, $clause_type, array(), 'query' );

					// Merge the clause type query arguments
					$query_args += $clause_type_query_args;
				}
			}

			return apply_filters( 'conductor_query_builder_query_args', $query_args, $post_id, $post_meta, $clause_types, $this );
		}

		/**
		 * This function returns the clause type post meta.
		 */
		public function get_clause_type_post_meta( $post_id, $clause_type, $query_builder_data = array(), $context = 'database' ) {
			global $post;

			// Post ID
			$post_id = ( empty( $post_id ) ) ? get_post_field( 'ID', $post ) : $post_id;

			// Bail if the post type isn't a Conductor Query Builder post type
			// TODO: This logic interfered with Beaver Builder logic
			//if ( get_post_type( $post_id ) !== $this->post_type_name )
			//	return array();

			// Post meta
			$post_meta = array();

			// Switch based on context
			switch ( $context ) {
				// Query
				case 'query':
					// Grab the meta value for this clause type
					$post_meta = get_post_meta( $post_id, $this->meta_key_prefix . $clause_type, true );
				break;

				// Database
				case 'database':
					// If we have data for this clause type
					if ( ! empty( $query_builder_data ) && isset( $query_builder_data[$clause_type] ) && ! empty( $query_builder_data[$clause_type] ) ) {
						// Grab the field types
						$field_types = $this->get_field_types();

						// Loop through clause groups
						foreach ( $query_builder_data[$clause_type] as $clause_group_id => $clause_group_data ) {
							// Cast the clause group ID to an integer
							$clause_group_id = ( int ) $clause_group_id;

							// If we have a valid clause group ID
							if ( $clause_group_id >= 0 ) {
								// Add this clause group ID to the post meta value
								$post_meta[$clause_group_id] = array();

								// Loop through the sub-clause groups
								foreach ( $clause_group_data as $sub_clause_group_id => $sub_clause_group_data ) {
									// Cast the sub-clause group ID to an integer
									$sub_clause_group_id = ( int ) $sub_clause_group_id;

									// If we have a valid sub-clause group ID
									if ( $sub_clause_group_id >= 0 ) {
										// Add this sub-clause group ID to the post meta value
										$post_meta[$clause_group_id][$sub_clause_group_id] = array();

										// Grab the parameters
										$parameters = $query_builder_data[$clause_type][$clause_group_id][$sub_clause_group_id]['parameters'];

										// Grab the parameters data
										$parameters_data = $this->get_parameters_data( $parameters, $clause_type );

										// Loop through sub-clause fields
										foreach ( $sub_clause_group_data as $field_type => $field_data ) {
											// If this is a valid field type
											if ( in_array( $field_type, $field_types ) && array_key_exists( $field_type, $this->clauses[$clause_type]['config']['columns'] ) ) {
												// Add this field type to the post meta value
												$post_meta[$clause_group_id][$sub_clause_group_id][$field_type] = array();

												// Switch based on field type
												switch ( $field_type ) {
													// Parameters
													case 'parameters':
														// If we have field data
														if ( ! empty( $field_data ) ) {
															// If we have a single parameter and it's valid
															if ( ! is_array( $field_data ) && array_key_exists( $field_data, $this->clauses[$clause_type][$field_type] ) )
																// Add this parameter to the post meta data
																$post_meta[$clause_group_id][$sub_clause_group_id][$field_type] = sanitize_text_field( $field_data );
															// Otherwise we don't have a single parameter or it's not valid
															else {
																// If we don't have an array of field data
																if ( ! is_array( $field_data ) )
																	// Convert the field data to an array
																	$field_data = array(
																		$field_data
																	);

																// Loop through field data
																foreach ( $field_data as $field_value )
																	// If this is a valid field value (parameter)
																	if ( array_key_exists( $field_value, $this->clauses[$clause_type][$field_type] ) )
																		// Add this parameter to the post meta data
																		$post_meta[$clause_group_id][$sub_clause_group_id][$field_type][] = sanitize_text_field( $field_value );
															}
														}
													break;

													// Operators
													case 'operators':
														// If we have an operator and it's an operator that exists in our list of operators
														if ( ! empty( $field_data ) && array_key_exists( $field_data, $this->operators ) )
															// If we have a single parameter
															if ( ! is_array( $parameters ) ) {
																// Grab the valid operators
																$operators = ( is_array( $parameters_data ) && isset( $parameters_data['operators'] ) ) ? $parameters_data['operators'] : ( ( ! is_array( $parameters_data ) ) ? ( array ) $parameters_data : $parameters_data );

																// If we have operators and a valid operator is selected
																if ( ! empty( $operators ) && in_array( $field_data, $operators ) )
																	$post_meta[$clause_group_id][$sub_clause_group_id][$field_type] = sanitize_text_field( $field_data );
															}
													break;

													// Values
													// TODO: We need to ensure BETWEEN/NOT BETWEEN (and other operators) 'limit' parameter is utilized here, keep the first?|last? two values
													case 'values':
														// If we have field data
														if ( ! empty( $field_data ) ) {
															// If we have a type data for this parameter
															if ( isset( $parameters_data['type'] ) && ! empty( $parameters_data['type'] ) )
																// Switch based on type
																switch ( $parameters_data['type'] ) {
																	// Integer
																	case 'int':
																		// If we have a single parameter
																		if ( ! is_array( $field_data ) )
																			// Add this parameter to the post meta data
																			$post_meta[$clause_group_id][$sub_clause_group_id][$field_type] = ( int ) $field_data;
																		// Otherwise if we have an array
																		else
																			// Map the field data
																			$post_meta[$clause_group_id][$sub_clause_group_id][$field_type] = array_map( 'intval', $field_data );
																	break;

																	// Default
																	default:
																		// If we have a single parameter
																		if ( ! is_array( $field_data ) )
																			// Add this parameter to the post meta data
																			$post_meta[$clause_group_id][$sub_clause_group_id][$field_type] = sanitize_text_field( $field_data );
																		// Otherwise if we have an array
																		else
																			// Map the field data
																			$post_meta[$clause_group_id][$sub_clause_group_id][$field_type] = array_map( 'sanitize_text_field', $field_data );
																	break;
																}
															// Otherwise we'll use generic sanitization
															else
																// If we have a single parameter
																if ( ! is_array( $field_data ) )
																	// Add this parameter to the post meta data
																	$post_meta[$clause_group_id][$sub_clause_group_id][$field_type] = sanitize_text_field( $field_data );
																// Otherwise if we have an array
																else
																	// Map the field data
																	$post_meta[$clause_group_id][$sub_clause_group_id][$field_type] = array_map( 'sanitize_text_field', $field_data );
														}

														// TODO: Allow support for empty values; it's possible that a user may want to query by an empty value (in a meta_query clause for instance)
													break;
												}
											}
										}

										// Grab the clause type field types
										$clause_group_field_types = array_keys( $this->clauses[$clause_type]['config']['columns'] );

										// Remove empty values from this sub-clause
										$post_meta[$clause_group_id][$sub_clause_group_id] = array_filter( $post_meta[$clause_group_id][$sub_clause_group_id] );

										// Grab this sub-clause type field types
										$sub_clause_field_types = array_keys( $post_meta[$clause_group_id][$sub_clause_group_id] );

										// Grab the differences between this sub-clause group field types and the clause group field types
										$field_type_differences = array_diff( $clause_group_field_types, $sub_clause_field_types );

										// If we don't have post meta, or all of the required meta, unset this value
										if ( empty( $post_meta[$clause_group_id][$sub_clause_group_id] ) || count( $field_type_differences ) )
											// If the only difference isn't values
											if ( empty( $field_type_differences ) || ( count( $field_type_differences ) !== 1 || ! in_array( 'values', $field_type_differences ) ) )
												// Remove this sub-clause group
												unset( $post_meta[$clause_group_id][$sub_clause_group_id] );
									}
								}

								/*
								 * All data is sanitized at this point, now we'll look for duplicate clause
								 * groups and sub-clause groups that don't belong (should be unique).
								 */

								// If we have post meta for this clause group
								if ( ! empty( $post_meta[$clause_group_id] ) ) {
									// Reset the sub-clause group IDs
									$post_meta[$clause_group_id] = array_values( $post_meta[$clause_group_id] );

									/// If there is more than one sub-clause group
									if ( count( $post_meta[$clause_group_id] ) > 1 ) {
										// Loop through field types
										foreach ( $field_types as $field_type ) {
											// If this is the parameters field
											if ( $field_type === 'parameters' ) {
												// Grab the parameters for this sub-clause
												$sub_clause_parameters = array_column( $post_meta[$clause_group_id], $field_type );

												// Grab the operators for this sub-clause
												$sub_clause_operators = array_column( $post_meta[$clause_group_id], 'operators' );

												// Grab the sub-clause IDs
												$sub_clause_ids = array_keys( $sub_clause_parameters );

												// Grab the unique sub-clause IDs
												$unique_sub_clause_ids = array_keys( array_unique( $sub_clause_parameters ) );

												// Grab the duplicate sub-clause IDs (by default array_unique() will always return the first array key for duplicates)
												$duplicate_sub_clause_ids = ( $sub_clause_ids !== $unique_sub_clause_ids ) ? array_diff( $sub_clause_ids, $unique_sub_clause_ids ) : array();

												// Grab the clause type field types
												$clause_group_field_types = array_keys( $this->clauses[$clause_type]['config']['columns'] );

												// If we have duplicates and this clause group supports operators or values
												if ( ! empty( $duplicate_sub_clause_ids ) && ( in_array( 'operators', $clause_group_field_types ) || in_array( 'values', $clause_group_field_types ) ) ) {
													// Loop through the unique sub-clause IDs
													foreach ( $unique_sub_clause_ids as $unique_sub_clause_id ) {
														// Grab the parameters data for the unique sub-clause group
														$parameters_data = $this->get_parameters_data( $sub_clause_parameters[$unique_sub_clause_id], $clause_type );

														// Loop through the duplicate sub-clause group IDs
														foreach ( $duplicate_sub_clause_ids as $duplicate_sub_clause_id ) {
															// If this duplicate parameter matches the unique sub-clause group and the operators are not supposed to be unique
															if ( $sub_clause_parameters[$unique_sub_clause_id] === $sub_clause_parameters[$duplicate_sub_clause_id] && ( ! isset( $parameters_data['unique'] ) || ( ! in_array( $sub_clause_operators[$unique_sub_clause_id], $parameters_data['unique'] ) && ! in_array( $sub_clause_operators[$duplicate_sub_clause_id], $parameters_data['unique'] ) ) ) ) {
																// If the operators match, we don't have values, or we have multiple values in the unique sub-clause and the duplicate sub-clause has an operator that supports multiple values
																if ( $sub_clause_operators[$unique_sub_clause_id] === $sub_clause_operators[$duplicate_sub_clause_id] || ( ! isset( $post_meta[$clause_group_id][$unique_sub_clause_id]['values'] ) || ! isset( $post_meta[$clause_group_id][$duplicate_sub_clause_id]['values'] ) ) || ( isset( $parameters_data['multiple'] ) && is_array( $parameters_data['multiple'] ) && ! empty( $parameters_data['multiple'] ) && isset( $post_meta[$clause_group_id][$unique_sub_clause_id]['values'] ) && in_array( $sub_clause_operators[$duplicate_sub_clause_id], $parameters_data['multiple'] ) ) ) {
																	// If this clause group doesn't support values or we have a type data for this parameter and it's Boolean
																	if ( ( ! in_array( 'values', $clause_group_field_types ) || ! isset( $post_meta[$clause_group_id][$unique_sub_clause_id]['values'] ) ) || ( isset( $parameters_data['type'] ) && ! empty( $parameters_data['type'] ) && $parameters_data['type'] === 'bool' ) ) {
																		// Operators
																		if ( ! in_array( 'values', $clause_group_field_types ) )
																			// Set the unique sub-clause group operator to the duplicate sub-clause group operator
																			$post_meta[$clause_group_id][$unique_sub_clause_id]['operators'] = $post_meta[$clause_group_id][$duplicate_sub_clause_id]['operators'];
																		// Values
																		else
																			// Set the unique sub-clause group value to the duplicate sub-clause group value
																			$post_meta[$clause_group_id][$unique_sub_clause_id]['values'] = $post_meta[$clause_group_id][$duplicate_sub_clause_id]['values'];
																	}
																	// Otherwise if this clause group supports values, merge the unique sub-clause group values with the duplicate sub-clause group values
																	else if ( in_array( 'values', $clause_group_field_types ) ) {
																		// If we can have multiple values
																		if ( isset( $parameters_data['multiple'] ) && $parameters_data['multiple'] === true || ( is_array( $parameters_data['multiple'] ) && ! empty( $parameters_data['multiple'] ) ) ) {
																			// If the unique sub-clause group values are not an array, cast it now so duplicates can be merged
																			if ( ! is_array( $post_meta[$clause_group_id][$unique_sub_clause_id]['values'] ) )
																				$post_meta[$clause_group_id][$unique_sub_clause_id]['values'] = ( array ) $post_meta[$clause_group_id][$unique_sub_clause_id]['values'];

																			// Merge the values of the duplicate with the unique sub-clause group
																			$post_meta[$clause_group_id][$unique_sub_clause_id]['values'] = array_merge( $post_meta[$clause_group_id][$unique_sub_clause_id]['values'], $post_meta[$clause_group_id][$duplicate_sub_clause_id]['values'] );
																		}
																		// Otherwise we can only have a single value, the last value set in this case will be used
																		else
																			$post_meta[$clause_group_id][$unique_sub_clause_id]['values'] = $post_meta[$clause_group_id][$duplicate_sub_clause_id]['values'];
																	}

																	// Remove this duplicate sub-clause group from post meta
																	unset( $post_meta[$clause_group_id][$duplicate_sub_clause_id] );
																}
															}
														}

														// If this clause group supports values and we have values
														if ( in_array( 'values', $clause_group_field_types ) && isset( $post_meta[$clause_group_id][$unique_sub_clause_id]['values'] ) && ! empty( $post_meta[$clause_group_id][$unique_sub_clause_id]['values'] ) ) {
															// Reset the unique sub-clause group values keys
															$post_meta[$clause_group_id][$unique_sub_clause_id]['values'] = array_values( $post_meta[$clause_group_id][$unique_sub_clause_id]['values'] );

															// If we have multiple values and the parameter data allows for it but the operator doesn't
															if ( count( $post_meta[$clause_group_id][$unique_sub_clause_id]['values'] ) > 1 && isset( $parameters_data['multiple'] ) && is_array( $parameters_data['multiple'] ) && ! empty( $parameters_data['multiple'] ) && ! in_array( $post_meta[$clause_group_id][$unique_sub_clause_id]['operators'], $parameters_data['multiple'] ) ) {
																// Set the operator to the first operator that supports multiple values
																$post_meta[$clause_group_id][$unique_sub_clause_id]['operators'] = ( array) reset( $parameters_data['multiple'] );
															}
														}
													}
												}
											}
										}

										// Reset the clause group keys
										$post_meta[$clause_group_id] = array_values( $post_meta[$clause_group_id] );
									}
								}
							}
						}
					}
				break;
			}

			return $post_meta;
		}

		/**
		 * This function returns the clause type query arguments.
		 */
		public function get_clause_type_query_args( $post_id, $clause_type, $clause_type_post_meta = array(), $context = 'database', $ajax = false ) {
			// Clause type query args
			$clause_type_query_args = array();

			// Switch based on context
			switch ( $context ) {
				// Query
				case 'query':
					// Grab the meta value for this clause type
					$query_args = ( ! $ajax && empty( $clause_type_post_meta ) ) ? get_post_meta( $post_id, $this->meta_key_prefix . $clause_type . $this->query_args_meta_key_suffix, true ) : $clause_type_post_meta;

					// If we have query arguments
					if ( $query_args ) {
						// Set the clause type query arguments
						$clause_type_query_args = $query_args;

						// Loop through clause type query arguments
						foreach ( $clause_type_query_args as $query_arg => $query_arg_value )
							// Switch based on clause type
							switch ( $clause_type ) {
								// WHERE Meta (Custom Field)
								// TODO: Move BETWEEN/NOT BETWEEN logic to database logic
								case 'meta_query':
									// Loop through the query argument values
									foreach ( $query_arg_value as $query_arg_parameter => $query_arg_parameter_value ) {
										// If this query argument parameter value is an array
										if ( is_array( $query_arg_parameter_value ) && ! isset( $query_arg_parameter_value['compare'] ) ) {
											// Loop through the query argument parameter values
											foreach ( $query_arg_parameter_value as $nested_query_arg_parameter => $nested_query_arg_parameter_value )
												// If this nested query argument parameter value is an array
												if ( is_array( $nested_query_arg_parameter_value ) && isset( $nested_query_arg_parameter_value['compare'] ) ) {
													// Set the correct operator for this nested query argument parameter value
													$clause_type_query_args[$query_arg][$query_arg_parameter][$nested_query_arg_parameter]['compare'] = $this->query_args_operators[$nested_query_arg_parameter_value['compare']];

													// If we have a value set
													if ( isset( $nested_query_arg_parameter_value['value'] ) )
														// Switch based on type of comparison
														switch ( $nested_query_arg_parameter_value['compare'] ) {
															// BETWEEN
															case 'BETWEEN':
															// NOT BETWEEN
															case 'NOT BETWEEN':
																// Map the field data
																$clause_type_query_args[$query_arg][$query_arg_parameter][$nested_query_arg_parameter]['value'] = array_map( 'intval', $nested_query_arg_parameter_value['value'] );

																// Ensure the type is NUMERIC
																$clause_type_query_args[$query_arg][$query_arg_parameter][$nested_query_arg_parameter]['type'] = 'NUMERIC';
															break;
														}
												}
										}
										// Otherwise if we have a compare clause just set the correct operator for this query argument parameter value
										else if ( isset( $query_arg_parameter_value['compare'] ) ) {
											$clause_type_query_args[$query_arg][$query_arg_parameter]['compare'] = $this->query_args_operators[$query_arg_parameter_value['compare']];

											// If we have a value set
											if ( isset( $query_arg_parameter_value['value'] ) )
												// Switch based on type of comparison
												switch ( $query_arg_parameter_value['compare'] ) {
													// BETWEEN
													case 'BETWEEN':
													// NOT BETWEEN
													case 'NOT BETWEEN':
														// Map the field data
														$clause_type_query_args[$query_arg][$query_arg_parameter]['value'] = array_map( 'intval', $query_arg_parameter_value['value'] );

														// Ensure the type is NUMERIC
														$clause_type_query_args[$query_arg][$query_arg_parameter]['type'] = 'NUMERIC';
													break;
												}
										}
									}
								break;

								// WHERE Taxonomy
								case 'tax_query':
									// If the query argument value is an array
									if ( is_array( $query_arg_value ) )
										// Loop through the query argument values
										foreach ( $query_arg_value as $query_arg_parameter => $query_arg_parameter_value ) {
											// If this query argument parameter value is an array
											if ( is_array( $query_arg_parameter_value ) && ! isset( $query_arg_parameter_value['operator'] ) ) {
												// Loop through the query argument parameter values
												foreach ( $query_arg_parameter_value as $nested_query_arg_parameter => $nested_query_arg_parameter_value )
													// If this nested query argument parameter value is an array
													if ( is_array( $nested_query_arg_parameter_value ) && isset( $nested_query_arg_parameter_value['operator'] ) )
														// Set the correct operator for this nested query argument parameter value
														$clause_type_query_args[$query_arg][$query_arg_parameter][$nested_query_arg_parameter]['operator'] = $this->query_args_operators[$nested_query_arg_parameter_value['operator']];
											}
											// Otherwise if we have an operator clause just set the correct operator for this query argument parameter value
											else if ( isset( $query_arg_parameter_value['operator'] ) )
												$clause_type_query_args[$query_arg][$query_arg_parameter]['operator'] = $this->query_args_operators[$query_arg_parameter_value['operator']];
										}
								break;

								// Default
								default:
									// If the query argument value is an array
									if ( is_array( $query_arg_value ) ) {
										// Loop through the query argument values
										foreach ( $query_arg_value as $query_arg_parameter => $query_arg_parameter_value )
											// If this query argument parameter value exists in our query argument operators
											if ( ! is_array( $query_arg_parameter_value ) && isset( $this->query_args_operators[$query_arg_parameter_value] ) )
												// Set the value to the query argument operator value
												$clause_type_query_args[$query_arg][$query_arg_parameter] = $this->query_args_operators[$query_arg_parameter_value];
									}
									// Otherwise the query argument value is a string
									else
										// If this query argument value exists in our query argument operators
										if ( isset( $this->query_args_operators[$query_arg_value] ) )
											// Set the value to the query argument operator value
											$clause_type_query_args[$query_arg] = $this->query_args_operators[$query_arg_value];
								break;
							}
					}
				break;

				// Database
				case 'database':
					// Clause type allows multiple clause groups
					$allows_multiple_clause_groups = ( ! isset( $this->clauses[$clause_type]['config']['limit'] ) || $this->clauses[$clause_type]['config']['limit'] > 1 );

					// Clause type has multiple clause groups
					$has_multiple_clause_groups = $this->clause_type_has_multiple_clause_groups( $clause_type, $allows_multiple_clause_groups, $clause_type_post_meta );

					// Clause type allows for multiple query argument values
					$allows_multiple_query_arg_values = ( isset( $this->clauses[$clause_type]['multiple_query_arg_values'] ) && $this->clauses[$clause_type]['multiple_query_arg_values'] );

					// Create the clause type query argument if we have multiple clause groups
					if ( $has_multiple_clause_groups )
						$clause_type_query_args[$clause_type] = array();

					// Loop through clause groups
					foreach ( $clause_type_post_meta as $clause_group_id => $clause_group_data ) {
						// Create the clause group query argument if we have multiple sub-clause groups
						if ( $has_multiple_clause_groups )
							$clause_type_query_args[$clause_type][$clause_group_id] = array();

						// Loop through sub-clause groups
						foreach ( $clause_group_data as $sub_clause_group_id => $sub_clause_group_data ) {
							// Grab the parameter(s)
							$parameters = $sub_clause_group_data['parameters'];

							// Grab the key from which to pull values from
							$values_key = ( array_key_exists( 'values', $this->clauses[$clause_type]['config']['columns'] ) && ( ( isset( $sub_clause_group_data['values'] ) && ! empty( $sub_clause_group_data['values'] ) ) || ( ( ! is_array( $parameters ) && ! isset( $this->parameters[$parameters] ) ) || ( ! is_array( $this->parameters[$parameters] ) || ( isset( $this->parameters[$parameters]['type'] ) && $this->parameters[$parameters]['type'] !== 'bool' ) ) ) ) ) ? 'values' : ( ( array_key_exists( 'operators', $this->clauses[$clause_type]['config']['columns'] ) && isset( $sub_clause_group_data['operators'] ) && ! empty( $sub_clause_group_data['operators'] ) ) ? 'operators' : 'parameters' );

							// If this clause type has a query argument specified
							if ( isset( $this->clauses[$clause_type]['query_arg'] ) && ! empty( $this->clauses[$clause_type]['query_arg'] ) ) {
								// If this clause type allows for multiple query argument values
								if ( $allows_multiple_query_arg_values ) {
									// Create the query argument array if it doesn't already exist
									if ( ! isset( $clause_type_query_args[$this->clauses[$clause_type]['query_arg']] ) )
										$clause_type_query_args[$this->clauses[$clause_type]['query_arg']] = array();

									// Append this query argument value
									$clause_type_query_args[$this->clauses[$clause_type]['query_arg']][$parameters] = $sub_clause_group_data[$values_key];
								}
								// Otherwise just overwrite the query argument value (use the last value)
								else
									$clause_type_query_args[$this->clauses[$clause_type]['query_arg']] = $sub_clause_group_data[$values_key];
							}
							// Otherwise if this should be considered a "global" parameter
							else if ( ! is_array( $parameters ) && isset( $this->parameters[$parameters] ) ) {
								// Grab the parameters data
								$parameters_data = $this->get_parameters_data( $parameters, $clause_type );

								// Grab the correct parameter
								$parameter = ( isset( $parameters_data['operators'] ) && ! empty( $parameters_data['operators'] ) && isset( $sub_clause_group_data['operators'] ) && ! empty( $sub_clause_group_data['operators'] ) ) ? array_search( $sub_clause_group_data['operators'], $parameters_data['operators'] ) : false;
								$parameter = ( ! $parameter || is_int( $parameter ) ) ? $parameters : $parameter;

								$clause_type_query_args[$parameter] = ( isset( $parameters_data['multiple'] ) && ! empty( $parameters_data['multiple'] ) && isset( $sub_clause_group_data['operators'] ) && ! empty( $sub_clause_group_data['operators'] ) && in_array( $sub_clause_group_data['operators'], $parameters_data['multiple'] ) ) ? $sub_clause_group_data[$values_key] : ( ( isset( $sub_clause_group_data[$values_key] ) && is_array( $sub_clause_group_data[$values_key] ) ) ? end( $sub_clause_group_data[$values_key] ) : ( ( isset( $sub_clause_group_data[$values_key] ) ) ? $sub_clause_group_data[$values_key] : false ) );

								// If the parameter value is an operator and it's Boolean use the Boolean value
								if ( array_key_exists( $parameter, $this->operators ) && is_array( $this->operators[$parameter] ) && isset( $this->operators[$parameter]['type'] ) && $this->operators[$parameter]['type'] === 'bool' )
									$clause_type_query_args[$parameter] = $this->operators[$clause_type_query_args[$parameter]]['value'];
							}
							// Otherwise this parameter will be added to the clause type query argument
							else {
								// Grab the parameters data
								$parameters_data = $this->get_parameters_data( $parameters, $clause_type );

								// Switch based on clause type
								switch ( $clause_type ) {
									// WHERE Meta (Custom Field)
									// TODO: Allow for TYPE to be specified
									case 'meta_query':
										// If we have multiple clause groups
										if ( $has_multiple_clause_groups )
											$meta_query_args = &$clause_type_query_args[$clause_type][$clause_group_id];
										// Otherwise we have just one clause group
										else
											$meta_query_args = &$clause_type_query_args[$clause_type];

										$meta_query_args[] = array(
											'key' => $parameters,
											'value' => ( isset( $parameters_data['multiple'] ) && ! empty( $parameters_data['multiple'] ) && in_array( $sub_clause_group_data['operators'], $parameters_data['multiple'] ) && isset( $sub_clause_group_data[$values_key] ) ) ? $sub_clause_group_data[$values_key] : ( ( isset( $sub_clause_group_data[$values_key] ) ) ? ( ( is_array( $sub_clause_group_data[$values_key] ) ) ? end( $sub_clause_group_data[$values_key] ) : $sub_clause_group_data[$values_key] ) : null ),
											'compare' => $sub_clause_group_data['operators']
										);

										// Grab the last index for the meta query arguments
										$meta_query_args_last_index = ( count( $meta_query_args ) - 1 );

										// If the value is null
										if ( is_null( $meta_query_args[$meta_query_args_last_index]['value'] ) )
											// Unset the value
											unset( $meta_query_args[$meta_query_args_last_index]['value'] );
									break;

									// WHERE Taxonomy
									case 'tax_query':
										// Split the parameter into taxonomy and field (taxonomy will be [0], field will be in [1])
										$parameters = explode( ':', $parameters );

										// If we have multiple clause groups
										if ( $has_multiple_clause_groups )
											$tax_query_args = &$clause_type_query_args[$clause_type][$clause_group_id];
										// Otherwise we have just one clause group
										else
											$tax_query_args = &$clause_type_query_args[$clause_type];

										$tax_query_args[] = array(
											'taxonomy' => $parameters[0],
											'field' => $parameters[1],
											'terms' => ( isset( $parameters_data['multiple'] ) && ! empty( $parameters_data['multiple'] ) && in_array( $sub_clause_group_data['operators'], $parameters_data['multiple'] ) ) ? $sub_clause_group_data[$values_key] : ( ( is_array( $sub_clause_group_data[$values_key] ) ) ? end( $sub_clause_group_data[$values_key] ) : $sub_clause_group_data[$values_key] ),
											'operator' => $sub_clause_group_data['operators']
										);
									break;
								}
							}
						}

						// If we have multiple sub-clause groups
						if ( $has_multiple_clause_groups ) {
							// Grab the count for this clause type query argument
							$clause_group_query_arg_count = count( $clause_type_query_args[$clause_type][$clause_group_id] );

							// If this sub-clause query argument is empty, remove it here
							if ( empty( $clause_type_query_args[$clause_type][$clause_group_id] ) )
								unset( $clause_type_query_args[$clause_type][$clause_group_id] );
							// Otherwise if we have more than one sub-clause query argument, set the relationship to AND
							else if ( $clause_group_query_arg_count > 1 )
								$clause_type_query_args[$clause_type][$clause_group_id]['relation'] = 'AND';
							// Otherwise if we only have one sub-clause query argument, set this clause group query argument to the sub-clause group query argument
							else
								$clause_type_query_args[$clause_type][$clause_group_id] = $clause_type_query_args[$clause_type][$clause_group_id][0];
						}
					}

					// If this clause type allows for multiple clause groups and we have multiple clause groups
					if ( $allows_multiple_clause_groups && $has_multiple_clause_groups ) {
						// Reset the array keys
						$clause_type_query_args[$clause_type] = array_values( $clause_type_query_args[$clause_type] );

						// If we have more than one query argument in this clause type
						if ( count( $clause_type_query_args[$clause_type] ) > 1 )
							// Set the relationship to OR
							$clause_type_query_args[$clause_type]['relation'] = 'OR';
					}
				break;
			}

			return apply_filters( 'conductor_query_builder_clause_type_query_args', $clause_type_query_args, $post_id, $clause_type_post_meta, $clause_type, $context, $this );
		}

		/**
		 * This function returns the query builder mode for a query.
		 */
		public function get_query_builder_mode_for_query( $post_id = false ) {
			global $post;

			// Post ID
			$post_id = ( empty( $post_id ) ) ? get_post_field( 'ID', $post ) : $post_id;

			// Grab the query builder mode from meta
			$query_builder_mode = get_post_meta( $post_id, $this->meta_key_prefix . $this->query_builder_mode_meta_key_suffix, true );

			// If we don't have a query builder mode
			if ( empty( $query_builder_mode ) )
				// Default to simple
				$query_builder_mode = 'simple';

			// TODO: Filter
			return $query_builder_mode;
		}

		/**
		 * This function returns the parameters data based on parameters.
		 */
		public function get_parameters_data( $parameter, $clause_type = false ) {
			// Parameters data
			$parameters_data = ( ! is_array( $parameter ) && array_key_exists( $parameter, $this->parameters ) ) ? $this->parameters[$parameter] : false;
			$has_possible_field_parameters = ( $clause_type && ! is_array( $parameter ) && isset( $this->clauses[$clause_type]['parameters'][$parameter] ) && isset( $this->clauses[$clause_type]['parameters'][$parameter]['field'] ) );

			// If we don't have parameter data yet, try to grab data from the field name
			if ( ! $parameters_data || $has_possible_field_parameters || ! isset( $parameters_data['operators'] ) ) {
				// Grab the field name
				$field_name = ( $has_possible_field_parameters ) ? $this->clauses[$clause_type]['parameters'][$parameter]['field'] : false;

				// If we have a field name
				if ( $field_name )
					// If the field name exists within parameters data
					if ( is_array( $this->parameters[$clause_type] ) && isset( $this->parameters[$clause_type]['fields'][$field_name] ) && ! empty( $this->parameters[$clause_type]['fields'][$field_name] ) )
						// Grab the parameter data from the field name
						$parameters_data = $this->parameters[$clause_type]['fields'][$field_name];
			}

			return apply_filters( 'conductor_query_builder_parameters_data', $parameters_data, $parameter, $clause_type, $this );
		}


		/**************
		 * Shortcodes *
		 **************/

		/**
		 * This function renders the [conductor] shortcode.
		 */
		public function conductor( $attributes ) {
			// Output
			$output = '';

			// Bail if this isn't the main query, we're not in the loop, we haven't passed wp_head, or we're already doing a Conductor Query
			if ( apply_filters( 'conductor_query_builder_skip_shortcode_render', ( ! is_main_query() || ! in_the_loop() || ! $this->did_action( 'wp_head' ) || $this->doing_conductor_query ), $attributes, $this ) )
				return $output;

			// Grab the post IDs
			$post_ids = ( isset( $attributes['id'] ) ) ? explode( ',', $attributes['id'] ) : ( ( isset( $attributes['ids'] ) ) ? explode( ',', $attributes['ids'] ) : array() );

			// Grab the titles
			$titles = ( isset( $attributes['title'] ) ) ? explode( '|', $attributes['title'] ) : ( ( isset( $attributes['titles'] ) ) ? explode( '|', $attributes['titles'] ) : array() );

			// Bail if we don't have at least one post ID
			if ( empty( $post_ids ) )
				return $output;

			// Start output buffering
			ob_start();

				// Loop through post IDs
				foreach ( $post_ids as $index => $post_id ) {
					// Trim/sanitize the post ID
					$post_id = trim( ( int ) $post_id );

					// Grab the title for this query
					$title = ( isset( $titles[$index] ) && ! empty( $titles[$index] ) ) ? $titles[$index] : '';

					// Render this Conductor Query
					$this->render( $post_id, $title, 'shortcode' );
				}

			// Grab the output from the buffer
			$output .= ob_get_clean();

			return $output;
		}

		/**
		 * This function renders the shortcode query builder elements.
		 */
		public function shortcode_query_builder( $form_element = 'form', $conductor_widget_instance = array() ) {
			global $post;

			// Bail if we shouldn't render the shortcode query builder markup
			if ( ! apply_filters( 'conductor_query_builder_output_shortcode_query_builder_markup', true, $post, $this ) )
				return;

			// Grab the post ID
			$post_id = get_post_field( 'ID', $post );

			// Grab the post meta
			$post_meta = $this->get_post_meta( $post_id );

			// Grab the Conductor Widget instance
			$conductor_widget = Conduct_Widget();

			// Grab the Conductor Widget instance data
			$conductor_widget_instance = ( empty( $conductor_widget_instance ) ) ? $this->get_conductor_widget_instance( $post_id ) : $conductor_widget_instance;
		?>
			<?php // Thickbox requires an element within the wrapper ?>
			<div id="conductor-qb-shortcode-wrapper-container" class="conductor-qb-shortcode-wrapper-container">
				<div id="conductor-qb-shortcode-wrapper" class="conductor-qb-shortcode-wrapper">
					<div id="conductor-qb-shortcode-tabs-wrapper" class="conductor-qb-tabs-wrapper conductor-qb-shortcode-tabs-wrapper">
						<h2 class="nav-tab-wrapper current conductor-qb-tabs conductor-qb-shortcode-tabs">
							<a class="nav-tab nav-tab-active" href="#conductor-qb-shortcode-insert-tab-content" data-type="conductor-qb-shortcode" data-shortcode-action="insert"><?php _e( 'Insert', 'conductor-query-builder' ); ?></a>
							<?php
								// If the current user has the Conductor capability
								if ( current_user_can( Conductor::$capability ) ) :
							?>
									<a class="nav-tab" href="#conductor-qb-shortcode-output-tab-content" data-type="conductor-qb-shortcode" data-shortcode-action="create"><?php _e( 'Create', 'conductor-query-builder' ); ?></a>
							<?php
								endif;
							?>
						</h2>
					</div>

					<div id="conductor-qb-shortcode-tab-content-wrapper" class="conductor-qb-tab-content-wrapper conductor-qb-shortcode-tab-content-wrapper">
						<?php
							/*
							 * Shortcode Insert Query Builder
							 */
							Conductor_Query_Builder_Admin_Views::shortcode_query_builder_insert_tab_content( $post, $post_meta, $this, $conductor_widget_instance, $conductor_widget );
						?>

						<?php
							/*
							 * Shortcode Create Query Builder
							 */

							// If the current user has the Conductor capability
							if ( current_user_can( Conductor::$capability ) )
								Conductor_Query_Builder_Admin_Views::shortcode_query_builder_create_tab_content( $post, $post_meta, $this, $conductor_widget_instance, $conductor_widget, $form_element );
						?>
					</div>

					<?php
						/*
						 * Shortcode Actions Query Builder
						 */
						Conductor_Query_Builder_Admin_Views::shortcode_query_builder_actions( $post, $post_meta, $this, $conductor_widget_instance, $conductor_widget );
					?>
				</div>
			</div>
		<?php
		}

		/**
		 * This function adjusts the Conductor Widget settings.
		 */
		public function conductor_widget_settings( $settings ) {
			// Bail if we don't have a current Conductor Widget instance
			if ( empty( $this->current_conductor_widget_instance ) )
				return $settings;

			// Add the the_widget() settings
			$settings[$this->the_widget_number] = $this->current_conductor_widget_instance;

			return $settings;
		}

		/**
		 * This function adjusts the Conductor query arguments based on the current Conductor
		 * Query arguments.
		 */
		public function conductor_query_args( $query_args, $type, $instance, $conductor_widget_query ) {
			global $wp_query;

			// If we're currently doing a preview
			if ( $this->doing_preview ) {
				// If the posts per page argument is not set to 1
				if ( ! isset( $query_args['posts_per_page'] ) || $query_args['posts_per_page'] !== 1 )
					// Set the posts per page argument to 1
					$query_args['posts_per_page'] = 1;

				// If the post status argument is not set
				if ( ! isset( $query_args['post_status'] ) )
					// Set the post status argument to publish
					$query_args['post_status'] = 'publish';
			}

			// Bail if this is a simple query or we don't have any current Conductor Query arguments
			if ( $this->current_query_builder_mode === 'simple' || empty( $this->current_query_args ) )
				return $query_args;

			// Get the "true" paged query variable from the main query (defaulting to 1)
			$paged = $query_args['paged'] = ( int ) get_query_var( 'paged' );
			// Use the paged query var if set
			if ( empty( $query_args['paged'] ) && isset( $wp_query->query['paged'] ) )
				$paged = ( int ) $wp_query->query['paged'];
			// Single post uses "page" instead of "paged"
			else if ( is_single() && ( int ) get_query_var( 'page' ) )
				$paged = ( int ) get_query_var( 'page' );
			// Otherwise assume page 1
			else if ( empty( $query_args['paged'] ) )
				$paged  = 1;

			// Grab the _conductor query argument from the original query
			$_conductor = ( isset( $query_args['_conductor'] ) ) ? $query_args['_conductor'] : array();

			// Loop through current query arguments
			foreach ( $this->current_query_args as $query_arg => $value ) {
				// Grab the parameters data
				$parameter_data = $this->get_parameters_data( $query_arg );

				// If we don't have parameter data or this parameter isn't specific to Conductor
				if ( empty( $parameter_data ) || ! isset( $parameter_data['conductor'] ) || ! $parameter_data['conductor'] ) {
					// If this query argument isn't set, or the current value doesn't match our value
					if ( ! isset( $query_args[$query_arg] ) || $query_args[$query_arg] !== $value )
						// Set the query argument to our query argument value
						$query_args[$query_arg] = $value;
				}
			}

			// Set the paged query argument
			$query_args['paged'] = $paged;

			// If we shouldn't have an offset
			if ( isset( $query_args['offset'] ) && $query_args['offset'] === 0 && ( ! isset( $this->current_query_args['offset'] ) || $this->current_query_args['offset'] === 0 ) )
				// Unset the offset
				unset( $query_args['offset'] );

			// If we have a posts per page query argument and the current query arguments do not have a maximum number of posts query argument or the posts per page query argument doesn't match the current query arguments maximum number of posts query argument
			if ( ! empty( $query_args['posts_per_page'] ) && ( ! isset( $this->current_query_args['max_num_posts'] ) || $query_args['posts_per_page'] !== $this->current_query_args['max_num_posts'] ) ) {
				// Grab the post counts
				$post_counts = wp_count_posts( ( is_array( $query_args['post_type'] ) ) ? $query_args['post_type'][0] : $query_args['post_type'] );

				// Grab the offset
				$offset = ( isset( $query_args['offset'] ) ) ? $query_args['offset'] : 0;

				// Grab the maximum number of pages
				// TODO: In some cases (e.g. custom data types) the publish post count may not exist
				$max_num_pages = ( ! isset( $this->current_query_args['max_num_posts'] ) ) ? ceil( ( $post_counts->publish - $offset ) / $query_args['posts_per_page'] ) : ceil( $this->current_query_args['max_num_posts'] / $query_args['posts_per_page'] ) ;

				// Set the maximum number of pages _conductor query argument
				$_conductor['max_num_pages'] = $max_num_pages;
			}

			// Set the _conductor query argument
			$query_args['_conductor'] = $_conductor;

			// If we're currently doing a preview
			if ( $this->doing_preview ) {
				// If the posts per page argument is not set to 1
				if ( ! isset( $query_args['posts_per_page'] ) || $query_args['posts_per_page'] !== 1 )
					// Set the posts per page argument to 1
					$query_args['posts_per_page'] = 1;

				// If the post status argument is not set
				if ( ! isset( $query_args['post_status'] ) )
					// Set the post status argument to publish
					$query_args['post_status'] = 'publish';
			}

			return $query_args;
		}

		/**
		 * This function adjusts the number of found posts for Conductor queries.
		 */
		public function conductor_query_found_posts( $found_posts, $conductor_query, $orig_found_posts ) {
			// Bail if this is a simple query, we don't have any current Conductor Query arguments or the maximum number of posts query argument is set
			if ( $this->current_query_builder_mode === 'simple' || empty( $this->current_query_args ) || isset( $this->current_query_args['max_num_posts'] ) )
				return $found_posts;

			// Set the found posts to the original value
			$found_posts = $orig_found_posts;

			return $found_posts;
		}

		/**
		 * This function adjusts the has pagination flag on Conductor queries.
		 */
		public function conductor_query_has_pagination( $has_pagination, $conductor_query ) {
			// Return false if we're currently doing a preview
			if ( $this->doing_preview )
				return false;

			// Bail if this is a simple query, we don't have any current Conductor Query arguments or the maximum number of posts query argument is set
			if ( $this->current_query_builder_mode === 'simple' || empty( $this->current_query_args ) || isset( $this->current_query_args['max_num_posts'] ) )
				return $has_pagination;

			// Grab the query
			$query = $conductor_query->get_query();

			// Set the has pagination flag
			$has_pagination = ( $query->max_num_pages > 0 );

			return $has_pagination;
		}

		/**
		 * This function adjusts the Conductor Widget before widget data attributes
		 */
		public function conductor_widget_before_widget_data_attributes( $before_widget_data_attrs, $params, $instance, $conductor_widget_settings, $conductor_widget ) {
			// Bail if this isn't the query builder temporary sidebar or we don't have query builder data
			if ( $params[0]['id'] !== $this->temporary_sidebar_id || ! isset( $params[0]['conductor_query_builder'] ) || empty( $params[0]['conductor_query_builder'] ) || ! isset( $params[0]['conductor_query_builder']['before_widget_data_attributes'] ) || empty( $params[0]['conductor_query_builder']['before_widget_data_attributes'] ) )
				return $before_widget_data_attrs;

			// Grab the before widget data attributes
			$query_builder_before_widget_data_attrs = $params[0]['conductor_query_builder']['before_widget_data_attributes'];

			// If we have the query builder widget ID before widget attribute
			if ( isset( $query_builder_before_widget_data_attrs['data-query-builder-widget-id'] ) && ! empty( $query_builder_before_widget_data_attrs['data-query-builder-widget-id'] ) )
				// Set the widget ID before widget data attribute to the query builder widget ID before widget data attribute
				$before_widget_data_attrs['data-widget-id'] = $query_builder_before_widget_data_attrs['data-query-builder-widget-id'];

			return $before_widget_data_attrs;
		}

		/**
		 * This function sets up the correct global references for Conductor Queries when doing
		 * a preview.
		 */
		public function conductor_query_builder_render_preview_before() {
			global $post;

			// Store a reference to the global $post
			$this->global_post = $post;

			// Bail if we don't have preview data
			if ( ! isset( $this->preview_data['query_args'] ) || empty( $this->preview_data['query_args'] ) || ! isset( $this->preview_data['conductor_widget_instance'] ) || empty( $this->preview_data['conductor_widget_instance'] ) || ! isset( $this->preview_data['query_builder_mode'] ) || empty( $this->preview_data['query_builder_mode'] ) )
				return;

			/*
			 * Setup the global references
			 */

			// Current query arguments
			$this->current_query_args = $this->preview_data['query_args'];

			// Current Conductor Widget instance
			$this->current_conductor_widget_instance = $this->preview_data['conductor_widget_instance'];

			// Current query builder mode
			$this->current_query_builder_mode = $this->preview_data['query_builder_mode'];
		}

		/**
		 * This function resets the global $post reference after previewing a Conductor query.
		 */
		public function conductor_query_builder_render_preview_after() {
			// Reset the global $post
			$this->reset_global_post();
		}

		/**
		 * This function outputs a "no posts" message for Conductor queries when doing a preview.
		 */
		public function conductor_widget_content_pieces_other( $query ) {
			// Bail if we're not doing a preview or we're in the admin and we have posts
			if ( ! $this->doing_preview || ( is_admin() && $query->have_posts() ) )
				return;
		?>
			<p class="conductor-qb-preview-no-results conductor-qb-preview-no-posts">
				<strong><?php _e( 'This query returned no results. Please adjust your query and try again.', 'conductor-query-builder' ); ?></strong>
			</p>
		<?php
		}


		/********
		 * AJAX *
		 ********/

		/**
		 * This function handles the AJAX request for creating a query.
		 */
		public function wp_ajax_conductor_query_builder_create_query() {
			// Generic error message
			$error = __( 'There was an error creating the query. Please try again later.', 'conductor' );

			// Status flags
			$status = array(
				'ID' => 0
			);

			// Check AJAX referrer
			if ( ! check_ajax_referer( sanitize_text_field( $_POST['nonce_action'] ), 'nonce', false ) ) {
				$status['error'] = $error;
				wp_send_json_error( $status );
			}

			// Return an error if the current user can't edit posts or the current user doesn't have the Conductor capability
			if ( ! current_user_can( 'edit_posts' ) || ! current_user_can( Conductor::$capability ) ) {
				$status['error'] = __( 'You do not have sufficient permissions to create a query on this site.', 'conductor' );
				wp_send_json_error( $status );
			}

			// Grab the post title
			$post_title = ( isset( $_POST['conductor_query_builder_shortcode_create_title' ] ) ) ? sanitize_text_field( $_POST['conductor_query_builder_shortcode_create_title' ] ) : '';

			// Return an error if we don't have a post title
			if ( empty( $post_title ) ) {
				$status['error'] = __( 'A title for this query was not specified. Please enter a title and try again.', 'conductor' );
				wp_send_json_error( $status );
			}

			// Insert the post
			$post_id = wp_insert_post( array(
				'post_title' => $post_title,
				'post_type' => $this->post_type_name,
				'post_status' => 'publish'
			) );

			// If the post was inserted successfully
			if ( $post_id ) {
				$status['ID'] = $post_id;
				$status['title'] = $post_title;
				wp_send_json_success( $status );
			}
			// Otherwise there was an error
			else {
				$status['error'] = $error;
				wp_send_json_error( $status );
			}
		}

		/**
		 * This function handles the AJAX request for previewing a query.
		 */
		// TODO: Likely need to fix PHP notices/warnings here when clause groups do not have complete data but the preview AJAX request was executed
		public function wp_ajax_conductor_query_builder_preview_query() {
			// Generic error message
			$error = __( 'There was an error previewing the query. Please try again later.', 'conductor' );

			// Grab the post ID
			$post_id = ( isset( $_POST['ID'] ) ) ? ( int ) $_POST['ID'] : false;

			// Grab the post type
			$post_type = ( $post_id ) ? get_post_type( $post_id ) : false;

			// Status flags
			$status = array();

			// Check post ID, post type, and AJAX referrer
			if ( ! $post_id || $post_type !== $this->post_type_name || ! check_ajax_referer( sanitize_text_field( $_POST['nonce_action'] ), 'nonce', false ) ) {
				$status['error'] = $error;
				wp_send_json_error( $status );
			}

			// Return an error if the current user can't edit this query
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				$status['error'] = __( 'You do not have sufficient permissions to preview a query on this site.', 'conductor' );
				wp_send_json_error( $status );
			}

			// Grab the query builder mode
			$query_builder_mode = $this->get_query_builder_mode();

			// Grab the Conductor Query Builder data (default to an empty array)
			$conductor_query_builder_data = $this->get_query_builder_data( $_POST, $query_builder_mode );

			do_action( 'conductor_query_builder_preview_query_before', $post_id, $post_type, $query_builder_mode, $conductor_query_builder_data, $this );

			// Grab the simple query builder data
			$simple_conductor_query_builder_data = $this->get_simple_query_builder_data( ( $query_builder_mode === 'simple' ) ? $_POST : $conductor_query_builder_data, $query_builder_mode );


			// Setup the query builder mode preview data reference
			$this->preview_data['query_builder_mode'] = $query_builder_mode;

			// Setup the Conductor Widget instance preview data reference
			$this->preview_data['conductor_widget_instance'] = $simple_conductor_query_builder_data;


			// Grab the clause types
			$clause_types = $this->get_clause_types();

			// Loop through the clause types
			foreach ( $clause_types as $clause_type ) {
				/*
				 * Setup the preview data references
				 */

				// Post Meta
				$this->preview_data['post_meta'][$clause_type] = $this->get_clause_type_post_meta( $post_id, $clause_type, $conductor_query_builder_data );

				// Query Arguments (call $this->get_clause_type_query_args() twice; once for the database value and once for the query value)
				$this->preview_data['query_args'] += $this->get_clause_type_query_args( $post_id, $clause_type, $this->get_clause_type_query_args( $post_id, $clause_type, $this->preview_data['post_meta'][$clause_type] ), 'query', true );
			}

			// Start output buffering
			ob_start();

			// Preview this Conductor Query
			$this->render_preview( $post_id );

			// Grab the output from the buffer
			$preview = ob_get_clean();

			do_action( 'conductor_query_builder_preview_query_after', $post_id, $post_type, $query_builder_mode, $conductor_query_builder_data, $preview, $this );

			// If the post was inserted successfully
			if ( $post_id ) {
				$status['preview'] = $preview;
				wp_send_json_success( $status );
			}
		}


		/**********************
		 * Internal Functions *
		 **********************/

		/**
		 * This function renders the Notes meta box.
		 */
		public function meta_box_notes( $post ) {
		?>
			<label class="screen-reader-text" for="excerpt"><?php _e( 'Notes', 'conductor-query-builder' ) ?></label>
			<textarea rows="1" cols="40" name="excerpt" id="excerpt"><?php echo get_post_field( 'post_excerpt', $post ); ?></textarea>
			<p><?php _e( 'Add notes to help describe this query (for internal use only).', 'conductor-query-builder' ); ?></p>
		<?php
		}

		/**
		 * This function renders the Shortcode meta box.
		 */
		public function meta_box_shortcode( $post ) {
		?>
			<label class="screen-reader-text" for="excerpt"><?php _e( 'Shortcode', 'conductor-query-builder' ) ?></label>
			<p><?php _e( 'Display this query anywhere in your content by using the following shortcodes:', 'conductor-query-builder' ); ?></p>
			<p><code>[<?php echo $this->shortcode; ?> id="<?php echo esc_attr( get_post_field( 'ID', $post ) ); ?>"]</code> <span class="conductor-qb-clipboard" title="<?php _e( 'Copy to clipboard', 'conductor-query-builder' ); ?>" data-clipboard-text="<?php echo esc_attr( '[' . $this->shortcode . ' id="' . get_post_field( 'ID', $post ) . '"]' ); ?>"><span class="dashicons dashicons-clipboard"></span></span></p>
			<p><code>[<?php echo $this->shortcode; ?> id="<?php echo esc_attr( get_post_field( 'ID', $post ) ); ?>" title="<?php echo esc_attr( get_the_title( $post ) ); ?>"]</code> <span class="conductor-qb-clipboard" title="<?php _e( 'Copy to clipboard', 'conductor-query-builder' ); ?>" data-clipboard-text="<?php echo esc_attr( '[' . $this->shortcode . ' id="' . get_post_field( 'ID', $post ) . '" title="' . esc_attr( get_the_title( $post ) ) . '"]' ); ?>"><span class="dashicons dashicons-clipboard"></span></span></p>
			<p><span class="description"><?php _e( 'Hint: You can specify a custom title in the <code>title</code> attribute.', 'conductor-query-builder' ); ?></span></p>
		<?php
		}

		/**
		 * This function renders the Function meta box.
		 */
		public function meta_box_function( $post ) {
		?>
			<label class="screen-reader-text" for="excerpt"><?php _e( 'Function', 'conductor-query-builder' ) ?></label>
			<p><?php _e( 'Display this query anywhere in your theme by using the following functions:', 'conductor-query-builder' ); ?></p>
			<p><textarea class="conductor-qb-function" cols="32" rows="3" readonly="readonly" onclick="this.focus(); this.select();">&lt;?php if ( function_exists( '<?php echo $this->function; ?>' ) ) <?php echo $this->function; ?>( <?php echo get_post_field( 'ID', $post ); ?> ); ?&gt;</textarea> <span class="conductor-qb-clipboard" title="<?php _e( 'Copy to clipboard', 'conductor-query-builder' ); ?>" data-clipboard-text="<?php echo esc_attr( '&lt;?php if ( function_exists( \'' .  $this->function . '\' ) ) ' . $this->function . '( ' . get_post_field( 'ID', $post ) . ' ); ?&gt;' ); ?>"><span class="dashicons dashicons-clipboard"></span></span></p>
			<p><textarea class="conductor-qb-function" cols="32" rows="5" readonly="readonly" onclick="this.focus(); this.select();">&lt;?php if ( function_exists( '<?php echo $this->function; ?>' ) ) <?php echo $this->function; ?>( <?php echo get_post_field( 'ID', $post ); ?>, '<?php echo get_the_title( $post ); ?>' ); ?&gt;</textarea> <span class="conductor-qb-clipboard" title="<?php _e( 'Copy to clipboard', 'conductor-query-builder' ); ?>" data-clipboard-text="<?php echo esc_attr( '&lt;?php if ( function_exists( \'' .  $this->function . '\' ) ) ' . $this->function . '( ' . get_post_field( 'ID', $post ) . ', \'' . get_the_title( $post ) . '\' ); ?&gt;' ); ?>"><span class="dashicons dashicons-clipboard"></span></span></p>
			<p><span class="description"><?php _e( 'Hint: You can specify a custom title in the <code>title</code> (second) argument.', 'conductor-query-builder' ); ?></span></p>
		<?php
		}

		/**
		 * This function renders the Preview meta box.
		 */
		public function meta_box_preview( $post ) {
		?>
			<label class="screen-reader-text" for="excerpt"><?php _e( 'Front-End Preview', 'conductor-query-builder' ) ?></label>
			<p><?php _e( 'The following is a preview of what this query will output on the front-end. Note: This preview will only show the first result of the query and is not styled to match the front-end (CSS).', 'conductor-query-builder' ); ?></p>
			<hr />

			<div id="conductor-query-builder-preview" class="conductor-query-builder-preview">
				<?php
					// Grab the post ID
					$post_id = get_post_field( 'ID', $post );

					// Grab the Conductor Widget instance
					$conductor_widget = Conduct_Widget();

					// Grab the query builder mode
					$query_builder_mode = $this->get_query_builder_mode();

					// Setup the query builder mode preview data reference
					$this->preview_data['query_builder_mode'] = $query_builder_mode;

					// Setup the post meta
					$this->preview_data['post_meta'] = $this->get_post_meta( $post_id );

					// Setup the Conductor Widget instance POST data (POST data is required for get_simple_query_builder_data())
					$_POST['widget-' . $conductor_widget->id_base] = array();
					$_POST['widget-' . $conductor_widget->id_base][0] = $this->get_conductor_widget_instance( $post_id );
					$_POST['widget-' . $conductor_widget->id_base][0]['output'] = wp_json_encode( $_POST['widget-' . $conductor_widget->id_base][0]['output'] );

					// Grab the simple query builder data
					$simple_conductor_query_builder_data = $this->get_simple_query_builder_data( $_POST );

					// Setup the Conductor Widget instance preview data reference
					$this->preview_data['conductor_widget_instance'] = $simple_conductor_query_builder_data;

					// Setup the query arguments
					$this->preview_data['query_args'] = $this->get_query_args( $post_id );

					// Preview this Conductor Query
					$this->render_preview( $post_id );

					// Remove the POST data reference
					unset( $_POST['widget-' . $conductor_widget->id_base] );
				?>
			</div>
		<?php
		}

		/**
		 * This function renders the Query Builder meta box.
		 */
		public function meta_box_query_builder( $post, $args, $conductor_widget_instance = array() ) {
			// Add an nonce field
			wp_nonce_field( 'conductor_query_builder_meta_box', 'conductor_qb_nonce' );

			// Grab the post ID
			$post_id = get_post_field( 'ID', $post );

			// Grab the post meta
			$post_meta = $this->get_post_meta( $post_id );

			// Grab the Conductor Widget instance
			$conductor_widget = Conduct_Widget();

			// Grab the Conductor Widget instance data
			$conductor_widget_instance = ( empty( $conductor_widget_instance ) ) ? $this->get_conductor_widget_instance( $post_id ) : $conductor_widget_instance;
		?>
			<div id="conductor-qb-query-builder-meta-box-tabs-wrapper" class="conductor-qb-tabs-wrapper conductor-qb-query-builder-meta-box-tabs-wrapper conductor-qb-cf">
				<h2 class="nav-tab-wrapper current conductor-qb-tabs conductor-qb-query-builder-meta-box-tabs conductor-qb-cf">
					<?php do_action( 'conductor_query_builder_query_builder_tab_before', $post, $post_meta, $this, $conductor_widget_instance, $conductor_widget ); ?>
					<a class="nav-tab nav-tab-active" href="#conductor-qb-meta-box-query-builder-tab-content" data-type="conductor-qb-query-builder"><?php _e( 'Query Builder', 'conductor-query-builder' ); ?></a>
					<?php do_action( 'conductor_query_builder_query_builder_tab_after', $post, $post_meta, $this, $conductor_widget_instance, $conductor_widget ); ?>

					<?php do_action( 'conductor_query_builder_output_tab_before', $post, $post_meta, $this, $conductor_widget_instance, $conductor_widget ); ?>
					<a class="nav-tab" href="#conductor-qb-meta-box-output-tab-content" data-type="conductor-qb-query-builder"><?php _e( 'Output', 'conductor-query-builder' ); ?></a>
					<?php do_action( 'conductor_query_builder_output_tab_after', $post, $post_meta, $this, $conductor_widget_instance, $conductor_widget ); ?>

					<?php do_action( 'conductor_query_builder_advanced_tab_before', $post, $post_meta, $this, $conductor_widget_instance, $conductor_widget ); ?>
					<a class="nav-tab" href="#conductor-qb-meta-box-advanced-tab-content" data-type="conductor-qb-query-builder"><?php _e( 'Advanced', 'conductor-query-builder' ); ?></a>
					<?php do_action( 'conductor_query_builder_advanced_tab_after', $post, $post_meta, $this, $conductor_widget_instance, $conductor_widget ); ?>
				</h2>
			</div>

			<div id="conductor-qb-query-builder-meta-box-tab-content-wrapper" class="conductor-qb-tab-content-wrapper conductor-qb-query-builder-meta-box-tab-content-wrapper conductor-qb-<?php echo esc_attr( $this->get_query_builder_mode() ); ?>-mode loading conductor-qb-loading spinner conductor-qb-spinner is-active conductor-qb-spinner-is-active">
				<?php // The Conductor Widget relies on the #widgets-right element being present ?>
				<div id="widgets-right" class="conductor-qb-cf">
					<div id="conductor-query-builder-conductor-widget" class="conductor-qb-widget widget conductor-qb-cf">
						<?php do_action( 'conductor_query_builder_query_builder_tab_content_before', $post, $post_meta, $this, $conductor_widget_instance, $conductor_widget ); ?>

						<?php
							/*
							 * Query Builder Tab Content
							 */
							Conductor_Query_Builder_Admin_Views::meta_box_query_builder_tab_content( $post, $post_meta, $this, $conductor_widget_instance, $conductor_widget );
						?>

						<?php do_action( 'conductor_query_builder_query_builder_tab_content_after', $post, $post_meta, $this, $conductor_widget_instance, $conductor_widget ); ?>


						<?php do_action( 'conductor_query_builder_output_tab_content_before', $post, $post_meta, $this, $conductor_widget_instance, $conductor_widget ); ?>

						<?php
							/*
							 * Output Tab Content
							 */
							Conductor_Query_Builder_Admin_Views::meta_box_output_tab_content( $post, $post_meta, $this, $conductor_widget_instance, $conductor_widget );
						?>

						<?php do_action( 'conductor_query_builder_output_tab_content_after', $post, $post_meta, $this, $conductor_widget_instance, $conductor_widget ); ?>


						<?php do_action( 'conductor_query_builder_advanced_tab_content_before', $post, $post_meta, $this, $conductor_widget_instance, $conductor_widget ); ?>

						<?php
							/*
							 * Advanced Tab Content
							 */
							Conductor_Query_Builder_Admin_Views::meta_box_advanced_tab_content( $post, $post_meta, $this, $conductor_widget_instance, $conductor_widget );
						?>

						<?php do_action( 'conductor_query_builder_advanced_tab_content_after', $post, $post_meta, $this, $conductor_widget_instance, $conductor_widget ); ?>
					</div>
				</div>
			</div>
		<?php
		}

		/**
		 * This function converts simple query builder data to a single array.
		 */
		public function convert_simple_query_builder_data_to_single_array( $data, $stripslashes_deep = true ) {
			$simple_conductor_query_builder_data = array();

			// Loop through data
			foreach ( $data as $value ) {
				// Flag to determine if the value is a multidimensional array
				$is_multidimensional_array = false;

				// Loop through the value
				foreach ( $value as $sub_value ) {
					// If the sub-value is an array
					if ( is_array( $sub_value ) ) {
						// Set the multidimensional array flag
						$is_multidimensional_array = true;

						// Break from the loop
						break;
					}
				}

				// If this value is a multidimensional array
				if ( $is_multidimensional_array )
					// Replace this value recursively into simple query builder data (using array_replace_recursive() because it will overwrite existing values and add new values, instead of creating new arrays for nested data like array_merge_recursive() does)
					$simple_conductor_query_builder_data = array_replace_recursive( $simple_conductor_query_builder_data, $value );
				// Otherwise this value isn't a multidimensional array
				else
					// Merge this value into simple query builder data
					$simple_conductor_query_builder_data += $value;
			}

			// Strip slashes (deep)
			$simple_conductor_query_builder_data = ( $stripslashes_deep ) ? stripslashes_deep( $simple_conductor_query_builder_data ) : $simple_conductor_query_builder_data;

			return $simple_conductor_query_builder_data;
		}

		/**
		 * This function converts simple query builder data to advanced query builder data.
		 */
		public function convert_simple_query_builder_data_to_advanced_data( $data, $convert_to_single_array = false ) {
			// Grab the converted simple query builder data if necessary
			$simple_conductor_query_builder_data = ( $convert_to_single_array ) ? $this->convert_simple_query_builder_data_to_single_array( $data ) : $data;

			// Advanced query builder data
			$advanced_query_builder_data = array();

			// Grab the clause types
			$clause_types = $this->get_clause_types();

			// Grab the field types
			$field_types = $this->get_field_types();

			// If this is a feature many query
			$is_feature_many = ( isset( $simple_conductor_query_builder_data['feature_many'] ) && ! empty( $simple_conductor_query_builder_data['feature_many'] ) );

			// Loop through the clause types
			foreach ( $clause_types as $clause_type ) {
				// Switch based on clause type
				switch ( $clause_type ) {
					// FROM
					case 'from':
						// If we have FROM parameters
						if ( ( $is_feature_many && isset( $simple_conductor_query_builder_data['post_type'] ) && ! empty( $simple_conductor_query_builder_data['post_type'] ) ) || ( isset( $simple_conductor_query_builder_data['post_id'] ) && ! empty( $simple_conductor_query_builder_data['post_id'] ) ) ) {
							// Create the clause group data
							$advanced_query_builder_data[$clause_type] = array(
								// Clause group
								0 => array(
									// Sub-clause group
									0 => array_fill_keys( $field_types, '' ) // Empty strings
								)
							);

							// Many
							if ( $is_feature_many )
								// Clause group parameters
								$advanced_query_builder_data[$clause_type][0][0]['parameters'] = array( $simple_conductor_query_builder_data['post_type'] );
							// Single
							else
								// Clause group parameters
								$advanced_query_builder_data[$clause_type][0][0]['parameters'] = array( get_post_type( $simple_conductor_query_builder_data['post_id'] ) );
						}

					break;

					// WHERE
					case 'where':
						// If we have WHERE parameters
						if ( ( $is_feature_many && ( ( isset( $simple_conductor_query_builder_data['post__in'] ) && ! empty( $simple_conductor_query_builder_data['post__in'] ) ) || ( isset( $simple_conductor_query_builder_data['post__not_in'] ) && ! empty( $simple_conductor_query_builder_data['post__not_in'] ) ) ) ) || ( ! $is_feature_many && isset( $simple_conductor_query_builder_data['post_id'] ) && ! empty( $simple_conductor_query_builder_data['post_id'] ) ) ) {
							// Create the clause group data
							$advanced_query_builder_data[$clause_type] = array(
								// Clause group
								0 => array(
									// Sub-clause group
									0 => array_fill_keys( $field_types, '' ) // Empty strings
								)
							);

							// Many
							if ( $is_feature_many ) {
								// Post In
								if ( isset( $simple_conductor_query_builder_data['post__in'] ) && ! empty( $simple_conductor_query_builder_data['post__in'] ) ) {
									// Clause group parameters
									$advanced_query_builder_data[$clause_type][0][0]['parameters'] = 'p';

									// Clause group operators
									$advanced_query_builder_data[$clause_type][0][0]['operators'] = 'IN';

									// Clause group values
									preg_match_all( '/\d+(?:,\d+)*/', $simple_conductor_query_builder_data['post__in'], $advanced_query_builder_data[$clause_type][0][0]['values'] );
									$advanced_query_builder_data[$clause_type][0][0]['values'] = ( isset( $advanced_query_builder_data[$clause_type][0][0]['values'][0] ) ) ? $advanced_query_builder_data[$clause_type][0][0]['values'][0] : $simple_conductor_query_builder_data['post__in'];
									$advanced_query_builder_data[$clause_type][0][0]['values'] = ( ! is_array( $advanced_query_builder_data[$clause_type][0][0]['values'] ) ) ? explode( ',', $advanced_query_builder_data[$clause_type][0][0]['values'] ) : $advanced_query_builder_data[$clause_type][0][0]['values'];
									$advanced_query_builder_data[$clause_type][0][0]['values'] = ( is_array( $advanced_query_builder_data[$clause_type][0][0]['values'] ) && count( $advanced_query_builder_data[$clause_type][0][0]['values'] ) === 1 && strpos( $advanced_query_builder_data[$clause_type][0][0]['values'][0], ',' ) !== false ) ? explode( ',', $advanced_query_builder_data[$clause_type][0][0]['values'][0] ) : $advanced_query_builder_data[$clause_type][0][0]['values'];
								}

								// Post Not In
								if ( isset( $simple_conductor_query_builder_data['post__not_in'] ) && ! empty( $simple_conductor_query_builder_data['post__not_in'] ) ) {
									// Sub-clause group ID
									$sub_clause_group_id = ( isset( $simple_conductor_query_builder_data['post__in'] ) && ! empty( $simple_conductor_query_builder_data['post__in'] ) ) ? 1 : 0;

									// If we have a new sub-clause group ID
									if ( ! isset( $advanced_query_builder_data[$clause_type][0][$sub_clause_group_id] ) )
										// Create the second sub-clause group
										$advanced_query_builder_data[$clause_type][0][$sub_clause_group_id] = array_fill_keys( $field_types, '' ); // Empty strings

									// Clause group parameters
									$advanced_query_builder_data[$clause_type][0][$sub_clause_group_id]['parameters'] = 'p';

									// Clause group operators
									$advanced_query_builder_data[$clause_type][0][$sub_clause_group_id]['operators'] = 'NOT IN';

									// Clause group values
									preg_match_all( '/\d+(?:,\d+)*/', $simple_conductor_query_builder_data['post__not_in'], $advanced_query_builder_data[$clause_type][0][$sub_clause_group_id]['values'] );
									$advanced_query_builder_data[$clause_type][0][$sub_clause_group_id]['values'] = ( isset( $advanced_query_builder_data[$clause_type][0][$sub_clause_group_id]['values'][0] ) ) ? $advanced_query_builder_data[$clause_type][0][$sub_clause_group_id]['values'][0] : $simple_conductor_query_builder_data['post__not_in'];
									$advanced_query_builder_data[$clause_type][0][$sub_clause_group_id]['values'] = ( ! is_array( $advanced_query_builder_data[$clause_type][0][$sub_clause_group_id]['values'] ) ) ? explode( ',', $advanced_query_builder_data[$clause_type][0][$sub_clause_group_id]['values'] ) : $advanced_query_builder_data[$clause_type][0][$sub_clause_group_id]['values'];
									$advanced_query_builder_data[$clause_type][0][$sub_clause_group_id]['values'] = ( is_array( $advanced_query_builder_data[$clause_type][0][$sub_clause_group_id]['values'] ) && count( $advanced_query_builder_data[$clause_type][0][$sub_clause_group_id]['values'] ) === 1 && strpos( $advanced_query_builder_data[$clause_type][0][$sub_clause_group_id]['values'][0], ',' ) !== false ) ? explode( ',', $advanced_query_builder_data[$clause_type][0][$sub_clause_group_id]['values'][0] ) : $advanced_query_builder_data[$clause_type][0][$sub_clause_group_id]['values'];
								}
							}
							// Single
							else {
								// Clause group parameters
								$advanced_query_builder_data[$clause_type][0][0]['parameters'] = 'p';

								// Clause group operators
								$advanced_query_builder_data[$clause_type][0][0]['operators'] = 'IS';

								// Clause group values
								$advanced_query_builder_data[$clause_type][0][0]['values'] = array( $simple_conductor_query_builder_data['post_id'] );
							}
						}
					break;

					// WHERE Meta (Custom Field)
					case 'meta_query':
						// Do nothing
					break;

					// WHERE Taxonomy
					case 'tax_query':
						// If we have WHERE Taxonomy parameters
						if ( $is_feature_many && isset( $simple_conductor_query_builder_data['cat'] ) && ! empty( $simple_conductor_query_builder_data['cat'] ) ) {
							// Create the clause group data
							$advanced_query_builder_data[$clause_type] = array(
								// Clause group
								0 => array(
									// Sub-clause group
									0 => array_fill_keys( $field_types, '' ) // Empty strings
								)
							);

							// Clause group parameters
							$advanced_query_builder_data[$clause_type][0][0]['parameters'] = 'cat';

							// Clause group operators
							$advanced_query_builder_data[$clause_type][0][0]['operators'] = 'IS';

							// Clause group values
							$advanced_query_builder_data[$clause_type][0][0]['values'] = array( $simple_conductor_query_builder_data['cat'] );
						}
					break;

					// ORDER BY
					case 'order_by':
						// If we have ORDER BY parameters
						if ( $is_feature_many && isset( $simple_conductor_query_builder_data['orderby'] ) && ! empty( $simple_conductor_query_builder_data['orderby'] ) && isset( $simple_conductor_query_builder_data['order'] ) && ! empty( $simple_conductor_query_builder_data['order'] ) ) {
							// Create the clause group data
							$advanced_query_builder_data[$clause_type] = array(
								// Clause group
								0 => array(
									// Sub-clause group
									0 => array_fill_keys( $field_types, '' ) // Empty strings
								)
							);

							// Clause group parameters
							$advanced_query_builder_data[$clause_type][0][0]['parameters'] = $simple_conductor_query_builder_data['orderby'];

							// Clause group operators
							$advanced_query_builder_data[$clause_type][0][0]['operators'] = $simple_conductor_query_builder_data['order'];
						}
					break;

					// LIMIT
					case 'limit':
						// If we have LIMIT parameters
						if ( $is_feature_many && ( ( isset( $simple_conductor_query_builder_data['posts_per_page'] ) && ! empty( $simple_conductor_query_builder_data['posts_per_page'] ) ) || ( isset( $simple_conductor_query_builder_data['offset'] ) && ! empty( $simple_conductor_query_builder_data['offset'] ) ) ) ) {
							// Create the clause group data
							$advanced_query_builder_data[$clause_type] = array(
								// Clause group
								0 => array(
									// Sub-clause group
									0 => array_fill_keys( $field_types, '' ) // Empty strings
								)
							);

							// If we have a posts per page value
							if ( isset( $simple_conductor_query_builder_data['posts_per_page'] ) && ! empty( $simple_conductor_query_builder_data['posts_per_page'] ) ) {
								// Clause group parameters
								$advanced_query_builder_data[$clause_type][0][0]['parameters'] = 'posts_per_page';

								// Clause group operators
								$advanced_query_builder_data[$clause_type][0][0]['operators'] = 'IS';

								// Clause group values
								$advanced_query_builder_data[$clause_type][0][0]['values'] = array( $simple_conductor_query_builder_data['posts_per_page'] );
							}

							// If we have an offset value
							if ( isset( $simple_conductor_query_builder_data['offset'] ) && ! empty( $simple_conductor_query_builder_data['offset'] ) && ( $simple_conductor_query_builder_data['offset'] - 1 ) !== 0 ) {
								// Sub-clause group ID
								$sub_clause_group_id = ( isset( $simple_conductor_query_builder_data['posts_per_page'] ) && ! empty( $simple_conductor_query_builder_data['posts_per_page'] ) ) ? 1 : 0;

								// If we have a new sub-clause group ID
								if ( ! isset( $advanced_query_builder_data[$clause_type][0][$sub_clause_group_id] ) )
									// Create the second sub-clause group
									$advanced_query_builder_data[$clause_type][0][$sub_clause_group_id] = array_fill_keys( $field_types, '' ); // Empty strings

								// Clause group parameters
								$advanced_query_builder_data[$clause_type][0][$sub_clause_group_id]['parameters'] = 'offset';

								// Clause group operators
								$advanced_query_builder_data[$clause_type][0][$sub_clause_group_id]['operators'] = 'IS';

								// Clause group values
								$advanced_query_builder_data[$clause_type][0][$sub_clause_group_id]['values'] = array( ( $simple_conductor_query_builder_data['offset'] - 1) );
							}
						}
					break;
				}
			}

			return apply_filters( 'conductor_query_builder_advanced_data_from_simple_data', $advanced_query_builder_data, $simple_conductor_query_builder_data, $clause_types, $field_types, $is_feature_many, $this );
		}

		/**
		 * This function converts advanced query builder data to simple query builder data.
		 */
		public function convert_advanced_query_builder_data_to_simple_data( $data, $conductor_widget_data = array() ) {
			// Simple query builder data
			$simple_conductor_query_builder_data = array();

			// Grab the query builder mode
			$query_builder_mode = $this->get_query_builder_mode();

			// Grab the clause types
			$clause_types = $this->get_clause_types();

			// TODO: Adjust code formatting
			// Loop through the clause types
			foreach ( $clause_types as $clause_type ) {
				// If we have data for this clause type
				if ( isset( $data[$clause_type] ) && ! empty( $data[$clause_type] ) ) {
					// Loop through clause groups
					foreach ( $data[$clause_type] as $clause_group_id => $clause_group_data ) {
						// Cast the clause group ID to an integer
						$clause_group_id = ( int ) $clause_group_id;

						// If we have a valid clause group ID
						if ( $clause_group_id >= 0 ) {
							// Loop through the sub-clause groups
							foreach ( $clause_group_data as $sub_clause_group_id => $sub_clause_group_data ) {
								// Cast the sub-clause group ID to an integer
								$sub_clause_group_id = ( int ) $sub_clause_group_id;

								// If we have a valid sub-clause group ID
								if ( $sub_clause_group_id >= 0 ) {
									// Switch based on clause type
									switch ( $clause_type ) {
										// FROM
										case 'from':
											// If we have parameters
											if ( isset( $sub_clause_group_data['parameters'] ) && ! empty( $sub_clause_group_data['parameters'] ) ) {
												// Post Type
												$simple_conductor_query_builder_data['post_type'] = ( is_array( $sub_clause_group_data['parameters'] ) ) ? end( $sub_clause_group_data['parameters'] ) : $sub_clause_group_data['parameters'];

												// Content type
												$simple_conductor_query_builder_data['content_type'] = $simple_conductor_query_builder_data['post_type'];
											}
										break;

										// WHERE
										case 'where':
											// If we have a post ID set
											if ( isset( $sub_clause_group_data['parameters'] ) && ! empty( $sub_clause_group_data['parameters'] ) && $sub_clause_group_data['parameters'] === 'p' && isset( $sub_clause_group_data['operators'] ) && ! empty( $sub_clause_group_data['operators'] ) && isset( $sub_clause_group_data['values'] ) && ! empty( $sub_clause_group_data['values'] ) ) {
												// Switch based on operator
												switch ( $sub_clause_group_data['operators'] ) {
													// IS
													case 'IS':
														// Post ID
														$simple_conductor_query_builder_data['post_id'] = ( is_array( $sub_clause_group_data['values'] ) ) ? end( $sub_clause_group_data['values'] ) : $sub_clause_group_data['values'];
													break;

													// IN
													case 'IN':
														// post__in
														$simple_conductor_query_builder_data['post__in'] = implode( ',', $sub_clause_group_data['values'] );

														// Unset post__not_in
														unset( $simple_conductor_query_builder_data['post__not_in'] );
													break;

													// NOT IN
													case 'NOT IN':
														// post__not_in
														$simple_conductor_query_builder_data['post__not_in'] = implode( ',', $sub_clause_group_data['values'] );

														// Unset post__in
														unset( $simple_conductor_query_builder_data['post__in'] );
													break;
												}
											}
										break;

										// WHERE Meta (Custom Field)
										case 'meta_query':
											// Do Nothing
										break;

										// WHERE Taxonomy
										case 'tax_query':
											// If we have an category ID set
											if ( isset( $sub_clause_group_data['parameters'] ) && ! empty( $sub_clause_group_data['parameters'] ) && $sub_clause_group_data['parameters'] === 'cat' && isset( $sub_clause_group_data['values'] ) && ! empty( $sub_clause_group_data['values'] ) ) {
												// Category ID
												$simple_conductor_query_builder_data['cat'] = ( is_array( $sub_clause_group_data['values'] ) ) ? end( $sub_clause_group_data['values'] ) : $sub_clause_group_data['values'];
											}
										break;

										// ORDER BY
										case 'order_by':
											// If we have an order by set
											if ( isset( $sub_clause_group_data['parameters'] ) && ! empty( $sub_clause_group_data['parameters'] ) && isset( $sub_clause_group_data['operators'] ) && ! empty( $sub_clause_group_data['operators'] ) ) {
												// Order By
												$simple_conductor_query_builder_data['orderby'] = $sub_clause_group_data['parameters'];

												// Order
												$simple_conductor_query_builder_data['order'] = $sub_clause_group_data['operators'];
											}
										break;

										// LIMIT
										case 'limit':
											// If we have posts per page set
											if ( isset( $sub_clause_group_data['parameters'] ) && ! empty( $sub_clause_group_data['parameters'] ) && $sub_clause_group_data['parameters'] === 'posts_per_page' && isset( $sub_clause_group_data['values'] ) && ! empty( $sub_clause_group_data['values'] ) ) {
												// Posts Per Page
												$simple_conductor_query_builder_data['posts_per_page'] = ( is_array( $sub_clause_group_data['values'] ) ) ? end( $sub_clause_group_data['values'] ) : $sub_clause_group_data['values'];
											}

											// If we have an offset set
											if ( isset( $sub_clause_group_data['parameters'] ) && ! empty( $sub_clause_group_data['parameters'] ) && $sub_clause_group_data['parameters'] === 'offset' && isset( $sub_clause_group_data['values'] ) && ! empty( $sub_clause_group_data['values'] ) ) {
												// Offset
												$simple_conductor_query_builder_data['offset'] = ( is_array( $sub_clause_group_data['values'] ) ) ? ( end( $sub_clause_group_data['values'] ) + 1 ) : ( $sub_clause_group_data['values'] + 1 );
											}

											// If we have a max number of posts set
											if ( isset( $sub_clause_group_data['parameters'] ) && ! empty( $sub_clause_group_data['parameters'] ) && $sub_clause_group_data['parameters'] === 'max_num_posts' && isset( $sub_clause_group_data['values'] ) && ! empty( $sub_clause_group_data['values'] ) ) {
												// Maximum number of posts
												$simple_conductor_query_builder_data['max_num_posts'] = ( is_array( $sub_clause_group_data['values'] ) ) ? end( $sub_clause_group_data['values'] ) : $sub_clause_group_data['values'];
											}
										break;
									}
								}
							}
						}
					}
				}
			}

			// Feature Many
			$simple_conductor_query_builder_data['feature_many'] = ( ! isset( $simple_conductor_query_builder_data['post_id'] ) ) ? 'true' : '';

			// Maximum number of posts
			$simple_conductor_query_builder_data['max_num_posts'] = ( isset( $simple_conductor_query_builder_data['max_num_posts'] ) ) ? $simple_conductor_query_builder_data['max_num_posts'] : ( ( $query_builder_mode === 'advanced' ) ? '' : get_option( 'posts_per_page' ) );


			// If the query builder mode is advanced
			if ( $query_builder_mode === 'advanced' ) {
				// If the Conductor Widget post__in data exists
				if ( isset( $conductor_widget_data['post__in'] ) )
					// Unset the Conductor Widget post__in data
					unset( $conductor_widget_data['post__in'] );

				// If the Conductor Widget post__not_in data exists
				if ( isset( $conductor_widget_data['post__not_in'] ) )
					// Unset the Conductor Widget post__not_in data
					unset( $conductor_widget_data['post__not_in'] );
			}

			// If we have Conductor Widget data
			if ( ! empty( $conductor_widget_data ) )
				// Loop through the Conductor Widget data
				foreach ( $conductor_widget_data as $conductor_widget_data_key => $conductor_widget_data_value )
					// If this data doesn't exist
					if ( ! isset( $simple_conductor_query_builder_data[$conductor_widget_data_key] ) )
						// Add it now
						$simple_conductor_query_builder_data[$conductor_widget_data_key] = $conductor_widget_data_value;

			// Strip slashes (deep)
			$simple_conductor_query_builder_data = stripslashes_deep( $simple_conductor_query_builder_data );

			return apply_filters( 'conductor_query_builder_simple_data_from_advanced_data', $simple_conductor_query_builder_data, $data, $clause_types, $this );
		}

		/**
		 * This function returns the simple query builder data based on the query builder mode.
		 */
		public function get_simple_query_builder_data( $data, $query_builder_mode = 'simple' ) {
			// Grab the Conductor Widget instance
			$conductor_widget = Conduct_Widget();

			// Grab the simple Conductor Query Builder data if we don't already have it
			if ( $query_builder_mode === 'simple' && isset( $data['widget-' . $conductor_widget->id_base] ) )
				$data = $data['widget-' . $conductor_widget->id_base];

			// Simple query builder data
			$simple_conductor_query_builder_data = array();

			// If the query builder mode is set to advanced
			if ( $query_builder_mode === 'advanced' )
				// Convert the advanced query builder data to simple query builder data
				$simple_conductor_query_builder_data = $this->convert_advanced_query_builder_data_to_simple_data( $data, ( isset( $_POST['widget-' . $conductor_widget->id_base] ) ) ? $this->convert_simple_query_builder_data_to_single_array( $_POST['widget-' . $conductor_widget->id_base], false ) : array() );
			// Otherwise if the query builder mode is set to simple
			else if ( $query_builder_mode === 'simple' )
				// Convert the simple query builder data to a single array
				$simple_conductor_query_builder_data = $this->convert_simple_query_builder_data_to_single_array( $data );

			// Sanitize the Conductor Widget instance data
			$simple_conductor_query_builder_data = $conductor_widget->update( $simple_conductor_query_builder_data, $this->get_conductor_widget_instance() );

			return apply_filters( 'conductor_query_builder_simple_query_builder_data', $simple_conductor_query_builder_data, $data, $query_builder_mode, $this );
		}

		/**
		 * This function determines if an action has already been completed. It also checks to make
		 * sure that the current filter does not match the $tag to ensure the action is not currently
		 * running, but rather has been completely executed.
		 */
		public function did_action( $tag ) {
			return ( int ) did_action( $tag ) - ( int ) doing_action( $tag );
		}


		/********************
		 * Helper Functions *
		 ********************/

		/**
		 * This function determines if an action button should be disabled upon rendering.
		 */
		public function is_action_button_disabled( $clause_type, $post_id = false, $post_meta = array() ) {
			global $post;

			// Return value
			$ret = true;

			// Post ID
			$post_id = ( $post_id === -1 ) ? get_post_field( 'ID', $post ) : $post_id;

			// Post meta
			$post_meta = ( empty( $post_meta ) ) ? $this->get_post_meta( $post_id ) : $post_meta;

			// TODO: Future:
			// TODO: Need to check action buttons config here based on current value
			// TODO: ^ For Ninja Forms, we always want to disable the WHERE TAX QUERY button

			// If this clause type has a limit
			if ( isset( $this->clauses[$clause_type]['config']['limit'] ) && $this->clauses[$clause_type]['config']['limit'] > 0 )
				// If we have post meta set for this clause type and we've met the limit
				if ( isset( $post_meta[$clause_type] ) && ! empty( $post_meta[$clause_type] ) && count( $post_meta[$clause_type] ) < $this->clauses[$clause_type]['config']['limit'] )
					// Reset the disabled flag
					$ret = false;

			return $ret;
		}

		/**
		 * This function returns count of Conductor Queries that are published.
		 */
		public function get_query_count() {
			$count = wp_count_posts( $this->post_type_name )->publish;

			return apply_filters( 'conductor_query_builder_query_count', $count, $this );
		}

		/**
		 * This function returns Conductor Queries that are published.
		 */
		public function get_queries() {
			global $wpdb;

			$queries = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT SQL_CALC_FOUND_ROWS p.ID, p.post_title FROM $wpdb->posts AS p WHERE 1=1 AND p.post_type = %s AND p.post_status = 'publish' ORDER BY p.post_title ASC LIMIT 0, %d", $this->post_type_name, $this->get_query_count()
				)
			);

			return apply_filters( 'conductor_query_builder_queries', $queries, $this );
		}

		/**
		 * This function returns the query builder modes.
		 */
		public function get_query_builder_modes() {
			return apply_filters( 'conductor_query_builder_modes', $this->query_builder_modes, $this );
		}

		/**
		 * This function returns the query builder mode from the current user's settings.
		 */
		public function get_query_builder_mode() {
			// Grab the mode (default to simple)
			$mode = get_user_setting( 'conductor-qb-mode', apply_filters( 'conductor_query_builder_default_mode', 'simple', $this ) );

			// Default to simple query builder mode if the current value isn't a valid query builder mode
			$mode = ( in_array( $mode, $this->get_query_builder_modes() ) ) ? $mode : 'simple';

			return apply_filters( 'conductor_query_builder_mode', $mode, $this );
		}

		/**
		 * This function returns an instance for use in Conductor Widgets.
		 */
		public function get_conductor_widget_instance( $post_id = false, $title = '', $instance = array() ) {
			global $post;

			// Post ID
			$post_id = ( $post_id === -1 ) ? get_post_field( 'ID', $post ) : $post_id;

			// Grab the Conductor Widget instance
			$conductor_widget = Conduct_Widget();

			// Grab the Conductor Widget instance data
			$instance = ( empty( $instance ) ) ? get_post_meta( $post_id, $this->meta_key_prefix . $this->conductor_widget_meta_key_suffix, true ) : $instance;

			// Ensure we have an array for empty meta values (as of WordPress 4.6, an empty string is returned when meta does not exist)
			if ( empty( $instance ) && ! is_array( $instance ) )
				$instance = array();

			// If we have a title
			if ( ! empty( $title ) )
				// Set the instance title now
				$instance['title'] = $title;

			// Parse the instance with the defaults
			$instance = wp_parse_args( $instance, $conductor_widget->defaults );


			/*
			 * Conductor Widget sanitization (Conductor_Widget::update()) requires these parameters.
			 */

			$instance['post_type'] = ( isset( $instance['post_type'] ) ) ? $instance['post_type'] : $conductor_widget->defaults['query_args']['post_type']; // Post Type
			$instance['cat'] = ( isset( $instance['cat'] ) ) ? $instance['cat'] : $conductor_widget->defaults['query_args']['cat']; // Category ID
			$instance['orderby'] = ( isset( $instance['orderby'] ) ) ? $instance['orderby'] : $conductor_widget->defaults['query_args']['orderby']; // Order By
			$instance['order'] = ( isset( $instance['order'] ) ) ? $instance['order'] : $conductor_widget->defaults['query_args']['order']; // Order
			$instance['max_num_posts'] = ( $this->current_query_builder_mode !== 'advanced' && isset( $instance['max_num_posts'] ) ) ? $instance['max_num_posts'] : ''; // Maximum Number of Posts
			$instance['offset'] = ( isset( $instance['offset'] ) ) ? $instance['offset'] : $conductor_widget->defaults['query_args']['offset']; // Offset
			$instance['posts_per_page'] = ( isset( $instance['posts_per_page'] ) ) ? $instance['posts_per_page'] : $conductor_widget->defaults['query_args']['posts_per_page']; // Posts Per Page

			return apply_filters( 'conductor_query_builder_conductor_widget_instance', $instance, $post_id, $this );
		}

		/**
		 * This function renders a Conductor query.
		 */
		public function render( $post_id = false, $title = '', $type = 'widget', $query_args = array(), $current_query_builder_mode = false, $current_conductor_widget_instance = array() ) {
			global $post;

			// Post ID
			$post_id = ( $post_id === false ) ? get_post_field( 'ID', $post ) : sanitize_text_field( $post_id );

			// Adjust the Conductor Widget settings
			add_filter( 'conductor_widget_settings', array( $this, 'conductor_widget_settings' ) );

			// Adjust the Conductor query arguments
			add_filter( 'conductor_query_args', array( $this, 'conductor_query_args' ), 10, 4 );

			// Adjust the number of found posts
			add_filter( 'conductor_query_found_posts', array( $this, 'conductor_query_found_posts' ), 10, 3 );

			// Adjust the has pagination flag
			add_filter( 'conductor_query_has_pagination', array( $this, 'conductor_query_has_pagination' ), 10, 2 );

			// Grab the query arguments
			$query_args = ( empty( $query_args ) ) ? $this->get_query_args( $post_id ) : $query_args;

			// If we have query arguments or we're doing a preview
			if ( ! empty( $query_args ) || $this->doing_preview ) {
				// Grab the post
				$the_post = get_post( $post_id );

				// Grab the post type
				$post_type = get_post_type( $the_post );

				// Grab the post status
				$post_status = get_post_status( $the_post );

				// If this is a Conductor Query, this Conductor Query isn't published, and we're on the front-end
				if ( $post_type === $this->post_type_name && $post_status !== 'publish' && ! is_admin() ) {
					// If the current user is logged in and can edit this piece of content
					if ( is_user_logged_in() && current_user_can( 'edit_post', $post_id ) )
						// Output a message to the user
						printf( '<div class="conductor-qb-notice conductor-qb-editor-notice"><p><strong>%1$s</strong> %2$s</p></div>',
							__( 'Please Note:', 'conductor-query-builder' ),
							sprintf( '%1$s <br /><small><em>%2$s</em></small>',
								__( 'The Conductor Query which you have chosen to display is not currently published. Please publish the query and try again.', 'conductor-query-builder' ),
								__( 'This message is only displayed to logged in users with permissions to publish this query. You are seeing this message because you are logged in.', 'conductor-query-builder' )
							)
						);
				}
				// Otherwise, this Conductor Query is published, we're in the admin, or this isn't a Conductor Query
				else {
					// Widget
					$widget = false;

					// If the type is an array
					if ( is_array( $type ) && ! empty( $type ) && isset( $type['type'] ) ) {
						// Switch based on type
						switch ( $type['type'] ) {
							// Widget
							case 'widget':
								// If we have a widget
								if ( isset( $type['widget'] ) )
									// Set the widget
									$widget = $type['widget'];

								// Set the type
								$type = $type['type'];
							break;
						}
					}

					// Add this Conductor Query to the list of rendered queries
					$this->rendered[$type][] = $post_id;

					// Grab the render number for this query
					$number = count( $this->rendered[$type] );

					// Grab the query builder mode for this query and set the global reference
					$this->current_query_builder_mode = ( empty( $current_query_builder_mode ) ) ? $this->get_query_builder_mode_for_query( $post_id ) : $current_query_builder_mode;

					// Grab the Conductor Widget instance
					$conductor_widget = Conduct_Widget();

					// Grab the Conductor Widget instance data for this query and set the global reference
					$this->current_conductor_widget_instance = $this->get_conductor_widget_instance( $post_id, $title, $current_conductor_widget_instance );

					// Set the global query arguments reference
					$this->current_query_args = $query_args;

					// Set the global doing Conductor Query flag
					$this->doing_conductor_query = true;

					// Query builder button element
					$query_builder_button_el = '<a class="post-edit-link button conductor-query-builder-button conductor-query-builder-edit-button conductor-query-builder-edit-query-button conductor-qb-button conductor-qb-edit-button conductor-qb-edit-query-button" href="' . esc_url( get_edit_post_link( $post_id ) ) . '" style="font-size: 1rem; display: inline-block; vertical-align: middle;">' . __( 'Edit Query', 'conductor-query-builder' ) . '</a>';

					// Start output buffering
					ob_start();

						// Display the opening query builder button wrapper element
						$conductor_widget->display_widget_wrapper_el( 'widget_query_builder_edit_button', $this->current_conductor_widget_instance, array(), 'open', array(
							'conductor-query-builder-button-wrap',
							'conductor-query-builder-button-wrapper',
							'conductor-query-builder-edit-button-wrap',
							'conductor-query-builder-edit-button-wrapper',
							'conductor-query-builder-edit-query-button-wrap',
							'conductor-query-builder-edit-query-button-wrapper',
							'conductor-qb-button-wrap',
							'conductor-qb-button-wrapper',
							'conductor-qb-edit-button-wrap',
							'conductor-qb-edit-button-wrapper',
							'conductor-qb-edit-query-button-wrap',
							'conductor-qb-edit-query-button-wrapper'
						) );

					// Grab the opening query builder button wrapper element from the buffer
					$opening_query_builder_button_wrapper_el = ob_get_clean();

					// Start output buffering
					ob_start();

						// Display the closing query builder button wrapper element
						$conductor_widget->display_widget_wrapper_el( 'widget_query_builder_edit_button', $this->current_conductor_widget_instance, array() );

					// Grab the opening query builder button wrapper element from the buffer
					$closing_query_builder_button_wrapper_el = ob_get_clean();


					// Before widget data attributes
					$raw_before_widget_data_attrs = array(
						'data-query-builder-post-id' => $post_id,
						'data-query-builder-type' => $type,
						'data-query-builder-number' => $number,
						'data-query-builder-widget-id' => '',
						'data-query-builder-widget-number' => $number
					);

					// Switch based on type
					switch ( $type ) {
						// Function
						case 'function':
							// Set the widget ID widget data attribute TODO: Future: Convert prefix into a variable?
							$raw_before_widget_data_attrs['data-query-builder-widget-id'] = 'conductor-query-builder-function-' . $number;
						break;

						// Shortcode
						case 'shortcode':
							// Set the widget ID widget data attribute TODO: Future: Convert prefix into a variable?
							$raw_before_widget_data_attrs['data-query-builder-widget-id'] = 'conductor-query-builder-shortcode-' . $number;
						break;

						// Widget
						case 'widget':
							// If we have a widget
							if ( $widget ) {
								// Set the widget ID widget data attribute
								$raw_before_widget_data_attrs['data-query-builder-widget-id'] = $widget->id;

								// Set the widget number before widget data attribute
								$raw_before_widget_data_attrs['data-query-builder-widget-number'] = $widget->number;
							}
						break;
					}

					// Filter before widget data attributes
					$raw_before_widget_data_attrs = apply_filters( 'conductor_query_builder_widget_before_widget_data_attributes', $raw_before_widget_data_attrs, $number, $widget, $post_id, $type, $query_args, $conductor_widget, $this );

					// Prepare the data attributes
					$before_widget_data_attrs = $conductor_widget->prepare_data_attributes( $raw_before_widget_data_attrs );


					// Adjust the Conductor Widget before widget data attributes
					add_filter( 'conductor_widget_before_widget_data_attributes', array( $this, 'conductor_widget_before_widget_data_attributes' ), 10, 5 );

					// Mimic dynamic sidebar parameters
					$dynamic_sidebar_params = apply_filters( 'dynamic_sidebar_params', array(
						array(
							'name' => __( 'Conductor Query Builder Temporary Sidebar', 'conductor-query-builder' ),
							'id' => $this->temporary_sidebar_id,
							'description' => __( 'This widget area is the temporary sidebar used by Conductor Query Builder when rendering a Conductor Query.', 'conductor-query-builder' ),
							'class' => '', // This is almost always empty
							'before_widget' => '<div id="' . esc_attr( sprintf( 'conductor-qb-widget-%1$s-%2$s-%3$s', $post_id, $type, $number ) ) . '" class="widget conductor-qb-widget conductor-query-builder-widget ' . esc_attr( sprintf( 'conductor-qb-widget-%1$s conductor-qb-widget-%1$s-%2$s conductor-qb-widget-%1$s-%2$s-%3$s', $post_id, $type, $number ) ) . ' %s" ' . $before_widget_data_attrs . '>',
							'after_widget' => ( is_user_logged_in() && ! is_admin() && current_user_can( 'edit_post', $post_id ) ) ? $opening_query_builder_button_wrapper_el . $query_builder_button_el . $closing_query_builder_button_wrapper_el . '</div>' : '</div>',
							'before_title' => '<h3 class="widgettitle widget-title conductor-qb-widget-title conductor-query-builder-widget-title ' . esc_attr( sprintf( 'conductor-qb-widget-title-%1$s conductor-qb-widget-title-%1$s-%2$s conductor-qb-widget-title-%1$s-%2$s-%3$s', $post_id, $type, $number ) ) . '">',
							'after_title' => ( is_user_logged_in() && ! is_admin() && current_user_can( 'edit_post', $post_id ) ) ? ' - ' . $query_builder_button_el .'</h3>' : '</h3>',
							'widget_id' => $conductor_widget->id_base . '-' . $number,
							'widget_name' => $conductor_widget->name,
							// Query Builder
							'conductor_query_builder' => array(
								'widget' => $widget,
								'before_widget_data_attributes' => $raw_before_widget_data_attrs
							)
						),
						array(
							'number' => $this->the_widget_number
						)
					) );

					// Remove the Conductor Widget before widget data attributes
					remove_filter( 'conductor_widget_before_widget_data_attributes', array( $this, 'conductor_widget_before_widget_data_attributes' ) );

					// Arguments
					$args = apply_filters( 'conductor_query_builder_the_widget_args', array(
						'before_widget' => $dynamic_sidebar_params[0]['before_widget'],
						'after_widget' => $dynamic_sidebar_params[0]['after_widget'],
						'before_title' => $dynamic_sidebar_params[0]['before_title'],
						'after_title' => $dynamic_sidebar_params[0]['after_title'],
					), $dynamic_sidebar_params, $this->current_query_args, $this->current_query_builder_mode, $this->current_conductor_widget_instance, $number, $conductor_widget, $this );

					do_action( 'conductor_query_builder_render_before', $type, $post_id, $title, $args, $this->current_query_args, $this->current_query_builder_mode, $this->current_conductor_widget_instance, $number, $this );
					do_action( 'conductor_query_builder_render_' . $type . '_before', $post_id, $title, $args, $this->current_query_args, $this->current_query_builder_mode, $this->current_conductor_widget_instance, $number, $this );

					// Conductor Widget
					// TODO: There's a chance sprintf in WordPress logic isn't working properly here for some reason
					the_widget( get_class( $conductor_widget ), $this->current_conductor_widget_instance, $args );

					do_action( 'conductor_query_builder_render_' . $type . '_after', $post_id, $title, $args, $this->current_query_args, $this->current_query_builder_mode, $this->current_conductor_widget_instance, $number, $this );
					do_action( 'conductor_query_builder_render_after', $type, $post_id, $title, $args, $this->current_query_args, $this->current_query_builder_mode, $this->current_conductor_widget_instance, $number, $this );
				}
			}

			// Remove the Conductor Widget settings adjustment
			remove_filter( 'conductor_widget_settings', array( $this, 'conductor_widget_settings' ) );

			// Remove the Conductor query arguments adjustment
			remove_filter( 'conductor_query_args', array( $this, 'conductor_query_args' ) );

			// Remove the Conductor query found posts adjustment
			remove_filter( 'conductor_query_found_posts', array( $this, 'conductor_query_found_posts' ) );

			// Remove the has pagination flag adjustment
			remove_filter( 'conductor_query_has_pagination', array( $this, 'conductor_query_has_pagination' ) );

			// Remove all display hooks
			remove_all_actions( 'conductor_widget_display_content_' . $this->the_widget_number );


			// Reset the global doing Conductor Query flag
			$this->doing_conductor_query = false;

			// Reset the global query arguments reference
			$this->current_query_args = array();

			// Reset the global Conductor Widget instance reference
			$this->current_conductor_widget_instance = array();

			// Reset the global query builder mode reference
			$this->current_query_builder_mode = false;

		}

		/**
		 * This function renders a preview of a Conductor Query.
		 */
		public function render_preview( $post_id = false ) {
			global $post;

			// Post ID
			$post_id = ( $post_id === -1 ) ? get_post_field( 'ID', $post ) : $post_id;

			// Set the doing preview flag
			$this->doing_preview = true;

			// Hook into conductor_query_builder_render_preview_before and conductor_query_builder_render_preview_after
			add_action( 'conductor_query_builder_render_preview_before', array( $this, 'conductor_query_builder_render_preview_before' ) );
			add_action( 'conductor_query_builder_render_preview_after', array( $this, 'conductor_query_builder_render_preview_after' ) );

			// Hook into conductor_widget_content_pieces_other
			add_action( 'conductor_widget_content_pieces_other', array( $this, 'conductor_widget_content_pieces_other' ) );

			// Render this Conductor Query
			$this->render( $post_id, '', 'preview' );

			// Remove hook from conductor_widget_content_pieces_other
			remove_action( 'conductor_widget_content_pieces_other', array( $this, 'conductor_widget_content_pieces_other' ) );

			// Remove hook from conductor_query_builder_render_preview_before and conductor_query_builder_render_preview_after
			remove_action( 'conductor_query_builder_render_preview_before', array( $this, 'conductor_query_builder_render_preview_before' ) );
			remove_action( 'conductor_query_builder_render_preview_after', array( $this, 'conductor_query_builder_render_preview_after' ) );

			// Reset the doing preview flag
			$this->doing_preview = false;
		}

		/**
		 * This function resets the global $post variable using data stored on the class.
		 *
		 * This is necessary because there is no global $wp_query object when viewing a single
		 * post in the admin.
		 */
		public function reset_global_post() {
			global $post;

			// If we have a global $post reference
			if ( $this->global_post ) {
				// Reset/restore the global $post
				$post = $this->global_post;

				// Reset the global $post reference
				$this->global_post = null;
			}
		}

		/**
		 * This function determines if the clause type truly has multiple clause groups.
		 *
		 * This function will take into account "global" query arguments. It will not include
		 * clause groups which contain "global" query arguments in the counting logic.
		 */
		public function clause_type_has_multiple_clause_groups( $clause_type, $allows_multiple_clause_groups, $clause_type_post_meta ) {
			// Bail if this clause type doesn't allow for multiple clause groups or we only have one clause group
			if ( ! $allows_multiple_clause_groups || count( $clause_type_post_meta ) === 1 )
				return false;

			// "Global" sub-clause IDs
			$global_sub_clause_group_ids = array();

			// Loop through clause groups
			foreach ( $clause_type_post_meta as $clause_group_id => $clause_group_data ) {
				// Create the "global" clause array for this clause group
				$global_sub_clause_group_ids[$clause_group_id] = array();

				// Loop through sub-clause groups
				foreach ( $clause_group_data as $sub_clause_group_id => $sub_clause_group_data ) {
					// Grab the parameter(s)
					$parameters = $sub_clause_group_data['parameters'];

					// If this clause type has a query argument specified
					if ( isset( $this->clauses[$clause_type]['query_arg'] ) && ! empty( $this->clauses[$clause_type]['query_arg'] ) ) {
						$global_sub_clause_group_ids[$clause_group_id][] = $sub_clause_group_id;
					}
					// Otherwise if this should be considered a "global" parameter
					else if ( ! is_array( $parameters ) && isset( $this->parameters[$parameters] ) ) {
						// Grab the parameters data
						$parameters_data = $this->get_parameters_data( $parameters, $clause_type );

						// Grab the correct parameter
						$parameter = ( isset( $parameters_data['operators'] ) && ! empty( $parameters_data['operators'] ) && isset( $sub_clause_group_data['operators'] ) && ! empty( $sub_clause_group_data['operators'] ) ) ? array_search( $sub_clause_group_data['operators'], $parameters_data['operators'] ) : false;
						$parameter = ( ! $parameter || is_int( $parameter ) ) ? $parameters : $parameter;

						// If we have a parameter this query arguments considered "global"
						if ( ! empty( $parameter ) )
							$global_sub_clause_group_ids[$clause_group_id][] = $sub_clause_group_id;
					}
				}
			}

			// Remove empty "global" clause groups
			$global_sub_clause_group_ids = array_filter( $global_sub_clause_group_ids );

			// If we have any "global" clause groups
			if ( ! empty( $global_sub_clause_group_ids ) )
				// Loop through "global" clause groups
				foreach ( $global_sub_clause_group_ids as $clause_group_id => $sub_clause_group_ids )
					// If the total number of sub-clause group IDs in this clause group doesn't match the meta value count
					if ( count( $sub_clause_group_ids ) !== count( $clause_type_post_meta[$clause_group_id] ) )
						// Remove this clause group from the "global" clause group IDs
						unset( $global_sub_clause_group_ids[$clause_group_id] );

			// Determine the difference between the total clause groups and the total "global" clause groups
			$clause_group_count_difference = ( count( $clause_type_post_meta ) - count( $global_sub_clause_group_ids ) );

			// If we have no "global" sub-clause group IDs, we have multiple clause groups
			$has_multiple_clause_groups = ( $clause_group_count_difference > 1 );

			return apply_filters( 'conductor_query_builder_clause_type_has_multiple_clause_groups', $has_multiple_clause_groups, $clause_type, $clause_type_post_meta, $global_sub_clause_group_ids, $clause_group_count_difference, $this );
		}
	}

	/**
	 * Create an instance of the Conductor_Query_Builder class.
	 */
	function Conduct_Query_Builder() {
		return Conductor_Query_Builder::instance();
	}

	Conduct_Query_Builder(); // Conduct your content!
}