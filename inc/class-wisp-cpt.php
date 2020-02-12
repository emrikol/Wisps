<?php
/**
 * Custom Post Type class file for Wisps.
 *
 * This file controls the `wisp` custom post type
 * and any functionality pertaining to its saving
 * and display.
 *
 * @package WordPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Custom Post Type class for Wisps.
 */
class Wisps_CPT {
	/**
	 * Initializes hooks for admin screen.
	 */
	public function init_hooks() {
		add_action( 'init', array( $this, 'wisps_register_cpt' ) );
		add_action( 'init', array( $this, 'wisps_add_rewrites' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wisps_add_code_editor' ) );
		add_action( 'add_meta_boxes', array( $this, 'wisps_add_metaboxes' ) );
		add_action( 'save_post', array( $this, 'wisps_save_data' ) );
		add_action( 'template_redirect', array( $this, 'wisps_display_raw_content' ) );

		add_filter( 'the_content', array( $this, 'wisps_safely_display_content' ), PHP_INT_MIN, 1 );
		add_filter( 'enter_title_here', array( $this, 'wisps_title_placeholder' ) );
		add_filter( 'gettext', array( $this, 'wisps_rename_excerpt' ), 10, 2 );
		add_filter( 'embed_html', array( $this, 'wisp_filter_embed_html' ), 10, 4 );
	}

	/**
	 * Registers the custom post type.
	 */
	public function wisps_register_cpt() {
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


	/**
	 * Adds rewrites to view and download wisps.
	 */
	public function wisps_add_rewrites() {
		add_rewrite_tag( '%wisp_raw%', '([^&]+)' );
		add_rewrite_rule( 'wisp/(.+?)/view/?$', 'index.php?post_type=wisp&post_name=$matches[1]&wisp_raw=view', 'top' );
		add_rewrite_rule( 'wisp/(.+?)/raw/?$', 'index.php?post_type=wisp&post_name=$matches[1]&wisp_raw=view', 'top' );
		add_rewrite_rule( 'wisp/(.+?)/download/?$', 'index.php?post_type=wisp&post_name=$matches[1]&wisp_raw=download', 'top' );
		add_rewrite_rule( 'wisp/(.+?)/embed/?$', 'index.php?post_type=wisp&post_name=$matches[1]&wisp_raw=embed', 'top' );
	}

	/**
	 * Sets up admin scripts and the code editor.
	 *
	 * @param string $hook The page hook being ran on.
	 */
	public function wisps_add_code_editor( $hook ) {
		global $post;

		if ( ! $post || 'wisp' !== $post->post_type ) {
			return;
		}

		if ( 'post.php' === $hook || 'post-new.php' === $hook ) {
			if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$wisp_mime = get_post_meta( (int) $_GET['post'], '_wisp_mime', true ) ?? 'text/plain'; // phpcs:ignore Generic.PHP.Syntax.PHPSyntax, WordPress.Security.NonceVerification.Recommended
			}

			wp_enqueue_code_editor( array( 'type' => $wisp_mime ) );
			wp_enqueue_script( 'wisp-code-editor', plugin_dir_url( __FILE__ ) . '../code-editor.js', array( 'jquery' ), filemtime( plugin_dir_path( __FILE__ ) . '../code-editor.js' ), true );
		}
	}

	/**
	 * Registers meta boxes.
	 */
	public function wisps_add_metaboxes() {
		add_meta_box( 'wisp-code', esc_html__( 'Wisp Code', 'wisps' ), array( $this, 'wisps_metabox_editor' ), 'wisp', 'advanced', 'high' );
		add_meta_box( 'wisp-mime', esc_html__( 'Mime Type', 'wisps' ), array( $this, 'wisps_metabox_mime_type' ), 'wisp', 'side', 'high' );
	}

	/**
	 * Sets up the metabox for the code editor.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function wisps_metabox_editor( $post ) {
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
	public function wisps_metabox_mime_type( $post ) {
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
	public function wisps_save_data( $post_id ) {
		if ( defined( 'DOING_AJAX' ) ) {
			return;
		}

		if ( isset( $_POST['wisp_data'] ) ) {
			$wisp_data = wp_unslash( $_POST['wisp_data'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$wisp_mime = isset( $_POST['wisp_mime'] ) ? sanitize_text_field( wp_unslash( $_POST['wisp_mime'] ) ) : 'text/plain';
			$wisp_name = sanitize_file_name( get_the_title( $post_id ) );

			update_post_meta( $post_id, '_wisp_mime', $wisp_mime );
			update_post_meta( $post_id, '_wisp_data', base64_encode( $wisp_data ) );

			add_action( 'save_post', 'wisps_save_data' );
		}
	}


	/**
	 * Filters the wisp content to safely display it for themes that do not have wisp support.
	 *
	 * @param string $content Post Object Content.
	 * @erturn string Post Object Content.
	 */
	public function wisps_safely_display_content( $content ) {
		if ( ! current_theme_supports( 'wisps' ) ) {
			global $post;

			if ( 'wisp' === $post->post_type ) {
				$wisp_data = base64_decode( get_post_meta( $post->ID, '_wisp_data', true ) );
				return '<pre>' . esc_html( $wisp_data ) . '</pre>';
			}
		}

		return $content;
	}


	/**
	 * Template redirect to either display the raw text or downloads the file.
	 */
	public function wisps_display_raw_content() {
		global $wp_query;

		if ( 'wisp' === $wp_query->query_vars['post_type'] && isset( $wp_query->query_vars['wisp_raw'] ) ) {
			global $post;

			if (
				( 'publish' !== get_post_status( $post->ID ) && ! current_user_can( 'edit_posts' ) ) ||
				! empty( $post->post_password )
			) {
				wp_die( 'Unauthorized', 401 );
			}

			if ( 'embed' === $wp_query->query_vars['wisp_raw'] ) {
				header( 'Content-Type: text/html' );
				require 'oembed-template.php';
				exit;
			}

			$wisp_data = base64_decode( get_post_meta( $post->ID, '_wisp_data', true ) );

			if ( 'view' === $wp_query->query_vars['wisp_raw'] ) {
				header( 'Content-Type: text/plain' );
			}

			if ( 'download' === $wp_query->query_vars['wisp_raw'] ) {
				header( 'Content-Description: File Transfer' );
				header( 'Content-Type: application/octet-stream' );
				header( 'Content-Disposition: attachment; filename=' . sanitize_file_name( $post->post_title ) );
				header( 'Content-Transfer-Encoding: binary' );
				header( 'Content-Length: ' . strlen( $wisp_data ) );
			}

			echo $wisp_data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			exit;
		}
	}

	/**
	 * Filters the title placeholder.
	 *
	 * @param string $title The title placeholder.
	 * @erturn string The title placeholder.
	 */
	public function wisps_title_placeholder( $title ) {
		$screen = get_current_screen();

		if ( 'wisp' === $screen->post_type ) {
			$title = esc_html__( 'Filename including extension...', 'wisps' );
		}

		return $title;
	}

	/**
	 * Renames the excerpt metabox text.  Hacky, but works.
	 *
	 * @param string $translation The new "translated" text.
	 * @param string $original    The original text.
	 * @erturn string The new "translated" text.
	 */
	public function wisps_rename_excerpt( $translation, $original ) {
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

	public function wisp_filter_embed_html( $output, $post, $width, $height ) {
		if ( 'wisp' !== $post->post_type ) {
			return $output;
		}

		// Mostly borrowed from core's `get_post_embed_html()`.
		$embed_url = get_post_embed_url( $post );

		$output = '<blockquote class="wp-embedded-content"><a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . "</a></blockquote>\n";
		$output .= "<script type='text/javascript'>\n";
		$output .= "<!--//--><![CDATA[//><!--\n";

		$output .= <<< 'EOT'
/**
 * WordPress inline HTML embed
 *
 * @since 4.4.0
 * @output wp-includes/js/wp-embed.js
 *
 * This file cannot have ampersands in it. This is to ensure
 * it can be embedded in older versions of WordPress.
 * See https://core.trac.wordpress.org/changeset/35708.
 */
(function ( window, document ) {
	'use strict';
	console.log( 'here!' );

	var supportedBrowser = false,
		loaded = false;

		if ( document.querySelector ) {
			if ( window.addEventListener ) {
				supportedBrowser = true;
			}
		}

	/** @namespace wp */
	window.wp = window.wp || {};

	if ( !! window.wp.receiveEmbedMessage ) {
		return;
	}

	window.wp.receiveEmbedMessage = function( e ) {
		var data = e.data;

		if ( ! data ) {
			return;
		}

		if ( ! ( data.secret || data.message || data.value ) ) {
			return;
		}

		if ( /[^a-zA-Z0-9]/.test( data.secret ) ) {
			return;
		}

		console.log( 'have data' );

		var iframes = document.querySelectorAll( 'iframe[data-secret="' + data.secret + '"]' ),
			blockquotes = document.querySelectorAll( 'blockquote[data-secret="' + data.secret + '"]' ),
			i, source, height, sourceURL, targetURL;

		for ( i = 0; i < blockquotes.length; i++ ) {
			blockquotes[ i ].style.display = 'none';
		}

		for ( i = 0; i < iframes.length; i++ ) {
			source = iframes[ i ];

			if ( e.source !== source.contentWindow ) {
				continue;
			}

			source.removeAttribute( 'style' );

			/* Resize the iframe on request. */
			if ( 'height' === data.message ) {
				height = parseInt( data.value, 10 );
				if ( height > 1000 ) {
					height = 1000;
				} else if ( ~~height < 200 ) {
					height = 200;
				}

				source.height = height;
			}

			/* Link to a specific URL on request. */
			if ( 'link' === data.message ) {
				sourceURL = document.createElement( 'a' );
				targetURL = document.createElement( 'a' );

				sourceURL.href = source.getAttribute( 'src' );
				targetURL.href = data.value;

				/* Only continue if link hostname matches iframe's hostname. */
				if ( targetURL.host === sourceURL.host ) {
					if ( document.activeElement === source ) {
						window.top.location.href = data.value;
					}
				}
			}
		}
	};

	function onLoad() {
		if ( loaded ) {
			return;
		}

		loaded = true;

		var isIE10 = -1 !== navigator.appVersion.indexOf( 'MSIE 10' ),
			isIE11 = !!navigator.userAgent.match( /Trident.*rv:11\./ ),
			iframes = document.querySelectorAll( 'iframe.wp-embedded-content' ),
			iframeClone, i, source, secret;

		for ( i = 0; i < iframes.length; i++ ) {
			source = iframes[ i ];

			if ( ! source.getAttribute( 'data-secret' ) ) {
				/* Add secret to iframe */
				secret = Math.random().toString( 36 ).substr( 2, 10 );
				source.src += '#?secret=' + secret;
				source.setAttribute( 'data-secret', secret );
			}

			/* Remove security attribute from iframes in IE10 and IE11. */
			if ( ( isIE10 || isIE11 ) ) {
				iframeClone = source.cloneNode( true );
				iframeClone.removeAttribute( 'security' );
				source.parentNode.replaceChild( iframeClone, source );
			}
		}
	}

	if ( supportedBrowser ) {
		window.addEventListener( 'message', window.wp.receiveEmbedMessage, false );
		document.addEventListener( 'DOMContentLoaded', onLoad, false );
		window.addEventListener( 'load', onLoad, false );
	}
})( window, document );
EOT;

		$output .= "\n//--><!]]>";
		$output .= "\n</script>";

		$output .= sprintf(
			'<iframe sandbox="allow-scripts" security="restricted" src="%1$s" width="%2$d" height="%3$d" title="%4$s" frameborder="0" marginwidth="0" marginheight="0" scrolling="no" class="wp-embedded-content"></iframe>',
			esc_url( $embed_url ),
			absint( $width ),
			absint( $height ),
			esc_attr(
				sprintf(
					/* translators: 1: Post title, 2: Site title. */
					__( '&#8220;%1$s&#8221; &#8212; %2$s' ),
					get_the_title( $post ),
					get_bloginfo( 'name' )
				)
			)
		);

		return $output;
	}


}