<?php

/*                                                                        *
 * This script is backported from the FLOW3 package "TYPO3.Fluid".        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Enter description here...
 */
class Tx_Fluid_Core_Parser_Fixtures_PostParseFacetViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper implements Tx_Fluid_Core_ViewHelper_Facets_PostParseInterface {

	public static $wasCalled = FALSE;

	public function __construct() {
	}

	static public function postParseEvent(Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode $viewHelperNode, array $arguments, Tx_Fluid_Core_ViewHelper_TemplateVariableContainer $variableContainer) {
		self::$wasCalled = TRUE;
	}

	public function initializeArguments() {
	}

	public function render() {
	}
}

?>