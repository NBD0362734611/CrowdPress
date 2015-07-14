<?php
// require_once("aws.phar");
require_once('vendor/autoload.php');
use Aws\S3\S3Client;
use Aws\Common\Enum\Region;
use Aws\S3\Exception\S3Exception;
use Guzzle\Http\EntityBody;
// use Aws\S3\MultipartUploader;
// use Aws\Exception\MultipartUploadException;

class pages extends controller {
    function help()
    {
        $this->loadView( "pages/help" );
    }

    function error()
    {
        $this->loadView( "pages/error" );
    }

    function nopage()
    {
        $this->loadView( "pages/404" );
    }

    function terms()
    {
        $this->loadView( "pages/terms" );
    }

    function privacy()
    {
        $this->loadView( "pages/privacy" );
    }

    function commentNumUpdate(){
        $release = $this->loadModel( "release" );
        for ($i=813523; $i < 816235; $i++){
            $isReleae = $release->isRlease($i);
            if ( $isReleae ) {
                $release->release_comment_number_update($i);
            }else{
                continue;
            }
        }
    }

    function newrelease()
    {
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );
        $newrelease = 1;
        $data = array();
        $user_data = array();
        $release_comment_data = array();
        $clap_user_list = array();
        $scrap_user_list = array();
        $source = array();

        // get the user data from database
        if ( isset($_SESSION["user"]) ){
            $user_data = $user->find_by_id( $_SESSION["user"] );
        }

        $release_data = $release->get_new_release();
        $tags = $release->get_tagcloud();
        if ( isset($release_data) ) {
            foreach ($release_data as $release1) {
                $rid = $release1["rid"];
                $release_comment_data[$rid] = $user->release_comment_select($rid);
                $clap_user_list[$rid] = $user->get_clap_user_list($rid);
                $scrap_user_list[$rid] = $user->get_scrap_user_list($rid);
                switch ( $release1["prcid"] ){
                case 1:
                    $source[$rid] = "nikkei";
                    break;
                case 2:
                    $source[$rid] = "fashion";
                    break;
                case 3:
                    $source[$rid]  = "politics";
                    break;
                }
            }
        } else {
            $release_data = array();
        }

