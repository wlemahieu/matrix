<?php

/**
 * API Controller
 *
 * Please note:
 * Don't use the same name for class and method, as this might trigger an (unintended) __construct of the class.
 * This is really weird behaviour, but documented here: http://php.net/manual/en/language.oop5.decon.php
 *
 */
class API extends Controller {

    public function index() {
		
		// load specific API call
        require APP . 'controller/api/' . $_SESSION['method'] . '.php';
    }
}