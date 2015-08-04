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

include_once("../Dist/GoogleSearch.php");
include_once("../Dist/GoogleSearchResults.php");
include_once("../Dist/GoogleSearchResult.php");
include_once("../Dist/GoogleSearchAdvertisementResult.php");
include_once("../Dist/GoogleSearchElementInternalLink.php");
include_once("../Dist/GoogleSearchAdvertisementElementInternalLink.php");
include_once("../Dist/GoogleSearchLocationEnum.php");
include_once("../Dist/GoogleSearchLanguageResultEnum.php");
include_once("../Dist/GoogleSearchElementLink.php");
include_once("../Dist/GoogleSearchElementAddress.php");
include_once("../Dist/GoogleSearchAdvertisementElementAddress.php");
include_once("../Dist/Exception/ExtensionException.php");

use GoogleSearch\GoogleSearch;
use GoogleSearch\Exception\ExtensionException;
use GoogleSearch\GoogleSearchElementLink;

/*
$gsl = new GoogleSearchElementLink("http://www.googleadservices.com/pagead/aclk?sa=L&ai=CXR3GOGC_Vf-bCoGXzAOO4YGQDbGg6I4C4ejSncECo5bR4DYIABACKAJg6aTFhdAaoAHFw73-A8gBAakC5qQBw6HMkT6qBCVP0A0DTZ73AtcEMmn34MQKnIzpcwVCjnV8u8sPOgKkbdpuJeJQiAYB0gYNEOjxvwkYi7XiiwMoB4AHo7zCAZAHA6gHosIbqAemvhvYBwE&ohost=www.google.pl&cid=5Gig8Vyl-taK3C3-QgcCir5vK5FTUvchy8Vp4gnP8zNHow&sig=AOD64_1kEdp8EnlPlgE_o4vLsuARfrg0bA&clui=9&ctype=50&rct=j&q=&ved=0CDcQmxBqFQoTCK7Nwc_2jMcCFaeUcgodtmcAlw&adurl=https://maps.google.pl/maps%3Foe%3Dutf-8%26gws_rd%3Dcr%26um%3D1%26ie%3DUTF-8%26daddr%3DHutnicza%2B9,%2B20-218%2BLublin,%2B20-218%2BLublin%26geocode%3DFdfeDQMdoMhYASmVYreqvFciRzFt9hPk2w6Rjw%26f%3Dd%26saddr%3D%26iwstate1%3Ddir:to%26fb%3D1%26slad%3D0ALHuxZppDWHMDv25LGPtKcrNSOi_kN3f4gChN3d3cuZG9icnluYWRydWsucGwvEkhodHRwOi8vd3d3LmRvYnJ5bmFkcnVrLnBsL09EWklFWl9SRUtMQU1PV0EvS29zenVsa2ktcGMtMTM1LTExMl8xMzUtLmh0bWwaKktvc3p1bGtpIFJla2xhbW93ZSBMdWJsaW4gLSBkb2JyeW5hZHJ1ay5wbCIgTmFkcnVrIG5hIGtvc3p1bGthY2gsIHNvbGlkbmllIGkqIXN6eWJrby4gV3N6eXN0a28gdyBkb2JyZWogY2VuaWUgIQ", "abc");
echo "<pre>";
print_r($gsl);
echo "</pre>";
exit;
*/

$gs = new GoogleSearch($_GET['q'], 1, 5);
//echo $gs->GetLink()."<br>";
//echo $gs->Html();
//exit;

echo "<pre>";
print_r($gs->FindSimilarQueries());
echo "</pre>";

?>