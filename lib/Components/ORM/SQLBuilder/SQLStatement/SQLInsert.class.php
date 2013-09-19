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
 * Classe SQLInsert
 *
 * Permet de préparer une requete SQL de type INSERT.
 * Cette requete est directement compréhensible pour un moteur SQL.
 *
 * Il y a deux cas d'utilisations :
 *
 * Cas 1 : On veux ajouter un enregistrement en donnant des valeurs à certaines colonnes seulement
 * $sqlinsert = new SQLInsert();
 * $sqlinsert->insertInto('FooBar')
 *              ->valueForColumn('Foo', 'bar')
 *              ->valueForColumn('Bar', 'Foo')
 *              ->build();
 *
 * Trace :
 *  INSERT INTO FOOBAR (bar, Foo)
 *  VALUES ('Foo', 'Bar') ;
 *
 * Cas 2 : On veux ajouter un enregistrement complet
 * $sqlinsert = new SQLInsert();
 * $sqlinsert->insertInto('FooBar')
 *              ->values(array('Foo', 'Bar', 'Baz', 'Qux'))
 *              ->build();
 *
 * Trace :
 *  INSERT INTO FOOBAR (bar, Foo)
 *  VALUES ('Foo', 'Bar') ;
 *
 *
 * 
 * @author Emmanuel Corbeau
 *
 */
class SQLInsert  extends SQLRequest
{
    /** La table dont on veut ajouter un enregistrement */
    private $table;
    /** Les valeurs à ajouter */
    private $values = array();
    /** Les valeurs à ajouter pour des colonnes précises */
    private $valuesForColumn = array();

    public function insertInto($tableName)
    {
        $this->table = $tableName;
        return $this;
    }

    public function values($values = array())
    {
        $index = 0;
        while($index < count($values))
            $this->values[] = $values[$index++];
        return $this;
    }

    public function valueForColumn($value, $forColumn)
    {
        $this->valuesForColumn[$forColumn] = $value;
        return $this;
    }

    public function build()
    {
        // INSERT INTO table
        $this->request = 'INSERT INTO ';
        $this->request .= strtoupper($this->table);
        $this->request .= ' ';
        
        // 2 cas possibles à ce moment :
        // 1 - Soit on ajoute un enregistrement complet
        // 2 - Soit on veut seulement remplir certaines colonnes

        // Cas 1 : On ajoute un enregistrement complet
        if(!empty ($this->values))
        {
            $index = 0;
            $this->request .= 'VALUES (';
            $this->request .= '\''. $this->values[$index++];
            while ($index < count($this->values))
            {
                $this->request .= '\',\'' . addslashes ($this->values[$index++]);
            }
            $this->request .= '\')';
        }
        else // Cas 2 : on ajoute des valeurs pour certaines colonnes
        {
            $index = 0;
            $this->request .= '(';
            $columns = array_keys($this->valuesForColumn);
            $this->request .= $columns[$index++];
            while($index < count($columns))
            {
                $this->request .= ', ' . $columns[$index++];
            }
            $this->request .= ') ';

            $index = 0;
            $this->request .= 'VALUES (';
            // Si on a spécifié la colonne id, il faut vérifier si elle vaut null (pour l'auto-increment)
            // ou si elle a une valeur, qui dans ce cas est un entier. Il faut alors ne pas mettre les quotes (')
            if($columns[$index] == 'id')
            {
                $value = $this->valuesForColumn[$columns[$index++]];
                if($value == 'NULL' || $value == NULL)
                    $this->request .= 'NULL';
                elseif(is_int($value))
                    $this->request .= $value;
            }
            else
            {
                $this->request .= '\'';
                $this->request .= addslashes ($this->valuesForColumn[$columns[$index++]]);
                $this->request .= '\'';
            }
            //$this->request .= ',';
            while($index < count($columns))
            {
                $this->request .= ',';
                // Si on a spécifié la colonne id, il faut vérifier si elle vaut null (pour l'auto-increment)
                // ou si elle a une valeur, qui dans ce cas est un entier. Il faut alors ne pas mettre les quotes (')
                if($columns[$index] == 'id')
                {
                    $value = $this->valuesForColumn[$columns[$index++]];
                    if($value == 'NULL' || $value == NULL)
                    {
                        $this->request .= 'NULL';
                    }
                    elseif(is_int($value))
                    {
                        $this->request .= $value;
                    }
                }
                else
                {
                    $this->request .= '\'';
                    $this->request .= addslashes ($this->valuesForColumn[$columns[$index++]]);
                    $this->request .= '\'';
                }
            }
            $this->request .= ')';
        }

        $this->request .= ' ;' ;

        //echo $this->request;
        parent::build();
        return $this;
    }
}

?>
