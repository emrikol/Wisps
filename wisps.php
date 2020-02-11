<?php
/**
 * Plugin Name: Wisps
 * Plugin URI: https://github.com/emrikol/wisps
 * Description: Wisps are Gist-like code posts for WordPress
 * Version: 2.0.0
 * Author: Derrick Tennant
 * Author URI: https://derrick.blog/
 * GitHub Plugin URI: https://github.com/emrikol/wisps
 * Text Domain: wisps
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Sets up admin scripts and the code editor.
 *
 * @param string $hook The page hook being ran on.
 */
function wisps_add_code_editor( $hook ) {
	global $post;

	if ( ! $post || 'wisp' !== $post->post_type ) {
		return;
	}

	if ( 'post.php' === $hook || 'post-new.php' === $hook ) {
		if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$wisp_mime = get_post_meta( (int) $_GET['post'], '_wisp_mime', true ) ?? 'text/plain'; // phpcs:ignore Generic.PHP.Syntax.PHPSyntax, WordPress.Security.NonceVerification.Recommended
		}

		wp_enqueue_code_editor( array( 'type' => $wisp_mime ) );
		wp_enqueue_script( 'wisp-code-editor', plugin_dir_url( __FILE__ ) . 'code-editor.js', array( 'jquery' ), filemtime( plugin_dir_path( __FILE__ ) . 'code-editor.js' ), true );
	}
}
add_action( 'admin_enqueue_scripts', 'wisps_add_code_editor' );

/**
 * Registers meta boxes.
 */
