<?php
/*
 * Copyright (C) 2015 Valentin Zickner <valentin.zickner@helfenkannjeder.de>
 *
 * This file is part of HelfenKannJeder.
 *
 * HelfenKannJeder is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * HelfenKannJeder is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with HelfenKannJeder.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Querformatik\HelfenKannJeder\Tests\Functional;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Functional test for DraftToLiveService,
 * see also http://wiki.typo3.org/Functional_testing
 */
class DraftToLiveServiceTest extends \TYPO3\CMS\Core\Tests\FunctionalTestCase {

	const EXTENSION_PATH = 'typo3conf/ext/helfen_kann_jeder/';

	/**
	 * @var array
	 */
	protected $coreExtensionsToLoad = array('extbase', 'fluid', 'workspaces');

	/**
	 * @var array
	 */
	protected $testExtensionsToLoad = array(
		'typo3conf/ext/qu_base',
		self::EXTENSION_PATH,
	);

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface The object manager
	 */
	protected $objectManager;


	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 */
	protected $persistentManager;

	/**
	 * @var \Querformatik\HelfenKannJeder\Domain\Repository\OrganisationDraftRepository
	 */
	protected $draftRepository;

	/**
	 * @var \Querformatik\HelfenKannJeder\Domain\Repository\OrganisationRepository
	 */
	protected $liveRepository;

	/**
	 * @var \Querformatik\HelfenKannJeder\Service\DraftToLiveService
	 */
	protected $draftToLiveService;

	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->importDataSet(ORIGINAL_ROOT . 'typo3/sysext/core/Tests/Functional/Fixtures/pages.xml');

		$this->setUpFrontendRootPage(
			1,
			array(
				self::EXTENSION_PATH . 'Tests/Functional/Fixtures/TypoScript.ts',
			)
		);

		$this->importDataSet(ORIGINAL_ROOT . self::EXTENSION_PATH . 'Tests/Functional/Fixtures/OrganisationData.xml');

		$this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->persistentManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
		$this->draftRepository =
			$this->objectManager->get('Querformatik\\HelfenKannJeder\\Domain\\Repository\\OrganisationDraftRepository');
		$this->liveRepository =
			$this->objectManager->get('Querformatik\\HelfenKannJeder\\Domain\\Repository\\OrganisationRepository');
		$this->draftToLiveService = $this->objectManager->get('Querformatik\\HelfenKannJeder\\Service\\DraftToLiveService');
	}

	/**
	 * @test
	 * @return void
	 */
	public function testCountDraftObjects() {
		$this->assertSame(2, count($this->draftRepository->findAll()));
	}

	/**
	 * Test the completeness of a new synchronisation
	 *
	 * @test
	 * @return void
	 */
	public function testSyncObjects() {
		$firstOrganisation = $this->draftRepository->findByUid(1);
		$this->assertNotSame(NULL, $firstOrganisation);
		$this->draftToLiveService->draft2live($firstOrganisation);
		$liveObjects = $this->liveRepository->findAll();
		$this->assertSame(1, count($liveObjects));

		$draft = $this->draftRepository->findByUid(1);
		$live = $draft->getReference();
		$this->assertNotNull($live);

		// Test
		$this->assertEquals('THW Karlsruhe', $live->getName());
		$this->assertEquals('49.037659', $live->getLatitude());
		$this->assertEquals('8.352747', $live->getLongitude());
		$this->assertEquals('http://www.thw-karlsruhe.de', $live->getWebsite());

		$this->assertEquals($draft->getOrganisationtype(), $live->getOrganisationtype());

		$this->assertEquals('THW', $live->getOrganisationtype()->getAcronym());

		// Employees and contactperson
		$this->assertEquals(2, count($live->getEmployees()));
		$this->assertEquals(1, $live->getContactpersons()->count());

		$contactperson = $live->getContactpersons()->current();
		$this->assertEquals('Domjahn', $contactperson->getSurname());
		$this->assertEquals('David', $contactperson->getPrename());
		$this->assertEquals('nospam@helfenkannjeder.de', $contactperson->getMail());
	}
}
