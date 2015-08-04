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

class GoogleSearchAdvertisementElementInternalLink extends GoogleSearchElementInternalLink
{
    public $Label = "";
    public $AdvertisementLink = "";
    public $Link = "";
    public $HtmlContent = "";
    public $PlainTextContent = "";

    public function __construct($label, $link, $advertisement_link = "", $html_content = "", $plain_text_content = "")
    {
        $this->Label = $label;
        $this->Link = new GoogleSearchElementLink($link, $label);
        $this->AdvertisementLink = new GoogleSearchElementLink($advertisement_link, $label);
        $this->HtmlContent = $html_content;
        $this->PlainTextContent = $plain_text_content;
    }
}

?>