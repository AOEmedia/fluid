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
 * @version $Id: ViewHelperNodeTest.php 2895 2009-07-27 15:45:24Z sebastian $
 */
/**
 * Testcase for [insert classname here]
 *
 * @version $Id: ViewHelperNodeTest.php 2895 2009-07-27 15:45:24Z sebastian $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
include_once(dirname(__FILE__) . '/../Fixtures/ChildNodeAccessFacetViewHelper.php');
require_once(t3lib_extMgm::extPath('extbase', 'Tests/Base_testcase.php'));
class Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNodeTest_testcase extends Tx_Extbase_Base_testcase {

	/**
	 * Rendering Context
	 * @var Tx_Fluid_Core_Rendering_RenderingContext
	 */
	protected $renderingContext;

	/**
	 * Object factory mock
	 * @var Tx_Fluid_Compatibility_ObjectFactory
	 */
	protected $mockObjectFactory;

	/**
	 * Template Variable Container
	 * @var Tx_Fluid_Core_ViewHelper_TemplateVariableContainer
	 */
	protected $templateVariableContainer;

	/**
	 *
	 * @var Tx_Extbase_MVC_Controller_ControllerContext
	 */
	protected $controllerContext;

	/**
	 * @var Tx_Fluid_Core_ViewHelper_ViewHelperVariableContainer
	 */
	protected $viewHelperVariableContainer;

	/**
	 * Setup fixture
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function setUp() {
		$this->renderingContext = new Tx_Fluid_Core_Rendering_RenderingContext();

		$this->mockObjectFactory = $this->getMock('Tx_Fluid_Compatibility_ObjectFactory');
		$this->renderingContext->injectObjectFactory($this->mockObjectFactory);

		$this->templateVariableContainer = $this->getMock('Tx_Fluid_Core_ViewHelper_TemplateVariableContainer');
		$this->renderingContext->setTemplateVariableContainer($this->templateVariableContainer);

		$this->controllerContext = $this->getMock('Tx_Extbase_MVC_Controller_ControllerContext');
		$this->renderingContext->setControllerContext($this->controllerContext);

		$this->viewHelperVariableContainer = $this->getMock('Tx_Fluid_Core_ViewHelper_ViewHelperVariableContainer');
		$this->renderingContext->setViewHelperVariableContainer($this->viewHelperVariableContainer);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function constructorSetsViewHelperClassNameAndArguments() {
		$viewHelperClassName = 'MyViewHelperClassName';
		$arguments = array('foo' => 'bar');
		$viewHelperNode = $this->getMock($this->buildAccessibleProxy('Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode'), array('dummy'), array($viewHelperClassName, $arguments));

		$this->assertEquals($viewHelperClassName, $viewHelperNode->getViewHelperClassName());
		$this->assertEquals($arguments, $viewHelperNode->_get('arguments'));
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function childNodeAccessFacetWorksAsExpected() {
		$childNode = $this->getMock('Tx_Fluid_Core_Parser_SyntaxTree_TextNode', array(), array('foo'));

		$mockViewHelper = $this->getMock('Tx_Fluid_Core_Parser_Fixtures_ChildNodeAccessFacetViewHelper', array('setChildNodes', 'initializeArguments', 'render', 'prepareArguments', 'setRenderingContext', 'isObjectAccessorPostProcessorEnabled'));

		$mockViewHelperArguments = $this->getMock('Tx_Fluid_Core_ViewHelper_Arguments', array(), array(), '', FALSE);

		$this->mockObjectFactory->expects($this->at(0))->method('create')->with('Tx_Fluid_ViewHelpers_TestViewHelper')->will($this->returnValue($mockViewHelper));
		$this->mockObjectFactory->expects($this->at(1))->method('create')->with('Tx_Fluid_Core_ViewHelper_Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode = new Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode('Tx_Fluid_ViewHelpers_TestViewHelper', array());
		$viewHelperNode->addChildNode($childNode);

		$mockViewHelper->expects($this->once())->method('setChildNodes')->with($this->equalTo(array($childNode)));
		$mockViewHelper->expects($this->once())->method('isObjectAccessorPostProcessorEnabled')->will($this->returnValue(TRUE));
		//$mockViewHelper->expects($this->once())->method('setRenderingContext')->with($this->renderingContext);

		$viewHelperNode->setRenderingContext($this->renderingContext);
		$viewHelperNode->evaluate();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function validateArgumentsIsCalledByViewHelperNode() {
		$mockViewHelper = $this->getMock('Tx_Fluid_Core_ViewHelper_AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments'));
		$mockViewHelper->expects($this->once())->method('validateArguments');

		$mockViewHelperArguments = $this->getMock('Tx_Fluid_Core_ViewHelper_Arguments', array(), array(), '', FALSE);

		$this->mockObjectFactory->expects($this->at(0))->method('create')->with('Tx_Fluid_Core_ViewHelper_AbstractViewHelper')->will($this->returnValue($mockViewHelper));
		$this->mockObjectFactory->expects($this->at(1))->method('create')->with('Tx_Fluid_Core_ViewHelper_Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode = new Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode('Tx_Fluid_Core_ViewHelper_AbstractViewHelper', array());

		$viewHelperNode->setRenderingContext($this->renderingContext);
		$viewHelperNode->evaluate();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderMethodIsCalledWithCorrectArguments() {
		$arguments = array(
			'param0' => new Tx_Fluid_Core_ViewHelper_ArgumentDefinition('param1', 'string', 'Hallo', TRUE, null, FALSE),
			'param1' => new Tx_Fluid_Core_ViewHelper_ArgumentDefinition('param1', 'string', 'Hallo', TRUE, null, TRUE),
			'param2' => new Tx_Fluid_Core_ViewHelper_ArgumentDefinition('param2', 'string', 'Hallo', TRUE, null, TRUE)
		);

		$mockViewHelper = $this->getMock('Tx_Fluid_Core_ViewHelper_AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments'));
		$mockViewHelper->expects($this->once())->method('prepareArguments')->will($this->returnValue($arguments));
		$mockViewHelper->expects($this->once())->method('render')->with('a', 'b');

		$mockViewHelperArguments = $this->getMock('Tx_Fluid_Core_ViewHelper_Arguments', array(), array(), '', FALSE);

		$this->mockObjectFactory->expects($this->at(0))->method('create')->with('Tx_Fluid_Core_ViewHelper_AbstractViewHelper')->will($this->returnValue($mockViewHelper));
		$this->mockObjectFactory->expects($this->at(1))->method('create')->with('Tx_Fluid_Core_ViewHelper_Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode = new Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode('Tx_Fluid_Core_ViewHelper_AbstractViewHelper', array(
			'param2' => new Tx_Fluid_Core_Parser_SyntaxTree_TextNode('b'),
			'param1' => new Tx_Fluid_Core_Parser_SyntaxTree_TextNode('a'),
		));

		$viewHelperNode->setRenderingContext($this->renderingContext);
		$viewHelperNode->evaluate();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evaluateMethodPassesControllerContextToViewHelper() {
		$mockViewHelper = $this->getMock('Tx_Fluid_Core_ViewHelper_AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments', 'setControllerContext'));
		$mockViewHelper->expects($this->once())->method('setControllerContext')->with($this->controllerContext);

		$viewHelperNode = new Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode('Tx_Fluid_Core_ViewHelper_AbstractViewHelper', array());
		$mockViewHelperArguments = $this->getMock('Tx_Fluid_Core_ViewHelper_Arguments', array(), array(), '', FALSE);

		$this->mockObjectFactory->expects($this->at(0))->method('create')->with('Tx_Fluid_Core_ViewHelper_AbstractViewHelper')->will($this->returnValue($mockViewHelper));
		$this->mockObjectFactory->expects($this->at(1))->method('create')->with('Tx_Fluid_Core_ViewHelper_Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode->setRenderingContext($this->renderingContext);
		$viewHelperNode->evaluate();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function evaluateMethodPassesViewHelperVariableContainerToViewHelper() {
		$mockViewHelper = $this->getMock('Tx_Fluid_Core_ViewHelper_AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments', 'setViewHelperVariableContainer'));
		$mockViewHelper->expects($this->once())->method('setViewHelperVariableContainer')->with($this->viewHelperVariableContainer);

		$viewHelperNode = new Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode('Tx_Fluid_Core_ViewHelper_AbstractViewHelper', array());
		$mockViewHelperArguments = $this->getMock('Tx_Fluid_Core_ViewHelper_Arguments', array(), array(), '', FALSE);

		$this->mockObjectFactory->expects($this->at(0))->method('create')->with('Tx_Fluid_Core_ViewHelper_AbstractViewHelper')->will($this->returnValue($mockViewHelper));
		$this->mockObjectFactory->expects($this->at(1))->method('create')->with('Tx_Fluid_Core_ViewHelper_Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode->setRenderingContext($this->renderingContext);
		$viewHelperNode->evaluate();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	public function multipleEvaluateCallsShareTheSameViewHelperInstance() {
		$mockViewHelper = $this->getMock('Tx_Fluid_Core_ViewHelper_AbstractViewHelper', array('render', 'validateArguments', 'prepareArguments', 'setViewHelperVariableContainer'));
		$mockViewHelper->expects($this->any())->method('render')->will($this->returnValue('String'));

		$viewHelperNode = new Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode('Tx_Fluid_Core_ViewHelper_AbstractViewHelper', array());
		$mockViewHelperArguments = $this->getMock('Tx_Fluid_Core_ViewHelper_Arguments', array(), array(), '', FALSE);

		$this->mockObjectFactory->expects($this->at(0))->method('create')->with('Tx_Fluid_Core_ViewHelper_AbstractViewHelper')->will($this->returnValue($mockViewHelper));
		$this->mockObjectFactory->expects($this->at(1))->method('create')->with('Tx_Fluid_Core_ViewHelper_Arguments')->will($this->returnValue($mockViewHelperArguments));
		$this->mockObjectFactory->expects($this->at(2))->method('create')->with('Tx_Fluid_Core_ViewHelper_Arguments')->will($this->returnValue($mockViewHelperArguments));

		$viewHelperNode->setRenderingContext($this->renderingContext);
		$viewHelperNode->evaluate();
		$viewHelperNode->evaluate();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertArgumentValueCallsConvertToBooleanForArgumentsOfTypeBoolean() {
		$viewHelperNode = $this->getMock($this->buildAccessibleProxy('Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode'), array('convertToBoolean'), array(), '', FALSE);
		$viewHelperNode->_set('renderingContext', $this->renderingContext);
		$argumentViewHelperNode = $this->getMock('Tx_Fluid_Core_Parser_SyntaxTree_AbstractNode', array('evaluate'), array(), '', FALSE);
		$argumentViewHelperNode->expects($this->once())->method('evaluate')->will($this->returnValue('foo'));

		$viewHelperNode->expects($this->once())->method('convertToBoolean')->with('foo')->will($this->returnValue('bar'));

		$actualResult = $viewHelperNode->_call('convertArgumentValue', $argumentViewHelperNode, 'boolean');
		$this->assertEquals('bar', $actualResult);
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertToBooleanProperlyConvertsValuesOfTypeBoolean() {
		$viewHelperNode = $this->getMock($this->buildAccessibleProxy('Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode'), array('dummy'), array(), '', FALSE);

		$this->assertFalse($viewHelperNode->_call('convertToBoolean', FALSE));
		$this->assertTrue($viewHelperNode->_call('convertToBoolean', TRUE));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertToBooleanProperlyConvertsValuesOfTypeString() {
		$viewHelperNode = $this->getMock($this->buildAccessibleProxy('Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode'), array('dummy'), array(), '', FALSE);

		$this->assertFalse($viewHelperNode->_call('convertToBoolean', ''));
		$this->assertFalse($viewHelperNode->_call('convertToBoolean', 'false'));
		$this->assertFalse($viewHelperNode->_call('convertToBoolean', 'FALSE'));

		$this->assertTrue($viewHelperNode->_call('convertToBoolean', 'true'));
		$this->assertTrue($viewHelperNode->_call('convertToBoolean', 'TRUE'));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertToBooleanProperlyConvertsNumericValues() {
		$viewHelperNode = $this->getMock($this->buildAccessibleProxy('Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode'), array('dummy'), array(), '', FALSE);

		$this->assertFalse($viewHelperNode->_call('convertToBoolean', 0));
		$this->assertFalse($viewHelperNode->_call('convertToBoolean', -1));
		$this->assertFalse($viewHelperNode->_call('convertToBoolean', -.5));

		$this->assertTrue($viewHelperNode->_call('convertToBoolean', 1));
		$this->assertTrue($viewHelperNode->_call('convertToBoolean', .5));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertToBooleanProperlyConvertsValuesOfTypeArray() {
		$viewHelperNode = $this->getMock($this->buildAccessibleProxy('Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode'), array('dummy'), array(), '', FALSE);

		$this->assertFalse($viewHelperNode->_call('convertToBoolean', array()));

		$this->assertTrue($viewHelperNode->_call('convertToBoolean', array('foo')));
		$this->assertTrue($viewHelperNode->_call('convertToBoolean', array('foo' => 'bar')));
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function convertToBooleanProperlyConvertsObjects() {
		$viewHelperNode = $this->getMock($this->buildAccessibleProxy('Tx_Fluid_Core_Parser_SyntaxTree_ViewHelperNode'), array('dummy'), array(), '', FALSE);

		$this->assertFalse($viewHelperNode->_call('convertToBoolean', NULL));

		$this->assertTrue($viewHelperNode->_call('convertToBoolean', new stdClass()));
	}
}

?>