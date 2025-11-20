<?php

/**
 * @defgroup oai_format_jats
 */

/**
 * @file OAIMetadataFormat_OpenAIRE.inc.php
 *
 * Copyright (c) 2013-2020 Simon Fraser University
 * Copyright (c) 2003-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OAIMetadataFormat_OpenAIRE
 * @ingroup oai_format
 * @see OAI
 *
 * @brief OAI metadata format class -- OpenAIRE
 */

namespace APP\plugins\generic\openAIRE;

use APP\core\Application;
use PKP\core\PKPString;
use PKP\db\DAORegistry;
use PKP\oai\OAIMetadataFormat;
use PKP\plugins\Hook;
use PKP\plugins\PluginRegistry;

class OAIMetadataFormat_OpenAIRE extends OAIMetadataFormat
{

    /**
     * @see OAIMetadataFormat#toXml
     * @throws \Exception
     */
    function toXml($record, $format = null)
    {
        $request = Application::get()->getRequest();
        $article = $record->getData('article');
        $journal = $record->getData('journal');
        $section = $record->getData('section');
        $issue = $record->getData('issue');
        $galleys = $record->getData('galleys');
        $articleId = $article->getId();
        // Be defensive: some records may not have a current publication (eg, legacy data)
        $publication = $article->getCurrentPublication();
        if (!$publication && method_exists($article, 'getLatestPublication')) {
            $publication = $article->getLatestPublication();
        }
        $abbreviation = $journal->getLocalizedSetting('abbreviation');
        $printIssn = $journal->getSetting('printIssn');
        $onlineIssn = $journal->getSetting('onlineIssn');
        $articleLocale = $article->getData('locale');
        $publisherInstitution = $journal->getSetting('publisherInstitution');
        $datePublished = $article->getData('datePublished');
        $articleDoi = $article->getStoredPubId('doi');
        $accessRights = $this->_getAccessRights($journal, $issue, $article);
        $resourceType = ($section->getData('resourceType') ? $section->getData('resourceType') : 'http://purl.org/coar/resource_type/c_6501'); # COAR resource type URI, defaults to "journal article"
        if (!$datePublished) $datePublished = $issue->getData('datePublished');
        if ($datePublished) $datePublished = strtotime($datePublished);
        $parentPlugin = PluginRegistry::getPlugin('generic', 'openaireplugin');

        $response = "
		<article 
			dtd-version=\"1.1\" 
			xmlns:xlink=\"http://www.w3.org/1999/xlink\" 
			xmlns:mml=\"http://www.w3.org/1998/Math/MathML\" 
			xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" 
			xmlns:ali=\"http://www.niso.org/schemas/ali/1.0\" 
			xmlns=\"https://jats.nlm.nih.gov/publishing/1.1/\" 
			article-type=\"" . htmlspecialchars($this->_mapCoarResourceTypeToJatsArticleType($resourceType)) . "\" 
			xml:lang=\"" . substr($articleLocale, 0, 2) . "\">
		<front>
		<journal-meta>
			<journal-id journal-id-type=\"ojs\">" . htmlspecialchars($journal->getPath()) . "</journal-id>
			<journal-title-group>
			<journal-title xml:lang=\"" . substr($journal->getPrimaryLocale(), 0, 2) . "\">" . htmlspecialchars($journal->getName($journal->getPrimaryLocale())) . "</journal-title>\n";
        // Translated journal titles
        $journalNames = $journal->getName(null);
        if (is_array($journalNames)) foreach ($journalNames as $locale => $title) {
            if ($locale == $journal->getPrimaryLocale()) continue;
            $response .= "\t\t\t<trans-title-group xml:lang=\"" . substr($locale, 0, 2) . "\"><trans-title>" . htmlspecialchars($title) . "</trans-title></trans-title-group>\n";
        }
        $response .= ($journal->getAcronym($journal->getPrimaryLocale()) ? "\t\t\t<abbrev-journal-title xml:lang=\"" . substr($journal->getPrimaryLocale(), 0, 2) . "\">" . htmlspecialchars($journal->getAcronym($journal->getPrimaryLocale())) . "</abbrev-journal-title>" : '');
        $response .= "\n\t\t\t</journal-title-group>\n";

        $response .=
            (!empty($onlineIssn) ? "\t\t\t<issn pub-type=\"epub\">" . htmlspecialchars($onlineIssn) . "</issn>\n" : '') .
            (!empty($printIssn) ? "\t\t\t<issn pub-type=\"ppub\">" . htmlspecialchars($printIssn) . "</issn>\n" : '') .
            ($publisherInstitution != '' ? "\t\t\t<publisher><publisher-name>" . htmlspecialchars($publisherInstitution) . "</publisher-name></publisher>\n" : '') .
            "\t\t</journal-meta>\n" .
            "\t\t<article-meta>\n" .
            "\t\t\t<article-id pub-id-type=\"publisher-id\">" . $article->getId() . "</article-id>\n" .
            (!empty($articleDoi) ? "\t\t\t<article-id pub-id-type=\"doi\">" . htmlspecialchars($articleDoi) . "</article-id>\n" : '') .
            "\t\t\t<article-categories><subj-group xml:lang=\"" . $journal->getPrimaryLocale() . "\" subj-group-type=\"heading\"><subject>" . htmlspecialchars($section->getLocalizedTitle()) . "</subject></subj-group></article-categories>\n" .
            "\t\t\t<title-group>\n" .
            "\t\t\t\t<article-title xml:lang=\"" . substr($articleLocale, 0, 2) . "\">" . htmlspecialchars(strip_tags((string)$article->getData('title', $articleLocale))) . "</article-title>\n";
        if (!empty($subtitle = $article->getData('subtitle', $articleLocale))) $response .= "\t\t\t\t<subtitle xml:lang=\"" . substr($articleLocale, 0, 2) . "\">" . htmlspecialchars($subtitle) . "</subtitle>\n";

        // Translated article titles
        $allTitles = $article->getData('title');
        if (is_array($allTitles)) foreach ($allTitles as $locale => $title) {
            if ($locale == $articleLocale) continue;
            if ($title) {
                $response .= "\t\t\t\t<trans-title-group xml:lang=\"" . substr($locale, 0, 2) . "\">\n";
                $response .= "\t\t\t\t\t<trans-title>" . htmlspecialchars(strip_tags((string)$title)) . "</trans-title>\n";
                if (!empty($subtitle = $article->getData('subtitle', $locale))) $response .= "\t\t\t\t\t<trans-subtitle>" . htmlspecialchars($subtitle) . "</trans-subtitle>\n";
                $response .= "\t\t\t\t\t</trans-title-group>\n";
            }
        }
        $response .=
            "\t\t\t</title-group>\n" .
            "\t\t\t<contrib-group content-type=\"author\">\n";

        // Authors
        $affiliations = array();
        $authors = $publication ? $publication->getData('authors') : (method_exists($article, 'getAuthors') ? $article->getAuthors() : []);
        if (is_array($authors)) foreach ($authors as $author) {
            $affiliation = method_exists($author, 'getLocalizedAffiliation') ? $author->getLocalizedAffiliation() : $author->getData('affiliation');
            $affiliationToken = array_search($affiliation, $affiliations);
            if ($affiliation && !$affiliationToken) {
                $affiliationToken = 'aff-' . (count($affiliations) + 1);
                $affiliations[$affiliationToken] = $affiliation;
            }
            $response .=
                "\t\t\t\t<contrib " . ($author->getPrimaryContact() ? 'corresp="yes" ' : '') . ">\n" .
                "\t\t\t\t\t<name name-style=\"western\">\n" .
                "\t\t\t\t\t\t<surname>" . htmlspecialchars(method_exists($author, 'getLastName') ? $author->getLastName() : $author->getLocalizedFamilyName()) . "</surname>\n" .
                "\t\t\t\t\t\t<given-names>" . htmlspecialchars(method_exists($author, 'getFirstName') ? $author->getFirstName() : $author->getLocalizedGivenName()) . (((method_exists($author, 'getMiddleName') && ($s = $author->getMiddleName())) != '') ? " $s" : '') . "</given-names>\n" .
                "\t\t\t\t\t</name>\n" .
                ($affiliationToken ? "\t\t\t\t\t<xref ref-type=\"aff\" rid=\"$affiliationToken\" />\n" : '') .
                ($author->getOrcid() ? "\t\t\t\t\t<contrib-id contrib-id-type=\"orcid\" authenticated=\"true\">" . htmlspecialchars($author->getOrcid()) . "</contrib-id>\n" : '') .
                "\t\t\t\t</contrib>\n";
        }
        $response .= "\t\t\t</contrib-group>\n";
        foreach ($affiliations as $affiliationToken => $affiliation) {
            $response .= "\t\t\t<aff id=\"$affiliationToken\"><institution content-type=\"orgname\">" . htmlspecialchars($affiliation) . "</institution></aff>\n";
        }

        // Publication date
        if ($datePublished) {
            $response .=
                "\t\t\t<pub-date date-type=\"pub\" publication-format=\"epub\">\n" .
                "\t\t\t\t<day>" . date('d', $datePublished) . "</day>\n" .
                "\t\t\t\t<month>" . date('m', $datePublished) . "</month>\n" .
                "\t\t\t\t<year>" . date('Y', $datePublished) . "</year>\n" .
                "\t\t\t</pub-date>\n";
        }

        // Issue details
        if ($issue->getVolume() && $issue->getShowVolume())
            $response .= "\t\t\t<volume>" . htmlspecialchars($issue->getVolume()) . "</volume>\n";
        if ($issue->getNumber() && $issue->getShowNumber())
            $response .= "\t\t\t<issue>" . htmlspecialchars($issue->getNumber()) . "</issue>\n";

        // Page info, if available and parseable.
        $pageInfo = $this->_getPageInfo($article);
        if ($pageInfo) {
            $response .=
                "\t\t\t\t<fpage>" . $pageInfo['fpage'] . "</fpage>\n" .
                "\t\t\t\t<lpage>" . $pageInfo['lpage'] . "</lpage>\n";
        }

        // Fetch funding data from other plugins if available
        $fundingReferences = null;
        Hook::call('OAIMetadataFormat_OpenAIRE::findFunders', [&$articleId, &$fundingReferences]);
        if ($fundingReferences) {
            $response .= $fundingReferences;
        }

        // Copyright, license and other permissions
        $copyrightYear = $article->getData('copyrightYear');
        $copyrightHolder = $article->getData('copyrightHolder');
        $licenseUrl = $article->getData('licenseUrl');
        $ccBadge = Application::get()->getCCLicenseBadge($licenseUrl);
        $openAccessDate = null;
        if ($accessRights == 'embargoedAccess') {
            $openAccessDate = date('Y-m-d', strtotime($issue->getOpenAccessDate()));
        }
        if ($copyrightYear || $copyrightHolder || $licenseUrl || $ccBadge || $openAccessDate || $accessRights == "openAccess") $response .=
            "\t\t\t<permissions>\n" .
            (($copyrightYear || $copyrightHolder) ? "\t\t\t\t<copyright-statement>" . htmlspecialchars(__('submission.copyrightStatement', array('copyrightYear' => $copyrightYear, 'copyrightHolder' => $copyrightHolder))) . "</copyright-statement>\n" : '') .
            ($copyrightYear ? "\t\t\t\t<copyright-year>" . htmlspecialchars($copyrightYear) . "</copyright-year>\n" : '') .
            ($copyrightHolder ? "\t\t\t\t<copyright-holder>" . htmlspecialchars($copyrightHolder) . "</copyright-holder>\n" : '') .
            ($licenseUrl ? "\t\t\t\t<license xlink:href=\"" . htmlspecialchars($licenseUrl) . "\">\n" .
                ($ccBadge ? "\t\t\t\t\t<license-p>" . strip_tags($ccBadge) . "</license-p>\n" : '') .
                "\t\t\t\t</license>\n" : '') .
            ($openAccessDate ? "\t\t\t\t<ali:free_to_read xmlns:ali=\"http://www.niso.org/schemas/ali/1.0\" start_date=\"" . htmlspecialchars($openAccessDate) . "\" />\n" : '') .
            ($accessRights == "openAccess" ? "\t\t\t\t<ali:free_to_read xmlns:ali=\"http://www.niso.org/schemas/ali/1.0\" />\n" : '') .
            "\t\t\t</permissions>\n";

        // landing page link
        $response .= "\t\t\t<self-uri xlink:href=\"" . htmlspecialchars($request->url($journal->getPath(), 'article', 'view', [$article->getBestId()])) . "\" />\n";

        // full text links
        $galleys = $article->getGalleys();
        $primaryGalleys = array();
        if ($galleys) {
            $genreDao = DAORegistry::getDAO('GenreDAO');
            $primaryGenres = $genreDao->getPrimaryByContextId($journal->getId())->toArray();
            $primaryGenreIds = array_map(function ($genre) {
                return $genre->getId();
            }, $primaryGenres);
            foreach ($galleys as $galley) {
                // Fix typo and read remote URL correctly
                $remoteUrl = $galley->getData('remoteUrl');
                $file = $galley->getFile();
                if (!$remoteUrl && !$file) {
                    continue;
                }
                if ($remoteUrl || in_array($file->getGenreId(), $primaryGenreIds)) {
                    $response .= "\t\t\t<self-uri content-type=\"" . $galley->getFileType() . "\" xlink:href=\"" . htmlspecialchars($request->url($journal->getPath(), 'article', 'download', array($article->getBestId(), $galley->getBestGalleyId()), null, null, true)) . "\" />\n";
                }
            }
        }

        // Keywords
        $subjects = array();
        if (is_array($article->getData('subject'))) foreach ($article->getData('subject') as $locale => $subject) {
            $s = array_map('trim', explode(';', $subject));
            if (!empty($s)) $subjects[$locale] = $s;
        }
        if (!empty($subjects)) foreach ($subjects as $locale => $s) {
            $response .= "\t\t\t<kwd-group xml:lang=\"" . substr($locale, 0, 2) . "\">\n";
            foreach ($s as $subject) $response .= "\t\t\t\t<kwd>" . htmlspecialchars($subject) . "</kwd>\n";
            $response .= "\t\t\t</kwd-group>\n";
        }

        // Publication keywords (guard against missing publication and DAO differences across OJS versions)
        if ($publication) {
            $pubKeywords = $publication->getData('keywords'); // expected: [locale => [kw1, kw2, ...]]
            if (is_array($pubKeywords)) {
                foreach ($pubKeywords as $locale => $keywords) {
                    if (empty($keywords) || !is_array($keywords)) continue;
                    $response .= "\t\t\t<kwd-group xml:lang=\"" . substr($locale, 0, 2) . "\">\n";
                    foreach ($keywords as $keyword) {
                        $response .= "\t\t\t\t<kwd>" . htmlspecialchars($keyword) . "</kwd>\n";
                    }
                    $response .= "\t\t\t</kwd-group>\n";
                }
            }
        }

        // abstract
        if ($article->getData('abstract', $articleLocale)) {
            $abstract = PKPString::html2text($article->getData('abstract', $articleLocale));
            $response .= "\t\t\t<abstract xml:lang=\"" . substr($articleLocale, 0, 2) . "\"><p>" . htmlspecialchars($abstract) . "</p></abstract>\n";
        }
        // Include translated abstracts
        $abstracts = $article->getData('abstract');
        if (is_array($abstracts)) foreach ($abstracts as $locale => $abstract) {
            if ($locale == $articleLocale) continue;
            if ($abstract) {
                $abstract = PKPString::html2text($abstract);
                $response .= "\t\t\t<trans-abstract xml:lang=\"" . substr($locale, 0, 2) . "\"><p>" . htmlspecialchars($abstract) . "</p></trans-abstract>\n";
            }
        }

        // Page count
        $response .=
            ($pageInfo ? "\t\t\t<counts><page-count count=\"" . (int)$pageInfo['pagecount'] . "\" /></counts>\n" : '');

        // OpenAIRE COAR Access Rights and OpenAIRE COAR Resource Type
        $coarAccessRights = $this->_getCoarAccessRights();
        $coarResourceLabel = $parentPlugin && method_exists($parentPlugin, '_getCoarResourceType') ? $parentPlugin->_getCoarResourceType($resourceType) : null;

        if ($accessRights || $coarResourceLabel) {
            $response .= "\t\t\t<custom-meta-group>\n";
            if ($accessRights) $response .=
                "\t\t\t\t<custom-meta specific-use=\"access-right\">\n" .
                "\t\t\t\t\t<meta-name>" . $coarAccessRights[$accessRights]['label'] . "</meta-name>\n" .
                "\t\t\t\t\t<meta-value>" . $coarAccessRights[$accessRights]['url'] . "</meta-value>\n" .
                "\t\t\t\t</custom-meta>\n";
            if ($coarResourceLabel) $response .=
                "\t\t\t\t<custom-meta specific-use=\"resource-type\">\n" .
                "\t\t\t\t\t<meta-name>" . $coarResourceLabel . "</meta-name>\n" .
                "\t\t\t\t\t<meta-value>" . $resourceType . "</meta-value>\n" .
                "\t\t\t\t</custom-meta>\n";
            $response .= "\t\t\t</custom-meta-group>\n";
        }

        $response .=
            "\t\t</article-meta>\n" .
            "\t</front>\n" .
            "</article>";

        return $response;
    }

