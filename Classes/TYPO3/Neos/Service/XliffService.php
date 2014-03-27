<?php
namespace TYPO3\Neos\Service;

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
use TYPO3\Flow\Error\Error;
use TYPO3\Flow\Error\Notice;
use TYPO3\Flow\Error\Warning;
use TYPO3\Flow\Utility\Files;

/**
 * The xliff service provides general methods for xliff related operations
 *
 * @Flow\Scope("singleton")
 */
class XliffService {

	/**
	 * @Flow\Inject(setting="userInterface.localeIncludePaths", package="TYPO3.Neos")
	 * @var array
	 */
	protected $localeIncludesPaths;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\I18n\Xliff\XliffParser
	 */
	protected $xliffParser;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Cache\Frontend\VariableFrontend
	 */
	protected $xliffToJsonTranslationsCache;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Log\SystemLoggerInterface
	 */
	protected $logger;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Error\Result
	 */
	protected $result;


	/**
	 * Return path and filename for a xliff file defined by locale, sourceCatalog, xliffPath and package
	 *
	 * @param string $locale The locale string
	 * @param string $sourceCatalog The source catalog to use
	 * @param string $xliffPath Resource pointer to the path where the xliff file is located
	 * @param string $package The package
	 * @return string
	 */
	public function getXliffPathAndFilenameFromLocaleSourceCatalogXliffPathAndPackage($locale, $sourceCatalog, $xliffPath = 'Private/Translations', $package = 'TYPO3.Neos') {
		$xliffPathAndFilename = 'resource://' . Files::getNormalizedPath($package) . Files::getNormalizedPath($xliffPath) . Files::getNormalizedPath($locale) . $sourceCatalog . '.xlf';

		return $xliffPathAndFilename;
	}

	/**
	 * Return the json array for a given locale, sourceCatalog, xliffPath and package.
	 * The array will be cached.
	 *
	 * @param string $locale The locale string
	 * @param string $sourceCatalog The source catalog to use
	 * @param string $xliffPath Resource pointer to the path where the xliff file is located
	 * @param string $package The package
	 * @return \TYPO3\Flow\Error\Result
	 */
	public function getCachedJsonArrayFromXliffFile($locale, $sourceCatalog, $xliffPath, $package) {
		$cacheIdentifier = md5(implode('#', array($locale, $sourceCatalog, $xliffPath, $package)));

		if ($this->xliffToJsonTranslationsCache->has($cacheIdentifier)) {
			$jsonArray = $this->xliffToJsonTranslationsCache->get($cacheIdentifier);
			//echo 'CACHED';
		} else {
			$xliffPathAndFilename = $this->getXliffPathAndFilenameFromLocaleSourceCatalogXliffPathAndPackage($locale, $sourceCatalog, $xliffPath, $package);
			$jsonArray = $this->parseXliffToJsonArray($xliffPathAndFilename, $package, $sourceCatalog);

			$this->xliffToJsonTranslationsCache->set($cacheIdentifier, $jsonArray);
			//echo 'UNCACHED';
		}

		return $jsonArray;
	}

	/**
	 * Read the xliff file and create the desired json array
	 *
	 * @param string $xliffFile
	 * @param string $package
	 * @param string $sourceCatalog
	 * @return string
	 */
	public function parseXliffToJsonArray($xliffFile, $package, $sourceCatalog) {
		// TODO: Check file

		/** @var array $parsedData */
		$parsedData = $this->xliffParser->getParsedData($xliffFile);
		$arrayData = array();
		foreach ($parsedData['translationUnits'] as $key => $value) {
			$valueToStore = $value[0]['target'] ? $value[0]['target'] : $value[0]['source'];
			$this->setArrayDataValue($arrayData, $package . '.' . $sourceCatalog . '.' . $key, $valueToStore);
		}

		return json_encode($arrayData);
	}

	/**
	 * Helper method to create the needed json array from a dotted xliff id
	 *
	 * @param array $arrayPointer
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	protected function setArrayDataValue(array &$arrayPointer, $key, $value) {
		$keys = explode('.', $key);

		// Extract the last key
		$lastKey = array_pop($keys);

		// Walk/build the array to the specified key
		while ($arrayKey = array_shift($keys)) {
			if (!array_key_exists($arrayKey, $arrayPointer)) {
				$arrayPointer[$arrayKey] = array();
			}
			$arrayPointer = &$arrayPointer[$arrayKey];
		}

		// Set the final key
		$arrayPointer[$lastKey] = $value;
	}

}