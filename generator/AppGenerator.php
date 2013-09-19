<?php
/**
 *  Copyright (C) 2013 Emmanuel CORBEAU / manucorbeau{at}gmail{dot}com
 *
 *  This library is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU Lesser General Public
 *  License as published by the Free Software Foundation; either
 *  version 2.1 of the License, or (at your option) any later version.
 *
 *  This library is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 *  Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public
 *  License along with this library; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */


/*
 * WELCOME TO THE APP GENERATOR
 * This task will configure an new frameworkized app 
 * The script should be executed in CLI mode
 */

function prompt($line) {
    echo $line."\n";
}
function getInput($stdin) {
    return trim(fgets($stdin),"\n");
}

$stdin = fopen('php://stdin', "r");
if (false == $stdin) {
    prompt("Cannot open standard input");
    exit(1);
}

prompt("----------------------------");
prompt('WELCOME TO THE APP GENERATOR');
prompt('This task will configure an new frameworkized app');
do {
    prompt("----------------------------");
    prompt('Enter the name of this app : ');  $appName = getInput($stdin);
    prompt('Enter the database hostname : '); $dbHost = getInput($stdin);
    prompt('Enter the database username : '); $dbUser = getInput($stdin);
    prompt('Enter the database password : '); $dbPass = getInput($stdin);
    prompt('Enter the database name : '); $dbName = getInput($stdin);

    prompt("Application name : " . $appName);
    prompt("Database host : " . $dbHost);
    prompt("Database username : " . $dbUser);
    prompt("Database password : " . $dbPass);
    prompt("Database name : " . $dbName);

    prompt("Are you sure ? (y/n)");
    $confim = strtolower(getInput($stdin));
} while(false == in_array($confim, array('y', 'yes', 'o', 'oui')));

$index_php = file_get_contents('AppGenerator_templates/index.php.tpl');
$index_php = str_replace("%app_name%", $appName, $index_php);
$app_xml = file_get_contents('AppGenerator_templates/config/app.xml.tpl');
$app_xml = str_replace("%db_host%", $dbHost, $app_xml);
$app_xml = str_replace("%db_user%", $dbUser, $app_xml);
$app_xml = str_replace("%db_pass%", $dbPass, $app_xml);
$app_xml = str_replace("%db_name%", $dbName, $app_xml);
$database_xml = file_get_contents('AppGenerator_templates/config/database.xml.tpl');
$database_xml = str_replace("%db_name%", $dbName, $database_xml);
$routes_xml = file_get_contents('AppGenerator_templates/config/routes.xml.tpl');
$e404_php = file_get_contents('AppGenerator_templates/404.php.tpl');
$default_controller = file_get_contents('AppGenerator_templates/modules/Default/DefaultController.class.php.tpl');
$default_controller_index = file_get_contents('AppGenerator_templates/modules/Default/views/index.php.tpl');
$layout_php = file_get_contents('AppGenerator_templates/templates/layout.php.tpl');
$header_php = file_get_contents('AppGenerator_templates/templates/partials/header.php.tpl');
$header_php = str_replace("%app_name%", $appName, $header_php);

mkdir("../apps/".$appName, 0760, true);
mkdir("../apps/".$appName."/config", 0760, true);
mkdir("../apps/".$appName."/modules", 0760, true);
mkdir("../apps/".$appName."/modules/Default", 0760, true);
mkdir("../apps/".$appName."/modules/Default/views", 0760, true);
mkdir("../apps/".$appName."/templates", 0760, true);
mkdir("../apps/".$appName."/templates/partials", 0760, true);
mkdir("../apps/".$appName."/templates/errors", 0760, true);
mkdir("../web/".$appName."/", 0760, true);
mkdir("../web/".$appName."/js", 0760, true);
mkdir("../web/".$appName."/css", 0760, true);
mkdir("../web/".$appName."/img", 0760, true);
mkdir("../web/".$appName."/jquery", 0760, true);
mkdir("../web/".$appName."/bootstrap", 0760, true);
mkdir("../web/".$appName."/bootstrap/css", 0760, true);
mkdir("../web/".$appName."/bootstrap/js", 0760, true);
mkdir("../web/".$appName."/bootstrap/img", 0760, true);

file_put_contents("../web/index.php", $index_php);
file_put_contents("../apps/".$appName."/config/app.xml", $app_xml);
file_put_contents("../apps/".$appName."/config/database.xml", $database_xml);
file_put_contents("../apps/".$appName."/config/routes.xml", $routes_xml);
file_put_contents("../apps/".$appName."/templates/errors/404.php", $e404_php);
file_put_contents("../apps/".$appName."/modules/Default/DefaultController.class.php", $default_controller);
file_put_contents("../apps/".$appName."/modules/Default/views/index.php", $default_controller_index);
file_put_contents("../apps/".$appName."/templates/layout.php", $layout_php);
file_put_contents("../apps/".$appName."/templates/partials/header.php", $header_php);

copy("AppGenerator_templates/jquery/jquery-1.10.2.min.js", "../web/".$appName."/jquery/jquery-1.10.2.min.js");
copy("AppGenerator_templates/bootstrap/css/bootstrap.css", "../web/".$appName."/bootstrap/css/bootstrap.css");
copy("AppGenerator_templates/bootstrap/img/glyphicons-halflings-white.png", "../web/".$appName."/bootstrap/img/glyphicons-halflings-white.png");
copy("AppGenerator_templates/bootstrap/img/glyphicons-halflings.png", "../web/".$appName."/bootstrap/img/glyphicons-halflings.png");
copy("AppGenerator_templates/bootstrap/js/bootstrap.js", "../web/".$appName."/bootstrap/js/bootstrap.js");


fclose($stdin);