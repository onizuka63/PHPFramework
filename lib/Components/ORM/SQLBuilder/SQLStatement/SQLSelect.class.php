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
 * Classe SQLSelect
 *
 * Permet de préparer une requete SQL de type SELECT.
 * Cette requete est directement compréhensible pour un moteur SQL.
 *
 * Exemple d'utilisation :
 *
 * $request = new SQLSelect();
 * 
 *        $request->select('login')
 *             ->select('password')
 *             ->from('user')
 *             ->where('login', 'LIKE', '%_pattern_%')
 *             ->orderBy('login')
 *                  ->offset(5)
 *                  ->limit(10)
 *                  ->desc()
 *             ->build();
 *               
 *        if($request->isBuild())
 *            echo $request;
 *
 * Trace :
 *  SELECT login, password
 *  FROM user
 *  WHERE login LIKE ('%_pattern_%')
 *  ORDER BY registerDate
 *  OFFSET 5 LIMIT 10
 *  DESC ;
 *
 * @author Emmanuel Corbeau
 *
 */
class SQLSelect  extends SQLRequest
{
    private $select  =  array();
    private $from    =  array();
    private $where   =  array();
    private $orderBy =  array();
    private $offset  =  0 ;
    private $limit   = -1 ;
    private $desc    =  0;

    public function select($column)
    {
        $this->select[] = $column;

        return $this;
    }

    public function from($table)
    {
        $this->from[] = $table;

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
            $str = $column . ' ' . $condition ;
            if(preg_match('/^[0-9]+$/', $value))
                $str .= ' ' . $value ;
            else if(is_string($value))
                $str .= ' \'' . $value . '\'';
            else
                $str .= ' ' . $value ;
            $this->where[] = $str;
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
    
    public function orderBy($column)
    {
        $this->orderBy[] = $column;
        return $this;
    }

    public function offset($offset = 0)
    {
        if($offset > 0) $this->offset = $offset;
        return $this;
    }

    public function limit($limit = -1)
    {
        if($limit > $this->offset && $limit > 0) $this->limit = $limit;
        return $this;
    }

    public function desc($enable = 1)
    {
        $this->desc = $enable;
        return $this;
    }
    
    public function build()
    {

        // SELECT
        $this->request = 'SELECT ';
        $index = 0;
        $this->request .= strtoupper($this->select[$index++]);
        while($index < count($this->select))
        {
            $this->request .= ', ';
            $this->request .= strtoupper($this->select[$index++]);
        }
        $this->request .= ' ';

        // FROM
        $this->request .= 'FROM ' ;
        $index = 0;
        $this->request .= strtoupper($this->from[$index++]);
        while($index < count($this->from))
        {
            $this->request .= ', ';
            $this->request .= strtoupper($this->from[$index++]);
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
                $this->request .= strtoupper($this->where[$index++]);
                
            }
            $this->request .= ' ';
        }

        // ORDERBY
        if(!empty ($this->orderBy))
        {
            $this->request .= 'ORDER BY ' ;
            $index = 0;
            $this->request .= strtoupper($this->orderBy[$index++]);
            while($index < count($this->orderBy))
            {
                $this->request .= ', ';
                $this->request .= strtoupper($this->orderBy[$index++]);
            }
            $this->request .= ' ';
        }

        // DESC
        if($this->desc == true)
        {
            $this->request .= 'DESC ';
        }

        // LIMIT
        if($this->limit > $this->offset)
        {
            $this->request .= 'LIMIT ' . $this->limit . ' ';
        }
        
        // OFFSET
        if($this->offset > 0)
        {
            $this->request .= 'OFFSET ' . $this->offset . ' ';
        }

        $this->request .= ' ;' ;

        //echo $this->request . '<br/>';
        parent::build();
        return $this;
    }


}

?>
