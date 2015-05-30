<?php
class home extends controller {
	// let move to the subject... to signin signup users
	function index()
	{
		$this->redirect( "users/login" );
	}

    function header(){
        $release = $this->loadModel( "release" );
        $user = $this->loadModel( "user" );

        if ( isset( $_SESSION["user"] ) ) {
            $notification = array();
            $unread_comment_paperid = $release->unread_paper_comment();
            $data = array( "notification" => $notification);
            $this->loadView( "common/header", $data );
        } else {
            $this->loadView( "common/header_before_login" );
        }
    }
}
