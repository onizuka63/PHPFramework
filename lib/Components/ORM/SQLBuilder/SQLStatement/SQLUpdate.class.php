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
 * Classe SQLUpdate
 *
 * Permet de préparer une requete SQL de type UPDATE.
 * Cette requete est directement compréhensible pour un moteur SQL.
 *
 * Exemple d'utilisation :
 *
 * $request = new SQLUpdate();
 * 
 *        $sqlupdate = new SQLUpdate();
 *        $sqlupdate->update('FooBar')
 *              ->set('Foo', 'Bar')
 *              ->set('Baz', 'Qux')
 *              ->where('id', '=', 1)
 *              ->build();
 *               
 *        if($request->isBuild())
 *            echo $request;
 *
 * Trace :
 *  UPDATE FOOBAR
 *  SET Foo='Bar', Baz='Qux'
 *  WHERE ID = 1 ;
 *
 * @author Emmanuel Corbeau
 *
 */
class SQLUpdate  extends SQLRequest
{
    private $table;
    private $set   = array();
    private $where = array();

    public function update($table)
    {
        $this->table = $table;
        return $this;
    }

    public function set($column, $newValue)
    {
        $this->set[$column] = $newValue;
        return $this;
    }

    public function where($column, $condition, $value)
    {
        if($condition == '='
             || $condition == '>='
             || $condition == '>'
             || $condition == '<='
             || $condition == '<')
        {
            $this->where[] = $column . ' ' . $condition . ' ' . $value;
        }
        else if ($condition == 'LIKE')
        {
            $this->where[] = $column . ' ' . $condition . ' (\'' . $value . '\')';
        }
        else
        {
            throw new SQLException('SQLException : Builder cannot execute action where().
                                    Cause : $condition member isn\'t valid
                                    Solution : try =, >=, >, <=, < or LIKE operators');
        }

        return $this;
    }
    
    public function build()
    {
        // UPDATE
        $this->request = 'UPDATE ';
        $this->request .= strtoupper($this->table);
        
        $this->request .= ' ';

        // SET
        $this->request .= 'SET ' ;
        $index = 0;
        $columns = array_keys($this->set);
        $this->request .= $columns[$index] . '=' .'\''. addslashes ($this->set[$columns[$index++]]).'\'';
        while($index < count($this->set))
        {
            $this->request .= ', ';
            $this->request .= $columns[$index] . '=' . '\''. addslashes ($this->set[$columns[$index++]]).'\'';
        }
        $this->request .= ' ';

        // WHERE
        if(!empty ($this->where))
        {
            $this->request .= 'WHERE ' ;
            $index = 0;
            $this->request .= strtoupper($this->where[$index++]);
            while($index < count($this->where))
            {
                $this->request .= ' AND ';
                if(is_string($this->where[$index]))
                {
                    $this->request .= '\'';
                    $this->request .= strtoupper($this->where[$index++]);
                    $this->request .= '\'';
                }
                else
                {
                    $this->request .= strtoupper($this->where[$index++]);
                }
            }
            $this->request .= ' ';
        }

        $this->request .= ' ;' ;

        //echo $this->request;
        parent::build();
        return $this;
    }


}

?>
