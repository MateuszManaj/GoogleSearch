<?php
/**
 * This is the Google search API without any authorization keys.
 *
 * See COPYING for license information.
 *
 * @author Mateusz Manaj <mmanaj@softgraf.pl>
 * @copyright Copyright (c) 2015
 * @package GoogleSearch
 */

namespace GoogleSearch;

class GoogleSearchResult 
{
    public $Link = "";
    public $Label = "";
    public $HtmlContent = "";
    public $PlainTextContent = "";
    public $InternalLinks = NULL;
    public $Breadcrumbs = Array();

    public function __construct($label = "", $link = "", $html_content = "", $plain_text_content = "", $internal_links = NULL, $breadcrumbs = Array())
    {
        $this->Label = $label;
        $this->Link = $link;
        $this->HtmlContent = $html_content;
        $this->PlainTextContent = $plain_text_content;
        $this->InternalLinks = $internal_links;
        $this->Breadcrumbs = $breadcrumbs;
    }
}

?>