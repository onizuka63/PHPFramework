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


abstract class DBManager extends     ApplicationComponent
              /*  implements  DataSourceAdapter,
                            TransactionAdapter,
                            CrudAdapter */
{
    protected $dataBase;
    protected $sqlBuilder;

    public function  __construct($app)
    {
        parent::__construct($app);
        
        $this->sqlBuilder = new SQLBuilder($app);
    }

    /****** Implementation de Transaction Adapter ******/

    /**
     * Pour signaler à la base que l'on va executer une requête
     */
    public function beginTransaction()
    {
        $this->dataBase->beginTransaction();
    }
    /**
     * Pour executer une requête en base
     */
    public function commit()
    {
        $this->dataBase->commit();
    }
    /**
     * Pour annuler l'execution en cas de problèmes
     */
    public function rollBack()
    {
        $this->dataBase->rollBack();
    }

    /****** Implementation de DataSourceAdapter ******/

    /**
     * Compte le nombre d'objet de type $entity en base
     * Exemple :
     *
     *  $post = new Post();
     *  $dbManager = Application::getComponent('PDOManager');
     *  echo 'Nombre de '. $post->className() . ' dans la base : ';
     *  echo $dbManager->count($post) . '<br/>';
     *
     * Résultat :
     *  Nombre de Post dans la base : 2
     *
     * @param DBEntity $entity le nom de la table à interoger
     * @return int le nombre d'enregistrements pour la table
     */
    public function count($entity, $where = array())
    {
        try
        {
            // la Requete vaut SELECT * FROM Entity ;
            $request = $this->sqlBuilder
                                ->select('COUNT(*)')
                                ->from($entity->className);
                                
            $cpt = 0;
            if(!empty ($where)) {
                while($cpt < count($where))
                {
                    $clause = $where[$cpt++];
                    $frags = explode(" ", $clause);
                    for($i=3; $i<count($frags); $i++) $frags[2] .= ' ' . $frags[$i];
                    $request->where($frags[0], $frags[1], $frags[2]);
                }
            }

            $request->build();
            //echo $request;
        }
        catch(SQLException $e)
        {
            echo 'SQLException !' . '<br/>'
                   . 'Message : ' . '<br/>'
                   . $e->getMessage();
        }

        if($this->sqlBuilder->isRequestReady())
        {
            try
            {
                $this->beginTransaction();
                $statement = $this->dataBase->prepare($request);
                $statement->execute();
                $this->commit();
            }
            catch (PDOException $e)
            {
                $this->rollBack();
                echo 'PDOException !' . '<br/>'
                       . 'Message : ' . '<br/>'
                       . $e->getMessage();
            }

            $res = $statement->fetch();
            return $res[0];
        }
    }
    public function findAll($entity)
    {
        return $this->find($entity);
    }
    public function find($entity, $where = array(), $order = array(), $limit = -1, $offset = 0, $desc = false)
    {
        try
        {
            // la Requete vaut SELECT * FROM Entity ;
            $request = $this->sqlBuilder
                                ->select('*')
                                ->from($entity->className)
                                ->limit($limit)
                                ->offset($offset);
            $cpt = 0;
            if(!empty ($where)) {
                while($cpt < count($where))
                {
                    $clause = $where[$cpt++];
                    $frags = explode(" ", $clause);
                    for($i=3; $i<count($frags); $i++) $frags[2] .= ' ' . $frags[$i];
                    $request->where($frags[0], $frags[1], $frags[2]);
                }
            }
            $cpt = 0;
            if(!empty ($order)) {
                while($cpt < count($order))
                {
                    $request->orderBy($order[$cpt++]);
                }
            }
            $request->desc($desc);
            $request->build();
            //echo $request . '<br/>';
           
        }
        catch(SQLException $e)
        {
            echo 'SQLException !' . '<br/>'
                   . 'Message : ' . '<br/>'
                   . $e->getMessage();
        }

        if($this->sqlBuilder->isRequestReady())
        {
            try
            {
                $this->beginTransaction();
                $statement = $this->dataBase->prepare($request);
                $statement->execute();
                $this->commit();
            }
            catch (PDOException $e)
            {
                $this->rollBack();
                echo 'PDOException !' . '<br/>'
                       . 'Message : ' . '<br/>'
                       . $e->getMessage();
            }

            $res = $statement->fetchAll();
            //print_r($res);
                        
            return $res;
        }
    }
    public function describe($table)
    {
        $request = 'DESCRIBE ' . strtoupper($table) . ' ;';

        //echo $request . '<br/>';

        try // Alors on execute la requête en base
        {
            $this->beginTransaction();
            $statement = $this->dataBase->prepare($request);
            $statement->execute();
            $this->commit();
            // Transaction Terminée ! On peut extraire le résultat
        }
        catch (PDOException $e)
        {
            $this->rollBack();
            echo 'PDOException !' . '<br/>'
                   . 'Message : ' . '<br/>'
                   . $e->getMessage();
        }
        $res = $statement->fetchAll();
        return $res;
    }
    
    /****** Implementation de CrudAdapter ******/
    public function create($entity)
    {
        try
        {
            $flex = new ReflectionObject($entity);
            $properties = $flex->getProperties(ReflectionProperty::IS_PUBLIC);

            $request = $this->sqlBuilder
                   ->insertInto($entity->className);

            foreach($properties as $propertie)
            {
                $request->valueForColumn($entity->__get($propertie->getName()), $propertie->getName());
            }

            $request->build();
           // echo $request;
        }
        catch(SQLException $e)
        {
            echo 'SQLException !' . '<br/>'
                   . 'Message : ' . '<br/>'
                   . $e->getMessage();
        }

        // Si on a terminé d'écrire la requête
        if($this->sqlBuilder->isRequestReady())
        {
            try // Alors on execute la requête en base
            {
                // Transmission...
                $this->beginTransaction();
                $statement = $this->dataBase->prepare($request);
                $statement->execute();
                $this->commit();

                // On veut récupérer l'identifiant de la ligne insérée
                // Puisque tout le système fonctionne à base d'id auto-incrémentés
                // On peut utiliser la requête SELECT MAX('id') FROM TABLE
                // Elle retourne l'identifiant max qui est forcément celui que l'on vient d'ajouter
                $request = $this->sqlBuilder->select('MAX(id)')
                                        ->from($entity->className)
                                        ->build();

                if($this->sqlBuilder->isRequestReady())
                {
                    // Transmission...
                    $this->beginTransaction();
                    $statement = $this->dataBase->prepare($request);
                    $statement->execute();
                    $this->commit();

                    $res = $statement->fetchAll();
                    return $res[0][0];
                }
            }
            catch (PDOException $e)
            {
                $this->rollBack();
                echo 'PDOException !' . '<br/>'
                       . 'Message : ' . '<br/>'
                       . $e->getMessage();
            }
        }
    }
    public function read($entity, $id)
    {
        try
        {
            // la Requete vaut SELECT * FROM Entity ;
            $request = $this->sqlBuilder
                                ->select('*')
                                ->from($entity->className)
                                ->where('id', '=', $id)
                                ->build();  // On la construit
                                // Quand on a terminé de l'ecrire
        }
        catch(SQLException $e)
        {
            echo 'SQLException !' . '<br/>'
                   . 'Message : ' . '<br/>'
                   . $e->getMessage();
        }

        // Si on a terminé d'écrire la requête
        if($this->sqlBuilder->isRequestReady())
        {
            try // Alors on execute la requête en base
            {
                $this->beginTransaction();
                $statement = $this->dataBase->prepare($request);
                $statement->execute();
                $this->commit();
                // Transaction Terminée ! On peut extraire le résultat
                
                $res = $statement->fetchAll();
                $res = $res[0];
            }
            catch (PDOException $e)
            {
                $this->rollBack();
                echo 'PDOException !' . '<br/>'
                       . 'Message : ' . '<br/>'
                       . $e->getMessage();
            }

            /// Mise à jour des données de l'entité
            $entity->hydrate($res);
        }
    }
    public function update($entity)
    {
        try
        {
            $flex = new ReflectionObject($entity);
            $properties = $flex->getProperties(ReflectionProperty::IS_PUBLIC);

            $request = $this->sqlBuilder
                                ->update($entity->className);

            foreach($properties as $propertie)
            {
                $request->set($propertie->getName(),$entity->__get($propertie->getName()));
            }

            $request->where('id', '=', $entity->id);
            $request->build();
        }
        catch(SQLException $e)
        {
            echo 'SQLException !' . '<br/>'
                   . 'Message : ' . '<br/>'
                   . $e->getMessage();
        }
        // Si on a terminé d'écrire la requête
        if($this->sqlBuilder->isRequestReady())
        {
            try // Alors on execute la requête en base
            {
                $this->beginTransaction();
                $statement = $this->dataBase->prepare($request);
                $statement->execute();
                $this->commit();
                // Transaction Terminée ! On peut extraire le résultat

                $res = $statement->fetchAll();
                $res = $res[0];
            }
            catch (PDOException $e)
            {
                $this->rollBack();
                echo 'PDOException !' . '<br/>'
                       . 'Message : ' . '<br/>'
                       . $e->getMessage();
            }
        }
    }  
    public function delete($entity)
    {
        try
        {
            $request = $this->sqlBuilder->delete()
                   ->from($entity->className)
                   ->where('id', '=', $entity->id)
                   ->build();
        }
        catch(SQLException $e)
        {
            echo 'SQLException !' . '<br/>'
                   . 'Message : ' . '<br/>'
                   . $e->getMessage();
        }

        // Si on a terminé d'écrire la requête
        if($this->sqlBuilder->isRequestReady())
        {
            try // Alors on execute la requête en base
            {
                $this->beginTransaction();
                $statement = $this->dataBase->prepare($request);
                $statement->execute();
                $this->commit();
                // Transaction Terminée ! On peut extraire le résultat
            }
            catch (PDOException $e)
            {
                $this->rollBack();
                echo 'PDOException !' . '<br/>'
                       . 'Message : ' . '<br/>'
                       . $e->getMessage();
            }
        }
    }

}


?>
