<?php
namespace TYPO3\Neos\Controller\Backend;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Error\Result;
use TYPO3\Flow\Utility\Files;

class UserInterfaceController extends BackendController {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Neos\Service\XliffService
	 */
	protected $xliffService;

	/**
	 * @Flow\Inject(setting="userInterface.defaultLocale", package="TYPO3.Neos")
	 * @var string
	 */
	protected $defaultLocale;


	/**
	 *
	 *
	 * @return string
	 */
	public function getI18nAction() {

		// TODO: Should be current locale
		$locale = $this->defaultLocale;
		$locale = 'de';



		$xliffPath = 'Private/Translations';
		$sourceCatalog = 'Main';
		$package = 'TYPO3.Neos';

/*
		$this->response->setExpires(time() + 3600 * 24);
		$this->response->setPublic();
		$this->response->setMaximumAge(3600 * 24);
*/

		return $this->xliffService->getCachedJsonArrayFromXliffFile($locale, $sourceCatalog, $xliffPath, $package);
	}
}