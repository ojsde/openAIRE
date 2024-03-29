<?php

/**
 * @file OAIMetadataFormatPlugin_OpenAIRE.php
 *
 * Copyright (c) 2014-2023 Simon Fraser University
 * Copyright (c) 2003-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormatPlugin_OpenAIRE
 * @ingroup oai_format_openaire
 * @see OAI
 *
 * @brief OAI JATS XML format plugin for OpenAIRE.
 */

namespace APP\plugins\generic\openAIRE;

use PKP\plugins\OAIMetadataFormatPlugin;

class OAIMetadataFormatPlugin_OpenAIRE extends OAIMetadataFormatPlugin {
	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'OAIMetadataFormatPlugin_OpenAIRE';
	}

	/**
	 * @copydoc Plugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.oaiMetadata.openAIRE.displayName');
	}

	/**
	 * @copydoc Plugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.oaiMetadata.openAIRE.description');
	}

	function getFormatClass() {
		return '\APP\plugins\generic\openAIRE\OAIMetadataFormat_OpenAIRE';
	}

	static function getMetadataPrefix() {
		return 'oai_openaire_jats';
	}

	static function getSchema() {
		return 'https://jats.nlm.nih.gov/publishing/0.4/xsd/JATS-journalpublishing0.xsd';
	}

	static function getNamespace() {
		return 'http://jats.nlm.nih.gov';
	}
}
