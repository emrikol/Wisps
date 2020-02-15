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
$wisp_data = base64_decode( get_post_meta( $post->ID, '_wisp_data', true ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

switch ( $wisp_mime ) {
	case 'text/plain':
		$mime_type = 'language-none';
		break;
	case 'css':
		$mime_type = 'language-css';
		break;
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
		$mime_type = 'language-html';
		break;
	case 'text/html':
		$mime_type = 'language-html';
		break;
	case 'php':
		$mime_type = 'language-php';
		break;
	case 'application/x-httpd':
		$mime_type = 'language-php';
		break;
	case 'text/x-php':
		$mime_type = 'language-php';
		break;
	case 'javascript':
		$mime_type = 'language-js';
		break;
	case 'application/ecmascript':
		$mime_type = 'language-js';
		break;
	case 'application/json':
		$mime_type = 'language-json';
		break;
	case 'application/javascript':
		$mime_type = 'language-js';
		break;
	case 'application/ld+json':
		$mime_type = 'language-json';
		break;
	case 'text/typescript':
		$mime_type = 'language-typescript';
		break;
	case 'application/typescript':
		$mime_type = 'language-typescript';
		break;
	default:
		$mime_type = 'language-none';
}

?>
<!DOCTYPE html>
<html lang="en-US">
	<head>
		<title>Hello world! &#8211; Plugin: Wisps</title>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name='robots' content='noindex,follow' />
		<link rel="canonical" href="https://wisps.sish.emrikol.com/hello-world/" />
		<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.19.0/prism.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.19.0/plugins/autoloader/prism-autoloader.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.19.0/plugins/line-numbers/prism-line-numbers.min.js"></script>

		<link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.19.0/themes/prism.min.css" rel="stylesheet" />
		<link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.19.0/plugins/line-numbers/prism-line-numbers.min.css" rel="stylesheet" />
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
					<a class='view-raw' href="<?php echo esc_url( trailingslashit( get_permalink( $post->ID ) ) . 'raw/' ); ?>" style="float:right">view raw</a>
					<a class='permalink' href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>"><?php echo esc_html( get_the_title( $post->ID ) ); ?></a>
					displayed with ❤ by <a href="https://github.com/emrikol/wisps">Wisps</a>
				</div>
			</div>
		</div>
		<script><?php echo file_get_contents( ABSPATH . WPINC . '/js/wp-embed-template.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents,WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
	</body>
</html>
