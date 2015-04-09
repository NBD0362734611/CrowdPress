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

    function newrelease()
    {
        $data = array();
        // error_reporting(E_ALL ^ E_NOTICE);
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        // get the release data from database
        $user_data = $user->find_by_id( $_SESSION["user"] );
        $release_data = $release->get_new_release();

        if (isset($_SESSION["user"])) {
            foreach ($release_data as $release) {
            $row = $user->release_comment_select($release["rid"], $_SESSION["user"]);
            $release_comment_data[$release["rid"]] = $row;
            }
        }

        // $release_comment_data = $user->release_comment_select($rid, $_SESSION["user"]);

        // load profile view
        $data = array( "user_data" => $user_data, "release_data" => $release_data, "release_comment_data" => $release_comment_data);
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

        if (isset($release_data)){
            foreach ($release_data as $release) {
            $row = $user->release_comment_select($release["rid"], $_SESSION["user"]);
            $release_comment_data[$release["rid"]] = $row;
            }
        } else {   // １件もない場合のエラー対策
            $release_data = array();
        }

        // load profile view
        $data = array( "user_data" => $user_data, "release_data" => $release_data, "release_comment_data" => $release_comment_data);
        $this->loadView( "pages/scrap", $data );
    }

    function release_detail($rid)
    {
        $data = array();

        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        $user_data = $user->find_by_id( $_SESSION["user"] );
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
            $rid     = $_POST["rid"];
        }
        echo json_encode( $release->clap_insert($user_id, $rid) );
    }

    function scrap_insert()
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
            $rid     = $_POST["rid"];

            echo json_encode( $release->scrap_insert($user_id, $rid) );
        }
    }

    function scrap_paper_comment()
    {
        $data = array();

        // load user model
        $user = $this->loadModel( "user" );
        $release = $this->loadModel( "release" );

        // registration form submitted?
        if( count( $_POST ) ){
            $user_id   = $_SESSION["user"];
            $rid       = $_POST["rid"];
            $headline  = $_POST["headline"];
            $comment   = $_POST["comment"];

            $release->scrap_paper_comment($user_id, $rid, $headline, $comment);
        }
        echo "OK";
    }

    function release_comment_insert()
    {
        // load user model
        $uri = $_SERVER['HTTP_REFERER'];
        $user = $this->loadModel( "user" );

        if( !isset($_SESSION["user"])){
            echo "ログインしてください！";
            return false;
        }
        if( count( $_POST ) ){
            $user_id   = $_SESSION["user"];
            $rid       = $_POST["rid"];
            $comment   = $_POST["comment"];

            $user->release_comment_insert(null, $rid, $user_id, $comment);
        }
        //post元に戻る
        header("Location: ".$uri);
    }

    function paper_comment_insert()
    {
        // load user model
        $uri = $_SERVER['HTTP_REFERER'];
        $user = $this->loadModel( "user" );

        if( !isset($_SESSION["user"])){
            echo "ログインしてください！";
            return false;
        }
        if( count( $_POST ) ){
            $user_id   = $_SESSION["user"];
            $paper_id  = $_POST["paper_id"];
            $comment   = $_POST["comment"];

            $user->paper_comment_insert(null, $paper_id, $user_id, $comment);
        }
        //post元に戻る
        header("Location: ".$uri);
    }

    function make_paper()
    {
        // load user model
        $release = $this->loadModel( "release" );

        if( count( $_POST ) ){
            $user_id      = $_SESSION["user"];
            $checked_rid  = $_POST["checked_rid"];

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
}
