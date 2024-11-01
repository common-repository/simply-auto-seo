<?php

namespace cdb_2021_Simply_Auto_SEO\roles;

defined( 'ABSPATH' ) or exit;

/**
 * @package Simply Auto SEO
 * roles.php
 * Copyright (c) 2021 by Carl David Brubaker
 * All Rights Reserved
 * 
 * Handles registering and removing roles and capabilities of
 * Simply Auto SEO.
 */

class CDB_2021_Simply_Auto_SEO_Roles
{
	protected string $domain = CDB_2021_SIMPLY_AUTO_SEO_TEXT_DOMAIN;

	/**
	 * Roles to add and remove.
	 */
	protected array $roles = array(
		'simply_auto_seo_admin' => 'Simply Auto SEO Admin',
	);

	/**
	 * Role capabilities to add or modify.
	 */
	protected array $capabilities = array(
		array(
			'roles' => array(
				'administrator',
				'simply_auto_seo_admin',
			),
			'capabilities' => 'simply_auto_seo_settings'
		),
	);

	public function __construct()
	{
		register_activation_hook(
			CDB_2021_SIMPLY_AUTO_SEO_PATH . 'cdb-2021-simply-auto-seo.php',
			array( $this, 'register_activation_hook_roles_and_capabilities' )
		);
		register_deactivation_hook(
			CDB_2021_SIMPLY_AUTO_SEO_PATH . 'cdb-2021-simply-auto-seo.php',
			array( $this, 'register_deactivation_hook_roles_and_capabilities' )
		);
	}

	/**
	 * When plugin is activated, register plugin roles and add capabilities.
	 */
	public function register_activation_hook_roles_and_capabilities()
	{
		$this->addOrRemoveRolesAndCapabilities();
	}
	
	public function register_deactivation_hook_roles_and_capabilities()
	{
		$this->addOrRemoveRolesAndCapabilities( true );
	}

	public function addOrRemoveRolesAndCapabilities( bool $remove = false )
	{
		if ( ! empty( $this->roles ) ) {
			foreach ( $this->roles as $role_snake => $role_name ) {
				if ( $remove ) {
					remove_role( $role_snake, $role_name );
				} else {
					add_role( $role_snake, $role_name );

					/**
					 * Needed to access WP Admin.
					 */
					$role = get_role( $role_snake );

					if ( ! empty( $role ) ) {
						$role->add_cap( 'read' );
					}
				}
			}
			unset( $role_snake, $role_name, $role );
		}

		if ( ! empty( $this->capabilities ) ) {
			foreach ( $this->capabilities as $c ) {
				$roles = ( is_array( $c['roles'] ) ) ?
					$c['roles'] :
					array( $c['roles'] );

				$capabilities = ( is_array( $c['capabilities'] ) ) ?
					$c['capabilities'] :
					array( $c['capabilities'] );

				foreach ($roles as $the_role ) {
					$role = get_role( $the_role );

					if ( empty( $role ) ) {
						continue;
					}

					foreach ( $capabilities as $capability ) {
						if ( $remove ) {
							$role->remove_cap( $capability );
						} else {
							$role->add_cap( $capability );
						}
					}
				}
			}
			unset( $roles, $the_role, $role, $c, $capabilities, $capability );
		}
	}
}