<?php
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
        $data = array();
        $user_data = array();
        $release_comment_data = array();
        // error_reporting(E_ALL ^ E_NOTICE);
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        $prcid = 0;
        if ( isset($_GET["prcid"]) ) {
            $prcid = escape( $_GET["prcid"] );
        }

        // get the user data from database
        if ( isset($_SESSION["user"]) ){
            $user_data = $user->find_by_id( $_SESSION["user"] );
        }

        $release_data = $release->get_new_release(0,$prcid);
        $tags = $release->get_tagcloud();
        $release_comment_data = array();
        $source = array();

        if ( isset($release_data) ) {
            foreach ($release_data as $release) {
                $row = $user->release_comment_select($release["rid"]);
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
        $data = array( "user_data" => $user_data, "release_data" => $release_data, "release_comment_data" => $release_comment_data, "source" => $source, "tags" => $tags);
        $this->loadView( "pages/newrelease", $data );
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

        $release_data = $release->get_new_release( $start, $prcid, $sort, $words );
        $release_comment_data = array();
        $source = array();

        if ( isset($release_data) ) {
            foreach ($release_data as $release) {
                $row = $user->release_comment_select($release["rid"]);
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

    function release_sort_by_cname( $cname )
    {
        $data = array();
        $user_data = array();
        $cname = escape( $cname );

        // error_reporting(E_ALL ^ E_NOTICE);
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        // get the user data from database
        if ( isset($_SESSION["user"]) ){
            $user_data = $user->find_by_id( $_SESSION["user"] );
        }

        $release_data = $release->find_release_by_cname( $cname );
        $release_comment_data = array();

        if (isset($release_data)) {
            foreach ($release_data as $release) {
            if ( isset($_SESSION["user"]) ) {
                    $row = $user->release_comment_select($release["rid"], $_SESSION["user"]);
                }else{
                    $row = $user->release_comment_select($release["rid"]);
                }
            $release_comment_data[$release["rid"]] = $row;
            }
        } else {
            $release_data = array();
        }

        // $release_comment_data = $user->release_comment_select($rid, $_SESSION["user"]);

        // load profile view
        $data = array( "user_data" => $user_data, "release_data" => $release_data, "release_comment_data" => $release_comment_data, "cname" => $cname);
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

        if (isset($release_data)) {
            foreach ($release_data as $release) {
                if ( isset($_SESSION["user"]) ) {
                    $row = $user->release_comment_select($release["rid"], $_SESSION["user"]);
                }else{
                    $row = $user->release_comment_select($release["rid"]);
                }
            $release_comment_data[$release["rid"]] = $row;
            }
        } else {
            $release_data = array();
        }

        // load profile view
        $data = array( "user_data" => $user_data, "release_data" => $release_data, "release_comment_data" => $release_comment_data, "title" => $title, "tags" => $tags);
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

        if (isset($release_data)) {
            foreach ($release_data as $release) {
                if ( isset($_SESSION["user"]) ) {
                    $row = $user->release_comment_select($release["rid"], $_SESSION["user"]);
                }else{
                    $row = $user->release_comment_select($release["rid"]);
                }
            $release_comment_data[$release["rid"]] = $row;
            }
        } else {
            $release_data = array();
        }

        // load profile view
        $data = array( "user_data" => $user_data, "release_data" => $release_data, "release_comment_data" => $release_comment_data, "tag" => $tag, "tags" =>$tags);
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

        // load profile view
        $data = array( "user_data" => $user_data, "release_detail_data" => $release_detail_data, "release_comment_data" => $release_comment_data, "release_comment_number" => $release_comment_number, "prev_next_rid" => $prev_next_rid, "tags" => $tags );
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
        }
        // echo json_encode( $release->release_comment_ridselect( $rid ) );
        //post元に戻る
        header("Location: ".$uri);
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
}
