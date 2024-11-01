<?php

namespace cdb_2021_Simply_Auto_SEO\admin;

defined( 'ABSPATH' ) or exit;

/**
 * @package Simply Auto SEO
 * admin.php
 * Copyright (c) 2021 by Carl David Brubaker
 * All Rights Reserved
 * 
 * Handles admin options page for Simply Auto SEO.
 */

class CDB_2021_Simply_Auto_SEO_Admin
{
	protected string $domain = CDB_2021_SIMPLY_AUTO_SEO_TEXT_DOMAIN;

	public function __construct()
	{
		add_action(
			'admin_menu',
			array( $this, 'add_action_admin_settings_page' )
		);
		add_action(
			'admin_init',
			array( $this, 'add_action_register_settings' )
		);
		add_action(
			'admin_init',
			array( $this, 'add_action_activate_page_excerpts' )
		);
	}

	/**
	 * Add Simply Auto SEO settings page to 'Settings' in admin menu.
	 */
	public function add_action_admin_settings_page()
	{
		if ( ! current_user_can( 'simply_auto_seo_settings' ) ) {
			return;
		}

		if ( current_user_can( 'manage_options' ) ) {
			add_options_page(
				esc_html__( 'Simply Auto SEO Settings', $this->domain ),
				esc_html__( 'Simply Auto SEO', $this->domain ),
				'manage_options',
				'cdb-2021-simply-auto-seo-options',
				array( $this, 'options_page'),
			);
		} else {
			add_menu_page(
				esc_html__( 'Simply Auto SEO Settings', $this->domain ),
				esc_html__( 'Simply Auto SEO', $this->domain ),
				'simply_auto_seo_settings',
				'cdb-2021-simply-auto-seo-options',
				array( $this, 'options_page'),
				'dashicons-admin-generic',
				null
			);
		}
	}

	/**
	 * Registers settings.
	 */
	public function add_action_register_settings()
	{
		register_setting(
			'cdb_2021_simply_auto_seo_options',
			'cdb_2021_simply_auto_seo_options',
			array( $this, 'validate_options' ),
		);
		add_settings_section(
			'cdb_2021_simply_auto_seo_section_description',
			esc_html__( 'Description Settings', $this->domain ),
			array( $this, 'settings_section_description' ),
			'cdb-2021-simply-auto-seo-options'
		);
		add_settings_field(
			'cdb_2021_simply_auto_seo_trim_description',
			esc_html__( 'Trim Description at', $this->domain ),
			array( $this, 'trim_description_field' ),
			'cdb-2021-simply-auto-seo-options',
			'cdb_2021_simply_auto_seo_section_description',
		);
		add_settings_field(
			'cdb_2021_simply_auto_seo_enable_page_excerpts',
			esc_html__( 'Enable Page Excerpts', $this->domain ),
			array( $this, 'enable_page_excerpts_field' ),
			'cdb-2021-simply-auto-seo-options',
			'cdb_2021_simply_auto_seo_section_description',
		);
		add_settings_section(
			'cdb_2021_simply_auto_seo_section_uninstall',
			esc_html__( 'Uninstall Settings', $this->domain ),
			array( $this, 'settings_section_uninstall' ),
			'cdb-2021-simply-auto-seo-options'
		);
		add_settings_field(
			'cdb_2021_simply_auto_seo_uninstall_delete_all_data',
			esc_html__( 'Delete all plugin data on uninstall', $this->domain ),
			array( $this, 'uninstall_delete_all_data_field' ),
			'cdb-2021-simply-auto-seo-options',
			'cdb_2021_simply_auto_seo_section_uninstall',
		);
	}

	/**
	 * Description for 'Trim Description at' setting.
	 */
	public function settings_section_description()
	{
		echo '<p>'
			 . __( 'Enter a word or HTML ASCII code to trim the description. Useful if description is consistently displaying non-relevant information in the description, i.e. "Read More". The entered information will be excluded from the description. Trimming takes place before translations.' )
			 . '</p>';
	}

	/**
	 * Description for 'Delete all data' setting.
	 */
	public function settings_section_uninstall()
	{
		echo '<p>'
			 . __( 'Check if you want to delete all plugin data when plugin is uninstalled.' )
			 . '</p>';
	}

	/**
	 * trim_description form field.
	 */
	public function trim_description_field()
	{
		$options = get_option( 'cdb_2021_simply_auto_seo_options' );
		$trim = isset( $options['trim_description'] )
				? $options['trim_description'] : '';
		?>
		<input id="cdb_2021_simply_auto_seo_trim_description"
			   name="cdb_2021_simply_auto_seo_options[trim_description]"
			   type="text"
			   value="<?php echo esc_attr( $trim ); ?>">
		
		<?php
	}

	/**
	 * enable_page_excerpts form field.
	 */
	public function enable_page_excerpts_field()
	{
		$options = get_option( 'cdb_2021_simply_auto_seo_options' );
		$enable_page_excerpts = isset( $options['enable_page_excerpts'] )
				? $options['enable_page_excerpts'] : false;
		?>
		<input id="cdb_2021_simply_auto_seo_enable_page_excerpts"
			   name="cdb_2021_simply_auto_seo_options[enable_page_excerpts]"
			   type="checkbox"
			   <?php checked( $enable_page_excerpts, true, true ); ?>>
		<?php
	}

	/**
	 * uninstall_delete_all_data form field.
	 */
	public function uninstall_delete_all_data_field()
	{
		$options = get_option( 'cdb_2021_simply_auto_seo_options' );
		$delete_all_data = isset( $options['uninstall_delete_all_data'] )
				? $options['uninstall_delete_all_data'] : false;
		?>
		<input id="cdb_2021_simply_auto_seo_uninstall_delete_all_data"
			   name="cdb_2021_simply_auto_seo_options[uninstall_delete_all_data]"
			   type="checkbox"
			   <?php checked( $delete_all_data, true, true ); ?>>
		<?php
	}

	/**
	 * Displays the Simply Auto SEO admin page.
	 */
	public function options_page()
	{
		?>
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'cdb_2021_simply_auto_seo_options' );
			do_settings_sections( 'cdb-2021-simply-auto-seo-options' );
			submit_button();
			?>
		</form>
		<?php
	}

	/**
	 * Sanitizes submitted trim_description input.
	 * @return array $input - submitted form data.
	 */
	public function validate_options( $input )
	{
		if ( isset( $input['trim_description'] ) ) {
			$input['trim_description'] = sanitize_text_field( 
				$input['trim_description']
			);
		}

		$input['enable_page_excerpts'] = isset( $input['enable_page_excerpts'] ) ? true : false;

		$input['uninstall_delete_all_data'] = isset( $input['uninstall_delete_all_data'] ) ? true : false;

		return $input;
	}

	public function add_action_activate_page_excerpts()
	{
		$options = get_option( 'cdb_2021_simply_auto_seo_options' );
		if ( isset( $options['enable_page_excerpts'] ) ) {
			if ( $options['enable_page_excerpts']) {
				add_post_type_support( 'page', 'excerpt' );
			}
		}
	}
}