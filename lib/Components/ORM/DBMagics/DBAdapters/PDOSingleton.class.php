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


/**
 * Description of PDOSingleton : encapsulates the PDO object in a singleton template
 *
 * @author emmanuel
 */
class PDOSingleton extends PDO {

    private static $instance = NULL;
    private static $canInstanciate = false;
    
    public static function getInstance($dsn, $username, $password)
    {
        if (!isset(self::$instance))
        {
            self::$canInstanciate = true;
            try {
                self::$instance = new PDOSingleton($dsn, $username, $password);
            } catch(\Exception $e) {
                throw $e;
            }
            self::$canInstanciate = false;
        }

        return self::$instance;
    }

    public function __construct($dsn, $username, $password)
    {
        if (self::$canInstanciate != true) {
            trigger_error (__CLASS__.' is not supposed to be instantiated from the global scope because it is a Singleton class.', E_USER_ERROR);
        } else {
            try {
                parent::__construct($dsn, $username, $password);
            } catch (\PDOException $e) {
                throw $e;
            }
        }
    }

    public function __clone()
    {
        trigger_error('Cloning a singleton is not allowed.', E_USER_ERROR);
    }

}


?>
