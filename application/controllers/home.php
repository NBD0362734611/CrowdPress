<?php
class home extends controller {
	// let move to the subject... to signin signup users
	function index()
	{
		$this->redirect( "users/login" );
	}

    public function get_header(){
        $user = $this->loadModel( "user" );

        if ( isset( $_SESSION["user"] ) ) {
            $notifications = $user->notification( $_SESSION["user"] );
            $data = array( "notifications" => $notifications);
            $this->loadView( "common/header", $data );
        } else {
            $this->loadView( "common/header_before_login" );
        }
    }
}
