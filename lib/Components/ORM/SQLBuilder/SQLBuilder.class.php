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

/** Classe SQLBuilder
 *
 *  Permet de construire une requete sql en fournissant une interface simple
 *
 * Exemple d'utilisation :
 *
 *  $sqlBuilder = new SQLBuilder($this->app());
 *
 *       $sqlBuilder->select('login')
 *         ->select('password')
 *         ->from('user')
 *         ->where('login', 'LIKE', '%_pattern_%')
 *         ->orderBy('registerDate')
 *               ->offset(5)
 *               ->limit(10)
 *               ->desc()
 *         ->build();
 *
 *    if($sqlBuilder->isRequestReady())
 *        echo $sqlBuilder->getRequest();
 *
 * Trace :
 *
 * SELECT login, password
 * FROM user
 * WHERE login LIKE ('%_pattern_%')
 * ORDER BY registerDate
 * OFFSET 5 LIMIT 10
 * DESC ;
 *
 *
 *
 * @author Emmanuel Corbeau
 * 
 */
class SQLBuilder extends ApplicationComponent
{
    /** La requete sous forme de chaine de caractères */
    protected $request;

    /** Constructeur unique.
     *
     * @param Application $app L'application qui est execut�e
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }
    
    public function getRequest()
    {
        return $this->request;
    }

    public function  __toString()
    {
        return $this->request;
    }

    public function build()
    {
        $this->request->build();
        return $this;
    }

    public function isRequestReady()
    {
        return $this->request->isBuild();
    }

    /**
     *
     * @param string $column the column to select
     * @return SQLSelect the select builder
     */
    public function select($column)
    {
        $this->request = new SQLSelect();
        return $this->request->select($column);
    }

    public function insertInto($table)
    {
        $this->request = new SQLInsert();
        return $this->request->insertInto($table);
    }

    public function update($table)
    {
        $this->request = new SQLUpdate();
        return $this->request->update($table);
    }

    public function delete()
    {
        $this->request = new SQLDelete();
        return $this->request;
    }
}

?>
