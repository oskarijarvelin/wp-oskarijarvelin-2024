<?php
/**
 * Twenty Twenty-Four functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Twenty Twenty-Four
 * @since Twenty Twenty-Four 1.0
 */

/**
 * Register block styles.
 */

if ( ! function_exists( 'oj24_block_styles' ) ) :
	/**
	 * Register custom block styles
	 *
	 * @since Twenty Twenty-Four 1.0
	 * @return void
	 */
	function oj24_block_styles() {
		register_block_style(
			'core/details',
			array(
				'name'         => 'arrow-icon-details',
				'label'        => __( 'Arrow icon', 'oj24' ),
				/*
				 * Styles for the custom Arrow icon style of the Details block
				 */
				'inline_style' => '
				.is-style-arrow-icon-details {
					padding-top: var(--wp--preset--spacing--1);
					padding-bottom: var(--wp--preset--spacing--1);
				}

				.is-style-arrow-icon-details summary {
					list-style-type: "\2193\00a0\00a0\00a0";
				}

				.is-style-arrow-icon-details[open]>summary {
					list-style-type: "\2192\00a0\00a0\00a0";
				}',
			)
		);
		register_block_style(
			'core/post-terms',
			array(
				'name'         => 'pill',
				'label'        => __( 'Pill', 'oj24' ),
				/*
				 * Styles variation for post terms
				 * https://github.com/WordPress/gutenberg/issues/24956
				 */
				'inline_style' => '
				.is-style-pill a,
				.is-style-pill span:not([class], [data-rich-text-placeholder]) {
					display: inline-block;
					background-color: var(--wp--preset--color--light-2);
					padding: 0.375rem 0.875rem;
					border-radius: var(--wp--preset--spacing--2);
				}

				.is-style-pill a:hover {
					background-color: var(--wp--preset--color--dark-3);
				}',
			)
		);
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark', 'oj24' ),
				/*
				 * Styles for the custom checkmark list block style
				 * https://github.com/WordPress/gutenberg/issues/51480
				 */
				'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
		register_block_style(
			'core/navigation-link',
			array(
				'name'         => 'arrow-link',
				'label'        => __( 'With arrow', 'oj24' ),
				/*
				 * Styles for the custom arrow nav link block style
				 */
				'inline_style' => '
				.is-style-arrow-link .wp-block-navigation-item__label:after {
					content: "\2197";
					padding-inline-start: 0.25rem;
					vertical-align: middle;
					text-decoration: none;
					display: inline-block;
				}',
			)
		);
	}
endif;

add_action( 'init', 'oj24_block_styles' );

/**
 * Enqueue block stylesheets.
 */

if ( ! function_exists( 'oj24_block_stylesheets' ) ) :
	/**
	 * Enqueue custom block stylesheets
	 *
	 * @since Twenty Twenty-Four 1.0
	 * @return void
	 */
	function oj24_block_stylesheets() {
		/**
		 * The wp_enqueue_block_style() function allows us to enqueue a stylesheet
		 * for a specific block. These will only get loaded when the block is rendered
		 * (both in the editor and on the front end), improving performance
		 * and reducing the amount of data requested by visitors.
		 *
		 * See https://make.wordpress.org/core/2021/12/15/using-multiple-stylesheets-per-block/ for more info.
		 */
		wp_enqueue_block_style(
			'core/button',
			array(
				'handle' => 'oj24-button-style-outline',
				'src'    => get_parent_theme_file_uri( 'assets/css/button-outline.css' ),
				'ver'    => wp_get_theme( get_template() )->get( 'Version' ),
				'path'   => get_parent_theme_file_path( 'assets/css/button-outline.css' ),
			)
		);
	}
endif;

add_action( 'init', 'oj24_block_stylesheets' );

/**
 * Register pattern categories.
 */

if ( ! function_exists( 'oj24_pattern_categories' ) ) :
	/**
	 * Register pattern categories
	 *
	 * @since Twenty Twenty-Four 1.0
	 * @return void
	 */
	function oj24_pattern_categories() {

		register_block_pattern_category(
			'page',
			array(
				'label'       => _x( 'Pages', 'Block pattern category', 'oj24' ),
				'description' => __( 'A collection of full page layouts.', 'oj24' ),
			)
		);
	}
endif;

add_action( 'init', 'oj24_pattern_categories' );



// Register the 'patch' block
function register_patch_block() {
	register_block_type( __DIR__ . '/blocks/patch' );
}
add_action( 'init', 'register_patch_block', 5 );



// Change the permalink structure for the 'fixture' post type
function filter_post_type_permalink($link, $post) {
    if ($post->post_type != 'fixture') {
        return $link;
	}

    if ($cats = get_the_terms($post->ID, 'manufacturer')) {
        $link = str_replace('%manufacturer%', array_pop($cats)->slug, $link);
	}
    return $link;
}
add_filter('post_type_link', 'filter_post_type_permalink', 10, 2);



// Populates a select field with the current post title
function filter_field( array $field ) : array {
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	  $field['choices'] = array('test' => get_the_title( get_the_ID() ));
	}

	return $field;

}
add_filter( "acf/load_field/key=field_66998d0154b5e", 'filter_field', 10, 1 );



// Create Child-fixtures from the 'fixture' post type
add_action( 'save_post', 'create_child_fixtures_from_modes', 20, 2 );
function create_child_fixtures_from_modes( $post_id, $post ) {

	// bail out if this is an autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// bail out if this is not an event item
	if ( 'fixture' !== $post->post_type ) {
		return;
	}

	// check if fixture has modes
	$modes = array();
	if ( have_rows('modes', $post_id) ) :
		while ( have_rows('modes', $post_id) ) : the_row();
			array_push($modes, array('name' => get_sub_field('name'), 'channels' => get_sub_field('channels') ) );
		endwhile;
	endif;

	if ( empty($modes) ) {
		return;
	}

	if ( count($modes) === 1 ) {
		update_field('fixture', get_the_title($post_id), $post_id);
		update_field('mode', $modes[0]['name'], $post_id);
		update_field('channels', $modes[0]['channels'], $post_id);
		return;
	}

	$existing_child_fixtures = get_children( array(
		'post_parent' => $post_id,
		'post_type' => 'fixture',
		'numberposts' => -1,
	) );

	// create child fixtures
	foreach ($modes as $mode) {
		$parent_name = get_the_title($post_id); // parent fixture name
		$parent_manufacturer = wp_get_post_terms( $post->ID, 'manufacturer', array( 'fields' => 'ids' ) );

		// check if child fixture already exists
		$exists = array_filter($existing_child_fixtures, function($child) use ($mode) {
			return $child->post_title === $parent_name . ' - ' . $mode['name'];
		});

		// create child fixture
		if (!$exists) {
			$child_post = array(
				'post_title' => $parent_name . ' - ' . $mode['name'],
				'post_name' => sanitize_title($mode['name']),
				'post_type' => 'fixture',
				'post_status' => 'publish',
				'post_parent' => $post_id,
			);
			$child_post_id = wp_insert_post($child_post);
			if ($child_post_id) {
				wp_set_post_terms( $child_post_id, $parent_manufacturer, 'manufacturer' );
				//update_field('fixture', $parent_name, $child_post_id); TODO: fix this
				update_field('mode', $mode['name'], $child_post_id);
				update_field('channels', $mode['channels'], $child_post_id);
			}
		}

	}

	// TODO: delete child fixtures that are no longer needed
}