    /**
     * Get an associative array containing COAR Access Rights.
     * @return array
     */
    function _getCoarAccessRights()
    {
        static $coarAccessRights = array(
            'openAccess' => array('label' => 'open access', 'url' => 'http://purl.org/coar/access_right/c_abf2'),
            'embargoedAccess' => array('label' => 'embargoed access', 'url' => 'http://purl.org/coar/access_right/c_abf2'),
            'restrictedAccess' => array('label' => 'restricted access', 'url' => 'http://purl.org/coar/access_right/c_abf2'),
            'metadataOnlyAccess' => array('label' => 'metadata only access', 'url' => 'http://purl.org/coar/access_right/c_abf2')
        );
        return $coarAccessRights;
    }

    /**
     * Get a JATS article-type string based on COAR Resource Type URI.
     * https://jats.nlm.nih.gov/archiving/tag-library/1.1/attribute/article-type.html
     * @param $uri string
     * @return string
     */
    function _mapCoarResourceTypeToJatsArticleType($uri)
    {
        $resourceTypes = array(
            'http://purl.org/coar/resource_type/c_6501' => 'research-article',
            'http://purl.org/coar/resource_type/c_2df8fbb1' => 'research-article',
            'http://purl.org/coar/resource_type/c_dcae04bc' => 'review-article',
            'http://purl.org/coar/resource_type/c_beb9' => 'research-article',
            'http://purl.org/coar/resource_type/c_7bab' => 'research-article',
            'http://purl.org/coar/resource_type/c_b239' => 'editorial',
            'http://purl.org/coar/resource_type/c_545b' => 'letter',
            'http://purl.org/coar/resource_type/c_93fc' => 'case-report',
            'http://purl.org/coar/resource_type/c_efa0' => 'product-review',
            'http://purl.org/coar/resource_type/c_ba08' => 'book-review',
            'http://purl.org/coar/resource_type/c_5794' => 'meeting-report',
            'http://purl.org/coar/resource_type/c_46ec' => 'dissertation',
            'http://purl.org/coar/resource_type/c_8042' => 'research-article',
            'http://purl.org/coar/resource_type/c_816b' => 'research-article'
        );
        return $resourceTypes[$uri] ?? 'research-article';
    }

