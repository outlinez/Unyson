<?php if (!defined('FW')) die('Forbidden');

/**
 * Main framework class that contains everything
 *
 * Convention: All public properties should be only instances of the components (except special property: manifest)
 */
final class _Fw
{
	/** @var bool If already loaded */
	private static $loaded = false;

	/** @var FW_Framework_Manifest */
	public $manifest;

	/** @var _FW_Component_Extensions */
	public $extensions;

	/** @var _FW_Component_Backend */
	public $backend;

	/** @var _FW_Component_Theme */
	public $theme;

	public function __construct()
	{
		if (self::$loaded) {
			trigger_error('Framework already loaded', E_USER_ERROR);
		} else {
			self::$loaded = true;
		}

		// manifest
		{
			require fw_get_framework_directory('/core/class-fw-manifest.php');

			require fw_get_framework_directory('/manifest.php');
			/** @var array $manifest */

			$this->manifest = new FW_Framework_Manifest($manifest);

			add_action('fw_init', array($this, '_check_requirements'));
		}

		require fw_get_framework_directory('/core/extends/class-fw-extension.php');
		require fw_get_framework_directory('/core/extends/class-fw-option-type.php');

		// components
		{
			require fw_get_framework_directory('/core/components/extensions.php');
			$this->extensions = new _FW_Component_Extensions();

			require fw_get_framework_directory('/core/components/backend.php');
			$this->backend = new _FW_Component_Backend();

			require fw_get_framework_directory('/core/components/theme.php');
			$this->theme = new _FW_Component_Theme();
		}
	}

	/**
	 * @internal
	 */
	public function _check_requirements()
	{
		if (is_admin() && !$this->manifest->check_requirements()) {
			FW_Flash_Messages::add(
				'fw_requirements',
				__('Framework requirements not met: ') . $this->manifest->get_not_met_requirement_text(),
				'warning'
			);
		}
	}
}

/**
 * All framework components should extend this
 */
abstract class FW_Component
{
	/**
	 * Called after all components instances was created
	 */
	abstract protected function _init();

	/**
	 * If _init() was called
	 * @var bool
	 */
	private $initialized = false;

	/**
	 * @internal
	 */
	final public function _call_init()
	{
		if ($this->initialized) {
			trigger_error('Already initialized', E_USER_ERROR);
		} else {
			$this->initialized = true;
		}

		$this->_init();
	}
}

/**
 * @return _FW Framework instance
 */
function fw() {
	static $FW = null; // cache

	if ($FW === null) {
		$FW = new _Fw();
	}

	return $FW;
}
