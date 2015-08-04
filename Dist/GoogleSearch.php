<?php
/**
 * This is the Google search API without any authorization keys.
 * Remember Google might block your IP address if you send too much requests.
 *
 * GoogleSearch has/gives you:
 *    1. Regular results
 *    2. Advertisements results
 *    3. Similar keywords results
 *    4. Statistics results
 *    5. Caching
 *    6. Pagination
 *    7. Limiting results
 *    8. Results internationalization
 *
 * <code>
 * // Params: GoogleSearch("t-shirts" [, 1 [, 10]])
 * // Your query, current result page (optional), result limit (optional)
 *
 * $gs = new GoogleSearch("t-shirts");
 *
 * // $gs is always GoogleSearchResults object which is iterable and countable
 * $results = $gs->FindAll();
 * print_r($results);
 *
 * // In addition this class has simple statistics
 * print_r($results->Statistics);
 * </code>
 *
 * See COPYING for license information.
 *
 * @author Mateusz Manaj <mmanaj@softgraf.pl>
 * @copyright Copyright (c) 2015
 * @package GoogleSearch
 */

namespace GoogleSearch;

use GoogleSearch\Exception\CacheDirectoryException;
use GoogleSearch\Exception\ExtensionException;
use GoogleSearch\Exception\HttpCodeException;

class GoogleSearch
{
    public $RequestHeaders = Array("Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", "Referer: http://google.pl/");
    public $UserAgent = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.100 Safari/534.30";

    // Regular results
    private $_mainResultsQuery1 = "//div[contains(@class, 'srg')]/*[contains(@class, 'g')]/div[contains(@class, 'rc')]/h3[contains(@class, 'r')]/a|//ol[contains(@id, 'rso')]/*[contains(@class, 'g')]/div[contains(@class, 'rc')]/h3[contains(@class, 'r')]/a";
    private $_mainResultsQuery2 = "//ol[contains(@id, 'rso')]/div[contains(@class, 'srg')]/*[contains(@class, 'g')]/div[contains(@class, 'rc')]/h3[contains(@class, 'r')]/a";
    private $_mainResultsQuery3 = "//ol[contains(@id, 'rso')]/*[contains(@class, 'g')]/div/div[contains(@class, 'rc')]/h3[contains(@class, 'r')]/a";
    private $_descriptionResultsQuery = ".//*[contains(@class, 's')]/div/span[contains(@class, 'st')]";
    private $_innerLinksResultsQuery1 = ".//*[contains(@class, 's')]/div/div[contains(@class, 'osl')]/a";
    private $_innerLinksResultsQuery2 = ".//div[contains(@class, 'sld vsc')]";
    private $_innerLinksResultsQuery2link = ".//*[contains(@class, '_Tyb')]/h3/a";
    private $_innerLinksResultsQuery2desc = ".//*[contains(@class, 's')]/*[contains(@class, 'st')]";
    private $_breadcrumbsResultsQuery = ".//*[contains(@class, 's')]/div/div[contains(@class, 'f')]/cite";

    // Advertisements results
    private $_advMainResultsQuery = "//li[contains(@class, 'ads-ad')]";
    private $_advMainLinks = ".//h3/a";
    private $_advPhoneNumber = ".//*[contains(@class, '_r2b')]";
    private $_advDescription1 = ".//*[contains(@class, 'ads-creative')]";
    private $_advDescription2 = ".//*[contains(@class, '_knd _Tv')]";
    private $_advInternalLinks = ".//*[contains(@class, '_MEc _LEc')]/li|.//*[contains(@class, '_gBb')]/li";
    private $_advInternalLinksLink = ".//a";
    private $_advBreadcrumbs = ".//*[contains(@class, 'ads-visurl')]/cite";
    private $_advAddress = "(.//*[contains(@class, '_H2b')])[last()]/a";
    private $_advAddressPhNu = ".//*[contains(@class, '_xnd')]";

    // Similar results
    private $_similarMainResultsQuery = "//div[contains(@id, 'brs')]/div[contains(@class, 'card-section')]/div[contains(@class, 'brs_col')]/p/a";

    // Statistics results
    private $_statsMain = "//div[contains(@id, 'resultStats')]";
    private $_statsTimeload = ".//nobr";

    protected static $_extensionExists = NULL;
    public $Query = NULL;
    public $Page = 1;
    public $ResultsNumber = 1;
    public $Options = Array();
    public $CacheDirectory = "./GoogleSearch/_cache/";
    public $CacheLifeTime = 259200;

