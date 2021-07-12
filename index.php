<?php
include_once('src/scrapTiktok.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$tt = new scrapTiktok();

#scrapping tiktok profile
    $url = 'https://www.tiktok.com/@dewiperssik_real?lang=en';
    $url = 'https://www.tiktok.com/@natgeo?lang=en';
    $tt->echoPre($tt->getProfile($url, true));

#scrapping tiktok post data
    $url = 'https://www.tiktok.com/@marvel/video/6981861855081811206?lang=en&is_copy_url=1&is_from_webapp=v1';
    #$tt->echoPre($tt->getPost($url, true));

#scrapping tiktok hashtag data
    $url = 'https://www.tiktok.com/tag/marvelcomics?lang=en&is_copy_url=1&is_from_webapp=v1';
    #$tt->echoPre($tt->getHashtag($url, true));


?>