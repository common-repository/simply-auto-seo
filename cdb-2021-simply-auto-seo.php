<?php

/**
 * Plugin Name: Simply Auto SEO
 * Plugin URI: https://github.com/Spleeding1/cdb-2021-simply-auto-seo
 * Description: Automatically adds SEO tags to &lt;head&gt;. Does not display any field inputs in WordPress Editor. name="description" can be edited through post excerpts and taxonomy descriptions.
 * Version: 1.2.1
 * Requires at least: 5.8
 * Tested up to: 6.1
 * Requires PHP: 7.4
 * Author: Carl David Brubaker
 * Author URI: https://carlbrubaker.com/
 * License: GPLv3 (or later)
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: CDB_2021_SIMPLY_AUTO_SEO
 * Domain Path: /languages
 */

namespace cdb_2021_Simply_Auto_SEO;

use function current_user_can;

defined( 'ABSPATH' ) or exit;

$prefix = 'CDB_2021_SIMPLY_AUTO_SEO';

if ( ! defined( $prefix . '_PATH' ) ) {
	define( $prefix . '_PATH', plugin_dir_path(__FILE__) );
}

if ( ! defined($prefix . '_VERSION') ) {
	define( $prefix . '_VERSION', '1.2.0' );
}

if ( ! defined( $prefix . '_TEXT_DOMAIN') ) {
	define( $prefix . '_TEXT_DOMAIN', 'Text Domain' );
}

class CDB_2021_Simply_Auto_SEO
{	
	/**
	 * Plugin prefix defined above. Sets when class is contructed.
	 */
	protected string $prefix;

	/**
	 * Plugin text domain defined globally.
	 */
	protected string $domain = CDB_2021_SIMPLY_AUTO_SEO_TEXT_DOMAIN;

	/**
	 * Plugin version defined globally.
	 */
	protected string $version = CDB_2021_SIMPLY_AUTO_SEO_VERSION;

	/**
	 * Names of plugin transients stored in the transients table.
	 * 
	 * Used to delete_transient on uninstall.
	 * - List all possible options that can be set to ensure proper cleanup.
	 * 
	 * Can be used to set_transient if 'update' => true.
	 * 
	 * $this->prefix is added to key on creation and deletion.
	 * 
	 * example:
	 * 
	 * $prefix = 'my_plugin';
	 * $transients = array(
	 *     'my_transient' => array(
	 *         'value' => 'My Value',
	 *         'update' => true),
	 * );
	 */
	protected array  $transients = array();

	public function __construct( string $prefix )
	{
		$this->prefix = $prefix;
		add_action( 'init', array( $this, 'activateOrUpdate' ) );
		add_action( 'wp_head', array( $this, 'action_add_seo_meta_tags' ) );
	}
	
	/**
	 * Perform actions if $options['version'] does not match $this->version.
	 */
	public function activateOrUpdate()
	{
		if ( ! $this->pluginVersionOptionIsTheLatest() ) {
			// Do stuff if version has changed.
			$this->updatePluginTransients();
			delete_option( 'CDB_2021_SIMPLY_AUTO_SEO_VERSION' );
		}
	}
	
	/**
	 * Checks stored version in the options table and updates as necessary.
	 * @return bool
	 * true if plugin version matches stored version.
	 * false if plugin version does not match or no option stored.
	 * 
	 * Use this method to set and update
	 * array cdb_2021_simply_auto_seo_options.
	 */
	public function pluginVersionOptionIsTheLatest()
	{
		$options = get_option( 'cdb_2021_simply_auto_seo_options' );
	
		if ( $options ) {
			if (
				! empty( $option['version'] ) &&
				$option['version'] === $this->version
			) {
				return true;
			}
		} else {
			add_option(
				'cdb_2021_simply_auto_seo_options',
				array(
					'version' => $this->version,
					'uninstall_delete_all_data' => true,
					'enable_page_excerpts' => true,
				)
			);

			return false;
		}

		$options['version'] = $this->version;
		update_option( 'cdb_2021_simply_auto_seo_options', $options );
		
		return false;
	}

