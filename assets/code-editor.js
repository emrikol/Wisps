/**
 * Script to set up the WordPress core code editor.
 *
 * @package Wisps
 */

var wisp_editor;
( function ( $ ) {
	$(
		function () {
			if ( $( '#wisp_code_editor' ).length ) {
				wisp_editor = wp.codeEditor.initialize( $( '#wisp_code_editor' ) );
			}

			$( '#wisp_mime' ).on(
				'change',
				function ( e ) {
					wisp_editor.codemirror.setOption( 'mode', $( '#wisp_mime' ).val() );
				}
			);
		}
	);
} )( jQuery );
