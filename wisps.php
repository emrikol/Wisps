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

require __DIR__ . '/inc/class-wisp.php';
require __DIR__ . '/inc/class-wisp-cpt.php';

Wisps::get_instance()->cpt->init_hooks();
