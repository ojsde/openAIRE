{**
 * templates/controllers/grid/settings/section/form/sectionFormAdditionalFields.tpl
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Add fields for COAR Resource Types Vocabulary
 *
 *}
<div style="clear:both;">
	{fbvFormSection title="plugins.generic.openAIRE.resourceType.title" for="resourceType" inline=true}
		{fbvElement type="select" id="resourceType" from=$resourceTypeOptions selected=$resourceType label="plugins.generic.openAIRE.resourceType.description" translate=false}
	{/fbvFormSection}
</div>
