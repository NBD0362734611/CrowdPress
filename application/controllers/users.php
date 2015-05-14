<?php
class users extends controller {
	function index()
	{
		if( isset( $_SESSION["user"] ) ){
			$this->redirect( "users/profile" );
		}
		else{
			$this->redirect( "users/login" );
		}
	}

	function login()
	{
		$data = array();

		// login form submitted?
		if( count( $_POST ) ){
			// load user and authentication models
			$user = $this->loadModel( "user" );

			// get the user data from database
			$user_data = $user->find_by_email_and_password( escape( $_POST["email"] ), escape( $_POST["password"] ) );

			// user found?
			if( $user_data ){
				$_SESSION["user"] = $user_data["id"];
				$this->redirect( "users/profile" );
			}

			$data["error_message"] = '<b style="color:red">Bad Email or password! Try again.</b>';
		}

		// load login view
		$this->loadView( "users/login", $data );
	}

	function logout()
	{
		// every thing is within php sessions, just destory it
		$_SESSION = array();
		session_destroy();

		// go back home
		$this->redirect( "users/login" );
	}

	function register()
	{
		$data = array();

		// load user model
		$user = $this->loadModel( "user" );

		// registration form submitted?
		if( count( $_POST ) ){
			$email      = escape( $_POST["email"] );
			$password   = escape( $_POST["password"] );
			$first_name = escape( $_POST["first_name"] );
			$last_name  = escape( $_POST["last_name"] );

			if( ! $email || ! $password ){
				$data["error_message"] = '<br /><b style="color:red">Your email and a password are required!</b>';
			}
			else{
				// check if email is in use?
				$user_info = $user->find_by_email( $email );

				// if email used on users table, we display an error
				if( $user_info ){
					$data["error_message"] = '<br /><b style="color:red">Email alredy in use with another account!</b>';
				}
				else{
					// create new user
					$new_user_id = $user->create( $email, $password, $first_name, $last_name );

					// set user connected
					$_SESSION["user"] = $new_user_id;

					$this->redirect( "users/profile" );
				}
			}
		}

		$this->loadView( "users/register", $data );
	}

	function update_mypage()
	{
		$data = array();

		// load user model
		$user = $this->loadModel( "user" );

		// トークンチェック
        checkToken();

		// registration form submitted?
		if( count( $_POST ) ){
			$user_id        = $_SESSION["user"];
			$display_name   = escape( $_POST["display_name"] );
			$upapername     = escape( $_POST["upapername"] );
			$paper_explain  = escape( $_POST["paper_explain"] );
			$facebook_url   = escape( $_POST["facebook_url"] );
			$twitter_url    = escape( $_POST["twitter_url"] );
			$website_url    = escape( $_POST["website_url"] );
			$photo_url      = escape( $_POST["photo_url"] );
			$cover_url      = escape( $_POST["cover_url"] );

			$user->update_mypage( $user_id, $display_name, $upapername, $paper_explain, $facebook_url, $twitter_url, $website_url, $photo_url, $cover_url );
		}

		echo "OK";
	}


	function complete_registration()
	{
		$data = array();

		// load user model
		$user = $this->loadModel( "user" );

		// complete registration form submitted?
		if( count( $_POST ) ){
			$email      = escape( $_POST["email"] );
			$password   = escape( $_POST["password"] );
			$first_name = escape( $_POST["first_name"] );
			$last_name  = escape( $_POST["last_name"] );

			if( ! $email || ! $password ){
				$data["error_message"] = '<br /><b style="color:red">Your email and a password are really important for us!</b>';
			}
			else{
				// check if email is in use?
				$user_info = $user->find_by_email( $email );

				// if email used on users table, we display an error
				if( $user_info && $user_info["id"] != $_SESSION["user"] ){
					$data["error_message"] = '<br /><b style="color:red">Email already in use with another account!</b>';
				}
				else{
					// update user profile
					$user->update( $_SESSION["user"], $email, $password, $first_name, $last_name );

					// here we go
					$this->redirect( "users/profile" );
				}
			}
		}

		// get the user data from database
		$user_data = $user->find_by_id( $_SESSION["user"] );

		// load complete registration form view
		$data["user_data"] = $user_data;
		$this->loadView( "users/complete_registration", $data );
	}

