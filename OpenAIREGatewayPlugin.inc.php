<?php

/**
 * @file OpenAIREGatewayPlugin.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OpenAIREGateway
 * @ingroup plugins_gateways_OpenAIREGateway
 *
 * @brief OpenAIREGateway plugin
 */

import('lib.pkp.classes.plugins.GatewayPlugin');

class OpenAIREGatewayPlugin extends GatewayPlugin {
	protected $_parentPlugin;
	
	/**
	 * Constructor
	 * @param $parentPlugin OpenAIREPlugin
	 */
	function __construct($parentPlugin) {
		$this->_parentPlugin = $parentPlugin;
		parent::__construct();
	}

	function getName() {
		return 'OpenAIREGatewayPlugin';
	}

	function getDisplayName() {
		return __('plugins.generic.openAIRE.gateway.displayName');
	}

	function getDescription() {
		return __('plugins.generic.openAIRE.gateway.description');
	}

	public function getPluginPath() {
		return $this->_parentPlugin->getPluginPath();
	}

	public function getHideManagement() {
		return true;
	}	

	public function getEnabled() {
		return $this->_parentPlugin->getEnabled();
	}

	/**
	 * Handle fetch requests for this plugin.
	 */
	function fetch($args, $request) {
		if (!$this->getEnabled()) {
			return false;
		}

		$scheme = array_shift($args);
		switch ($scheme) {
			case 'objects':
				$this->showObjects();
				break;
		}

		// Failure.
		header('HTTP/1.0 404 Not Found');
		$templateMgr = TemplateManager::getManager($request);
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON);
		$templateMgr->assign('message', 'plugins.generic.openAIRE.gateway.errorMessage');
		$templateMgr->display('frontend/pages/message.tpl');
		exit;
	}

	function showObjects() {
		$journalDao = DAORegistry::getDAO('JournalDAO');
		$issueDao = DAORegistry::getDAO('IssueDAO');
		$journals = $journalDao->getAll(true);
		$request = $this->getRequest();
		$dispatcher = $request->getDispatcher();
		header('content-type: text/plain');
		header('content-disposition: attachment; filename=objects-' . date("Y-m-d") . '.txt');
		while ($journal = $journals->next()) {
			if ( ($journal->getSetting('onlineIssn') || $journal->getSetting('printIssn') ) && $journal->getEnabled() && $journal->getSetting('publishingMode') != PUBLISHING_MODE_NONE) {
					$journalData[$journal->getId()]['url'] = $dispatcher->url($request, ROUTE_PAGE, $journal->getPath());
					$journalData[$journal->getId()]['issn'] = $journal->getSetting('printIssn');
					$journalData[$journal->getId()]['eissn'] = $journal->getSetting('onlineIssn');
					$journalData[$journal->getId()]['primaryLanguage'] = $journal->getPrimaryLocale();
					$journalData[$journal->getId()]['name'] = $journal->getName(null);
			}
		}
		echo json_encode($journalData);
		exit;	
	}
}

?>
