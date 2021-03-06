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
namespace Querformatik\HelfenKannJeder\Domain\Model;

/**
 * "Helfen Kann Jeder" Project
 *
 * @description: This class represents addresses of an organisation.
 * @author: Valentin Zickner
 *    Querformatik UG (haftungsbeschraenkt)
 *    Technisches Hilfswerk Karlsruhe
 * @date: 2011-08-17
 */
class Address extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {
	/**
	 * @var \Querformatik\HelfenKannJeder\Domain\Model\Organisation
	 */
	protected $organisation;

	/**
	 * @var string
	 */
	protected $addressappendix;

	/**
	 * @var string
	 */
	protected $street;

	/**
	 * @var string
	 */
	protected $city;

	/**
	 * @var string
	 */
	protected $zipcode;

	/**
	 * @var float
	 */
	protected $longitude;

	/**
	 * @var float
	 */
	protected $latitude;

	/**
	 * @var string
	 */
	protected $telephone;

	/**
	 * @var string
	 */
	protected $website;

	/**
	 * @var \Querformatik\HelfenKannJeder\Domain\Model\AddressDraft
	 * @lazy
	 */
	protected $reference;


	public function getOrganisation() {
		return $this->organisation;
	}

	public function setOrganisation($organisation) {
		$this->organisation = $organisation;
	}

	public function getAddressappendix() {
		return $this->addressappendix;
	}

	public function setAddressappendix($addressappendix) {
		$this->addressappendix = $addressappendix;
	}

	public function getStreet() {
		return $this->street;
	}

	public function setStreet($street) {
		$this->street = $street;
	}

	public function getCity() {
		return $this->city;
	}

	public function setCity($city) {
		$this->city = $city;
	}

	public function getZipcode() {
		return $this->zipcode;
	}

	public function setZipcode($zipcode) {
		$this->zipcode = $zipcode;
	}

	public function getLongitude() {
		return $this->longitude;
	}

	public function setLongitude($longitude) {
		$this->longitude = $longitude;
	}

	public function getLatitude() {
		return $this->latitude;
	}

	public function setLatitude($latitude) {
		$this->latitude = $latitude;
	}

	public function getTelephone() {
		return $this->telephone;
	}

	public function setTelephone($telephone) {
		$this->telephone = $telephone;
	}

	public function getAddress() {
		return $this->getStreet() . '\n' . (($this->getZipcode() == 0) ? '' : $this->getZipcode()) . ' ' . $this->getCity();
	}

	// TODO: Out source to validate class
	public function validate($googleMapsService) {
		$listedCitys = $googleMapsService->calculateCityAndDepartment('Germany, ' . $this->getAddress());
		if (count($listedCitys) >= 1) {
			$cityInfo = $listedCitys[0];
			$this->setZipcode($cityInfo['postal_code']);
			$this->setCity($cityInfo['locality']);
			$this->setLatitude($cityInfo['latitude']);
			$this->setLongitude($cityInfo['longitude']);
			return TRUE;
		} else {
			$this->setLatitude(0.0000);
			$this->setLongitude(0.0000);
			return $listedCitys;
		}
	}

	/**
	 * @return void
	 */
	public function setReference($reference) {
		$this->reference = $reference;
	}

	public function getReference() {
		return $this->reference;
	}

	public function setWebsite($website) {
		$this->website = $website;
	}

	public function getWebsite() {
		return $this->website;
	}
}
