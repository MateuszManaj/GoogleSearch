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

class GoogleSearchAdvertisementResult extends GoogleSearchResult
{
    public $AdvertisementLink = "";
    public $Location = NULL;
    public $PhoneNumber = NULL;
    public $Address = NULL;

    public function __construct($label = "", $link = "", $html_content = "", $plain_text_content = "", $internal_links = NULL, $breadcrumbs = Array(), $advertisement_link = "", GoogleSearchLocationEnum $location = NULL, $phone_number = NULL, $address = NULL)
    {
        $this->Label = $label;
        $this->AdvertisementLink = $advertisement_link;
        $this->Link = $link;
        $this->HtmlContent = $html_content;
        $this->PlainTextContent = $plain_text_content;
        $this->InternalLinks = $internal_links;
        $this->Breadcrumbs = $breadcrumbs;
        $this->Location = $location;
        $this->PhoneNumber = $phone_number;
        $this->Address = $address;
    }
}

?>