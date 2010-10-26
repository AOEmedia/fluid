<?php

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * This class is a TypoScript view helper for the Fluid templating engine.
 *
 */
class Tx_Fluid_ViewHelpers_CObjectViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * @var tslib_cObj
	 */
	protected $contentObject;

	/**
	 * @var array
	 */
	protected $typoScriptSetup;

	/**
	 * @var	t3lib_fe contains a backup of the current $GLOBALS['TSFE'] if used in BE mode
	 */
	protected $tsfeBackup;

	/**
	 * Constructor. Used to create an instance of tslib_cObj used by the render() method.
	 *
	 * @param tslib_cObj $contentObject injector for tslib_cObj (optional)
	 * @param array $typoScriptSetup global TypoScript setup (optional)
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function __construct($contentObject = NULL, array $typoScriptSetup = NULL) {
		$this->contentObject = $contentObject !== NULL ? $contentObject : t3lib_div::makeInstance('tslib_cObj');
		if ($typoScriptSetup !== NULL) {
			$this->typoScriptSetup = $typoScriptSetup;
		} else {
			$configurationManager = Tx_Extbase_Dispatcher::getConfigurationManager();
			if ($configurationManager === NULL) {
				$configurationManager = t3lib_div::makeInstance('Tx_Extbase_Configuration_FrontendConfigurationManager');
				$configurationManager->setContentObject($this->contentObject);
			}
			$this->typoScriptSetup = $configurationManager->loadTypoScriptSetup();
		}
	}

	/**
	 * Renders the TypoScript object in the given TypoScript setup path.
	 *
	 * @param string $typoscriptObjectPath the TypoScript setup path of the TypoScript object to render
	 * @param mixed $data the data to be used for rendering the cObject. Can be an object, array or string. If this argument is not set, child nodes will be used
	 * @param string $currentValueKey
	 * @return string the content of the rendered TypoScript object
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @author Niels Pardon <mail@niels-pardon.de>
	 */
	public function render($typoscriptObjectPath, $data = NULL, $currentValueKey = NULL) {
		if (TYPO3_MODE === 'BE') {
			$this->simulateFrontendEnvironment();
		}

		if ($data === NULL) {
			$data = $this->renderChildren();
		}
		$currentValue = NULL;
		if (is_object($data)) {
			$data = Tx_Extbase_Reflection_ObjectAccess::getAccessibleProperties($data);
		} elseif (is_string($data)) {
			$currentValue = $data;
			$data = array($data);
		}
		$this->contentObject->start($data);
		if ($currentValue !== NULL) {
			$this->contentObject->setCurrentVal($currentValue);
		} elseif ($currentValueKey !== NULL && isset($data[$currentValueKey])) {
			$this->contentObject->setCurrentVal($data[$currentValueKey]);
		}

		$pathSegments = t3lib_div::trimExplode('.', $typoscriptObjectPath);
		$lastSegment = array_pop($pathSegments);
		$setup = $this->typoScriptSetup;
		foreach ($pathSegments as $segment) {
			if (!array_key_exists($segment . '.', $setup)) {
				throw new Tx_Fluid_Core_ViewHelper_Exception('TypoScript object path "' . htmlspecialchars($typoscriptObjectPath) . '" does not exist' , 1253191023);
			}
			$setup = $setup[$segment . '.'];
		}
		$content = $this->contentObject->cObjGetSingle($setup[$lastSegment], $setup[$lastSegment . '.']);

		if (TYPO3_MODE === 'BE') {
			$this->resetFrontendEnvironment();
		}

		return $content;
	}

	/**
	 * Sets the $TSFE->cObjectDepthCounter in Backend mode
	 * This somewhat hacky work around is currently needed because the cObjGetSingle() function of tslib_cObj relies on this setting
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function simulateFrontendEnvironment() {
		$this->tsfeBackup = isset($GLOBALS['TSFE']) ? $GLOBALS['TSFE'] : NULL;
		$GLOBALS['TSFE'] = new stdClass();
		$GLOBALS['TSFE']->cObjectDepthCounter = 100;
	}

	/**
	 * Resets $GLOBALS['TSFE'] if it was previously changed by simulateFrontendEnvironment()
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @see simulateFrontendEnvironment()
	 */
	protected function resetFrontendEnvironment() {
		$GLOBALS['TSFE'] = $this->tsfeBackup;
	}
}

?>