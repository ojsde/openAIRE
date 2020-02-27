<?php

/**
 * @file plugins/generic/openAIRE/OpenAIREPlugin.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OpenAIREPlugin
 * @ingroup plugins_generic_openAIRE
 *
 * @brief OpenAIRE plugin class
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class OpenAIREPlugin extends GenericPlugin {
	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null) {
		$success = parent::register($category, $path, $mainContextId);
		if ($success && $this->getEnabled($mainContextId)) {
			$this->import('OAIMetadataFormatPlugin_OpenAIRE');
			PluginRegistry::register('oaiMetadataFormats', new OAIMetadataFormatPlugin_OpenAIRE($this), $this->getPluginPath());
			$this->import('OpenAIREGatewayPlugin');
			PluginRegistry::register('gateways', new OpenAIREGatewayPlugin($this), $this->getPluginPath());

			# Handle COAR resource types in section forms
			HookRegistry::register('sectiondao::getAdditionalFieldNames', array($this, 'addSectionDAOFieldNames'));			
			HookRegistry::register('Templates::Manager::Sections::SectionForm::AdditionalMetadata', array($this, 'addSectionFormFields'));
			HookRegistry::register('sectionform::initdata', array($this, 'initDataSectionFormFields'));
			HookRegistry::register('sectionform::readuservars', array($this, 'readSectionFormFields'));
			HookRegistry::register('sectionform::execute', array($this, 'executeSectionFormFields'));

			$this->_registerTemplateResource();
		}
		return $success;
	}

	/**
	 * @copydoc Plugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.generic.openAIRE.displayName');
	}

	/**
	 * @copydoc Plugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.generic.openAIRE.description');
	}

	/**
	 * Add section settings to SectionDAO
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option SectionDAO
	 *		@option array List of additional fields
	 * ]
	 */
	public function addSectionDAOFieldNames($hookName, $args) {
		$fields =& $args[1];
		$fields[] = 'resourceType';
	}

	/**
	 * Add fields to the section editing form
	 *
	 * @param $hookName string `Templates::Manager::Sections::SectionForm::AdditionalMetadata`
	 * @param $args array [
	 *		@option array [
	 *				@option name string Hook name
	 *				@option sectionId int
	 *		]
	 *		@option Smarty
	 *		@option string
	 * ]
	 * @return bool
	 */
	public function addSectionFormFields($hookName, $args) {
		$smarty =& $args[1];
		$output =& $args[2];
		$smarty->assign('resourceTypeOptions', $this->_getResourceTypeOptions());
		$output .= $smarty->fetch($this->getTemplateResource('controllers/grids/settings/section/form/sectionFormAdditionalFields.tpl'));
		return false;
	}

	/**
	 * Initialize data when form is first loaded
	 *
	 * @param $hookName string `sectionform::initData`
	 * @parram $args array [
	 *		@option SectionForm
	 * ]
	 */
	public function initDataSectionFormFields($hookName, $args) {
		$sectionForm = $args[0];
		$request = Application::getRequest();
		$context = $request->getContext();
		$contextId = $context ? $context->getId() : CONTEXT_ID_NONE;
		$sectionDao = DAORegistry::getDAO('SectionDAO');
		$section = $sectionDao->getById($sectionForm->getSectionId(), $contextId);
		if ($section) $sectionForm->setData('resourceType', $section->getData('resourceType'));
	}

	/**
	 * Read user input from additional fields in the section editing form
	 *
	 * @param $hookName string `sectionform::readUserVars`
	 * @parram $args array [
	 *		@option SectionForm
	 *		@option array User vars
	 * ]
	 */
	public function readSectionFormFields($hookName, $args) {
		$sectionForm =& $args[0];
		$request = Application::getRequest();
		$sectionForm->setData('resourceType', $request->getUserVar('resourceType'));
	}

	/**
	 * Save additional fields in the section editing form
	 *
	 * @param $hookName string `sectionform::execute`
	 * @param $args array
	 *
	 */
	public function executeSectionFormFields($hookName, $args) {
		$sectionForm = $args[0];
		$resourceType = $sectionForm->getData('resourceType') ? $sectionForm->getData('resourceType') : '';
		if (!empty($resourceType)) {
			$sectionDao = DAORegistry::getDAO('SectionDAO');
			$section = $sectionDao->getById($sectionForm->getSectionId());			
			$section->setData('resourceType', $resourceType);
			$sectionDao->updateObject($section);
		}
	}

	/**
	 * Get a COAR Resource Type by URI. If $uri is null return all.
	 * @param $uri string
	 * @return mixed
	 */
	function _getCoarResourceType($uri = null) {
		$resourceTypes = array(
				'http://purl.org/coar/resource_type/c_6501' => 'journal article',
				'http://purl.org/coar/resource_type/c_2df8fbb1' => 'research article',
				'http://purl.org/coar/resource_type/c_dcae04bc' => 'review article',
				'http://purl.org/coar/resource_type/c_beb9' => 'data paper',
				'http://purl.org/coar/resource_type/c_7bab' => 'software paper',
				'http://purl.org/coar/resource_type/c_b239' => 'editorial',
				'http://purl.org/coar/resource_type/c_545b' => 'letter to the editor',
				'http://purl.org/coar/resource_type/c_93fc' => 'report',
				'http://purl.org/coar/resource_type/c_efa0' => 'review',
				'http://purl.org/coar/resource_type/c_ba08' => 'book review',
				'http://purl.org/coar/resource_type/c_26e4' => 'interview',
				'http://purl.org/coar/resource_type/c_8544' => 'lecture',
				'http://purl.org/coar/resource_type/c_5794' => 'conference paper',
				'http://purl.org/coar/resource_type/c_46ec' => 'thesis',
				'http://purl.org/coar/resource_type/c_8042' => 'working paper',
				'http://purl.org/coar/resource_type/c_816b' => 'preprint',
				'http://purl.org/coar/resource_type/c_1843' => 'other'
		);
		if ($uri){
			return $resourceTypes[$uri];
		} else {
			return $resourceTypes;
		}
	}

	/**
	 * Get an associative array of all COAR Resource Type Genres for select element
	 * (Includes default '' => "Choose One" string.)
	 * @return array resourceTypeUri => resourceTypeLabel
	 */
	function _getResourceTypeOptions() {		
		$resourceTypeOptions = $this->_getCoarResourceType(null);
		$chooseOne = __('common.chooseOne');
		$chooseOneOption = array('' => $chooseOne);
		$resourceTypeOptions  = $chooseOneOption + $resourceTypeOptions ;
		return $resourceTypeOptions;
	}
}

