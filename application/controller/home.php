<?php

// the main traffic controller for the Matrix.
// points the end-user to the proper router dependant on their session
class Home extends Controller {

    // Home page
    // Generally loads a specific view based on the type of user you are, 
    // or loads the default index.php view
    public function index() {

        // configure OAuth
        require APP . 'config/oauth_config.php';

        // connect to db
        require APP . 'model/db-model.php';
        
        // load master model
        require APP . 'model/universal.php';

        // load custom OAuth methods
        require APP . 'model/OAuth.php';
        require APP . 'model/ClockingControl.php';

        // instantiate classes
        $OAuth = new OAuth2($this->db);
        $ClockingControl = new ClockingControl($this->db);

        // perform oauth-related tasks
        require APP . 'controller/oauth_listener.php';

        // application
        require APP . 'view/layout.php';
    }
}