	function profile()
	{
		$data = array();
		$release_comment_data = array();

		// user connected?
		if( ! isset( $_SESSION["user"] ) ){
			$this->redirect( "users/login" );
		}

		// load user and authentication models
		$user = $this->loadModel( "user" );
		$authentication = $this->loadModel( "authentication" );
		$release = $this->loadModel( "release" );

		// get the user data from database
		$user_data = $user->find_by_id( $_SESSION["user"] );
		$paper_data = $release->find_publish_by_user_id( $_SESSION["user"] );
		$follow_status = $user->follow ( $_SESSION["user"], $_SESSION["user"] );

		// provider like twitter, linkedin, do not provide the user email
		// in this case, we should ask them to complete their profile before continuing
		if( ! $user_data["email"] ){
			$this->redirect( "users/complete_registration" );
		}

		// get the user authentication info from db, if any
		$user_authentication = $authentication->find_by_user_id( $_SESSION["user"] );

		foreach ($paper_data as $paper) {
            $row = $user->paper_comment_select($paper["id"]);
            $release_comment_data[$paper["id"]] = $row;
         }

		// load profile view
		$data = array( "user_data" => $user_data, "user_authentication" => $user_authentication, "paper_data" => $paper_data, "release_comment_data" => $release_comment_data, "follow_status" => $follow_status);
		$this->loadView( "users/profile", $data );
	}

	function profile_user($user_id)
	{
		$data = array();
		$release_comment_data = array();

		// load user and authentication models
		$user = $this->loadModel( "user" );
		$authentication = $this->loadModel( "authentication" );
		$release = $this->loadModel( "release" );

		// get the user data from database
		$user_data = $user->find_by_id( $user_id );
		$paper_data = $release->find_publish_by_user_id( $user_id );
		$follow_status = $user->follow ( $user_id, $_SESSION["user"] );

		// get the user authentication info from db, if any
		$user_authentication = $authentication->find_by_user_id( $user_id );

		foreach ($paper_data as $paper) {
            $row = $user->paper_comment_select($paper["id"]);
            $release_comment_data[$paper["id"]] = $row;
         }

		// load profile view
		$data = array( "user_data" => $user_data, "user_authentication" => $user_authentication, "paper_data" => $paper_data, "release_comment_data" => $release_comment_data, "follow_status" => $follow_status);
		$this->loadView( "users/profile", $data );
	}

	function profile_user_scrap( $user_id )
	{
		$data = array();
		$release_comment_data = array();

		// load user and authentication models
		$user = $this->loadModel( "user" );
		$authentication = $this->loadModel( "authentication" );
		$release = $this->loadModel( "release" );

		// get the user data from database
		$user_data = $user->find_by_id( $user_id );
		$paper_data = $release->find_publish_by_scrap( $user_id );
		$follow_status = $user->follow ( $user_id, $_SESSION["user"] );

		// get the user authentication info from db, if any
		$user_authentication = $authentication->find_by_user_id( $user_id );

		foreach ($paper_data as $paper) {
            $row = $user->paper_comment_select($paper["id"]);
            $release_comment_data[$paper["id"]] = $row;
         }

		// load profile view
		$data = array( "user_data" => $user_data, "user_authentication" => $user_authentication, "paper_data" => $paper_data, "release_comment_data" => $release_comment_data, "follow_status" => $follow_status);
		$this->loadView( "users/pscrap", $data );
	}

