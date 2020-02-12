<?php
/**
 * Main class file for Wisps.
 *
 * @package WordPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The primary class for Pagegen.
 */
class Wisps {
	/**
	 * The unique instance of the plugin.
	 *
	 * @var Pagegen
	 */
	private static $instance;

	/**
	 * The unique instance of the plugin.
	 *
	 * @var Pagegen
	 */
	public static $cpt;

	public function __construct() {
		$this->cpt = new Wisps_CPT();
	}

	/**
	 * Gets an instance of our plugin.
	 *
	 * @return Pagegen
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

}