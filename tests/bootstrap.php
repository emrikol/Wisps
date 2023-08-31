<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// Manually load the plugin being tested.
function _manually_load_plugin() {
	require dirname( __DIR__ ) . '/wisps.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Activates this plugin in WordPress so it can be tested.
$GLOBALS['wp_tests_options'] = array(
	'active_plugins' => array( 'wisps/wisps.php' ),
);

require $_tests_dir . '/includes/bootstrap.php';