	/**
	 * Sets plugin transients on the options table, using
	 * $this->transients array.
	 */
	protected function updatePluginTransients()
	{
		if ( empty( $this->transients ) ) {
			return;
		}

		foreach ( $this->transient as $transient => $setting ) {
			$update = $setting['update'] ?? null;
			$value  = $setting['value']  ?? null;
			if ( empty( $update ) || empty( $value ) ) {
				continue;
			}

			$transient_value = get_transient( $this->prefix . $transient );

			if ( $transient_value === $value ) {
				continue;
			} else {
				set_transient( $this->prefix . $transient, $value );
			}
		}
	}

	/**
	 * Adds SEO meta tags to head if current page. 
	 * 
	 * is_singular - will display entered excerpt or pull from page content.
	 *             - will trim description at string given on settings page.
	 * is_category, is_tag, is_author, is_post_type_archive, is_tax - will only display if description is set.
	 * 
	 */
	public function action_add_seo_meta_tags()
	{
		if ( ! get_post_status() === 'public' ) {
			return;
		}

		global $wp;
		$description = null;

		if ( is_singular() || is_front_page() ) {
			$options = get_option ('cdb_2021_simply_auto_seo_options');
			$description = get_the_excerpt();
			if ( ! empty( $options['trim_description'] ) ) {
				if ( strpos( $description, $options['trim_description'] ) ) {
					$description = explode(
						$options['trim_description'],
						$description
					)[0] . '&hellip;';
				}
			} else {
				$description = get_the_excerpt();
			}
		} else if ( is_category() || is_tag() || is_author() || is_post_type_archive() || is_tax()) {
			$description = get_the_archive_description();
		}

		if ( ! empty( $description ) ) {
			$description = esc_attr__(
				strip_tags( $description ),
				$this->domain
			);
			?>
			<meta name="description"
				  content="<?php echo esc_attr( $description ); ?>">
			<meta property="og:description"
				  content="<?php echo esc_attr( $description ); ?>">
			<?php
		}

		$title = esc_attr__(
			is_front_page() ? get_bloginfo( 'description' ) : get_the_title(),
			$this->domain
		);

		if ( ! empty( $title ) ) {
			?>
			<meta property="og:title" content="<?php echo $title; ?>">
			<?php
		}
		?>

		<meta property="og:type" content="website">
		<meta property="og:url"
			  content="<?php echo esc_url( home_url( $wp->request ) ); ?>">
		
		<?php
		$site_name = esc_attr__( get_bloginfo( 'name' ), $this->domain );

		if ( ! empty( $site_name ) ) {
			?>
			<meta property="og:site_name"
			  content="<?php echo $site_name; ?>">
			<?php
		}
		
		$language = esc_attr( get_bloginfo( 'language' ) );

		if ( ! empty( $language ) ) {
			?>
			<meta property="og:locale"
			  content="<?php echo $language; ?>">
			<?php
		}
	}
}

require_once CDB_2021_SIMPLY_AUTO_SEO_PATH . 'roles.php';

if ( class_exists( 'cdb_2021_Simply_Auto_SEO\roles\CDB_2021_Simply_Auto_SEO_Roles' ) ) {
	$roles = new roles\CDB_2021_Simply_Auto_SEO_Roles();

	$new_role = get_role( 'simply_auto_seo_admin' );

	if ( empty( $new_role ) ) {
		$roles->addOrRemoveRolesAndCapabilities();
	}
}

if ( is_admin() ) {
	require_once CDB_2021_SIMPLY_AUTO_SEO_PATH . 'admin.php';

	if ( class_exists( 'cdb_2021_Simply_Auto_SEO\admin\CDB_2021_Simply_Auto_SEO_Admin' ) ) {
		new admin\CDB_2021_Simply_Auto_SEO_Admin();
	}
}

if ( class_exists( 'cdb_2021_Simply_Auto_SEO\CDB_2021_Simply_Auto_SEO' ) ) {
	new CDB_2021_Simply_Auto_SEO( $prefix );
}
