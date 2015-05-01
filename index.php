<?php
// this is a 2k micro mvc 'thing'.., what you looking for in inside ./application directory
// based on http://www.henriquebarroso.com/how-to-create-a-simple-mvc-framework-in-php/
error_reporting( E_ALL );
ini_set( "display_errors", 1 );

session_start();

//32バイトのCSRFトークンを作成
function get_csrf_token() {
  $TOKEN_LENGTH = 16;//16*2=32バイト
  $bytes = openssl_random_pseudo_bytes($TOKEN_LENGTH);
  return bin2hex($bytes);
}

//トークンをセッションにセット
function setToken(){
    $token = get_csrf_token();
    $_SESSION['token'] = $token;
}

//トークンをセッションから取得
function checkToken(){
    //セッションが空か生成したトークンと異なるトークンでPOSTされたときは不正アクセス
    if(empty($_SESSION['token']) || ($_SESSION['token'] != $_POST['token'])){
        echo '不正なPOSTが行われました', PHP_EOL;
        exit;
    }
}

$uri = array();

if( isset( $_GET['route'] ) ){
	$array_tmp_uri = preg_split('[\\/]', $_GET['route'], -1, PREG_SPLIT_NO_EMPTY);
	$uri['controller'] = @ $array_tmp_uri[0];
	$uri['method']     = @ $array_tmp_uri[1];
	$uri['var']        = @ $array_tmp_uri[2];
}
else{
	$uri['controller'] = "home";
	$uri['method']     = "index";
	$uri['var']        = "";
}

//Load config and base
require_once("application.config.php");
require_once("application/base.php");

//loads controller
$application = new application( $uri );

