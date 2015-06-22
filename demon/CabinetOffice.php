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

$datas = array();

$url = "http://www.kantei.go.jp/index-jnews.rdf";
$rss = simplexml_load_file($url);

foreach ($rss->channel->item as $item) {
    $datas[] = array('url' => (string)$item->link, 'title'=>(string)$item->title, 'date' => strtotime($item->pubDate));
}

foreach ($datas as $data) {
    $title = $data["title"];
    $url = $data["url"];
    $prrid = $data["date"];
    $time = date("Y-m-d H:i:s",$prrid);
    $prcid = 3;

    // if exsist skip
    $sql = "SELECT * FROM `release` WHERE `prcid` = '$prcid' AND `prrid` = '$prrid'";
    $result = mysql_db_query($db_name, $sql);
    echo mysql_error();
    if (mysql_num_rows($result)) {
        continue;
    }

    $crawler = $client->request('GET',$url);
    // $text = $crawler->filter('.text')->html();
    $img = array("","","","","");
    $body = $crawler->html();
    if (strpos($body, '<div class="text">')) {
        $body = $crawler->filter('div.text')->html();
    }else{
        $pos_start = strpos($body, '<!-- /.actionVisualBlock -->');
        $body = substr($body, $pos_start+35);
        $pos_end = strpos($body, '<!-- /.contentBlock -->');
        $body = substr($body, 0, $pos_end);
    }
    $body = strip_tags($body, '<p><br>');
    $img = $crawler->filter('p.photo')->each(function ($node) {
        $imgurl = $node->filter('img')->attr('src');
        $pos = strpos($imgurl, '?');
        $imgurl = substr($imgurl, 0, $pos);
        return "http://www.kantei.go.jp".$imgurl;
    });
    if (!mysql_num_rows($result)) {
        $sql = "INSERT INTO `release`(`prcid`, `prrid`, `url`, `cname`, `title`, `img1`, `img2`, `img3`, `img4`, `img5`, `time`, `body`) VALUES ('$prcid', '$prrid', '$url', '首相官邸', '$title', '$img[0]' ,'$img[1]', '$img[2]', '$img[3]', '$img[4]', '$time', '$body')";
        $result = mysql_db_query($db_name, $sql);
        echo mysql_error();
     }
}


