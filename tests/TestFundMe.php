<?php
/**
 * Class TestFundMe
 *
 * @package Fund_me
 */

/**
 * Tests for the fund-me plugin's action-link and header-registration behavior.
 */
class TestFundMe extends WP_UnitTestCase {

	/**
	 * fundme_extra_funding_uri() adds Funding URI when missing.
	 */
	public function test_extra_funding_uri_adds_header_when_missing() {
		$headers = array( 'Some Other Header' );

		$result = fundme_extra_funding_uri( $headers );

		$this->assertContains( FUND_ME_PLUGIN_HEADER, $result );
	}

	/**
	 * fundme_extra_funding_uri() returns the list unchanged (no duplicate) when already present.
	 */
	public function test_extra_funding_uri_does_not_duplicate_when_present() {
		$headers = array( 'Some Other Header', FUND_ME_PLUGIN_HEADER );

		$result = fundme_extra_funding_uri( $headers );

		$this->assertSame( $headers, $result );
	}

	/**
	 * fundme_plugins_action_links() appends a support link when Funding URI is present.
	 */
	public function test_plugins_action_links_appends_link_when_funding_uri_present() {
		$actions     = array( 'deactivate' => 'Deactivate' );
		$plugin_data = array( FUND_ME_PLUGIN_HEADER => 'https://example.com/sponsor' );

		$result = fundme_plugins_action_links( $actions, 'fund-me/fund-me.php', $plugin_data );

		$this->assertCount( 2, $result );
		$this->assertSame(
			fundme_action_link( 'https://example.com/sponsor' ),
			array_values( $result )[1]
		);
	}

	/**
	 * fundme_plugins_action_links() returns actions unchanged when Funding URI is missing.
	 */
	public function test_plugins_action_links_unchanged_when_funding_uri_missing() {
		$actions     = array( 'deactivate' => 'Deactivate' );
		$plugin_data = array();

		$result = fundme_plugins_action_links( $actions, 'fund-me/fund-me.php', $plugin_data );

		$this->assertSame( $actions, $result );
	}

	/**
	 * fundme_plugins_action_links() returns actions unchanged when Funding URI is empty.
	 */
	public function test_plugins_action_links_unchanged_when_funding_uri_empty() {
		$actions     = array( 'deactivate' => 'Deactivate' );
		$plugin_data = array( FUND_ME_PLUGIN_HEADER => '' );

		$result = fundme_plugins_action_links( $actions, 'fund-me/fund-me.php', $plugin_data );

		$this->assertSame( $actions, $result );
	}

	/**
	 * fundme_themes_action_links() appends a support link when the theme declares a Funding URI.
	 */
	public function test_themes_action_links_appends_link_when_funding_uri_present() {
		$actions = array( 'customize' => 'Customize' );

		$theme = new WP_Theme( 'fundme-test-theme-with-funding', __DIR__ . '/data/themes' );

		$result = fundme_themes_action_links( $actions, $theme );

		$this->assertCount( 2, $result );
		$this->assertSame(
			fundme_action_link( 'https://example.com/theme-sponsor' ),
			array_values( $result )[1]
		);
	}

	/**
	 * fundme_themes_action_links() returns actions unchanged when the theme has no Funding URI.
	 */
	public function test_themes_action_links_unchanged_when_funding_uri_empty() {
		$actions = array( 'customize' => 'Customize' );

		$theme = new WP_Theme( 'fundme-test-theme-no-funding', __DIR__ . '/data/themes' );

		$result = fundme_themes_action_links( $actions, $theme );

		$this->assertSame( $actions, $result );
	}

	/**
	 * fundme_action_link() builds the expected anchor markup.
	 */
	public function test_action_link_markup() {
		$link = fundme_action_link( 'https://example.com/sponsor' );

		$this->assertStringContainsString( 'target="_blank"', $link );
		$this->assertStringContainsString( 'href="' . esc_attr( 'https://example.com/sponsor' ) . '"', $link );
		$this->assertStringContainsString( 'Show support', $link );
	}

	/**
	 * fundme_admin_init() registers the expected filters.
	 */
	public function test_admin_init_registers_filters() {
		fundme_admin_init();

		$this->assertNotFalse( has_filter( 'plugin_action_links', 'fundme_plugins_action_links' ) );
		$this->assertNotFalse( has_filter( 'network_admin_plugin_action_links', 'fundme_plugins_action_links' ) );
		$this->assertNotFalse( has_filter( 'theme_action_links', 'fundme_themes_action_links' ) );
	}
}
