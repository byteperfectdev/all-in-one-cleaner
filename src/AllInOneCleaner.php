<?php
/**
 * Class AllInOneCleaner.
 *
 * @package all_in_one_cleaner
 */

declare( strict_types=1 );

namespace all_in_one_cleaner;

use all_in_one_cleaner\interfaces\Module;
use all_in_one_cleaner\modules\Core;

/**
 * Class AllInOneCleaner.
 *
 * @package all_in_one_cleaner
 */
class AllInOneCleaner {
	/**
	 * Whether the plugin has been initialized.
	 *
	 * @var bool
	 */
	protected bool $initialized;

	/**
	 * Modules.
	 *
	 * @var array<Module>
	 */
	protected array $modules;

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
		}

		return $instance;
	}

	/**
	 * AllInOneCleaner constructor.
	 */
	private function __construct() {
		$this->initialized = false;
		$this->modules     = array();

		$this->register_hooks();
	}

	/**
	 * Initialize.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( true !== $this->initialized ) {
			$this->get_settings()->initialize();

			$handler = $this->get_handler();

			foreach ( $this->get_modules() as $module ) {
				if ( $handler->get_handler_version() !== $module->get_handler_version() ) {
					$this->log(
						__( 'The module is not compatible with the current version of the handler.', 'all_in_one_cleaner' ),
						array(
							'Module name'     => get_class( $module ),
							'Handler version' => $handler->get_handler_version(),
							'Module version'  => $module->get_handler_version(),
						)
					);

					add_action( 'admin_notices', array( $module, 'handler_is_not_compatible_notice' ) );

					continue;
				}

				$module->initialize();
			}

			$this->initialized = true;
		}
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	protected function register_hooks() {
		add_action( 'plugins_loaded', array( $this, 'initialize' ) );
		add_action( 'all_in_one_cleaner_on_save_fields', array( $this, 'dispatch_cleaner' ) );
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
	 * @return array<Module>
	 */
	public function get_modules(): array {
		if ( 0 === count( $this->modules ) ) {
			$this->modules = apply_filters(
				'all_in_one_cleaner_modules',
				array(
					'Core' => new Core(),
				)
			);
		}

		return $this->modules;
	}

	/**
	 * Get handler.
	 *
	 * @return AllInOneCleanerHandler
	 */
	public function get_handler(): AllInOneCleanerHandler {
		static $handler;

		if ( is_null( $handler ) ) {
			$handler = new AllInOneCleanerHandler();
		}

		return $handler;
	}

	/**
	 * Dispatch cleaner.
	 *
	 * @return void
	 */
	public function dispatch_cleaner() {
		$data = (array) apply_filters( 'all_in_one_cleaner_push_to_queue', array() );

		if ( count( $data ) > 0 ) {
			$this->get_handler()->data( $data );
			$this->get_handler()->save();
			$this->get_handler()->dispatch();
		} else {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'The cleaner has not been dispatched because the queue is empty.' );
		}
	}

	/**
	 * Logs a message and optional data.
	 *
	 * This method logs a message and optional data to the PHP error log.
	 *
	 * @param string $message The message to log.
	 * @param mixed  $data    Optional data to log. Default is null.
	 *
	 * @return void
	 */
	public function log( string $message, $data = null ): void {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions
		$message .= PHP_EOL . print_r( $data, true );

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions
		error_log( $message );
	}
}
