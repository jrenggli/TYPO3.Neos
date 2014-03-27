<?php
namespace TYPO3\Neos\Command;

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
use TYPO3\Flow\Cli\CommandController;

/**
 * Generate translations from XLIFF files ready for usage in the UI. It is a developer tool and not relevant for end-users.
 */
class TranslateCommandController extends CommandController {

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
	 * Create JSON file to be used in the Neos UI from an XLIFF file.
	 *
	 * Use the "--locale all" argument to render the Json file for all locales.
	 *
	 * TODO: where
	 *
	 * @param string $xliffPath The path to the translation files
	 * @param string $locale The locale (defaults to default locale)
	 * @param string $sourceCatalog The source catalog to be used (defaults to Main)
	 * @param string $jsonFile The json file to be created (resource path)
	 * @param string $package The package key (defaults to TYPO3.Neos)
	 * @param boolean $verbose Set this to true to view errors and warnings (default only notices are shown)
	 */
	public function createJsonFromXliffCommand($xliffPath = 'Private/Translations', $locale = NULL, $sourceCatalog = 'Main', $jsonFile = NULL, $package = 'TYPO3.Neos', $verbose = FALSE) {
		if ($locale === NULL) {
			$locale = $this->defaultLocale;
		}

		$result = new Result();

		if ($locale === 'all') {
			$path = 'resource://' . $package . '/' . Files::getNormalizedPath($xliffPath);

			foreach (new \DirectoryIterator($path) as $fileInfo) {
				$localePathPart = substr($fileInfo->getPathname(), -2);
				if (!$fileInfo->isDir() || $localePathPart === '/.' || $localePathPart === '..') {
					continue;
				}
				$result->merge($this->xliffService->createJsonFile($localePathPart, $package, $fileInfo->getPathname(), $sourceCatalog, $jsonFile));
			}
		} else {
			$result->merge($this->xliffService->createJsonFile($locale, $package, 'resource://' . $package . '/' . Files::getNormalizedPath($xliffPath) . $locale, $sourceCatalog, $jsonFile));
		}

		$this->render($result, $verbose);
	}

	/**
	 * Render the call to the xliff service and render the output for the command line interface
	 *
	 * @param Result $result
	 * @param boolean $verbose
	 * @return void
	 */
	protected function render(Result $result, $verbose) {
		$outputLines = array();
		if ($result->hasNotices()) {
			/** @var \TYPO3\Flow\Error\Notice $notice */
			foreach ($result->getNotices() as $notice) {
				$outputLines[] = $notice->getMessage();
			}
		}

		if ($verbose && $result->hasErrors()) {
			/** @var \TYPO3\Flow\Error\Error $error */
			foreach ($result->getErrors() as $error) {
				$outputLines[] = $error->getMessage();
			}
		}

		if ($verbose && $result->hasWarnings()) {
			/** @var \TYPO3\Flow\Error\Warning $warning */
			foreach ($result->getWarnings() as $warning) {
				$outputLines[] = $warning->getMessage();
			}
		}

		$outputLines = array_unique($outputLines);

		foreach ($outputLines as $outputLine) {
			$this->outputLine($outputLine);
		}
	}

}