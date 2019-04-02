# OpenAIRE Plugin 2.0

About
-----

This plugin adds a new OAI-PMH metadataformat that supports OpenAIRE Guidelines for Literature Repository Managers v4 (https://openaire-guidelines-for-literature-repository-managers.readthedocs.io/en/v4.0.0/). The metadataformat uses JATS XML format.

In section settings journals can choose a COAR Resource Type that best describes the articles published in that section (https://www.coar-repositories.org/activities/repository-interoperability/coar-vocabularies/deliverables/).

The article access rights are shown by using the COAR Access Rights Vocabulary (https://www.coar-repositories.org/activities/repository-interoperability/coar-vocabularies/access-rights-vocabulary/).

The OpenAIRE OAI metadaformat also contains a hook that can be used to inject funding data to the article entry. This means that journals can use any source of funding data they want. Funding Plugin for OJS3 supports this hook (https://github.com/ajnyga/funding).

The plugin has been developed in cooperation with OpenAIRE.

License
-------
This plugin is licensed under the GNU General Public License v3. See the file LICENSE for the complete terms of this license.

System Requirements
-------------------
OJS 3.1.2 or greater.
PHP 7.0 or greater.

Install
-------

Installing using a release from Github:

 1. Download the latest compatible release from https://github.com/ojsde/openAIRE/releases. Unzip.
 2. Disable the old OpenAIRE plugin from Settings -> Website -> Plugins -> Generic Plugin -> OpenAIRE Plugin.
 3. Remove the old OpenAIRE plugin folder from plugins/generic/.
 4. Move the new OpenAIRE plugin folder to OJS plugins/generic/ folder.
 5. Go to Settings -> Website -> Plugins -> Generic Plugin -> OpenAIRE Plugin and enable the plugin.

Version History
---------------

### Version 2.0

Version 2.0 was published in March 2019.

Sponsors
---------------

Version 2.0 of the plugin was created by The Federation of Finnish
Learned Societies (https://tsv.fi) with funding provided by the Deutsche
Forschungsgemeinschaft and Freie Universität Berlin/CeDiS.

TODO
---------------

