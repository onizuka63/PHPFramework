<?php

require_once '../autoload.php';

try {
    $app = new FrontendApplication('Test');
    $app->run();
}
catch(\Exception $e) {
    echo "<pre>";
    echo "COUGHT Exception : <br/><br/>Message :<br/>";
    echo $e->getMessage();
    echo "<br/><br/>Stack Trace : <br/>";
    echo $e->getTraceAsString();
    echo "</pre>";
}
