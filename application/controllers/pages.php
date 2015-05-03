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

    function newrelease()
    {
        $data = array();
        $user_data = array();
        $release_comment_data = array();
        // error_reporting(E_ALL ^ E_NOTICE);
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        // get the user data from database
        if ( isset($_SESSION["user"]) ){
            $user_data = $user->find_by_id( $_SESSION["user"] );
        }

        $release_data = $release->get_new_release();
        $release_comment_data = array();

        if ( isset($release_data) ) {
            foreach ($release_data as $release) {
                $row = $user->release_comment_select($release["rid"]);
                $release_comment_data[$release["rid"]] = $row;
            }
        } else {
            $release_data = array();
        }

        // $release_comment_data = $user->release_comment_select($rid, $_SESSION["user"]);

        // load profile view
        $data = array( "user_data" => $user_data, "release_data" => $release_data, "release_comment_data" => $release_comment_data);
        $this->loadView( "pages/newrelease", $data );
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
            $row = $user->release_comment_select($release["rid"], $_SESSION["user"]);
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

        // error_reporting(E_ALL ^ E_NOTICE);
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        // get the user data from database
        if ( isset($_SESSION["user"]) ){
            $user_data = $user->find_by_id( $_SESSION["user"] );
        }

        $release_data = $release->find_release_by_title( $title );
        $release_comment_data = array();

        if (isset($release_data)) {
            foreach ($release_data as $release) {
            $row = $user->release_comment_select($release["rid"], $_SESSION["user"]);
            $release_comment_data[$release["rid"]] = $row;
            }
        } else {
            $release_data = array();
        }

        // $release_comment_data = $user->release_comment_select($rid, $_SESSION["user"]);

        // load profile view
        $data = array( "user_data" => $user_data, "release_data" => $release_data, "release_comment_data" => $release_comment_data, "title" => $title);
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

        // get the release data from database

        // error_reporting(0);

        $user_data = $user->find_by_id( $_SESSION["user"] );
        $release_data = $release->get_user_scrap( $_SESSION["user"] );
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

        // load profile view
        $data = array( "user_data" => $user_data, "release_data" => $release_data, "release_comment_data" => $release_comment_data, "publish_comment_data" => $publish_comment_data);
        $this->loadView( "pages/scrap", $data );
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

        if( count( $_POST ) ){
            $title     = escape( $_POST["title"] );
        }
        // get the release data from database

        // error_reporting(0);

        $user_data = $user->find_by_id( $_SESSION["user"] );
        $release_data = $release->find_scrap_by_title( $_SESSION["user"], $title );
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

        // load profile view
        $data = array( "user_data" => $user_data, "release_detail_data" => $release_detail_data, "release_comment_data" => $release_comment_data, "release_comment_number" => $release_comment_number );
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
        }
        echo json_encode( $release->paper_clap_insert($user_id, $pid) );
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
        }

        echo json_encode( $release->paper_scrap_insert($user_id, $pid) );
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

        // トークンチェック
        checkToken();

        if( count( $_POST ) ){
            $user_id   = $_SESSION["user"];
            $rid       = escape( $_POST["rid"] );
            $comment   = escape( $_POST["comment"] );

            $user->release_comment_insert(null, $rid, $user_id, $comment);
        }
        //post元に戻る
        header("Location: ".$uri);
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

            $user->paper_comment_insert(null, $paper_id, $user_id, $comment);
        }
        //post元に戻る
        header("Location: ".$uri);
    }

    function make_paper()
    {
        // load user model
        $release = $this->loadModel( "release" );

        if ( !isset($_SESSION["user"])) {
            $this->redirect( "users/login" );
        }

        if( count( $_POST ) ){
            $user_id      = $_SESSION["user"];
            $checked_rid  = escape( $_POST["checked_rid"] );
            $paper_id = $release->publish_id_insert_paper($user_id, $checked_rid);
        }
        // //post元に戻る
        // $uri = $_SERVER['HTTP_REFERER'];
        // header("Location: ".$uri);
         $this->redirect( "pages/display_paper/$paper_id[0]" );
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

        function contact()
    {
        $data = array();

        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        // load view
        $this->loadView( "pages/contact", $data );
    }
}
