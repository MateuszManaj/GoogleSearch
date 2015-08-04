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

class GoogleSearchAdvertisementElementAddress extends GoogleSearchElementAddress
{
    public $AdvertisementLink = "";

    public function __construct($label = "", $gmaps_link = NULL, $phone_number = "", $advertisement_link = NULL)
    {
        $this->Label = $label;
        $this->GMapsLink = $gmaps_link;
        $this->PhoneNumber = $phone_number;
        $this->AdvertisementLink = $advertisement_link;
    }
}

?>