function wisps_add_metaboxes() {
	add_meta_box( 'wisp-code', esc_html__( 'Wisp Code', 'wisps' ), 'wisps_metabox_editor', 'wisp', 'advanced', 'high' );
	add_meta_box( 'wisp-mime', esc_html__( 'Mime Type', 'wisps' ), 'wisps_metabox_mime_type', 'wisp', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'wisps_add_metaboxes' );

/**
 * Sets up the metabox for the code editor.
 *
 * @param WP_Post $post Post object.
 */
function wisps_metabox_editor( $post ) {
	$post_id   = $post->ID;
	$wisp_data = base64_decode( get_post_meta( (int) $_GET['post'], '_wisp_data', true ) );
	$wisp_name = get_the_title( $post_id );

	?>
	<fieldset>
		<textarea id="wisp_code_editor" rows="5" name="wisp_data" class="widefat textarea"><?php echo esc_textarea( $wisp_data ); ?></textarea>
	</fieldset>
	<?php
}

/**
 * Sets up the metabox for the mime type picker.
 *
 * @param WP_Post $post Post object.
 */
function wisps_metabox_mime_type( $post ) {
	$post_id   = $post->ID;
	$wisp_mime = get_post_meta( $post_id, 'wisp_mime', true );
	// Available mime types are taken from the wp_enqueue_code_editor function source.
	?>
	<fieldset>
		<select id="wisp_mime" name="wisp_mime">
			<option value="text/plain" <?php selected( $wisp_mime, 'text/plain' ); ?>>text/plain</option>
			<option value="css" <?php selected( $wisp_mime, 'css' ); ?>>css</option>
			<option value="text/css" <?php selected( $wisp_mime, 'text/css' ); ?>>text/css</option>
			<option value="text/x-scss" <?php selected( $wisp_mime, 'text/x-scss' ); ?>>text/x-scss</option>
			<option value="text/x-less" <?php selected( $wisp_mime, 'text/x-less' ); ?>>text/x-less</option>
			<option value="htmlmixed" <?php selected( $wisp_mime, 'htmlmixed' ); ?>>htmlmixed</option>
			<option value="text/html" <?php selected( $wisp_mime, 'text/html' ); ?>>text/html</option>
			<option value="php" <?php selected( $wisp_mime, 'php' ); ?>>php</option>
			<option value="application/x-httpd-php" <?php selected( $wisp_mime, 'application/x-httpd-php' ); ?>>application/x-httpd-php</option>
			<option value="text/x-php" <?php selected( $wisp_mime, 'text/x-php' ); ?>>text/x-php</option>
			<option value="javascript" <?php selected( $wisp_mime, 'javascript' ); ?>>javascript</option>
			<option value="application/ecmascript" <?php selected( $wisp_mime, 'application/ecmascript' ); ?>>application/ecmascript</option>
			<option value="application/json" <?php selected( $wisp_mime, 'application/json' ); ?>>application/json</option>
			<option value="application/javascript" <?php selected( $wisp_mime, 'application/javascript' ); ?>>application/javascript</option>
			<option value="application/ld+json" <?php selected( $wisp_mime, 'application/ld+json' ); ?>>application/ld+json</option>
			<option value="text/typescript" <?php selected( $wisp_mime, 'text/typescript' ); ?>>text/typescript</option>
			<option value="application/typescript" <?php selected( $wisp_mime, 'application/typescript' ); ?>>application/typescript</option>
		</select>
	</fieldset>
	<?php
}

/**
 * Saves code and corresponding metadata.
 *
 * @param int $post_id Post Object ID.
 */
function wisps_save_data( $post_id ) {
	if ( defined( 'DOING_AJAX' ) ) {
		return;
	}

	if ( isset( $_POST['wisp_data'] ) ) {
		$wisp_data = wp_unslash( $_POST['wisp_data'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$wisp_mime = isset( $_POST['wisp_mime'] ) ? sanitize_text_field( wp_unslash( $_POST['wisp_mime'] ) ) : 'text/plain';
		$wisp_name = sanitize_file_name( get_the_title( $post_id ) );

		// Stop Infinite Loop!
		remove_action( 'save_post', 'wisps_save_data' );

		wp_update_post(
			[
				'ID'           => $post_id,
				'post_content' => $wisp_data, // Save in post_content for better searching.
				'post_title'   => $wisp_name,
			]
		);

		update_post_meta( $post_id, '_wisp_mime', $wisp_mime );
		update_post_meta( $post_id, '_wisp_data', base64_encode( $wisp_data ) );

		add_action( 'save_post', 'wisps_save_data' );
	}
}
add_action( 'save_post', 'wisps_save_data' );


/**
 * Filters the wisp content to safely display it for themes that do not have wisp support.
 *
 * @param string $content Post Object Content.
 * @erturn string Post Object Content.
 */
function wisps_safely_display_content( $content ) {
	if ( ! current_theme_supports( 'wisps' ) ) {
		global $post;

		if ( 'wisp' === $post->post_type ) {
			remove_filter( 'the_content', 'wpautop' );
			return '<pre>' . esc_html( $content ) . '</pre>';
		}
	}

	return $content;
}
add_filter( 'the_content', 'wisps_safely_display_content', PHP_INT_MIN, 1 );

/**
 * Adds rewrites to view and download wisps.
 */
function wisps_add_rewrites() {
	add_rewrite_tag( '%wisp_raw%', '([^&]+)' );
	add_rewrite_rule( 'wisp/(.+?)/view/?$', 'index.php?post_type=wisp&post_name=$matches[1]&wisp_raw=view', 'top' );
	add_rewrite_rule( 'wisp/(.+?)/raw/?$', 'index.php?post_type=wisp&post_name=$matches[1]&wisp_raw=view', 'top' );
	add_rewrite_rule( 'wisp/(.+?)/download/?$', 'index.php?post_type=wisp&post_name=$matches[1]&wisp_raw=download', 'top' );
}
add_action( 'init', 'wisps_add_rewrites' );

/**
 * Template redirect to either display the raw text or downloads the file.
 */
function wisps_display_raw_content() {
	global $wp_query;

	if ( 'wisp' === $wp_query->query_vars['post_type'] && isset( $wp_query->query_vars['wisp_raw'] ) ) {
		global $post;

		if (
			( 'publish' !== get_post_status( $post->ID ) && ! current_user_can( 'edit_posts' ) ) ||
			! empty( $post->post_password )
		) {
			wp_die( 'Unauthorized', 401 );
		}

		if ( 'view' === $wp_query->query_vars['wisp_raw'] ) {
			header( 'Content-Type: text/plain' );
		}

		if ( 'download' === $wp_query->query_vars['wisp_raw'] ) {
			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Disposition: attachment; filename=' . sanitize_file_name( $post->post_title ) );
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Content-Length: ' . strlen( $post->post_content ) );
		}

		echo $post->post_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		die();
	}
}
add_action( 'template_redirect', 'wisps_display_raw_content' );

/**
 * Filters the title placeholder.
 *
 * @param string $title The title placeholder.
 * @erturn string The title placeholder.
 */
function wisps_title_placeholder( $title ) {
	$screen = get_current_screen();

	if ( 'wisp' === $screen->post_type ) {
		$title = esc_html__( 'Filename including extension...', 'wisps' );
	}

	return $title;
}
add_filter( 'enter_title_here', 'wisps_title_placeholder' );

/**
 * Renames the excerpt metabox text.  Hacky, but works.
 *
 * @param string $translation The new "translated" text.
 * @param string $original    The original text.
 * @erturn string The new "translated" text.
 */
function wisps_rename_excerpt( $translation, $original ) {
	if ( function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();
		if ( 'wisp' === $screen->post_type ) {
			if ( 'Excerpt' === $original ) {
				return esc_html__( 'Wisp Description', 'wisps' );
			} elseif ( false !== strpos( $original, 'Excerpts are optional hand-crafted summaries of your' ) ) {
				return '';
			}
		}
	}

	return $translation;
}
add_filter( 'gettext', 'wisps_rename_excerpt', 10, 2 );

/**
 * Registers the custom post type.
 */
function wisps_register_cpt() {
	$labels = array(
		'name'                  => esc_html_x( 'Wisps', 'Post Type General Name', 'wisps' ),
		'singular_name'         => esc_html_x( 'Wisp', 'Post Type Singular Name', 'wisps' ),
		'menu_name'             => esc_html__( 'Wisps', 'wisps' ),
		'name_admin_bar'        => esc_html__( 'Wisp', 'wisps' ),
		'archives'              => esc_html__( 'Wisp Archives', 'wisps' ),
		'attributes'            => esc_html__( 'Wisp Attributes', 'wisps' ),
		'parent_item_colon'     => esc_html__( 'Parent Wisp:', 'wisps' ),
		'all_items'             => esc_html__( 'All Wisps', 'wisps' ),
		'add_new_item'          => esc_html__( 'Add New Wisp', 'wisps' ),
		'add_new'               => esc_html__( 'Add New', 'wisps' ),
		'new_item'              => esc_html__( 'New Wisp', 'wisps' ),
		'edit_item'             => esc_html__( 'Edit Wisp', 'wisps' ),
		'update_item'           => esc_html__( 'Update Wisp', 'wisps' ),
		'view_item'             => esc_html__( 'View Wisp', 'wisps' ),
		'view_items'            => esc_html__( 'View Wisps', 'wisps' ),
		'search_items'          => esc_html__( 'Search Wisp', 'wisps' ),
		'not_found'             => esc_html__( 'Not found', 'wisps' ),
		'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'wisps' ),
		'featured_image'        => esc_html__( 'Featured Image', 'wisps' ),
		'set_featured_image'    => esc_html__( 'Set featured image', 'wisps' ),
		'remove_featured_image' => esc_html__( 'Remove featured image', 'wisps' ),
		'use_featured_image'    => esc_html__( 'Use as featured image', 'wisps' ),
		'insert_into_item'      => esc_html__( 'Insert into wisp', 'wisps' ),
		'uploaded_to_this_item' => esc_html__( 'Uploaded to this wisp', 'wisps' ),
		'items_list'            => esc_html__( 'Wisps list', 'wisps' ),
		'items_list_navigation' => esc_html__( 'Wisps list navigation', 'wisps' ),
		'filter_items_list'     => esc_html__( 'Filter wisps list', 'wisps' ),
	);

	$args = array(
		'label'               => esc_html__( 'Wisp', 'wisps' ),
		'description'         => esc_html__( 'Wisps', 'wisps' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'comments', 'revisions', 'excerpt' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-editor-code',
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
		'show_in_rest'        => true,
	);
	register_post_type( 'wisp', $args );

}
add_action( 'init', 'wisps_register_cpt' );
