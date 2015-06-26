<?php
require_once 'goutte.phar';
use Goutte\Client;

/*sql*/
$connect_db = "localhost";
$connect_id = "root";
$connect_pw = "alue1029";
$db_name = "CrowdPress";
$connect = mysql_connect($connect_db,$connect_id,$connect_pw);
echo mysql_error();
mysql_set_charset('utf8');

$client = new Client();
$crawler = $client->request('GET','http://www.fashion-press.net/news/');

$entries = array();

$targetSelector = '.news-box';
$entries = $crawler->filter($targetSelector)->each(function ($node) {
    $title = $node->filter('.recent-news-title')->text();
    $link = $node->filter('.recent-news-title')->attr('href');
    $date = $node->filter('.date-genre')->text();
    return array("title" => $title, "link" => 'http://www.fashion-press.net'.$link, "date" => $date);
});

foreach ($entries as $entry) {
    $img = array("","","","","");
    $prcid = 2;
    $title = $entry["title"];
    $url = $entry["link"];
    $time = substr($entry["date"], 5);
    $prrid = preg_replace('/[^0-9]/', '', $url);
    $data = preg_replace('/[^0-9]/', '', $data);

    // if exsist skip
    $sql = "SELECT * FROM `release` WHERE `prcid` = '$prcid' AND `prrid` = '$prrid'";
    $result = mysql_db_query($db_name, $sql);
    echo mysql_error();
    if (mysql_num_rows($result)) {
        continue;
    }

    $crawler = $client->request('GET',$url);
    $img = $crawler->filter('.g-i')->each(function ($node) {
        $imgurl = str_replace("https", "http" , $node->filter('img')->attr('data-original') );
        $imgurl = str_replace("125x125_", "" , $imgurl );
        return $imgurl;
    });
    if (!$img[0]) {
        $img = $crawler->filter('.g-i')->each(function ($node) {
            $imgurl = str_replace("125x125_", "" , $node->filter('img')->attr('src') );
            $imgurl = "http://www.fashion-press.net/".$imgurl;
            return $imgurl;
        });
    }
    if (!$img[0]) {
        $img = $crawler->filter('.g-i-collection')->each(function ($node) {
            $imgurl = str_replace("100x150_", "" , $node->filter('img')->attr('src') );
            $imgurl = "http://www.fashion-press.net/".$imgurl;
            return $imgurl;
        });
    }
    $body = $crawler->filter('#entry-news')->html();
    $pos = strpos($body, '<!-- END//BookMarks -->');
    $body = substr($body, $pos+23);
    $body = str_replace('<p style="text-align: center;">', "" , $body);
    $body = strip_tags($body, '<p><br>');
    $body = mysql_real_escape_string($body);
    if (!mysql_num_rows($result)) {
        $sql = "INSERT INTO `release`(`prcid`, `prrid`, `url`, `cname`, `title`, `img1`, `img2`, `img3`, `img4`, `img5`, `time`, `body`) VALUES ('$prcid', '$prrid', '$url', 'ファッション', '$title', '$img[0]' ,'$img[1]', '$img[2]', '$img[3]', '$img[4]', '$time', '$body')";
        $result = mysql_db_query($db_name, $sql);
        echo mysql_error();
     }
}
