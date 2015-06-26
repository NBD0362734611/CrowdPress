<?php
/*sql*/
$connect_db = "localhost";
$connect_id = "root";
$connect_pw = "alue1029";
$db_name = "CrowdPress";
$connect = mysql_connect($connect_db,$connect_id,$connect_pw);
//おまじない
mysql_query("SET NAMES utf8",$connect);

$result = 1;
for ($page = 1; $result; $page++) {
  $nstr = nikkei_get_nstr($page);
  $relIDend = 6;
  $relIDstart[0] = 0;
  $i = 0;
  $nurl = "http://release.nikkei.co.jp/?page=".$page."";
  while(strpos ( "$nstr" , '"detail.cfm?relID=',$relIDstart[$i]+1)){
    $i++;
    $relIDstart[$i] = strpos ( "$nstr" , '"detail.cfm?relID=',$relIDstart[$i-1]+1);
    $relID[$i] = substr ("$nstr" , $relIDstart[$i] + 18  , $relIDend );
    $sql = "SELECT * FROM `release` WHERE `prrid` = ".$relID[$i] .";";
    $result = mysql_db_query($db_name, $sql);
    var_dump($sql);
    var_dump(mysql_num_rows($result));
    if(mysql_num_rows($result)==0){
        $prstr = nikkei_get_prstr($relID[$i]);
      //タイトル
        $titlename = nikkei_get_title($prstr);
      //詳細
        $cname = nikkei_get_cname($relID[$i],$prstr);
      //会社名
        $cid = nikkei_get_cid($relID[$i],$prstr);
        if(!isset($cid)||$cid<0){
          $cid = 0;
        }
        //本文
        $body = nikkei_get_article($relID[$i],$prstr);
        //iイメージ
        $prurl = "http://release.nikkei.co.jp/detail.cfm?relID=0".$relID[$i]."";
        $img[1] = "";
        $img[2] = "";
        $img[3] = "";
        $img[4] = "";
        $img[5] = "";
        $flg = 1;
        for ($j=1; $j < 6; $j++) {
          $imgurl[$j] = "http://release.nikkei.co.jp/attach_file/0".$relID[$i]."_0".$j.".jpg";
          $imgstrsjis[$j] = file_get_contents($imgurl[$j]);
          $imgstrutf8[$j] = mb_convert_encoding($imgstrsjis[$j], "UTF-8", "SJIS");
          $response[$j] = strpos($imgstrutf8[$j], "エラー");
          if ($response[$j] === false){
            $img[$flg]=$imgurl[$j];
            $flg++;
          }
        }
        $sql = "INSERT INTO `release`(`prcid`,`prrid`,`url`,`sid`,`cname`,`title`,`img1`,`img2`,`img3`,`img4`,`img5`,`body`) ";
        $sql .= "VALUES (1,".$relID[$i].",'".$prurl."',".$cid.",'".$cname."','".$titlename."','".$img[1]."','".$img[2]."','".$img[3]."','".$img[4]."','".$img[5]."', '".$body."');";
        echo $sql;
        echo "<br>";
        $result = mysql_db_query($db_name, $sql);
        var_dump($result);
        echo "<br>";

     }else{
      exit;
     }
  }
}
function nikkei_get_nstr($page){
  $nurl = "http://release.nikkei.co.jp/?page=".$page."";
  $nstrsjis = file_get_contents("$nurl");
  $nstr = mb_convert_encoding($nstrsjis, "UTF-8", "SJIS");
  $nstr = file_get_contents("$nurl");
  return $nstr;
}
function nikkei_get_prstr($relID){
  $prurl = "http://release.nikkei.co.jp/detail.cfm?relID=".$relID."";
  $prstrsjis = file_get_contents("$prurl");
  $prstr = mb_convert_encoding($prstrsjis, "UTF-8", "SJIS");
  return $prstr;
}
function nikkei_get_title($prstr){
  $titlestart = strpos ( "$prstr" , '<h1 id="heading" class="heading">') + 33;
  $titleend = strpos ( "$prstr" , '</h1>' , $titlestart) - $titlestart;
  $titlename = substr ("$prstr" , $titlestart, $titleend );
  return $titlename;
}
function nikkei_get_cname($relID,$prstr){
  $cnamestart = strpos ( "$prstr" , '企業名') + 20;
  $cnameend = strpos ( "$prstr" , '|' , $cnamestart) - $cnamestart-6;
  if($cnameend<=0){
    $cnameend = 90;
  }
  $cname = strip_tags(substr ("$prstr" , $cnamestart, $cnameend ));
  return $cname;
}
function nikkei_get_cid($relID,$prstr){
  $cidstart = strpos ( "$prstr" , '株式コード：') + 18;
  $cidend = strpos ( "$prstr" , '</a>' , $cidstart) - $cidstart;
  if($cidstart != 18){
    $cid = substr ("$prstr" , $cidstart, $cidend );
    return $cid;
  }
}
function nikkei_get_article($relID,$prstr){

  $titlestart = strpos ( "$prstr" , '<h1 id="heading" class="heading">') + 33;
  $bodystart = strpos("$prstr", '<p>', $titlestart);
  $bodyend = strpos ( "$prstr" , '</p>' , $bodystart) - $bodystart + 4;
  $body = substr ("$prstr" , $bodystart, $bodyend );
  return $body;
}
function nikkei_get_img($relID){
  $img = "";
  $flg = 0;
  for ($i=1; $i < 4; $i++) {
    $imgurl[$i] = "http://release.nikkei.co.jp/attach_file/0".$relID."_0".$i.".jpg";
    $imgstrsjis[$i] = file_get_contents($imgurl[$i]);
    $imgstrutf8[$i] = mb_convert_encoding($imgstrsjis[$i], "UTF-8", "SJIS");
    $response[$i] = strpos($imgstrutf8[$i], "エラー");
    if ($response[$i] === false){
      $img .= "<div class='article_img_box'><a href='".$imgurl[$i]."' target='_blank'><img class='article_img' src='".$imgurl[$i]."'></a></div>";
      $flg++;
    }
  }
  if($flg==0){
    $img = "<div class='article_img_box><img class='article_img' src='img/no_image.jpg'></div>";
  }
  return $img;
}
?>
