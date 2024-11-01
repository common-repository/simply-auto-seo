<?php

/**
 * @package Simply Auto SEO
 * uninstall.php
 * Copyright (c) 2021 by Carl David Brubaker
 * All Rights Reserved
 * 
 * Uninstalls plugin Simply Auto SEO
 */

defined( 'ABSPATH' ) or exit;

// exit if uninstall constant is not defined
defined( 'WP_UNINSTALL_PLUGIN' ) or die;

// User wants to delete all data.
$options = get_option( 'cdb_2021_simply_auto_seo_options' );
if ( ! empty( $options['uninstall_delete_all_data'] ) ) {
	delete_option( 'cdb_2021_simply_auto_seo_options' );
}
