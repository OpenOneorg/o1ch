<?php
    $links = array(
        'Telegram' => 'https://t.me/openone_channel',
        'Github' => 'https://github.com/blopsoft/o1ch'
    );

    $email = "legubrawl@gmail.com";

    $theme = "css.css";

    if($antiddos == true){
        require "anti-ddos-lite/anti-ddos-lite.php";
    }

    session_start();

    if(!isset($_SESSION['theme'])){
        $_SESSION['theme'] = 'css.css';
    }

    $dbconn = array(
        'server' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'db' => 'o1ch'
    );

    $db = new PDO("mysql:host=" .$dbconn['server']. ";dbname=" .$dbconn['db'],
        $dbconn['user'],
        $dbconn['pass']
    );

    $db->exec("set names utf8mb4");

    if($db == false){
        echo('Ошибка подключение базы данных');
    }
?>
