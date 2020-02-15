=== Wisps ===
Contributors: emrikol
Donate link: https://wordpressfoundation.org/donate/
Tags: code, embed, gist, github, pastebin
Requires at least: 5.2
Tested up to: 5.3.2
Stable tag: 2.0.0
Requires PHP: 5.6
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
GitHub Plugin URI: https://github.com/emrikol/Wisps

Wisps are embeddable and sharable code snippets for WordPress.

== Description ==

With Wisps, you can have code snippets similar to [Gist](https://gist.github.com/), [Pastebin](https://pastebin.com/), or similar code sharing sites.  Using the built-in [WordPress code editor](https://make.wordpress.org/core/2017/10/22/code-editing-improvements-in-wordpress-4-9/), you can write snippets to post and share.  This has the benefit of WordPress revisions, auto-drafts, etc to keep a record of how code changes.

Wisps can be downloaded by appending `/download/` to the permalink, or viewed raw by adding `/view/` or `/raw/`.  There is full oEmbed support so you can just paste in a link to a wisp in the editor and it will be fully embedded.

[PrismJS](https://prismjs.com/) is used for syntax highlighting for oEmbeds.

== Developers ==

You can add Wisp support to your theme either by modifying the custom post type `page-wisp.php` template, which will continue to display Wisps in the loop securely, or you can use `add_theme_support( 'wisps' )` to tell the plugin to not automatically escape the output.  You can then do what you like, such as potentially adding frontend support for syntax highlighting.

== Installation ==

1. Upload the plugin package to the plugins directory.
2. Login to the dashboard and activate the plugin.
3. Enjoy your new Wisps

== Screenshots ==

1. A Wisp being edited.
2. A Wisp displayed on the frontend.
3. A Wisp displayed raw.
4. A Wisp being embedded into a post.

== Changelog ==

= 2.0.0 =

Refactored many things, this is a breaking change.  First published to the WordPress.org repository.

= 1.0.0 =

First Version