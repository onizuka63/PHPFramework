<?php

require_once '../autoload.php';

$app = new BackendApplication('TutoFramework');

try {
    $app->run();
}
catch(\Exception $e) {
    echo $e->getMessage();
}
