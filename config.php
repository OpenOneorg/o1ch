<?php
    require "anti-ddos-lite/anti-ddos-lite.php";

    session_start();

    if(empty($_SESSION['ip'])){
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
    } 
    
    if($_SERVER['REMOTE_ADDR'] != $_SESSION['ip']){
        die("Выключи VPN или Tor соединение!");
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
