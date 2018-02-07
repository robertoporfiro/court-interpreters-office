<?php

$db_params = parse_ini_file(getenv('HOME').'/.my.cnf');
$db = new PDO('mysql:host=localhost;dbname=office', $db_params['user'], $db_params['password'],[
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
]);

return $db;