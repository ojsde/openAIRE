# OpenAIRE Plugin 2.0

About
-----

This plugin adds a new OAI-PMH metadata format that complements the OpenAIRE Guidelines for Literature Repository Managers v4 (https://openaire-guidelines-for-literature-repository-managers.readthedocs.io/en/v4.0.0/) and is optimized for works published in journals. The metadata format used is JATS XML format.

In section settings journals can choose a publication type from the COAR Resource Type vocabulary that best describes the articles published in that section (https://www.coar-repositories.org/activities/repository-interoperability/coar-vocabularies/deliverables/).

The article access rights are shown by using the COAR Access Rights Vocabulary (https://www.coar-repositories.org/activities/repository-interoperability/coar-vocabularies/access-rights-vocabulary/).

The OpenAIRE OAI metada format also contains a hook that can be used to inject funding data to the article entry. This means that journals can use any source of funding data they want. The funding Plugin for OJS3 supports this hook (https://github.com/ajnyga/funding).

The plugin has been developed in cooperation with OpenAIRE.


License
-------
This plugin is licensed under the GNU General Public License v3. See the file LICENSE for the complete terms of this license.


System Requirements
-------------------
OJS 3.3.0 or greater.
PHP 7.0 or greater.


Version History
---------------

### Version 2.0.3.0

Support for OJS 3.3.0.

### Version 2.0.2.0

Support for OJS 3.2.0.

### Version 2.0.1.5

Fixes a bug with the creation of a new section.

### Version 2.0.1

Version 2.0.1 was published in April 2019 and is designed to work with OJS 3.1.2.


Sponsors
--------

Version 2.0 of the plugin was created by The Federation of Finnish
Learned Societies (https://tsv.fi) with funding provided by the Deutsche
Forschungsgemeinschaft and Freie Universität Berlin/CeDiS.


Install
-------

### Installing using a release from Github:

 1. Download the latest compatible release from https://github.com/ojsde/openAIRE/releases. Unzip.
 2. Disable the old OpenAIRE plugin from Settings -> Website -> Plugins -> Generic Plugin -> OpenAIRE Plugin.
 3. Remove the old OpenAIRE plugin folder from plugins/generic/.
 4. Move the new OpenAIRE plugin folder to OJS plugins/generic/ folder.
 5. Go to Settings -> Website -> Plugins -> Generic Plugin -> OpenAIRE Plugin and enable the plugin.

If you are hosting a single site with several OJS journals, you can enable the plugin from the site settings. This enables the custom OAI-PMH metadata format for the whole site. However, other features, like allowing journals to define content types for articles within specific sections, requires that journals enable the plugin from their own settings.


How to use the plugin
---------------------

After enabling the plugin you will see a new metadata format called *oai_openaire_jats* in your OAI-PMH.

You can go to Settings > Journal > Sections and edit the section settings. The settings form has a pull down menu that allows you to select a COAR Resource Type that best describes the articles published in that section. The selected resource type will be shown in the OAI-PMH metadata.

You can collect and attach funding data by installing and enabling the Funding plugin for OJS3 (https://github.com/ajnyga/funding).


How to register to OpenAIRE
---------------------------

### A) OJS journals that have already registered with OpenAIRE

After enabling the new OpenAIRE plugin, please open a ticket at OpenAIRE Helpdesk under https://www.openaire.eu/support/helpdesk (choose “Content Providers”) and let us know that you want your metadata to be included in the JATS format by using the new OpenAIRE plugin for OJS.

If you have used the old OpenAIRE plugin and have saved FP7 project ID's to the database, the new plugin will still show them in OAI-PMH.

### B) OJS journals that have not registered with OpenAIRE

After enabling the plugin, follow these steps to register with OpenAIRE

1. Go to https://provide.openaire.eu/dashboard to register your journal with OpenAIRE

2. Choose "Sign in" and either choose an existing account or choose "Sign up" to create an OpenAIRE account

3. After logging in you should be forwarded to https://provide.openaire.eu/dashboard

4. Choose "Register"

5. Depending on whether you are hosting one or several OJS journals on your site you either choose the option "Open Access Journals" or the option "Aggregator".

6. Fill in the form you selected

    #### Option "Open Access Journals", one OJS journal
    
    *Stage 1, Enter Information*
    
    Software Platform: OJS
    
    Official Name: your journal's name
    
    ISSN: if you only have eISSN fill it here, or fill in your print ISSN
    
    EISSN: if you have both print and eISSN, give eISSN here
    
    Description: a short description of your journal
    
    Country: the country where your journal is published
    
    Longitude and latitude: Fill in the geocoordinates.  
    To determine your geocoordinates, use e.g. https://www.openstreetmap.org, search for your institution/town, right-click it, pick “Show address” from the drop-down-menu, then copy the coordinates.

    Entry URL: the URL leading to the front page of your journal
    
    English Name: can be the same as above if you only have an English name for the journal
    
    Timezone: the time zone where the journal is situated
    
    Repository type: Journal
    
    Admin Email: Preferably the same as the one given for the OAI-PMH verb Identify  (see your OAI-PMH feed at http://yourdomain.com/index.php/yourjournal/oai => Identify)
    
    Click Next
    
    *Stage 2, Add Interfaces*
    
    Base OAI-PMH URL: Find your OAI-PMH URL http://yourdomain.com/index.php/yourjournal/oai. Visiting the correct URL should show a page with a title "OAI 2.0 Request Results".
    
    Validation Set: leave empty
    
    Desired Compatibility Level: Choose "OpenAIRE 3.0"
    
    Click Next and you are ready.
    
    Your journal is now registered with OpenAIRE.


    #### Option "Aggregator", OJS site with multiple journals (like https://journal.fi)
    
    *Stage 1, Enter Information*
    
    Software Platform: OJS
    
    Official Name: your OJS site's name (containing multiple journals)
    
    Description: a short description of your site
    
    Country: the country where your site is maintained
    
    Longitude and latitude: Longitude and latitude: Fill in the geocoordinates.  
    To determine your geocoordinates, use e.g. https://www.openstreetmap.org, search for your institution/town, right-click it, pick “Show address” from the drop-down-menu, then copy the coordinates.
    
    Entry URL: the URL leading to the front page of your site index
    
    Institution: the name of the organisation maintaining the site
    
    English Name: can be the same as above if you only have an English name for the OJS site
    
    Timezone: the time zone where the site is situated
    
    Repository type: Journal Aggregator/Publisher
    
    Admin Email: Should be the same as the one given for the OAI-PMH verb Identify (see your OAI-PMH feed at http://yourdomain.com/index.php/index/oai => Identify)
    
    Click Next
    
    *Stage 2, Add Interfaces*
    
    Base OAI-PMH URL: Find your site's main OAI-PMH URL http://yourdomain.com/index.php/index/oai. Visiting the correct url should show a page with a title "OAI 2.0 Request Results".
    
    Validation Set: leave empty
    
    Desired Compatibility Level: Choose "OpenAIRE 3.0"
    
    Click Next and you are ready.
    
    Your OJS site is now registered with OpenAIRE.

7. After registering your journal/site with OpenAIRE a validation process will start and you will be notified of the results. Note that currently the validation may fail, because OpenAIRE is still lacking the required validation rules for the JATS format that the new OpenAIRE plugin uses. However, this will not prevent your content of being registered with OpenAIRE.  

8. Please open a ticket at OpenAIRE Helpdesk under https://www.openaire.eu/support/helpdesk (choose “Content Providers”) to draw attention to your registration and let us know that you want your metadata to be included in the JATS format. 

Note, that eventually the registration process will be updated to include the JATS-option so that the last step of this guide will be redundant.
