<?php
/**
 * Class WispTest
 */

/**
 * General Tests.
 */
class WispTest extends WP_UnitTestCase {
	public $post_id           = false;
	public $default_wisp_data = '<script type="text/javascript">alert( "Hello World!" )</script>';

	public function setUp() {
		parent::setUp();

		$this->post_id = self::factory()->post->create( array(
			'post_type'   => 'wisp',
			'post_status' => 'publish',
			'post_title'  => 'test.js',
			'meta_input'  => array(
				'_wisp_mime' => 'application/javascript',
				'_wisp_data' => base64_encode( $this->default_wisp_data ),
			),
		) );
	}

	public function tearDown() {
		wp_delete_post( $this->post_id, true );

		parent::tearDown();
	}

	public function test_get_data() {
		$data = Wisps::get_instance()->meta_get_data( $this->post_id );
		$this->assertEquals( $data, $this->default_wisp_data );
	}

	public function test_update_data() {
		$old_data = Wisps::get_instance()->meta_get_data( $this->post_id );

		Wisps::get_instance()->meta_update_data( $this->post_id, 'alert( "PHPUnit Test" )' );
		$data = Wisps::get_instance()->meta_get_data( $this->post_id );

		$this->assertEquals( $data, 'alert( "PHPUnit Test" )' );

		Wisps::get_instance()->meta_update_data( $this->post_id, $old_data );
	}

	public function test_filtered_embed_html() {
		$embed_html = get_post_embed_html( 320, 200, $this->post_id );

		$this->assertContains( 'class="wp-embedded-content wisp-embedded-content"', $embed_html );
	}

	public function test_escaped_content() {
		global $post;
		$post = get_post( $this->post_id );

		$this->assertContains( esc_html( $this->default_wisp_data ), apply_filters( 'the_content', $post->post_content ) );
	}

}
