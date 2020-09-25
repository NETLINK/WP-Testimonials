<?php
/**
 * Plugin Name: Testimonials
 * Plugin URI: http://www.netlink.ie/
 * Description: Testimonials plugin.
 * Author: NETLINK IT SERVICES
 * Version: 1.1
 * Author URI: http://www.netlink.ie/

*/

$update_class = ABSPATH . '/wp-content/plugins/private-plugin-updater/update.class.php';

if ( is_file( $update_class ) )
{
	require_once $update_class;
	if ( class_exists( 'PrivatePluginUpdater' ) )
	{
		$updater = new PrivatePluginUpdater( __FILE__, NULL, 1 );
	}
}

include __DIR__ . '/plugin.php';