        // load profile view
        $data = array( "user_data" => $user_data, "release_data" => $release_data, "release_comment_data" => $release_comment_data, "source" => $source, "tags" => $tags, "newrelease" => $newrelease);
        $this->loadView( "pages/newrelease", $data );
    }

    function prcid_to_category($prcid){
        switch ( $prcid ){
                case 0:
                    $source= "unclassified";
                    break;
                case 1:
                    $source = "nikkei";
                    break;
                case 2:
                    $source = "fashion";
                    break;
                case 3:
                    $source  = "politics";
                    break;
        }
        return $source;
    }

    function loadrelease()
    {
        $data = array();
        $user_data = array();
        $release_comment_data = array();
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        // get the user data from database
        if ( isset($_SESSION["user"]) ){
            $user_data = $user->find_by_id( $_SESSION["user"] );
        }

        $start = escape( $_POST["count"] ) * 50;
        $prcid = 0;
        $sort  = 0;
        $words = 0;
        $cname = 0;
        $tag = 0;
        if ( isset($_POST["prcid"]) ) {
            $prcid = escape( $_POST["prcid"]);
        }
        if ( isset($_POST["sort"]) ) {
            $sort = escape( $_POST["sort"]);
        }
        if ( isset($_POST["keyword"]) ) {
            $keyword = escape( $_POST["keyword"] );
            $keyword = mb_convert_encoding($keyword,"UTF-8","UTF-8,EUC-JP,SJIS,Shift_JIS,ASCII");
            //全角空白があったら半角空白にそろえる
            $words = str_replace("　", " ", $keyword);
            $words = trim($words);
        }
        if ( isset($_POST["cname"]) ) {
            $cname = escape( $_POST["cname"]);
        }
        if ( isset($_POST["tag"]) ) {
            $tag = escape( $_POST["tag"]);
        }

        $release_data = $release->get_new_release( $start, $prcid, $sort, $words, $cname, $tag );
        $release_comment_data = array();
        $source = array();

        if ( isset($release_data) ) {
            foreach ($release_data as $release) {
                $row = $user->release_comment_select($release["rid"]);
                $release_comment_data[$release["rid"]] = $row;
                $source[$release["rid"]] = $this->prcid_to_category($release["prcid"]);
            }
        } else {
            $release_data = array();
        }
        $html = "";
        if ( $release_data ) {
            foreach ($release_data as $release) {
                $html .= '<div class="post-area clear-after">';
                $html .= '<section role="main" class="release">';
                $html .= '<h3 class="release-title ';
                $html .= $source[$release["rid"]];
                $html .= '">';
                $html .= '<a href="?route=pages/release_detail/';
                $html .= $release["rid"];
                $html .= '">';
                $html .= $release["title"];
                $html .= '</a></h3>';
                $html .= '<div class="portfolio-section preload">';
                if( !empty($release["img1"]) ) {
                    $html .= '<span class="item column fourth"><figure><img src=';
                    $html .= $release["img1"];
                    $html .= '></figure></span>';
                }
                if( !empty($release["img2"]) ) {
                    $html .= '<span class="item column fourth"><figure><img src=';
                    $html .= $release["img2"];
                    $html .= '></figure></span>';
                }
                if( !empty($release["img3"]) ) {
                    $html .= '<span class="item column fourth"><figure><img src=';
                    $html .= $release["img3"];
                    $html .= '></figure></span>';
                }
                $html .= '</div></section>';
                $html .= '<div class="widget meta-social column half">';
                $html .= '<ul class="inline">';
                $html .= '<li><a class="release-comment-toggle border-box"><i class="fa fa-comment-o fa-lg"></i></a></li>';
                $html .= '<li><a class="clap border-box" rid="';
                $html .= $release["rid"];
                $html .= '"><i class="fa fa-heart-o fa-lg"></i></a><span class="arrow_box">';
                $html .= $release["clap"];
                $html .= '</span></li>';
                $html .= '<li><a class="scrap border-box" rid="';
                $html .= $release["rid"];
                $html .='"><i class="fa fa-paperclip fa-lg"></i></a><span class="arrow_box">';
                $html .= $release["scrap"];
                $html .= '</span></li>';
                $html .= '</ul></div>';
                $html .= '<div class="column half right last">';
                $html .= '<h5 class="meta-post"><a class="company-name" href="?route=pages/release_sort_by_cname/';
                $html .=  $release["cname"];
                $html .= '">';
                $html .= $release["cname"];
                $html .= '</a> - <time datetime>';
                $html .= $release["time"];
                $html .= '</time></h5>';
                $html .= '</div>';
                $html .= '<div class="clear"></div>';
                $html .= '<form class="release-comment" style="display: none" action="?route=pages/release_comment_insert" method="post" >';
                $html .= '<input type="hidden" name="rid" value="';
                $html .=  $release["rid"];
                $html .= '" />';
                $html .=  '<input type="hidden" name="user_id" value="';
                if ( isset($_SESSION["user"]) ) {
                    $html .= $_SESSION["user"];
                }
                $html .= '" />';
                $html .= '<input type="hidden" name="token" value="';
                if ( isset($_SESSION['token']) ) {
                    $html .= $_SESSION['token'];
                }
                $html .= '">';
                $html .= '<input type="text" name="comment" value="リリースにコメントする" /></form>';
                $html .= '<div class="comment-area">';
                if(is_array($release_comment_data[$release["rid"]])){
                    foreach ($release_comment_data[$release["rid"]] as $release_comment) {
                        $html .= '<section class="line_wrapper">
                                    <div class="question_Box inline">
                                        <div class="question_image column inline-block">';
                        $html .= '<img src="';
                        $html .= $release_comment["photo_url"];
                        $html .= '" alt="ユーザーの写真"/>';
                        $html .= '<h5 class="username"><a href="?route=users/profile_user/';
                        $html .= $release_comment["id"];
                        $html .= '">';
                        $html .= $release_comment["display_name"];
                        $html .= '</a></h5></div>';
                        $html .= '<p class="arrow_question column nine reset inline-block">';
                        $html .= $release_comment["comment"];
                        $html .= '<div class="clear"></div></section>';
                    }
                }
                $html .= '</div>';
                $html .= '</div>';
            }
        }else{
            $html = '';
        }
        echo $html;
    }

    function release_sort_by_cname( $cname )
    {
        $data = array();
        $user_data = array();
        $cname = escape( $cname );

        // error_reporting(E_ALL ^ E_NOTICE);
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );
        $tags = $release->get_tagcloud();

        // get the user data from database
        if ( isset($_SESSION["user"]) ){
            $user_data = $user->find_by_id( $_SESSION["user"] );
        }

        $release_data = $release->find_release_by_cname( $cname );
        $release_comment_data = array();
        $source = array();

        if (isset($release_data)) {
            foreach ($release_data as $release) {
            if ( isset($_SESSION["user"]) ) {
                    $row = $user->release_comment_select($release["rid"], $_SESSION["user"]);
                }else{
                    $row = $user->release_comment_select($release["rid"]);
                }
                $release_comment_data[$release["rid"]] = $row;
                // switch ( $release["prcid"] ){
                // case 1:
                //     $source[$release["rid"]] = "nikkei";
                //     break;
                // case 2:
                //     $source[$release["rid"]] = "fashion";
                //     break;
                // case 3:
                //     $source[$release["rid"]]  = "politics";
                //     break;
                // }
                $source[$release["rid"]] = $this->prcid_to_category($release["prcid"]);
            }
        } else {
            $release_data = array();
        }

        // $release_comment_data = $user->release_comment_select($rid, $_SESSION["user"]);

        // load profile view
        $data = array( "user_data" => $user_data, "release_data" => $release_data, "release_comment_data" => $release_comment_data, "cname" => $cname, "tags" => $tags, "source" => $source);
        $this->loadView( "pages/newrelease", $data );
    }

       function release_search_by_title( $title )
    {
        $data = array();
        $user_data = array();

        $title = escape( $title );
        $keyword = mb_convert_encoding($title,"UTF-8","UTF-8,EUC-JP,SJIS,Shift_JIS,ASCII");
        //全角空白があったら半角空白にそろえる
        $words = str_replace("　", " ", $keyword);
        $words = trim($words);

        // error_reporting(E_ALL ^ E_NOTICE);
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        // get the user data from database
        if ( isset($_SESSION["user"]) ){
            $user_data = $user->find_by_id( $_SESSION["user"] );
        }

        $tags = $release->get_tagcloud();

        $start = 0;
        $prcid = 0;
        $sort  = 0;
        if (isset($_POST["count"])) {
            $start = $_POST["count"];
        }
        if (isset($_POST["prcid"])) {
            $prcid = $_POST["prcid"];
        }
        if (isset($_POST["sort"])) {
            $sort = $_POST["sort"];
        }
        $release_data = $release->find_release_by_title( $words, $start, $prcid, $sort );
        $release_comment_data = array();
        $source = array();

        if (isset($release_data)) {
            foreach ($release_data as $release) {
                if ( isset($_SESSION["user"]) ) {
                    $row = $user->release_comment_select($release["rid"], $_SESSION["user"]);
                }else{
                    $row = $user->release_comment_select($release["rid"]);
                }
                $release_comment_data[$release["rid"]] = $row;
                switch ( $release["prcid"] ){
                case 1:
                    $source[$release["rid"]] = "nikkei";
                    break;
                case 2:
                    $source[$release["rid"]] = "fashion";
                    break;
                case 3:
                    $source[$release["rid"]]  = "politics";
                    break;
                }
            }
        } else {
            $release_data = array();
        }

        // load profile view
        $data = array( "user_data" => $user_data, "release_data" => $release_data, "release_comment_data" => $release_comment_data, "title" => $title, "tags" => $tags, "source" => $source);
        $this->loadView( "pages/newrelease", $data );
    }

    function release_by_tag( $tag )
    {
        $data = array();
        $user_data = array();

        // error_reporting(E_ALL ^ E_NOTICE);
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );
        $tags = $release->get_tagcloud();

        // get the user data from database
        if ( isset($_SESSION["user"]) ){
            $user_data = $user->find_by_id( $_SESSION["user"] );
        }

        $start = 0;
        $prcid = 0;
        $sort  = 0;
        if (isset($_POST["count"])) {
            $start = $_POST["count"];
        }
        if (isset($_POST["prcid"])) {
            $prcid = $_POST["prcid"];
        }
        if (isset($_POST["sort"])) {
            $sort = $_POST["sort"];
        }

        $release_data = $release->find_release_by_tag( $tag, $start, $prcid, $sort );
        $release_comment_data = array();
        $source = array();

        if (isset($release_data)) {
            foreach ($release_data as $release) {
                if ( isset($_SESSION["user"]) ) {
                    $row = $user->release_comment_select($release["rid"], $_SESSION["user"]);
                }else{
                    $row = $user->release_comment_select($release["rid"]);
                }
                $release_comment_data[$release["rid"]] = $row;
                switch ( $release["prcid"] ){
                case 1:
                    $source[$release["rid"]] = "nikkei";
                    break;
                case 2:
                    $source[$release["rid"]] = "fashion";
                    break;
                case 3:
                    $source[$release["rid"]]  = "politics";
                    break;
                }
            }
        } else {
            $release_data = array();
        }

        // load profile view
        $data = array( "user_data" => $user_data, "release_data" => $release_data, "release_comment_data" => $release_comment_data, "tag" => $tag, "tags" =>$tags, "source" => $source);
        $this->loadView( "pages/newrelease", $data );
    }


    function scrap()
    {
        $data = array();
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        //ログインしていない場合
        if (!isset($_SESSION["user"])){
            $this->redirect( "users/login" );
        }

        $user_data = $user->find_by_id( $_SESSION["user"] );
        $release_data = $release->get_user_scrap( $_SESSION["user"] );
        $release_comment_data = array();
        $publish_comment_data = array();
        $source = array();

        if (isset($release_data)){
            foreach ($release_data as $release) {
            $row = $user->release_comment_select($release["rid"], $_SESSION["user"]);
            $release_comment_data[$release["rid"]] = $row;
            $row = $user->latest_publish_comment_select($release["rid"], $_SESSION["user"]);
            $publish_comment_data[$release["rid"]] = $row;
            switch ( $release["prcid"] ){
                case 1:
                    $source[$release["rid"]] = "nikkei";
                    break;
                case 2:
                    $source[$release["rid"]] = "fashion";
                    break;
                case 3:
                    $source[$release["rid"]]  = "politics";
                    break;
                }
            }
        } else {   // １件もない場合のエラー対策
            $release_data = array();
        }

        // load profile view
        $data = array( "user_data" => $user_data, "release_data" => $release_data, "release_comment_data" => $release_comment_data, "publish_comment_data" => $publish_comment_data, "source" => $source);
        $this->loadView( "pages/scrap", $data );
    }

    function loadscrap()
    {
        $data = array();
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        $user_data = $user->find_by_id( $_SESSION["user"] );
        $start = escape( $_POST["count"] ) * 50;
        $release_data = $release->get_user_scrap( $_SESSION["user"], $start );
        $release_comment_data = array();
        $publish_comment_data = array();

        if (isset($release_data)){
            foreach ($release_data as $release) {
            $row = $user->release_comment_select($release["rid"], $_SESSION["user"]);
            $release_comment_data[$release["rid"]] = $row;
            $row = $user->latest_publish_comment_select($release["rid"], $_SESSION["user"]);
            $publish_comment_data[$release["rid"]] = $row;
            }
        } else {   // １件もない場合のエラー対策
            $release_data = array();
        }

        $html = "";
        if ( $release_data ) {
            foreach ($release_data as $release) {
                $html .= '<div class="post-area clear-after">';
                $html .= '<section role="main" class="release">';
                $html .= '<h3 class="release-title">';
                $html .= '<a href="?route=pages/release_detail/';
                $html .= $release["rid"];
                $html .= '">';
                $html .= $release["title"];
                $html .= '</a></h3>';
                $html .= '<div class="portfolio-section preload">';
                if( !empty($release["img1"]) ) {
                    $html .= '<span class="item column fourth"><figure><img src=';
                    $html .= $release["img1"];
                    $html .= '></figure></span>';
                }
                if( !empty($release["img2"]) ) {
                    $html .= '<span class="item column fourth"><figure><img src=';
                    $html .= $release["img2"];
                    $html .= '></figure></span>';
                }
                if( !empty($release["img3"]) ) {
                    $html .= '<span class="item column fourth"><figure><img src=';
                    $html .= $release["img3"];
                    $html .= '></figure></span>';
                }
                $html .= '</div></section>';
                $html .= '<div class="widget meta-social column half">';
                $html .= '<ul class="inline">';
                $html .= '<li><a class="release-comment-toggle border-box"><i class="fa fa-comment-o fa-lg"></i></a></li>';
                $html .= '<li><a class="clap border-box" rid="';
                $html .= $release["rid"];
                $html .= '"><i class="fa fa-heart-o fa-lg"></i></a><span class="arrow_box">';
                $html .= $release["clap"];
                $html .= '</span></li>';
                $html .= '<li><a class="scrap border-box" rid="';
                $html .= $release["rid"];
                $html .='"><i class="fa fa-paperclip fa-lg"></i></a><span class="arrow_box">';
                $html .= $release["scrap"];
                $html .= '</span></li>';
                $html .= '</ul></div>';
                $html .= '<div class="column half right last">';
                $html .= '<h5 class="meta-post"><a class="company-name" href="?route=pages/release_sort_by_cname/';
                $html .=  $release["cname"];
                $html .= '">';
                $html .= $release["cname"];
                $html .= '</a> - <time datetime>';
                $html .= $release["time"];
                $html .= '</time></h5>';
                $html .= '</div>';
                $html .= '<div class="clear"></div>';
                $html .= '<form class="release-comment" style="display: none" action="?route=pages/release_comment_insert" method="post" >';
                $html .= '<input type="hidden" name="rid" value="';
                $html .=  $release["rid"];
                $html .= '" />';
                $html .=  '<input type="hidden" name="user_id" value="';
                if ( isset($_SESSION["user"]) ) {
                    $html .= $_SESSION["user"];
                }
                $html .= '" />';
                $html .= '<input type="hidden" name="token" value="';
                if ( isset($_SESSION['token']) ) {
                    $html .= $_SESSION['token'];
                }
                $html .= '">';
                $html .= '<input type="text" name="comment" value="リリースにコメントする" /></form>';
                if(is_array($release_comment_data[$release["rid"]])){
                    foreach ($release_comment_data[$release["rid"]] as $release_comment) {
                        $html .= '<section class="line_wrapper">
                                    <div class="question_Box inline">
                                        <div class="question_image column inline-block">';
                        $html .= '<img src="';
                        $html .= $release_comment["photo_url"];
                        $html .= '" alt="ユーザーの写真"/>';
                        $html .= '<h5 class="username"><a href="?route=users/profile_user/';
                        $html .= $release_comment["id"];
                        $html .= '">';
                        $html .= $release_comment["display_name"];
                        $html .= '</a></h5></div>';
                        $html .= '<p class="arrow_question column nine reset inline-block">';
                        $html .= $release_comment["comment"];
                        $html .= '<div class="clear"></div></section>';
                    }
                }
                $html .= '</div>';
            }
        }else{
            $html = '';
        }
        echo $html;
    }

    function scrap_sort_by_cname( $cname )
    {
        $data = array();
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );
        $cname = escape( $cname );

        //ログインしていない場合
        if (!isset($_SESSION["user"])){
            $this->redirect( "users/login" );
        }

        // get the release data from database

        // error_reporting(0);

        $user_data = $user->find_by_id( $_SESSION["user"] );
        $release_data = $release->find_scrap_by_cname( $_SESSION["user"], $cname );
        $release_comment_data = array();

        if (isset($release_data)){
            foreach ($release_data as $release) {
            $row = $user->release_comment_select($release["rid"], $_SESSION["user"]);
            $release_comment_data[$release["rid"]] = $row;
            $row = $user->latest_publish_comment_select($release["rid"], $_SESSION["user"]);
            $publish_comment_data[$release["rid"]] = $row;
            }
        } else {   // １件もない場合のエラー対策
            $release_data = array();
        }

        // load profile view
        $data = array( "user_data" => $user_data, "release_data" => $release_data, "release_comment_data" => $release_comment_data, "cname" => $cname, "publish_comment_data" => $publish_comment_data);
        $this->loadView( "pages/scrap", $data );
    }

    function scrap_search_by_title( $title )
    {
        $data = array();

        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        //ログインしていない場合
        if (!isset($_SESSION["user"])){
            $this->redirect( "users/login" );
        }

        $title     = escape( $title );
        $keyword   = mb_convert_encoding($title,"UTF-8","UTF-8,EUC-JP,SJIS,Shift_JIS,ASCII");
        //全角空白があったら半角空白にそろえる
        $words     = str_replace("　", " ", $keyword);
        $words     = trim($words);

        // get the release data from database

        // error_reporting(0);

        $user_data = $user->find_by_id( $_SESSION["user"] );
        $release_data = $release->find_scrap_by_title( $_SESSION["user"], $words );
        $release_comment_data = array();

        if (isset($release_data)){
            foreach ($release_data as $release) {
            $row = $user->release_comment_select($release["rid"], $_SESSION["user"]);
            $release_comment_data[$release["rid"]] = $row;
            $row = $user->latest_publish_comment_select($release["rid"], $_SESSION["user"]);
            $publish_comment_data[$release["rid"]] = $row;
            }
        } else {   // １件もない場合のエラー対策
            $release_data = array();
            $publish_comment_data = array();
        }

        // load profile view
        $data = array( "user_data" => $user_data, "release_data" => $release_data, "release_comment_data" => $release_comment_data, "title" => $title, "publish_comment_data" => $publish_comment_data);
        $this->loadView( "pages/scrap", $data );
    }

    function release_detail($rid)
    {
        $data = array();

        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        if ( isset( $_SESSION["user"] ) ) {
            $user_data = $user->find_by_id( $_SESSION["user"] );
        } else {
            $user_data = array();
        }

        $release_detail_data = $release->get_release_detail( $rid );
        $release_comment_data = $release->release_comment_ridselect($rid);
        $release_comment_number = $release->get_release_comment_number($rid);
        $prev_next_rid = $release->prev_next_rid($rid);
        $tags = $release->get_release_tags($rid);
        $clap_user_list = array();
        $clap_user_list = $user->get_clap_user_list($rid);
        $scrap_user_list = array();
        $scrap_user_list = $user->get_scrap_user_list($rid);

        // load profile view
        $data = array( "user_data" => $user_data, "release_detail_data" => $release_detail_data, "release_comment_data" => $release_comment_data, "release_comment_number" => $release_comment_number, "prev_next_rid" => $prev_next_rid, "tags" => $tags, "clap_user_list" => $clap_user_list, "scrap_user_list" => $scrap_user_list);
        $this->loadView( "pages/release", $data );
    }

    function clap_insert()
    {
        $data = array();

        // load user model
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        if (!isset($_SESSION["user"])){
            echo "この機能はログインしていないと使えません";
            return false;
        }

        // registration form submitted?
        if( count( $_POST ) ){
            $user_id = $_SESSION["user"];
            $rid     = escape( $_POST["rid"] );
        }
        echo json_encode( $release->clap_insert($user_id, $rid) );
    }

    function scrap_insert()
    {
        if (!isset($_SESSION["user"])){
            echo "この機能はログインしていないと使えません";
            return false;
        }

        $data = array();

        // load user model
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        // registration form submitted?
        if( count( $_POST ) ){
            $user_id = $_SESSION["user"];
            $rid     = escape( $_POST["rid"] );
        }
        echo json_encode( $release->scrap_insert($user_id, $rid) );
    }

    function paper_clap_insert()
    {
        $data = array();

        // load user model
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        if (!isset($_SESSION["user"])){
            echo "この機能はログインしていないと使えません";
            return false;
        }

        // registration form submitted?
        if( count( $_POST ) ){
            $user_id = $_SESSION["user"];
            $pid     = escape( $_POST["pid"] );
            $notice_user_id = $user->paperid_to_userid($pid);
        }
        list ($count, $kind_id, $flag) = $release->paper_clap_insert($user_id, $pid);
        echo json_encode( $count );
        $user->paper_comment_notice($pid, $notice_user_id, $user_id, 1, $kind_id, $flag);
    }

    function paper_scrap_insert()
    {
        $data = array();

        // load user model
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        if (!isset($_SESSION["user"])){
            echo "この機能はログインしていないと使えません";
            return false;
        }

        // registration form submitted?
        if( count( $_POST ) ){
            $user_id = $_SESSION["user"];
            $pid     = escape( $_POST["pid"] );
            $notice_user_id = $user->paperid_to_userid($pid);
        }
        list ($count, $kind_id, $flag) = $release->paper_scrap_insert($user_id, $pid);
        echo json_encode( $count );
        $user->paper_comment_notice($pid, $notice_user_id, $user_id, 2, $kind_id, $flag);
        $user->paper_scrap_number_update($user_id);
    }

    function scrap_paper_comment()
    {
        $data = array();

        // load user model
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        // トークンチェック
        checkToken();

        // registration form submitted?
        if( count( $_POST ) ){
            $user_id   = $_SESSION["user"];
            $rid       = escape( $_POST["rid"] );
            $headline  = escape( $_POST["headline"] );
            $comment   = escape( $_POST["comment"] );
        }
        $release->scrap_paper_comment($user_id, $rid, $headline, $comment);
        echo "OK";
    }

    function release_comment_insert_release()
    {
        if( !isset($_SESSION["user"])){
            echo "ログインしてください！";
            return false;
        }
        // load user model
        $uri = $_SERVER['HTTP_REFERER'];
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        // トークンチェック
        checkToken();

        if( count( $_POST ) ){
            $user_id   = $_SESSION["user"];
            $rid       = escape( $_POST["rid"] );
            $comment   = escape( $_POST["comment"] );
            $user->release_comment_insert(null, $rid, $user_id, $comment);
            $release->release_comment_number_update($rid);
            $release->release_comment_number_byuser_update($user_id);
        }
        $release_comment = $release->release_comment_ridselect( $rid );
        //echo json_encode( $release_comment );
        //post元に戻る
        header("Location: ".$uri);
    }

    function release_comment_insert()
    {
        if( !isset($_SESSION["user"])){
            echo "ログインしてください！";
            return false;
        }
        // load user model
        $uri = $_SERVER['HTTP_REFERER'];
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        // トークンチェック
        checkToken();

        if( count( $_POST ) ){
            $user_id   = $_SESSION["user"];
            $rid       = escape( $_POST["rid"] );
            $comment   = escape( $_POST["comment"] );
            $user->release_comment_insert(null, $rid, $user_id, $comment);
            $release->release_comment_number_update($rid);
            $release->release_comment_number_byuser_update($user_id);
        }
        $release_comment = $release->release_comment_ridselect( $rid );
        //echo json_encode( $release_comment );
        //post元に戻る
        //header("Location: ".$uri);
        $html = '';
        foreach ($release_comment as $comment){
            $html .= '<section class="line_wrapper">';
            $html .= '<div class="question_Box inline">';
            $html .= '<div class="question_image column inline-block">';
            $html .= "<img src=\"${comment['photo_url']}\" alt=\"ユーザーの写真\"/>";
            $html .= '<h5 class="username">';
            $html .= "<a href=\"?route=users/profile_user/${comment['id']}\">";
            $html .= $comment["display_name"];
            $html .= '</a></h5></div><p class="arrow_question column nine reset inline-block">';
            $html .= $comment["comment"];
            $html .= '</p></div><div class="clear"></div></section>';
        }
        echo $html;
    }

    function release_comment_remove()
    {
        if( !isset($_SESSION["user"])){
            echo "ログインしてください！";
            return false;
        }
        // load user model
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        // トークンチェック
        checkToken();

        if( count( $_POST ) ){
            $user_id   = $_SESSION["user"];
            $commentid = escape ( $_POST["commentid"] );
            $rid       = escape( $_POST["rid"] );
            $user->release_comment_remove( $commentid, $rid, $user_id );
            $release->release_comment_number_update($rid);
            $release->release_comment_number_byuser_update($user_id);
        }
        echo json_encode ( $release->get_release_comment_number( $rid ) );
    }

    function paper_comment_insert()
    {
        if( !isset($_SESSION["user"])){
            echo "ログインしてください！";
            $this->redirect( "users/login" );
        }

        // トークンチェック
        checkToken();

        // load user model
        $uri = $_SERVER['HTTP_REFERER'];
        $user = $this->loadModel( "user" );

        if( count( $_POST ) ){
            $user_id   = $_SESSION["user"];
            $paper_id  = escape( $_POST["paper_id"] );
            $comment   = escape( $_POST["comment"] );
            $notice_user_id = $user->paperid_to_userid($paper_id);
            $kind_id = $user->paper_comment_insert(null, $paper_id, $user_id, $comment);
            $user->paper_comment_notice($paper_id, $notice_user_id, $user_id, 3, $kind_id, 1);
        }
        //post元に戻る
        header("Location: ".$uri);
    }

    function paper_comment_remove()
    {
        if( !isset($_SESSION["user"])){
            echo "ログインしてください！";
            return false;
        }
        // load user model
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        // トークンチェック
        checkToken();

        if( count( $_POST ) ){
            $user_id   = $_SESSION["user"];
            $commentid = escape ( $_POST["commentid"] );
            $pid       = escape( $_POST["pid"] );

            $user->paper_comment_remove( $commentid, $pid, $user_id );
            $user->paper_comment_notice( 0,0,0,3,$commentid,0);
        }
        echo json_encode ( $release->get_paper_comment_number( $pid ) );
    }

    function make_paper()
    {
        // load user model
        $release = $this->loadModel( "release" );

        if ( !isset($_SESSION["user"])) {
            $this->redirect( "users/login" );
        }

        if( $_POST["checked_rid"] ){
            $user_id      = $_SESSION["user"];
            $checked_rid  = escape( $_POST["checked_rid"] );
            $paper_id = $release->publish_id_insert_paper($user_id, $checked_rid);
        }else{
            $this->redirect( "pages/scrap" );
        }

        if ( !$paper_id ) {
            exit("エラー! 論評を入力して確定をクリックしてからPUBLISHしてください");
        }else{
            $this->redirect( "pages/display_paper/$paper_id[0]" );
        }
    }

    function display_paper($id)
    {
        $data = array();

        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        // get the user data from database
        $paper_data = $release->find_paper_by_id( $id );
        $user_data = $user->find_by_id( $paper_data["user_id"] );
        $release_comment_data = $release->paper_comment_paper_id_select( $id );
        $release_comment_number = $release->get_paper_comment_number( $id );

        for ($i=1; $i <= 5 ; $i++) {
            $papers[] = $release->find_publish_by_id( $paper_data["publish_id_$i"] );
        }

        // load profile view
        $data = array( "user_data" => $user_data, "paper_data" => $paper_data, "papers" => $papers, "release_comment_data" => $release_comment_data, "release_comment_number" => $release_comment_number);
        $this->loadView( "pages/paper", $data );
    }

    function paper_remove()
    {
        if( !isset($_SESSION["user"])){
            echo "ログインしてください！";
            return false;
        }
        // load user model
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        // トークンチェック
        checkToken();

        if( count( $_POST ) ){
            $user_id   = $_SESSION["user"];
            $paper_id  = escape( $_POST["pid"] );

            $user_id_array = $user->user_id_by_scrap_paper( $paper_id );
            $user->paper_remove( $paper_id, $user_id );

            if ( $user_id_array ) {
                foreach ($user_id_array as $one) {
                    $user->paper_scrap_number_update( $one );
                }
            }
        }

        echo json_encode( $release->get_paper_publish_number( $user_id ) );
    }

    function contact()
    {
        $data = array();

        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        // load view
        $this->loadView( "pages/contact", $data );
    }

    function release_tag_insert()
    {
        if( !isset($_SESSION["user"])){
            echo "ログインしてください！";
            return false;
        }
        // load user model
        $uri = $_SERVER['HTTP_REFERER'];
        $release = $this->loadModel( "release" );

        // トークンチェック
        checkToken();

        if( count( $_POST ) ){
            $rid   = escape( $_POST["rid"] );
            $tag   = escape( $_POST["tag"] );
            $release->release_to_tag($rid, $tag);
        }
        // echo json_encode( $release->release_comment_ridselect( $rid ) );
        //post元に戻る
        header("Location: ".$uri);
    }

    function autocomplete(){
        $release = $this->loadModel( "release" );
        if (isset($_POST["param1"])){
            $term = escape($_POST["param1"]);
            $return_arr = $release->get_autocomplete($term);
            echo json_encode($return_arr, JSON_UNESCAPED_UNICODE);
        }
    }

    function request_comment_insert()
    {
        if( !isset($_SESSION["user"])){
            echo "ログインしてください！";
            return false;
        }
        // load user model
        $uri = $_SERVER['HTTP_REFERER'];
        $user = $this->loadModel( "user" );

        // トークンチェック
        checkToken();

        if( count( $_POST ) ){
            $user_id   = $_SESSION["user"];
            $comment   = escape( $_POST["comment"] );
            $user->request_comment_insert($user_id, $comment);
        }
        // echo json_encode( $release->release_comment_ridselect( $rid ) );
        //post元に戻る
        header("Location: ".$uri);
    }

    function request_comment_remove()
    {
        if( !isset($_SESSION["user"])){
            echo "ログインしてください！";
            return false;
        }
        // load user model
        $user = $this->loadModel( "user" );
        // トークンチェック
        checkToken();

        if( count( $_POST ) ){
            $user_id     = $_SESSION["user"];
            $request_id  = escape( $_POST["request_id"] );
            $user->request_comment_remove( $request_id, $user_id );
        }
        return 1;
    }

    function insert_request_like(){
        if( !isset($_SESSION["user"])){
            echo "ログインしてください！";
            return false;
        }
        $user = $this->loadModel( "user" );

        if( count( $_POST ) ){
            $user_id     = $_SESSION["user"];
            $request_id  = escape( $_POST["request_id"] );
            $count = $user->insert_request_like( $request_id );
        }
        echo json_encode($count["like"]);
    }

    function request()
    {
        $request_data = array();
        $user = $this->loadModel( "user" );
        if ( isset($_SESSION["user"]) ) {
            $user_data = $user->find_by_id( $_SESSION["user"] );
        }
        $request_data = $user->get_request_data();

        $data = array( "request_data" => $request_data);
        $this->loadView( "users/request", $data );
    }

    function release_register_preview(){
    }

    function release_register(){
        $release = $this->loadModel( "release" );

        if (count($_POST)) {
            checkToken();
            $url = escape( $_POST["url"] );
            $cname = escape( $_POST["cname"] );
            $title = escape( $_POST["title"] );
            $body = escape( $_POST["body"] );
            $prcid = escape( $_POST["prcid"] );

            // アップロードファイル名
            if (isset($_FILES['upfile']['error']) && is_array($_FILES['upfile']['error']) ) {
                $img = array("","","","","");
                $this->imgCheck($_FILES['upfile']['error']);
                foreach ($_FILES["upfile"]["tmp_name"] as $k => $file) {
                    if (is_uploaded_file($file)) {
                        $timestamp = date('YmdHis');
                        $key = base64_encode($cname)."img".$k.$timestamp;
                        $imgResize = $this->imgResize($file, 800);
                        $img[$k] = $this->S3ImgUpload('release', $key, $imgResize);
                        unlink($imgResize);
                    }else{
                        continue;
                    }
                }
                // //拡張子取得
                // $file_nm = $_FILES['upfile']['name'];
                // $extension = pathinfo($file_nm, PATHINFO_EXTENSION);
            }

            if (isset($img)) {
                $result = $release->release_register($prcid,$url,$cname,$title,$body,$img);
            }else{
                $result = $release->release_register($prcid,$url,$cname,$title,$body);
            }

            if ($result) {
                return 1;
            }else{
                return 0;
            }
        } else {
            $data = array();
            $this->loadView( "pages/release_register", $data );
        }
    }

    function imgResize($file, $max){
        $info = getimagesize($file);
        if ($info[0] >= $info[1]) {
            $dst_w = $max;
            $dst_h = ceil($max * $info[1] / max($info[0], 1));
        } else {
            $dst_w = ceil($max * $info[0] / max($info[1], 1));
            $dst_h = $max;
        }

        $create = str_replace('/', 'createfrom', $info['mime']);
        $output = str_replace('/', '', $info['mime']);

        $src = $create($file);

        // リサンプリング先画像リソースを生成する
        $dst = imagecreatetruecolor($dst_w, $dst_h);

        // getimagesize関数で得られた情報も利用してリサンプリングを行う
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $dst_w, $dst_h, $info[0], $info[1]);
        imagedestroy($src);

        $output($dst, sprintf('./resized/%s%s',sha1_file($file),image_type_to_extension($info[2]) ) );
        imagedestroy($dst);
        return sprintf('./resized/%s%s',sha1_file($file),image_type_to_extension($info[2]) );
    }

    function imgCheck($upfile_error){
        // 各ファイルをチェック
        foreach ($upfile_error as $k => $error) {

            try {

                // 更に配列がネストしていれば不正とする
                if (!is_int($error)) {
                    throw new RuntimeException("[{$k}] パラメータが不正です");
                }

                // $_FILES['upfile']['error'][$k] の値を確認
                switch ($error) {
                    case UPLOAD_ERR_OK: // OK
                        break;
                    case UPLOAD_ERR_NO_FILE:   // ファイル未選択
                        continue 2;
                    case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
                    case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過
                        throw new RuntimeException("[{$k}] ファイルサイズが大きすぎます");
                    default:
                        throw new RuntimeException("[{$k}] その他のエラーが発生しました");
                }

                // $_FILES['upfile']['mime']の値はブラウザ側で偽装可能なので
                // MIMEタイプを自前でチェックする
                if (!$info = @getimagesize($_FILES['upfile']['tmp_name'][$k])) {
                    throw new RuntimeException("[{$k}] 有効な画像ファイルを指定してください");
                }
                if (!in_array($info[2], [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
                    throw new RuntimeException("[{$k}] 未対応の画像形式です");
                }

            } catch (RuntimeException $e) {
                $msgs[] = ['red', $e->getMessage()];
            }
        }
    }

    function S3ImgUpload($prefix, $key, $tmpfile){
        $client = S3Client::factory(array(
              "key" => "AKIAIXGYHHT5PTKSV6IA",
              "secret" => "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",
              "region" => Region::AP_NORTHEAST_1
              // 'version' => 'latest'
            ));
        date_default_timezone_set("Asia/Tokyo");
        // バケット名
        $bucket = "s3php";

        try {
            $result = $client->putObject(array(
                'Bucket' => $bucket,
                'Key' => $prefix.'/'.$key,
                'Body' => EntityBody::factory(fopen($tmpfile, 'r')),
                // 'Body' => EntityBody::factory($tmpfile),
            ));
            $img = $result['ObjectURL'];
            return $img;
        } catch (S3Exception $exc) {
            echo $exc->getMessage();
        }
    }

}