    /** @var GoogleSearchLanguageResultEnum */
    public $ResultLanguage = GoogleSearchLanguageResultEnum::NO_LANG;

    protected $_stringResults;

    /**
     * @var \DomXPath
     */
    protected $_finder_handle = NULL;

    public function __construct($query, $page = 1, $results_number = 10)
    {
        if(is_null(static::$_extensionExists)) static::$_extensionExists = extension_loaded("dom") && extension_loaded("curl");
        if(!static::$_extensionExists) throw new ExtensionException("GoogleSearch needs DOM and CURL extension loaded in your environment");

        $this->CacheDirectory = rtrim($this->CacheDirectory, "/")."/";
        if(!file_exists($this->CacheDirectory))
        {
            if(!mkdir($this->CacheDirectory, 0777, true)) throw new CacheDirectoryException("Unable to create cache directory at '".$this->CacheDirectory."'");
            if(!is_writable($this->CacheDirectory)) throw new CacheDirectoryException("Directory '".$this->CacheDirectory."' isn't writable");
        }

        $this->Query = $query;
        $this->ResultsNumber = is_numeric($results_number) && $results_number > 0 ? intval($results_number) : 10;
        $this->Page = ((is_numeric($page) && $page > 0 ? intval($page) : 1) - 1) * $this->ResultsNumber;
    }

    public function GetLink()
    {
        $uri = Array("q" => $this->Query, "start" => $this->Page, "pws" => 0, "num" => $this->ResultsNumber);
        if(!is_null($this->ResultLanguage)) $uri['lr'] = $this->ResultLanguage;
        $uri = array_merge($uri, $this->Options);

        return "http://google.com/search?".http_build_query($uri);
    }

    private function _doSearch()
    {
        $cf = $this->CacheDirectory.md5($this->GetLink()).".html";

        if(file_exists($cf) && time() < filectime($cf) + $this->CacheLifeTime) return file_get_contents($cf);

        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $this->GetLink());
        curl_setopt($c, CURLOPT_HTTPHEADER, $this->RequestHeaders);
        curl_setopt($c, CURLOPT_USERAGENT, $this->UserAgent);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($c, CURLOPT_ENCODING , "gzip");
        curl_setopt($c, CURLOPT_HEADER, 1);
        $html = curl_exec($c);
        $httpcode = curl_getinfo($c, CURLINFO_HTTP_CODE);
        if($httpcode != 200) throw new HttpCodeException("Google has returned ".$httpcode." code. Make sure that google hasn't block your IP address.");

        curl_close($c);

        file_put_contents($cf, $html);

