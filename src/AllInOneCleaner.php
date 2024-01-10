<?php
/**
 * Class AllInOneCleaner.
 *
 * @package all_in_one_cleaner
 */

declare( strict_types=1 );

namespace all_in_one_cleaner;

use all_in_one_cleaner\modules\AbstractModule;
use all_in_one_cleaner\modules\Core;

/**
 * Class AllInOneCleaner.
 *
 * @package all_in_one_cleaner
 */
class AllInOneCleaner {
	/**
	 * Get instance of AllInOneCleaner.
	 *
	 * @return AllInOneCleaner
	 */
	public static function instance(): AllInOneCleaner {
		static $instance;

		// Instantiate only once.
		if ( is_null( $instance ) ) {
			$instance = new AllInOneCleaner();

			$instance->initialize();
		}

		return $instance;
	}

	/**
	 * Initialize.
	 *
	 * @return void
	 */
	protected function initialize(): void {
		static $initialized;

		if ( true !== $initialized ) {
			$this->get_settings()->initialize();

			foreach ( $this->get_modules() as $module ) {
				$module->initialize();
			}

			$initialized = true;
		}
	}

	/**
	 * Get settings.
	 *
	 * @return Settings
	 */
	public function get_settings(): Settings {
		static $settings;

		if ( is_null( $settings ) ) {
			$settings = new Settings();
		}

		return $settings;
	}

	/**
	 * Get list modules.
	 *
	 * @return array<AbstractModule>
	 */
	public function get_modules(): array {
		static $modules;

		if ( is_null( $modules ) ) {
			$modules = array(
				new Core(),
			);
		}

		return $modules;
	}
}