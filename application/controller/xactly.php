<?php

/**
 * Xactly Class
 *
 * Please note:
 * Don't use the same name for class and method, as this might trigger an (unintended) __construct of the class.
 * This is really weird behaviour, but documented here: http://php.net/manual/en/language.oop5.decon.php
 *
 */
class Xactly extends Controller
{
    /**
     * Home page
     */
    public function index()
    {
        require APP . 'config/oauth_config.php';
        require APP . 'model/db-model.php';
        require APP . 'model/universal.php';
        require APP . 'model/xactly_upload.php';

        $OA = new OAuth2($this->db);
        $XactlyUpload = new XactlyUpload($this->db);

        require APP . 'controller/oauth_listener.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/xactly/index.php';
        require APP . 'view/_templates/footer.php';
    }
}