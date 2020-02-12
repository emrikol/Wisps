<?php
global $post;
$wisp_mime = get_post_meta( $post->ID, '_wisp_mime', true ) ?? 'text/plain';
$wisp_data = base64_decode( get_post_meta( $post->ID, '_wisp_data', true ) );
//var_dump( $wisp_data );
//var_dump( $wisp_mime );
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
		<script>
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
		</script>
	</body>
</html>
