<?php

use FpDbTest\Database;
use FpDbTest\DatabaseTest;

spl_autoload_register(function ($class) {
    $a = array_slice(explode('\\', $class), 1);
    if (!$a) {
        throw new Exception();
    }
    $filename = implode('/', [__DIR__, ...$a]) . '.php';
    require_once $filename;
});

$mysqli = @new mysqli('localhost', 'root', 'password', 'database', 3306);
if ($mysqli->connect_errno) {
    throw new Exception($mysqli->connect_error);
}

$db = new Database($mysqli);
$test = new DatabaseTest($db);
$test->testBuildQuery();

exit('OK');
