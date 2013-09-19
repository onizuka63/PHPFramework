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


class PDOManager extends DBManager
{
    /** Réécriture du constructeur parent pour instancier la DAO
     * Dans notre cas, nous utilisons PDO pour accéder à la base de données
     *
     * @param Application $app l'application courante
     */
    public function __construct(Application $app,$dsn, $username, $password)
    {
        parent::__construct($app);

        try {
            $this->dataBase = DBFactory::getPdoMySqlAdapter($dsn, $username, $password);
        } catch (\Exception $e) {
            throw $e;
        }

    }
}

?>
