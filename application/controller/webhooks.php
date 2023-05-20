<?php

/**
 * Webhooks Controller
 *
 * Please note:
 * Don't use the same name for class and method, as this might trigger an (unintended) __construct of the class.
 * This is really weird behaviour, but documented here: http://php.net/manual/en/language.oop5.decon.php
 *
 */
class webhooks extends Controller {

    public function index() {
		
		// load specific API call
        require APP . 'controller/webhooks/' . $_SESSION['method'] . '.php';
    }
}