    /**
     * Get an associative array containing page info
     * @return array
     */
    function _getPageInfo($article)
    {
        $matches = $pageCount = null;
        $pages = (string)$article->getData('pages');
        if (preg_match('/^(\d+)$/', $pages, $matches)) {
            $matchedPage = htmlspecialchars($matches[1]);
            return array('fpage' => $matchedPage, 'lpage' => $matchedPage, 'pagecount' => '1');
        } elseif (preg_match('/^[Pp][Pp]?[.]?[ ]?(\d+)$/', $pages, $matches)) {
            $matchedPage = htmlspecialchars($matches[1]);
            return array('fpage' => $matchedPage, 'lpage' => $matchedPage, 'pagecount' => '1');
        } elseif (preg_match('/^[Pp][Pp]?[.]?[ ]?(\d+)[ ]?-[ ]?([Pp][Pp]?[.]?[ ]?)?(\d+)$/', $pages, $matches)) {
            $matchedPageFrom = htmlspecialchars($matches[1]);
            $matchedPageTo = htmlspecialchars($matches[3]);
            $pageCount = $matchedPageTo - $matchedPageFrom + 1;
            return array('fpage' => $matchedPageFrom, 'lpage' => $matchedPageTo, 'pagecount' => $pageCount);
        } elseif (preg_match('/^(\d+)[ ]?-[ ]?(\d+)$/', $pages, $matches)) {
            $matchedPageFrom = htmlspecialchars($matches[1]);
            $matchedPageTo = htmlspecialchars($matches[2]);
            $pageCount = $matchedPageTo - $matchedPageFrom + 1;
            return array('fpage' => $matchedPageFrom, 'lpage' => $matchedPageTo, 'pagecount' => $pageCount);
        } else {
            return null;
        }
    }

