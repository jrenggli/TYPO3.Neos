<?php
namespace TYPO3\Neos\TypoScript;

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
use TYPO3\Flow\Security\Authorization\AccessDecisionManagerInterface;
use TYPO3\Neos\Domain\Service\ContentContext;
use TYPO3\Neos\Service\ContentElementWrappingService;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TypoScript\TypoScriptObjects\AbstractTypoScriptObject;

/**
 * Adds meta data attributes to the processed Content Element
 */
class ContentElementWrappingImplementation extends AbstractTypoScriptObject {

	/**
	 * @Flow\Inject
	 * @var AccessDecisionManagerInterface
	 */
	protected $accessDecisionManager;

	/**
	 * @Flow\Inject
	 * @var ContentElementWrappingService
	 */
	protected $contentElementWrappingService;

	/**
	 * The string to be processed
	 *
	 * @var string
	 */
	protected $value;

	/**
	 * @param string $value
	 * @return void
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function getValue() {
		return $this->tsValue('value');
	}

	/**
	 * Evaluate this TypoScript object and return the result
	 *
	 * @return mixed
	 */
	public function evaluate() {
		$content = $this->getValue();
		if (!$this->accessDecisionManager->hasAccessToResource('TYPO3_Neos_Backend_GeneralAccess')) {
			return $content;
		}
		$currentContext = $this->tsRuntime->getCurrentContext();
		/** @var $node NodeInterface */
		$node = $currentContext['node'];
		/** @var $contentContext ContentContext */
		$contentContext = $node->getContext();
		if ($contentContext->getWorkspaceName() === 'live') {
			return $content;
		}

		if ($node->isRemoved()) {
			$content = '';
		}
		return $this->contentElementWrappingService->wrapContentObject($node, $this->getContentElementTypoScriptPath(), $content);
	}

	/**
	 * Returns the TypoScript path to the wrapped Content Element
	 *
	 * @return string
	 */
	protected function getContentElementTypoScriptPath() {
		$typoScriptPathSegments = explode('/', $this->path);
		$numberOfTypoScriptPathSegments = count($typoScriptPathSegments);
		if (isset($typoScriptPathSegments[$numberOfTypoScriptPathSegments - 3])
			&& $typoScriptPathSegments[$numberOfTypoScriptPathSegments - 3] === '__meta'
			&& isset($typoScriptPathSegments[$numberOfTypoScriptPathSegments - 2])
			&& $typoScriptPathSegments[$numberOfTypoScriptPathSegments - 2] === 'process') {

			// cut of the processing segments "__meta/process/contentElementWrapping<TYPO3.Neos:ContentElementWrapping>"
			return implode('/', array_slice($typoScriptPathSegments, 0, -3));
		}
		return $this->path;
	}

}
?>