<?php
namespace Querformatik\HelfenKannJeder\Service;

class SupportService implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * @var \Querformatik\HelfenKannJeder\Domain\Repository\SupporterRepository
	 * @inject
	 */
	private $supporterRepository;

	/**
	 * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository
	 * @inject
	 */
	private $frontendUserGroupRepository;

	private $defaultSupporter;
	private $searchedGroups;
	private $allSupporter;

	public function setDefaultSupporter($defaultSupporter) {
		$this->defaultSupporter = $defaultSupporter;
	}

	public function findSupporter($location, $supporterGroupId, $organisationtype=null) {
		$this->allSupporter = array();
		$this->searchedGroups = array();
		$searchIds = array($supporterGroupId);
		if ($organisationtype instanceof \Querformatik\HelfenKannJeder\Domain\Model\OrganisationType
			&& $organisationtype->getFegroup() instanceof \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup) {
			$searchIds[] = $organisationtype->getFegroup()->getUid();
		}
		if (is_array($location) && count($location) > 0) {
			$firstLocation = current($location);
			foreach ($firstLocation as $typeLevel => $nameLevel) {
				if (in_array($typeLevel, array("locality", "administrative_area_level_1", "administrative_area_level_2", "country"))) {
					$groups = $this->frontendUserGroupRepository->findByTitle((string)$nameLevel." (".$typeLevel.")");
					$this->searchedGroups[] = (string)$nameLevel." (".$typeLevel.")";
					if (count($groups) != 0) {
						$group = $groups[0];
						$supporter = $this->supporterRepository->findByUsergroups(array_merge(array($group->getUid()), $searchIds));
						if (count ($supporter) > 0) break;
					}
					$groups = $this->frontendUserGroupRepository->findByTitle((string)$nameLevel);
					$this->searchedGroups[] = (string)$nameLevel;
					if (count($groups) != 0) {
						$group = $groups[0];
						$supporter = $this->supporterRepository->findByUsergroups(array_merge(array($group->getUid()), $searchIds));
						if (count ($supporter) > 0) break;
					}
				}
			}
			$this->allSupporter = $supporter;
			$supporter = $supporter[mt_rand(0,count($supporter)-1)];
		} else {
			$supporter = null;
		}

		if (!($supporter instanceof \Querformatik\HelfenKannJeder\Domain\Model\Supporter)) {
			$supporter = $this->supporterRepository->findByUid($this->defaultSupporter);
		}
		return $supporter;
	}

	public function getSearchedGroups() {
		return $this->searchedGroups;
	}

	public function getAllSupporter() {
		return $this->allSupporter;
	}
}
?>