        return $html;
    }

    private function _doFind()
    {
        // Check whether DOMDocument exists in memory previously created.
        if(is_null($this->_finder_handle))
        {
            $this->_stringResults = $this->_doSearch();

            // Prepend content type for further DOMDocument parsing
            $this->_stringResults = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' .$this->_stringResults;

            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->substituteEntities = true;
            @$dom->loadHTML($this->_stringResults);
            $this->_finder_handle = new \DomXPath($dom);
        }
    }

    public function Html()
    {
        $html = $this->_doSearch();
        return $html;
    }

    /**
     * @return GoogleSearchResults
     */
    public function Find()
    {
        $gsr = new GoogleSearchResults();

        $this->_doFind();
        $results = $this->_find_regular();
        $gsr->Import($results);

        $this->_parse_statistics($gsr);

        return $gsr;
    }

    /**
     * @return GoogleSearchResults
     */
    public function FindAds() { return $this->FindAdvertisements(); }

    /**
     * @return GoogleSearchResults
     */
    public function FindAdvertisements()
    {
        $gsr = new GoogleSearchResults();

        $this->_doFind();
        $results = $this->_find_ads();
        $gsr->Import($results);

        $this->_parse_statistics($gsr);

        return $gsr;
    }

    /**
     * @return GoogleSearchResults
     */
    public function FindAll()
    {
        $gsr = new GoogleSearchResults();

        $this->_doFind();
        $results1 = $this->_find_regular();
        $results2 = $this->_find_ads();
        $gsr->Extend($results1)->Extend($results2);

        $this->_parse_statistics($gsr);

        return $gsr;
    }

    /**
     * @return GoogleSearchResults
     */
    public function FindSimilarQueries()
    {
        $gsr = new GoogleSearchResults();

        $this->_doFind();
        $results = $this->_find_similar();
        $gsr->Import($results);

        $this->_parse_statistics($gsr);

        return $gsr;
    }

    /**
     * @return array
     */
    private function _find_regular()
    {
        $results = Array();

        $nodes = $this->_finder_handle->query($this->_mainResultsQuery1);
        $main_nodes = Array($nodes);

        $add_nodes = $this->_finder_handle->query($this->_mainResultsQuery2);
        if($add_nodes->length > 0) { $main_nodes[] = $add_nodes; }
        else
        {
            $add_nodes = $this->_finder_handle->query($this->_mainResultsQuery3);
            if($add_nodes->length > 0) { array_unshift($main_nodes, $add_nodes); }
        }

        foreach($main_nodes as $nodes)
        {
            if($nodes->length > 0)
            {
                /* @var $node \DOMElement */
                foreach($nodes as $node)
                {
                    $href = $node->getAttribute('href');
                    $label = $node->nodeValue;

                    $parent = $node->parentNode->parentNode;

                    $desc_node = $this->_finder_handle->query($this->_descriptionResultsQuery, $parent)->item(0);
                    $innerlinks_node = $this->_finder_handle->query($this->_innerLinksResultsQuery1, $parent);
                    $il = Array();

                    if($innerlinks_node->length > 0)
                    {
                        /* @var $link \DOMElement */
                        foreach($innerlinks_node as $link)
                        {
                            $il[] = new GoogleSearchElementInternalLink($link->nodeValue, $link->getAttribute("href"));
                        }
                    }
                    else
                    {
                        $innerlinks_node = $this->_finder_handle->query($this->_innerLinksResultsQuery2, $parent->parentNode);
                        if($innerlinks_node->length > 0)
                        {
                            foreach($innerlinks_node as $il_node)
                            {
                                /* @var $a \DOMElement */
                                $a = $this->_finder_handle->query($this->_innerLinksResultsQuery2link, $il_node)->item(0);

                                /* @var $s \DOMElement */
                                $s = $this->_finder_handle->query($this->_innerLinksResultsQuery2desc, $il_node)->item(0);

                                $il[] = new GoogleSearchElementInternalLink($a->nodeValue, $a->getAttribute("href"), $s->nodeValue, strip_tags($s->nodeValue));
                            }
                        }
                    }

                    $breadcrumbs_node = $this->_finder_handle->query($this->_breadcrumbsResultsQuery, $parent);
                    $bc = Array();

                    if($breadcrumbs_node->length > 0)
                    {
                        $s = $this->_innerXML($breadcrumbs_node->item(0));
                        $s = strip_tags($s);
                        $bc = explode("›", $s);
                    }

                    $results[] = new GoogleSearchResult($label, $href, $this->_innerXML($desc_node), strip_tags($this->_innerXML($desc_node)), $il, $bc);

                }
            }
        }

        return $results;
    }

    /**
     * @return array
     */
    private function _find_similar()
    {
        $results = Array();

        $nodes = $this->_finder_handle->query($this->_similarMainResultsQuery);
        if($nodes->length > 0)
        {
            /* @var $node \DOMElement */
            foreach($nodes as $node)
            {
                $results[] = new GoogleSearchElementLink($node->getAttribute("href"), $node->textContent);
            }
        }

        return $results;
    }

    /**
     * @return array
     */
    private function _find_ads()
    {
        $results = Array();

        $nodes = $this->_finder_handle->query($this->_advMainResultsQuery);

        if($nodes->length > 0)
        {
            /* @var $node \DOMElement */
            foreach($nodes as $node)
            {
                $item = new GoogleSearchAdvertisementResult();

                $label_nodes = $this->_finder_handle->query($this->_advMainLinks, $node);

                /* @var $a1 \DOMElement */
                $a1 = $label_nodes->item(0);

                /* @var $a2 \DOMElement */
                $a2 = $label_nodes->item(1);

                $item->Label = $a2->nodeValue;
                $item->Link = $a2->getAttribute("href");
                $item->AdvertisementLink = $a1->getAttribute("href");

                // PHONE NUMBER
                $phone_nodes = $this->_finder_handle->query($this->_advPhoneNumber, $node);
                if($phone_nodes->length > 0) { $item->PhoneNumber = $phone_nodes->item(0)->nodeValue; }

                // DESCRIPTION
                $desc_node = $this->_finder_handle->query($this->_advDescription1, $node)->item(0);
                if(!is_null($desc_node))
                {
                    $item->HtmlContent = $desc_node->nodeValue;
                    $item->PlainTextContent = strip_tags($item->HtmlContent);
                }

                $desc2_node = $this->_finder_handle->query($this->_advDescription2, $node)->item(0);
                if(!is_null($desc2_node))
                {
                    $item->HtmlContent .= "<br>".$desc2_node->nodeValue;
                    $item->PlainTextContent .= "\n".strip_tags($desc2_node->nodeValue);
                }

                // INTERNAL LINKS
                $innerlinks_node = $this->_finder_handle->query($this->_advInternalLinks, $node);
                $il = Array();

                if($innerlinks_node->length > 0)
                {
                    /* @var $link_node \DOMElement */
                    foreach($innerlinks_node as $link_node)
                    {
                        $link = $this->_finder_handle->query($this->_advInternalLinksLink, $link_node);

                        /* @var $l1 \DOMElement */
                        $l1 = $link->item(0);

                        /* @var $l2 \DOMElement */
                        $l2 = $link->item(1);

                        $il[] = new GoogleSearchAdvertisementElementInternalLink($l2->nodeValue, $l2->getAttribute("href"), $l1->getAttribute("href"));
                    }
                }

                $item->InternalLinks = $il;

                // BREADCRUMBS
                $breadcrumbs_node = $this->_finder_handle->query($this->_advBreadcrumbs, $node);
                $bc = Array();

                if($breadcrumbs_node->length > 0)
                {
                    $s = $this->_innerXML($breadcrumbs_node->item(0));
                    $s = strip_tags($s);
                    $bc = explode("›", $s);
                }

                $item->Breadcrumbs = $bc;

                //LOCATION ON PAGE
                $location_node = $node->parentNode->parentNode;
                if($location_node->getAttribute("id") == "tads") { $item->Location = GoogleSearchLocationEnum::TOP; }
                else if($location_node->getAttribute("id") == "mbEnd") { $item->Location = GoogleSearchLocationEnum::RIGHT; }
                else if($location_node->getAttribute("id") == "tadsb") { $item->Location = GoogleSearchLocationEnum::BOTTOM; }

                //ADDRESS
                $address_node = $this->_finder_handle->query($this->_advAddress, $node);
                if($address_node->length > 0)
                {
                    /* @var $a \DOMElement */
                    $a = $address_node->item(0);
                    $al = $a->getAttribute("href");

                    $addr = new GoogleSearchAdvertisementElementAddress();
                    $addr->Label = $a->textContent;
                    $addr->AdvertisementLink = new GoogleSearchElementLink($al, $addr->Label);
                    if(isset($addr->AdvertisementLink->Parts->query->adurl) && preg_match('#maps\.google\.com#', $addr->AdvertisementLink->Parts->query->adurl) > 0) $addr->GMapsLink = new GoogleSearchElementLink($addr->AdvertisementLink->Parts->query->adurl, $addr->Label);

                    $aph_node = $this->_finder_handle->query($this->_advAddressPhNu, $a->parentNode);
                    if($aph_node->length > 0)
                    {
                        $addr->PhoneNumber = $aph_node->item(0)->textContent;
                    }

                    $item->Address = $addr;
                }

                $results[] = $item;
            }
        }

        return $results;
    }

    private function _innerXML(\DOMNode $node)
    {
        $doc = $node->ownerDocument;
        $frag = $doc->createDocumentFragment();

        /* @var $child \DOMElement */
        foreach ($node->childNodes as $child)
        {
            $frag->appendChild($child->cloneNode(true));
        }

        return $doc->saveXML($frag);
    }

    private function _parse_statistics(&$gsr)
    {
        $nodes = $this->_finder_handle->query($this->_statsMain);
        if($nodes->length > 0)
        {
            /* @var $node \DOMElement */
            $node = $nodes->item(0);
            $time_node = $this->_finder_handle->query($this->_statsTimeload, $node)->item(0);
            $node->removeChild($time_node);

            $results_count = 0;
            preg_match_all("#\\d#", $node->textContent, $m);
            if(isset($m[0]) && !empty($m[0])) { $results_count = intval(join("", $m[0])); }

            $time = str_replace(",", ".", $time_node->textContent);
            preg_match_all("#[\\d\\.]#", $time, $m);
            if(isset($m[0]) && !empty($m[0])) { $time = floatval(join("", $m[0])); }

            $stats['CountAll'] = $results_count;
            $stats['LoadTime'] = $time;
        }

        $stats['CountResults'] = count($gsr);
        $stats['Link'] = $this->GetLink();
        $stats['Query'] = $this->Query;
        $stats['CacheFile'] = $this->CacheDirectory.md5($this->GetLink()).".html";
        $stats['ResultsPage'] = $this->Page+1;

        $gsr->Statistics = (object)$stats;
    }
}

?>