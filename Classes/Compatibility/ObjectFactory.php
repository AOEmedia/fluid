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
 * @package
 * @subpackage
 * @version $Id:$
 */
/**
 * Class emulating the object factory for Fluid v4.
 *
 * DO NOT USE DIRECTLY!
 * @internal
 */
class Tx_Fluid_Compatibility_ObjectFactory implements t3lib_Singleton {

	protected $injectors = array(
		'Tx_Fluid_Core_ViewHelper_AbstractViewHelper' => array(
			'injectValidatorResolver' => 'Tx_Extbase_Validation_ValidatorResolver',
			'injectReflectionService' => 'Tx_Extbase_Reflection_Service'
		),
		'Tx_Fluid_Core_ViewHelper_TagBasedViewHelper' => array(
			'injectTagBuilder' => 'Tx_Fluid_Core_ViewHelper_TagBuilder'
		),
		'Tx_Fluid_Core_Parser_ParsingState' => array(
			'injectVariableContainer' => 'Tx_Fluid_Core_ViewHelper_TemplateVariableContainer'
		),
		'Tx_Fluid_Core_Parser_TemplateParser' => array(
			'injectObjectFactory' => 'Tx_Fluid_Compatibility_ObjectFactory'
		),
		'Tx_Fluid_Core_Rendering_RenderingContext' => array(
			'injectObjectFactory' => 'Tx_Fluid_Compatibility_ObjectFactory'
		)
	);

	/**
	 * Create a certain object name
	 *
	 * DO NOT USE DIRECTLY!
	 *
	 * @param string $objectName Object name to create
	 * @retrun object Object which was created
	 * @internal
	 */
	public function create($objectName) {
		$constructorArguments = func_get_args();

		$object = call_user_func_array(array('t3lib_div', 'makeInstance'), $constructorArguments);
		$injectObjects = array();

		if (isset($this->injectors[$objectName])) {
			$injectObjects = array_merge($injectObjects, $this->injectors[$objectName]);
		}
		foreach (class_parents($objectName) as $parentObjectName) {
			if (isset($this->injectors[$parentObjectName])) {
				$injectObjects = array_merge($injectObjects, $this->injectors[$parentObjectName]);
			}
		}
		foreach (class_implements($objectName) as $parentObjectName) {
			if (isset($this->injectors[$parentObjectName])) {
				$injectObjects = array_merge($injectObjects, $this->injectors[$parentObjectName]);
			}
		}
		foreach ($injectObjects as $injectMethodName => $injectObjectName) {
			call_user_func(array($object, $injectMethodName), $this->create($injectObjectName));
		}
		return $object;
	}
}

?>