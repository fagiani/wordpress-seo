<?php
/**
 * @package WPSEO\Admin
 */

/**
 * Represents the class that contains the list of possible extensions for Yoast SEO.
 */
class WPSEO_Extensions {

	/** @var array Array with the Yoast extensions */
	protected $extensions = array(
		'Yoast SEO Premium'     => array(
			'slug'      => 'yoast-seo-premium',
			'classname' => 'WPSEO_Premium',
		),
		'News SEO'              => array(
			'slug'      => 'news-seo',
			'classname' => 'WPSEO_News',
		),
		'Yoast WooCommerce SEO' => array(
			'slug'      => 'woocommerce-yoast-seo',
			'classname' => 'Yoast_WooCommerce_SEO',
		),
		'Video SEO'             => array(
			'slug'      => 'video-seo-for-wordpress',
			'classname' => 'WPSEO_Video_Sitemap',
		),
		'Local SEO'             => array(
			'slug'      => 'local-seo-for-wordpress',
			'classname' => 'WPSEO_Local_Core',
		),
	);

	/**
	 * Returns the set extensions.
	 *
	 * @return array All the extension names.
	 */
	public function get() {
		return array_keys( $this->extensions );
	}

	/**
	 * Checks if the extension is valid.
	 *
	 * @param string $extension The extension to get the name for.
	 *
	 * @return bool Returns true when valid.
	 */
	public function is_valid( $extension ) {
		$options = array();

		// On multisite we need to take the network activated state into account.
		if ( is_multisite() ) {
			$options[] = $this->get_site_option( $extension );
		}

		// Fetch the option for the current site.
		$options[] = $this->get_option( $extension );

		// If the site is either active on multisite level or current site level.
		foreach ( $options as $extension_option ) {
			if ( is_array( $extension_option ) && isset( $extension_option['status'] ) && $extension_option['status'] === 'valid' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Invalidates the extension by removing its option.
	 *
	 * @param string $extension The extension to invalidate.
	 */
	public function invalidate( $extension ) {
		delete_option( $this->get_option_name( $extension ) );
	}

	/**
	 * Checks if the plugin has been installed.
	 *
	 * @param string $extension The name of the plugin to check.
	 *
	 * @return bool Returns true when installed.
	 */
	public function is_installed( $extension ) {
		return class_exists( $this->extensions[ $extension ]['classname'] );
	}

	/**
	 * Retrieves the extension settings form a single site environment.
	 *
	 * @param string $extension The extension to get the name for.
	 *
	 * @return mixed Returns the option.
	 */
	protected function get_option( $extension ) {
		return get_option( $this->get_option_name( $extension ) );
	}

	/**
	 * Retrieves the extension settings from a multisite environment.
	 *
	 * @param string $extension The extension to get the name for.
	 *
	 * @return mixed Returns the option.
	 */
	protected function get_site_option( $extension ) {
		return get_site_option( $this->get_option_name( $extension ) );
	}

	/**
	 * Converts the extension to the required option name.
	 *
	 * @param string $extension The extension name to convert.
	 *
	 * @return string Returns the option name.
	 */
	protected function get_option_name( $extension ) {
		return sanitize_title_with_dashes( $this->extensions[ $extension ]['slug'] . '_', null, 'save' ) . 'license';
	}
}
