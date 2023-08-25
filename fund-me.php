<?php
/**
 * Plugin Name:       Fund me
 * Plugin URI:        https://github.com/natbienetre/wordpress-fund-me
 * Version:           0.0.1
 * GitHub Plugin URI: natbienetre/wordpress-fund-me
 * Funding URI:       https://github.com/sponsors/holyhope
 * License:           MPL-2.0
 * License URI:       https://www.mozilla.org/en-US/MPL/2.0/
 * Description:       Add a button to help get funding.
 * Author:            Pierre PÉRONNET
 * Author URI:        https://github.com/holyhope
 * Text Domain:       fundme
 * Domain Path:       /languages
 */

define( 'FUND_ME_PLUGIN_HEADER', 'Funding URI' );

if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
    require __DIR__ . '/vendor/autoload.php';
}

add_action( 'admin_init', 'fundme_admin_init' );
function fundme_admin_init() {
    add_filter( 'plugin_action_links', 'fundme_plugins_action_links', 10, 3 );
    add_filter( 'network_admin_plugin_action_links', 'fundme_plugins_action_links', 10, 3 );
    add_filter( 'theme_action_links', 'fundme_themes_action_links', 10, 2 );
}

function fundme_plugins_action_links( array $actions, string $plugin_file, array $plugin_data ) {
    if ( empty( $plugin_data[ FUND_ME_PLUGIN_HEADER ] ) ) {
        return $actions;
    }


    $actions[] = fundme_action_link( $plugin_data[ FUND_ME_PLUGIN_HEADER ] );


    return $actions;
}

function fundme_themes_action_links( array $actions, WP_Theme $theme ) {
    $url = $theme->get( FUND_ME_PLUGIN_HEADER );
    if ( ! $url ) {
        return $actions;
    }

    $actions[] = fundme_action_link( $url );

    return $actions;
}

function fundme_action_link( string $url ): string {
    return '<a target="_blank" href="' . esc_attr( $url ) . '">' .
        _x( "❤️\u{00A0}Show support", 'text link to sponsor the developper', 'fundme' ) .
    '</a>';
}

add_filter( 'extra_plugin_headers', 'fundme_extra_funding_uri' );
add_filter( 'extra_theme_headers', 'fundme_extra_funding_uri' );
function fundme_extra_funding_uri( array $headers ): array {
    if ( ! in_array( FUND_ME_PLUGIN_HEADER, $headers ) ) {
        $headers[] = FUND_ME_PLUGIN_HEADER;
    }

    return $headers;
}
