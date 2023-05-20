<?php

/**
 * Class Home
 *
 * Please note:
 * Don't use the same name for class and method, as this might trigger an (unintended) __construct of the class.
 * This is really weird behaviour, but documented here: http://php.net/manual/en/language.oop5.decon.php
 *
 */
class Welcome extends Controller {

    /**
     * PAGE: index
     * This method handles what happens when you move to http://yourproject/home/index (which is the default page btw)
     */
    public function index() {

        // configure OAuth
        require APP . 'config/oauth_config.php';
        // connect to db
        require APP . 'model/db-model.php';
        // load master model
        require APP . 'model/universal.php';
        // load custom OAuth methods
        require APP . 'model/OAuth.php';
        // load clocking control methods
        require APP . 'model/ClockingControl.php';

        // instantiate classes
        $OAuth = new OAuth2($this->db);
        $ClockingControl = new ClockingControl($this->db);

        // perform oauth-related tasks
        require APP . 'controller/oauth_listener.php';

        // oauth callback file
        require APP . 'controller/welcome_listener.php';
    }
}