<?php
/**
 * Default template for oEmbed response.
 *
 * @package Wisps
 */

// phpcs:disable WordPress.WP.EnqueuedResources
global $post;

$wisp_mime = get_post_meta( $post->ID, '_wisp_mime', true );
if ( empty( $wisp_mime ) ) {
	$wisp_mime = 'text/plain';
}
$wisp_data = Wisps::get_instance()->meta_get_data( $post->ID );

switch ( $wisp_mime ) {
	case 'text/plain':
		$mime_type = 'language-none';
		break;
	case 'css':
	case 'text/css':
		$mime_type = 'language-css';
		break;
	case 'text/x-scss':
		$mime_type = 'language-scss';
		break;
	case 'text/x-less':
		$mime_type = 'language-less';
		break;
	case 'htmlmixed':
	case 'text/html':
		$mime_type = 'language-html';
		break;
	case 'php':
	case 'application/x-httpd':
	case 'text/x-php':
		$mime_type = 'language-php';
		break;
	case 'javascript':
	case 'application/ecmascript':
	case 'application/javascript':
		$mime_type = 'language-js';
		break;
	case 'application/json':
	case 'application/ld+json':
		$mime_type = 'language-json';
		break;
	case 'text/typescript':
	case 'application/typescript':
		$mime_type = 'language-typescript';
		break;
	default:
		$mime_type = 'language-none';
}

$prismjs_js = add_query_arg(
	array( 'ver' => rawurlencode( filemtime( plugin_dir_path( __FILE__ ) . '../assets/prismjs.min.js' ) ) ),
	plugin_dir_url( __FILE__ ) . '../assets/prismjs.min.js'
);

$prismjs_css = add_query_arg(
	array( 'ver' => rawurlencode( filemtime( plugin_dir_path( __FILE__ ) . '../assets/prismjs.css' ) ) ),
	plugin_dir_url( __FILE__ ) . '../assets/prismjs.css'
);

?>
<!DOCTYPE html>
<html lang="en-US">
	<head>
		<title><?php echo esc_html( get_the_title( $post->ID ) ); ?></title>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<?php noindex(); ?>
		<link rel="canonical" href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" />
		<script src="<?php echo esc_url( $prismjs_js ); ?>"></script>

		<link href="<?php echo esc_url( $prismjs_css ); ?>" rel="stylesheet" />
		<style>
			body {
				margin: 0;
				padding: 0;
			}
			.wisp-embed .wisp-meta {
				padding: 10px;
				overflow: hidden;
				font: 12px -apple-system,BlinkMacSystemFont,Segoe UI,Helvetica,Arial,sans-serif,Apple Color Emoji,Segoe UI Emoji;
				color: #666;
				background-color: #ebe5e1;
				border-radius: 0 0 2px 2px;
			}
			.wisp-embed .wisp-meta a {
				font-weight: 600;
				color: #666;
				text-decoration: none;
				border: 0;
			}
			.wisp-embed pre {
				margin: 0;
				border-bottom: 1px solid #ddd;
				border-radius: 2px 2px 0 0;
			}
			.wisp-file {
				border-color: rgba(0,0,0,.125) !important;
				border-radius: 4px !important;
				margin-bottom: 0 !important;
				border: 1px solid;
			}
		</style>
	</head>
	<body>
		<div class='wisp-file'>
			<div class="wisp-embed">
				<div id="gist-data">
					<pre><code class="line-numbers <?php echo sanitize_html_class( $mime_type ); ?>"><?php echo esc_html( $wisp_data ); ?></code></pre>
				</div>

				<div class="wisp-meta">
					<a class='view-raw' href="<?php echo esc_url( trailingslashit( get_permalink( $post->ID ) ) . 'raw/' ); ?>" style="float:right"><?php echo esc_html__( 'view raw', 'wisps' ); ?></a>
					<?php
					printf(
						'<a class="permalink" href="%s">%s</a>%s<a href="%s">%s</a>',
						esc_url( get_permalink( $post->ID ) ),
						esc_html( get_the_title( $post->ID ) ),
						esc_html__( ' displayed with ❤ by ', 'wisps' ),
						esc_url( get_site_url() ),
						esc_html( get_bloginfo( 'name' ) )
					); ?>
				</div>
			</div>
		</div>
		<script><?php echo file_get_contents( ABSPATH . WPINC . '/js/wp-embed-template.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents,WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
	</body>
</html>