	function profile_user_following( $user_id )
	{
		$data = array();
		$release_comment_data = array();
		$followorfollower = "フォロー";
		$follow_data = array();

		// load user and authentication models
		$user = $this->loadModel( "user" );
		$authentication = $this->loadModel( "authentication" );

		// get the user data from database
		$user_data = $user->find_by_id( $user_id );
		$follow_status = $user->follow ( $user_id, $_SESSION["user"] );
		$follow = $user->get_user_following ( $user_id );
		if ( isset( $follow ) ) {
			$follow_data = $user->find_by_ids ( $follow );
		}
		$i = 0;
		foreach ($follow_data as $follow) {
			$follow_status_user_data = $user->follow( $follow["id"], $_SESSION["user"] );
			$follow_data[$i]["follow_status"] = $follow_status_user_data;
			$i++;
		}

		// get the user authentication info from db, if any
		$user_authentication = $authentication->find_by_user_id( $user_id );

		// load profile view
		$data = array( "user_data" => $user_data, "user_authentication" => $user_authentication, "follow_data" => $follow_data, "followorfollower" => $followorfollower, "follow_status" => $follow_status);
		$this->loadView( "users/follow", $data );
	}

	function profile_user_follower( $user_id )
	{
		$data = array();
		$release_comment_data = array();
		$followorfollower = "フォロワー";
		$follow_data = array();

		// load user and authentication models
		$user = $this->loadModel( "user" );
		$authentication = $this->loadModel( "authentication" );

		// get the user data from database
		$user_data = $user->find_by_id( $user_id );
		$follow_status = $user->follow ( $user_id, $_SESSION["user"] );
		$follower = $user->get_user_follower ( $user_id );
		if ( isset( $follower ) ) {
			$follow_data = $user->find_by_ids ( $follower );
		}
		$i = 0;
		foreach ($follow_data as $follow) {
			$follow_status_user_data = $user->follow( $follow["id"], $_SESSION["user"] );
			$follow_data[$i]["follow_status"] = $follow_status_user_data;
			$i++;
		}

		// get the user authentication info from db, if any
		$user_authentication = $authentication->find_by_user_id( $user_id );

		// load profile view
		$data = array( "user_data" => $user_data, "user_authentication" => $user_authentication, "follow_data" => $follow_data, "followorfollower" => $followorfollower, "follow_status" => $follow_status);
		$this->loadView( "users/follow", $data );
	}

	function myfeed()
	{
		$paper_data = array();
		$release_comment_data = array();
		$user = $this->loadModel( "user" );
		$release = $this->loadModel( "release" );

		$paper_data = $release->find_publish_by_follow( $_SESSION["user"] );

		if ( isset($paper_data) ) {
			foreach ($paper_data as $paper) {
            $row = $user->paper_comment_select($paper["id"]);
            $release_comment_data[$paper["id"]] = $row;
         }
		}

		if( ! isset( $_SESSION["user"] ) ){
			$this->redirect( "users/login" );
		}

		$data = array( "paper_data" => $paper_data, "release_comment_data" => $release_comment_data);
		$this->loadView( "users/myfeed", $data );
	}

	function setting()
	{
		// user connected?
		if( ! isset( $_SESSION["user"] ) ){
			$this->redirect( "users/login" );
		}

		// load user and authentication models
		$user = $this->loadModel( "user" );
		$authentication = $this->loadModel( "authentication" );

		// get the user data from database
		$user_data = $user->find_by_id( $_SESSION["user"] );

		// provider like twitter, linkedin, do not provide the user email
		// in this case, we should ask them to complete their profile before continuing
		if( ! $user_data["email"] ){
			$this->redirect( "users/complete_registration" );
		}

		// get the user authentication info from db, if any
		$user_authentication = $authentication->find_by_user_id( $_SESSION["user"] );

		// load profile view
		$data = array( "user_data" => $user_data, "user_authentication" => $user_authentication );
		$this->loadView( "users/setting", $data );
	}

	function following()
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
            $user_id     = escape( $_POST["user_id"] );
            $follower_id = $_SESSION["user"];
        }

        echo json_encode( $user->following($user_id, $follower_id) );
    }
}
