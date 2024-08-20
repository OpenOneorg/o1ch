<?php
    session_start();

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
