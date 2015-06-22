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
		if ( isset( $_SESSION["user"] ) ) {
			$follow_status = $user->follow ( $user_id, $_SESSION["user"] );
		}else{
			$follow_status = "購読する";
		}

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

	function loaduserprofile()
	{
		$data = array();
		$release_comment_data = array();

		// load user and authentication models
		$user = $this->loadModel( "user" );
		$release = $this->loadModel( "release" );
		$start = escape( $_POST["count"] ) * 50;
		$user_id = escape( $_POST["user_id"] );
		// get the user data from database
		$user_data = $user->find_by_id( $user_id );
		$paper_data = $release->find_publish_by_user_id( $user_id, $start );

		foreach ($paper_data as $paper) {
            $row = $user->paper_comment_select($paper["id"]);
            $release_comment_data[$paper["id"]] = $row;
         }

		$html = "";
		foreach ($paper_data as $paper) {
			$html .= "<div class='myfeed-wrapper $paper[id]'>";
			$html .= '<article role="main">';
			$html .= "<div class=\"question_image column inline-block\"><a href=\"?route=users/profile_user/$paper[user_id]\"><img src=\"$paper[photo_url]\" alt=\"プロフィール画像\"/></a></div>";
			$html .= '<div class="colmn inline-block">';
			$html .= "<h5 class=\"meta-post\"><a href=\"?route=pages/display_paper/$paper[id]\">$paper[upapername]第$paper[count]号</a> - <time datetime=\"$paper[created_at]\">$paper[created_at]</time></h5>";
			$html .= '</div>';
			$html .= "<section class=\"myfeed row section\"><h2 class=\"paper-subtitle\">";
			$html .= $paper["headline"];
			$html .= '</h2>';
			$html .= "<div class=\"release-article\"><p class=\"release\">";
			$html .= $paper["comment"];
			$html .= "…<a class=\"full-paper\" href=\"?route=pages/display_paper/$paper[id]\">この新聞を読む</a>";
			$html .="</p><figure class=\"column half\">";
			if( !empty($paper["img1"]) ) {
				$html .= '<img src="';
				$html .= $paper["img1"];
				$html .= '">';
				$html .= "<figcaption><a href=\"?route=pages/release_detail/$paper[rid]\">$paper[title]</a></figcaption>";
			}
			$html .= '</figure>';
			$html .= '</div>';
			$html .= "<div class=\"widget meta-social column half\">";
			$html .= "<ul class=\"inline\">";
			$html .= "<li><a class=\"paper-comment-toggle border-box\"><i class=\"fa fa-comment-o fa-lg\"></i></a></li>";
			$html .= "<li><a class=\"clap border-box\" pid=\"$paper[id]\"><i class=\"fa fa-heart-o fa-lg\"></i></a><span class=\"arrow_box\">$paper[clap]</span></li>";
			$html .= "<li><a class=\"scrap border-box\" pid=\"$paper[id]\"><i class=\"fa fa-paperclip fa-lg\"></i></a><span class=\"arrow_box\">$paper[scrap]</span></li>";
			if ( isset( $_SESSION["user"] ) ) {
				if ( $_SESSION["user"] == $paper["user_id"]  ) {
					$html .= "<li><a class=\"remove\" pid=\"";
					$html .= $paper["id"];
					$html .= "\" token=\"";
					$html .= $_SESSION['token'];
					$html .= "\"><i class=\"fa fa-times fa-lg\"></i></a></li>";
				}
			}
			$html .= '</ul></div>';
			$html .= "<form class=\"paper-comment\" style=\"display:none\" action=\"?route=pages/paper_comment_insert\" method=\"post\">";
			if ( isset( $_SESSION["user"] ) ) {
				$html .= "<input type=\"hidden\" name=\"paper_id\" value=\"$paper[id]\" />";
				$html .= "<input type=\"hidden\" name=\"user_id\" value=\"$_SESSION[user]\" />";
				$html .= "<input type=\"hidden\" name=\"token\" value=\"$_SESSION[token]\" />";
				$html .= "<input type=\"text\" name=\"comment\" value=\"新聞にコメントする\" />";
				$html .= '</form>';
			}
			if(is_array($release_comment_data[$paper["id"]])){
				foreach ($release_comment_data[$paper["id"]] as $release_comment) {
					$comment = "";
					$comment = <<< COMMENT
					<section class="line_wrapper">
                        <div class="question_Box inline">
                            <div class="question_image column inline-block">
                                <img src="$release_comment[photo_url]" alt="ユーザーの写真"/>
                            </div>
                            <p class="arrow_question column ten reset inline-block">
								$release_comment[comment]
                            </p><!-- /.arrow_question -->
                            <div>
                                <h5 class="username"><a href="?route=users/profile_user/$release_comment[id]">$release_comment[display_name]</a></h5>
                            </div>
                        </div><!-- /.question_Box -->
                        <div class="clear"></div>
                    </section><!-- /.line_wrappaer -->
COMMENT;
                    $html = $html . $comment;
				}
			}
			$html .= '</section></article></div>';
		}
		echo $html;
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
		$follow_status = $user->follow( $user_id, $_SESSION["user"] );
		$follow = $user->get_user_following( $user_id );
		if ( isset( $follow ) ) {
			$follow_data = $user->find_by_ids( $follow );
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
		if( ! isset( $_SESSION["user"] ) ){
			$this->redirect( "users/login" );
		}
		$paper_data = array();
		$release_comment_data = array();
		$follow_data = array();
		$users_data = array();

		$user = $this->loadModel( "user" );
		$release = $this->loadModel( "release" );
		$authentication = $this->loadModel( "authentication" );

		$user_data = $user->find_by_id( $_SESSION["user"] );
		$paper_data = $release->find_publish_by_follow( $_SESSION["user"] );
		$user_authentication = $authentication->find_by_user_id( $_SESSION["user"] );



		if ( $user_data["follow"] == 0 ) {
			$users = $user->get_latest_users( $_SESSION["user"] );
        	if ( $users ) {
            	$users_data = $user->find_by_ids( $users );
        	}
        	$i = 0;
        	foreach ($users_data as $users) {
            	$follow_status_user_data = $user->follow( $users["id"], $_SESSION["user"] );
            	$users_data[$i]["follow_status"] = $follow_status_user_data;
            	$i++;
        	}
			$data = array( "user_data" => $user_data, "user_authentication" => $user_authentication, "users_data" => $users_data);
			$this->loadView( "users/nofollow", $data );

		} else {
			if ( isset($paper_data) ) {
				foreach ($paper_data as $paper) {
            	$row = $user->paper_comment_select($paper["id"]);
            	$release_comment_data[$paper["id"]] = $row;
         		}
			}
			$data = array( "paper_data" => $paper_data, "release_comment_data" => $release_comment_data);
			$this->loadView( "users/myfeed", $data );
		}

	}

	function loadmyfeed()
	{
		$paper_data = array();
		$release_comment_data = array();
		$follow_data = array();
		$users_data = array();

		$user = $this->loadModel( "user" );
		$release = $this->loadModel( "release" );
		$start = escape( $_POST["count"] ) * 50;
		$user_data = $user->find_by_id( $_SESSION["user"] );
		$paper_data = $release->find_publish_by_follow( $_SESSION["user"], $start );

		if ( isset($paper_data) ) {
			foreach ($paper_data as $paper) {
        	$row = $user->paper_comment_select($paper["id"]);
        	$release_comment_data[$paper["id"]] = $row;
     		}
		}
		$html = "";
		foreach ($paper_data as $paper) {
			$html .= "<div class='myfeed-wrapper $paper[id]'>";
			$html .= '<article role="main">';
			$html .= "<div class=\"question_image column inline-block\"><a href=\"?route=users/profile_user/$paper[user_id]\"><img src=\"$paper[photo_url]\" alt=\"プロフィール画像\"/></a></div>";
			$html .= '<div class="colmn inline-block">';
			$html .= "<h5 class=\"meta-post\"><a href=\"?route=pages/display_paper/$paper[id]\">$paper[upapername]第$paper[count]号</a> - <time datetime=\"$paper[created_at]\">$paper[created_at]</time></h5>";
			$html .= '</div>';
			$html .= "<section class=\"myfeed row section\"><h2 class=\"paper-subtitle\">";
			$html .= $paper["headline"];
			$html .= '</h2>';
			$html .= "<div class=\"release-article\"><p class=\"release\">";
			$html .= $paper["comment"];
			$html .= "…<a class=\"full-paper\" href=\"?route=pages/display_paper/$paper[id]\">この新聞を読む</a>";
			$html .="</p><figure class=\"column half\">";
			if( !empty($paper["img1"]) ) {
				$html .= '<img src="';
				$html .= $paper["img1"];
				$html .= '">';
				$html .= "<figcaption><a href=\"?route=pages/release_detail/$paper[rid]\">$paper[title]</a></figcaption>";
			}
			$html .= '</figure>';
			$html .= '</div>';
			$html .= "<div class=\"widget meta-social column half\">";
			$html .= "<ul class=\"inline\">";
			$html .= "<li><a class=\"paper-comment-toggle border-box\"><i class=\"fa fa-comment-o fa-lg\"></i></a></li>";
			$html .= "<li><a class=\"clap border-box\" pid=\"$paper[id]\"><i class=\"fa fa-heart-o fa-lg\"></i></a><span class=\"arrow_box\">$paper[clap]</span></li>";
			$html .= "<li><a class=\"scrap border-box\" pid=\"$paper[id]\"><i class=\"fa fa-paperclip fa-lg\"></i></a><span class=\"arrow_box\">$paper[scrap]</span></li>";
			if ( $_SESSION["user"] == $paper["user_id"]  ) {
				$html .= "<li><a class=\"remove\" pid=\"";
				$html .= $paper["id"];
				$html .= "\" token=\"";
				$html .= $_SESSION['token'];
				$html .= "\"><i class=\"fa fa-times fa-lg\"></i></a></li>";
			}
			$html .= '</ul></div>';
			$html .= "<form class=\"paper-comment\" style=\"display:none\" action=\"?route=pages/paper_comment_insert\" method=\"post\">";
			$html .= "<input type=\"hidden\" name=\"paper_id\" value=\"$paper[id]\" />";
			$html .= "<input type=\"hidden\" name=\"user_id\" value=\"$_SESSION[user]\" />";
			$html .= "<input type=\"hidden\" name=\"token\" value=\"$_SESSION[token]\" />";
			$html .= "<input type=\"text\" name=\"comment\" value=\"新聞にコメントする\" />";
			$html .= '</form>';
			if(is_array($release_comment_data[$paper["id"]])){
				foreach ($release_comment_data[$paper["id"]] as $release_comment) {
					$comment = "";
					$comment = <<< COMMENT
					<section class="line_wrapper">
                        <div class="question_Box inline">
                            <div class="question_image column inline-block">
                                <img src="$release_comment[photo_url]" alt="ユーザーの写真"/>
                            </div>
                            <p class="arrow_question column ten reset inline-block">
								$release_comment[comment]
                            </p><!-- /.arrow_question -->
                            <div>
                                <h5 class="username"><a href="?route=users/profile_user/$release_comment[id]">$release_comment[display_name]</a></h5>
                            </div>
                        </div><!-- /.question_Box -->
                        <div class="clear"></div>
                    </section><!-- /.line_wrappaer -->
COMMENT;
                    $html = $html . $comment;
				}
			}
			$html .= '</section></article></div>';
		}
		echo $html;
	}

	function myfeed_search_by_keyword()
	{
		if( ! isset( $_SESSION["user"] ) ){
			$this->redirect( "users/login" );
		}

		$keyword   = escape ( $_GET["keyword"] );
		$scope     = escape ( $_GET["scope"] );
		// $keyword   = escape( $keyword );
        $keyword   = mb_convert_encoding($keyword,"UTF-8","UTF-8,EUC-JP,SJIS,Shift_JIS,ASCII");
        //全角空白があったら半角空白にそろえる
        $words     = str_replace("　", " ", $keyword);
        $words     = trim($words);
        if ( $scope == 1) {
        	$checked1 = "checked";
        	$checked2 = "";
        }else{
        	$checked1 = "";
        	$checked2 = "checked";
        }
		$paper_data = array();
		$release_comment_data = array();

		$release = $this->loadModel( "release" );
		$user = $this->loadModel( "user" );

		$users_id = $user->get_user_following( $_SESSION["user"] );
		array_push( $users_id, $_SESSION["user"] );
		$paper_data = $release->find_publish_by_keyword( $users_id, $words, $scope );

		if ( isset($paper_data) ) {
			foreach ($paper_data as $paper) {
            $row = $user->paper_comment_select($paper["id"]);
            $release_comment_data[$paper["id"]] = $row;
         	}
		}

		$data = array( "paper_data" => $paper_data, "release_comment_data" => $release_comment_data, "keyword" => $keyword, "checked1" => $checked1, "checked2" => $checked2);
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

    function user_search_by_keyword()
	{
		if( ! isset( $_SESSION["user"] ) ){
			$this->redirect( "users/login" );
		}

		if( count( $_POST ) ){
			$user_id   = $_SESSION["user"];
            $keyword   = escape ( $_POST["keyword"] );
            $token     = escape ( $_POST["token"] );
        }

        checkToken();

        $users = array();
        $users_data = array();

        $keyword   = mb_convert_encoding($keyword,"UTF-8","UTF-8,EUC-JP,SJIS,Shift_JIS,ASCII");
        //全角空白があったら半角空白にそろえる
        $words     = str_replace("　", " ", $keyword);
        $words     = trim($words);

		$user = $this->loadModel( "user" );
		$users = $user->find_by_keyword( $words );

        if ( $users ) {
            	$users_data = $user->find_by_ids( $users );
        	}
        	$i = 0;
        	foreach ($users_data as $users) {
            	$follow_status_user_data = $user->follow( $users["id"], $_SESSION["user"] );
            	$users_data[$i]["follow_status"] = $follow_status_user_data;
            	$i++;
        	}

		$data = array("users_data" => $users_data, "user_search_keyword" => $words);
		$this->loadView( "users/find_by_keyword", $data );
	}

}