    /**
     * Get article access rights
     * @param $journal
     * @param $issue
     * @param $article
     * @return string
     */
    function _getAccessRights($journal, $issue, $article)
    {
        $accessRights = null;
        $publishingMode = $journal ? $journal->getData('publishingMode') : null;
        // Use namespaced constants available in current OJS/OMP
        $PUBLISHING_MODE_OPEN = \APP\journal\Journal::PUBLISHING_MODE_OPEN;
        $PUBLISHING_MODE_SUBSCRIPTION = \APP\journal\Journal::PUBLISHING_MODE_SUBSCRIPTION;
        $ISSUE_ACCESS_OPEN = \APP\issue\Issue::ISSUE_ACCESS_OPEN;
        $ISSUE_ACCESS_SUBSCRIPTION = \APP\issue\Issue::ISSUE_ACCESS_SUBSCRIPTION;
        $ARTICLE_ACCESS_OPEN = \APP\submission\Submission::ARTICLE_ACCESS_OPEN;

        if ($publishingMode === $PUBLISHING_MODE_OPEN) {
            $accessRights = 'openAccess';
        } else if ($publishingMode === $PUBLISHING_MODE_SUBSCRIPTION) {
            if ($issue && ($issue->getAccessStatus() == 0 || $issue->getAccessStatus() === $ISSUE_ACCESS_OPEN)) {
                $accessRights = 'openAccess';
            } else if ($issue && $issue->getAccessStatus() === $ISSUE_ACCESS_SUBSCRIPTION) {
                // In newer OJS versions, article access is on the Publication
                $pub = method_exists($article, 'getCurrentPublication') ? $article->getCurrentPublication() : null;
                $articleAccess = $pub ? $pub->getData('accessStatus') : (method_exists($article, 'getAccessStatus') ? $article->getAccessStatus() : null);
                if ($articleAccess === $ARTICLE_ACCESS_OPEN) {
                    $accessRights = 'openAccess';
                } else if ($issue->getOpenAccessDate() !== null) {
                    $accessRights = 'embargoedAccess';
                } else {
                    $accessRights = 'metadataOnlyAccess';
                }
            }
        }
        if ($journal && ($journal->getData('restrictSiteAccess') == 1 || $journal->getData('restrictArticleAccess') == 1)) {
            $accessRights = 'restrictedAccess';
        }
        return $accessRights;
    }

}
