<?php

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 *
 *
 * @version $Id: ObjectAccessorPostProcessorInterface.php 3460 2009-11-06 14:58:53Z k-fish $
 * @package Fluid
 * @subpackage Core\Rendering
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
interface Tx_Fluid_Core_Rendering_ObjectAccessorPostProcessorInterface {

	/**
	 * Post-Process an Object Accessor
	 *
	 * @param mixed $object the object that is currently rendered
	 * @param boolean $enabled TRUE if post processing is currently enabled.
	 * @return mixed $object the original object. If not within arguments and of type string, the value is htmlspecialchar'ed
	 */
	public function process($object, $enabled);

}
?>