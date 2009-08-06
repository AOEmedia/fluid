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

include_once(dirname(__FILE__) . '/Fixtures/EmptySyntaxTreeNode.php');
include_once(dirname(__FILE__) . '/Fixtures/Fixture_UserDomainClass.php');
require_once(dirname(__FILE__) . '/../ViewHelperBaseTestcase.php');

/**
 * Test for the "Select" Form view helper
 *
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
require_once(t3lib_extMgm::extPath('extbase', 'Tests/Base_testcase.php'));
class Tx_Fluid_ViewHelpers_Form_SelectViewHelperTest_testcase extends Tx_Fluid_ViewHelpers_ViewHelperBaseTestcase {

	/**
	 * var Tx_Fluid_ViewHelpers_Form_SelectViewHelper
	 */
	protected $viewHelper;

	public function setUp() {
		parent::setUp();
		$this->viewHelper = $this->getMock($this->buildAccessibleProxy('Tx_Fluid_ViewHelpers_Form_SelectViewHelper'), array('setErrorClassAttribute'));
		$this->injectDependenciesIntoViewHelper($this->viewHelper);
		$this->viewHelper->initializeArguments();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function selectCorrectlySetsTagName() {
		$mockTagBuilder = $this->getMock('Tx_Fluid_Core_ViewHelper_TagBuilder', array('setTagName'), array(), '', FALSE);
		$mockTagBuilder->expects($this->once())->method('setTagName')->with('select');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$arguments = new Tx_Fluid_Core_ViewHelper_Arguments(array(
			'options' => array()
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function selectCreatesExpectedOptions() {
		$mockTagBuilder = $this->getMock('Tx_Fluid_Core_ViewHelper_TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$mockTagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$mockTagBuilder->expects($this->once())->method('setContent')->with('<option value="value1">label1</option>' . chr(10) . '<option value="value2" selected="selected">label2</option>' . chr(10));
		$mockTagBuilder->expects($this->once())->method('render');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$arguments = new Tx_Fluid_Core_ViewHelper_Arguments(array(
			'options' => array(
				'value1' => 'label1',
				'value2' => 'label2'
			),
			'value' => 'value2',
			'name' => 'myName'
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function multipleSelectCreatesExpectedOptions() {
		$mockTagBuilder = $this->getMock('Tx_Fluid_Core_ViewHelper_TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$mockTagBuilder->expects($this->at(0))->method('addAttribute')->with('multiple', 'multiple');
		$mockTagBuilder->expects($this->at(1))->method('addAttribute')->with('name', 'myName[]');
		$mockTagBuilder->expects($this->once())->method('setContent')->with('<option value="value1" selected="selected">label1</option>' . chr(10) . '<option value="value2">label2</option>' . chr(10) . '<option value="value3" selected="selected">label3</option>' . chr(10));
		$mockTagBuilder->expects($this->once())->method('render');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$arguments = new Tx_Fluid_Core_ViewHelper_Arguments(array(
			'options' => array(
				'value1' => 'label1',
				'value2' => 'label2',
				'value3' => 'label3'
			),
			'value' => array('value3', 'value1'),
			'name' => 'myName',
			'multiple' => 'multiple',
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function selectOnDomainObjectsCreatesExpectedOptions() {
		$mockPersistenceBackend = $this->getMock('Tx_Extbase_Persistence_BackendInterface');
		$mockPersistenceBackend->expects($this->any())->method('getIdentifierByObject')->will($this->returnValue(NULL));

		$mockPersistenceManager = $this->getMock('Tx_Extbase_Persistence_ManagerInterface');
		$mockPersistenceManager->expects($this->any())->method('getBackend')->will($this->returnValue($mockPersistenceBackend));
		$this->viewHelper->injectPersistenceManager($mockPersistenceManager);

		$mockTagBuilder = $this->getMock('Tx_Fluid_Core_ViewHelper_TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$mockTagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$mockTagBuilder->expects($this->once())->method('setContent')->with('<option value="1">Ingmar</option>' . chr(10) . '<option value="2" selected="selected">Sebastian</option>' . chr(10) . '<option value="3">Robert</option>' . chr(10));
		$mockTagBuilder->expects($this->once())->method('render');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$user_is = new Tx_Fluid_ViewHelpers_Fixtures_UserDomainClass(1, 'Ingmar', 'Schlecht');
		$user_sk = new Tx_Fluid_ViewHelpers_Fixtures_UserDomainClass(2, 'Sebastian', 'Kurfuerst');
		$user_rl = new Tx_Fluid_ViewHelpers_Fixtures_UserDomainClass(3, 'Robert', 'Lemke');

		$arguments = new Tx_Fluid_Core_ViewHelper_Arguments(array(
			'options' => array(
				$user_is,
				$user_sk,
				$user_rl
			),
			'value' => $user_sk,
			'optionValueField' => 'id',
			'optionLabelField' => 'firstName',
			'name' => 'myName'
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function multipleSelectOnDomainObjectsCreatesExpectedOptions() {
		$mockTagBuilder = $this->getMock('Tx_Fluid_Core_ViewHelper_TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$mockTagBuilder->expects($this->at(0))->method('addAttribute')->with('multiple', 'multiple');
		$mockTagBuilder->expects($this->at(1))->method('addAttribute')->with('name', 'myName[]');
		$mockTagBuilder->expects($this->once())->method('setContent')->with('<option value="1" selected="selected">Schlecht</option>' . chr(10) . '<option value="2">Kurfuerst</option>' . chr(10) . '<option value="3" selected="selected">Lemke</option>' . chr(10));
		$mockTagBuilder->expects($this->once())->method('render');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$user_is = new Tx_Fluid_ViewHelpers_Fixtures_UserDomainClass(1, 'Ingmar', 'Schlecht');
		$user_sk = new Tx_Fluid_ViewHelpers_Fixtures_UserDomainClass(2, 'Sebastian', 'Kurfuerst');
		$user_rl = new Tx_Fluid_ViewHelpers_Fixtures_UserDomainClass(3, 'Robert', 'Lemke');

		$arguments = new Tx_Fluid_Core_ViewHelper_Arguments(array(
			'options' => array(
				$user_is,
				$user_sk,
				$user_rl
			),
			'value' => array($user_rl, $user_is),
			'optionValueField' => 'id',
			'optionLabelField' => 'lastName',
			'name' => 'myName',
			'multiple' => 'multiple'
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function selectWithoutFurtherConfigurationOnDomainObjectsUsesUUIDForValueAndLabel() {
		$mockPersistenceBackend = $this->getMock('Tx_Extbase_Persistence_BackendInterface');
		$mockPersistenceBackend->expects($this->any())->method('getIdentifierByObject')->will($this->returnValue('fakeUUID'));

		$mockPersistenceManager = $this->getMock('Tx_Extbase_Persistence_ManagerInterface');
		$mockPersistenceManager->expects($this->any())->method('getBackend')->will($this->returnValue($mockPersistenceBackend));
		$this->viewHelper->injectPersistenceManager($mockPersistenceManager);

		$mockTagBuilder = $this->getMock('Tx_Fluid_Core_ViewHelper_TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$mockTagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$mockTagBuilder->expects($this->once())->method('setContent')->with('<option value="fakeUUID">fakeUUID</option>' . chr(10));
		$mockTagBuilder->expects($this->once())->method('render');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$user = new Tx_Fluid_ViewHelpers_Fixtures_UserDomainClass(1, 'Ingmar', 'Schlecht');

		$arguments = new Tx_Fluid_Core_ViewHelper_Arguments(array(
			'options' => array(
				$user
			),
			'name' => 'myName'
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function selectWithoutFurtherConfigurationOnDomainObjectsUsesToStringForLabelIfAvailable() {
		$mockPersistenceBackend = $this->getMock('Tx_Extbase_Persistence_BackendInterface');
		$mockPersistenceBackend->expects($this->any())->method('getIdentifierByObject')->will($this->returnValue('fakeUUID'));

		$mockPersistenceManager = $this->getMock('Tx_Extbase_Persistence_ManagerInterface');
		$mockPersistenceManager->expects($this->any())->method('getBackend')->will($this->returnValue($mockPersistenceBackend));
		$this->viewHelper->injectPersistenceManager($mockPersistenceManager);

		$mockTagBuilder = $this->getMock('Tx_Fluid_Core_ViewHelper_TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$mockTagBuilder->expects($this->once())->method('addAttribute')->with('name', 'myName');
		$mockTagBuilder->expects($this->once())->method('setContent')->with('<option value="fakeUUID">toStringResult</option>' . chr(10));
		$mockTagBuilder->expects($this->once())->method('render');
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$user = $this->getMock('Tx_Fluid_ViewHelpers_Fixtures_UserDomainClass', array('__toString'), array(1, 'Ingmar', 'Schlecht'));
		$user->expects($this->atLeastOnce())->method('__toString')->will($this->returnValue('toStringResult'));

		$arguments = new Tx_Fluid_Core_ViewHelper_Arguments(array(
			'options' => array(
				$user
			),
			'name' => 'myName'
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 * @expectedException Tx_Fluid_Core_ViewHelper_Exception
	 */
	public function selectOnDomainObjectsThrowsExceptionIfNoValueCanBeFound() {
		$mockPersistenceBackend = $this->getMock('Tx_Extbase_Persistence_BackendInterface');
		$mockPersistenceBackend->expects($this->any())->method('getIdentifierByObject')->will($this->returnValue(NULL));

		$mockPersistenceManager = $this->getMock('Tx_Extbase_Persistence_ManagerInterface');
		$mockPersistenceManager->expects($this->any())->method('getBackend')->will($this->returnValue($mockPersistenceBackend));
		$this->viewHelper->injectPersistenceManager($mockPersistenceManager);

		$mockTagBuilder = $this->getMock('Tx_Fluid_Core_ViewHelper_TagBuilder', array('addAttribute', 'setContent', 'render'), array(), '', FALSE);
		$this->viewHelper->injectTagBuilder($mockTagBuilder);

		$user = new Tx_Fluid_ViewHelpers_Fixtures_UserDomainClass(1, 'Ingmar', 'Schlecht');

		$arguments = new Tx_Fluid_Core_ViewHelper_Arguments(array(
			'options' => array(
				$user
			),
			'name' => 'myName'
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->initialize();
		$this->viewHelper->render();
	}

	/**
	 * @test
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function renderCallsSetErrorClassAttribute() {
		$arguments = new Tx_Fluid_Core_ViewHelper_Arguments(array(
			'options' => array()
		));
		$this->viewHelper->setArguments($arguments);

		$this->viewHelper->expects($this->once())->method('setErrorClassAttribute');
		$this->viewHelper->render();
	}
}

?>