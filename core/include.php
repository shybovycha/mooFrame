<?php
    require_once('Config.php');
    require_once('Log.php');
    require_once('Database.php');
    require_once('Renderer.php');
    require_once('DataObject.php');

    function encodePassword($password)
    {
        return md5(strrev($password));
    }

    $conn = Database::connect("mysql:host=localhost;dbname=moocoder", "root", "");

    session_start();
