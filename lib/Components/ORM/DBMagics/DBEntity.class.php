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
 * Classe DBEntity
 *
 * Une entité métier pouvant être persistée dans une base de données.
 * Toute classe héritant de DBEntity peut intéragir avec un DBManager
 * (ou un de ses fils) pour être enregistré en base.
 *
 * Pour être persistée correctement, l'entité devra porter le même nom
 * dans le système php et dans la base de données.
 */
abstract class DBEntity
{
    /** Constantes nomant les relations entre les entités */
    const NOT_RELATED = "NotRelated";  // Pas de relation
    const ONE_TO_ONE  = "OneToOne";  // Relation 1-1
    const ONE_TO_MANY = "OneToMany";  // Relation 1-N
    const MANY_TO_MANY= "ManyToMany";  // Relation N-N

    /** Constantes pour faire savoir si l'objet est nouveau lors de la construction */
    const IS_NEW = -1;

    /**
     * Le nom de l'entité. Est identique au nom adopté dans la base de données
     *
     * @var string nom de la classe
     */
    protected $className;

    /** Tableau des relations avec les autres entités
     *
     * @var array( 'NomEntité' = > 'RELATION_TYPE') le tableau des relations
     */
    protected $relatedWith = array();

    /** Pour les relations N-N, ce tableau conserve la clé étrangère de la table
     * Cela permet d'identifier sur autre chose que l'identifiant
     * Exemple : pour un objet Post
     * $post->foreignKeys['Tag'] = 'name';
     *
     * On évite des doublons et autre problèmes par la suite
     *
     * @var array( 'NomEntité' = > 'RELATION_TYPE') le tableau des relations
     */
    protected $foreignKeys = array();


    /** Le gestionnaire de données permet d'effectuer des requêtes en base.
     *
     * @var DBManager le gestionnaire de base de données
     */
    protected static $dbManager = NULL;

    /**
     * Constructeur d'une entité
     */
    public  function __construct($id = self::IS_NEW)
    {
        $flex = new ReflectionClass($this);
        $this->className = $flex->getName();
        $dbManager = Application::getComponent('PDOManager');
        if($id != self::IS_NEW)
        {
            $dbManager->read($this, $id);
            $this->getRelations();
        }
    }

    /** Getter magique pour tous les attributs
     *  Exemple :
     *      class A { public $attr; }
     *      $obj = new A();
     *      $attr = $obj->attr;
     *
     * @param string $name le nom de l'attribut
     * @return mixed l'attribut
     */
    public function  __get($name)
    {
        $ret = $this->{$name};
        return $ret;
    }


    /**
        // Test add
        $tag = new Tag();
        $tag->name = 'Tag 3';
        $post->addTag($tag);
        
     */
    public function add($array, $value)
    {
        // Pour éviter les doublons,
        // on teste si un objet a la même clé étrangère
        $value = $value[0];
        $foreignKey = $this->foreignKeys[$value->className];
        foreach ($this->$array as $member)
        {
            if($member->$foreignKey == $value->$foreignKey) return false;
        }

        array_push($this->$array, $value);
        return true;
    }

    /*
     *  print_r($this->$array); echo '<br/>';
        $currentItem = $this->$array[0];
        print_r($currentItem);
        for($i=0; $i<count($this->$array); $i++) {
            if($currentItem->$foreignKey == $value->foreignKey);
                return false;
        }
        array_push($this->$array, $value[0]);
        print_r($this->$array);
     */

    public function  __call($name,  $arguments)
    {
        if(!preg_match('/^(add|get|set)(\w+)$/', $name, $matches))
        {
            throw new \Exception("Call to undefined method {$name}");
        }

        switch ($matches[1])
        {
            case 'add' :
                $array = strtolower($matches[2]);
                return $this->add($array, $arguments);
            // Complete with other statements if needed
            default: break;
        }
    }

    /** Implémentation des MagicFinders
     * Permet d'utiliser la méthode find de manière plus intuitive,
     * 1/ Post::FindByAuthorOrderByDay('Author');
     *    SELECT * FROM POST WHERE AUTHOR = 'Author' ORDER BY DAY ;
     *
     * 2/ Post::FindByTitle('Post 1');
     *    SELECT * FROM POST WHERE TITLE = 'POST 1' ;
     *
     * 3/ Post::CountByAuthor('Author');
     *    SELECT COUNT(*) FROM POST WHERE AUTHOR = 'Author'
     *
     *
     * @param string $name le nom de la méthode à appeler
     * @param array $args $args[0] est le premier param, $arg[1] le deuxième, etc...
     */
    public static function  __callStatic($name,  $args)
    {
        $orderBy = null;
        $where   = null;

        if(!preg_match('/^([Ff]ind|[Cc]ount)By(\w+)$/', $name, $matches))
        {   // Non ? On leve une exception
            throw new \Exception("Call to undefined static method {$name}");
        }

        $method = $matches[1];

         // Clause OrderBy
        if(preg_match('/^(.*)(OrderBy)(\w+)$/', $matches[2], $orderBy))
        {
           $where = $orderBy[1];
           $orderBy = explode("And", $orderBy[3]);
        }
        else {
            $where = $matches[2];
        }


        // Clause Where
        $and = explode("And", $where);
        if(count($args) < count($and))
        {
            throw new \Exception("Error ! Not enough arguments for method ". $name);
        }

        $where = array();
        for($i=0; $i<count($and); $i++)
        {
            $where[] = $and[$i] . ' = ' . $args[$i];
        }


        if(preg_match('/^[Ff]ind$/', $method))
        {

            // On teste si la clause where fait référence à un N-N (comme Tag)
            // Pour cela on a besoin de parcourir le fichier de description de la base
            $xml = new DOMDocument;
            $xml->load(dirname($_SERVER['SCRIPT_FILENAME']).'/../apps/'.self::$dbManager->app()->name().'/config/database.xml');

            $elements = $xml->getElementsByTagName('table');

            // A partir de la, c'est super moche

            // Pour chaque Table déclarée dans le fichier
            foreach ($elements as $element)
            {
                // On regarde si son nom correspond à la classe appellante
                $isElementIsTable = (strtolower($element->getAttribute('name')) == strtolower(get_called_class())) ? true : false;

                // Si c'est le cas, on va chercher les différentes tables reliée à celle-ci
                if($isElementIsTable)
                {
                    if ($element->hasChildNodes())
                    {
                        // Pour chacune des tables en relation avec la table appelante
                        $childNodes = $element->childNodes;
                        foreach($childNodes as $child)
                        {
                            // On va regarder si la table reliée est citée dans la clause where
                            if ($child->hasAttribute('with') == false) {
                                continue;
                            }
                            $relatedTable = $child->getAttribute('with'); // marche pas
                            
                            for($i=0; $i< count($where); $i++)
                            {
                                // Si c'est le cas
                                $frags = explode($where[$i], ' = ');
                                if($frags[0] == $relatedTable )
                                {
                                    // Il faut regarder le type de relation
                                    $relationType = $relatedTable->getAttribute('type');
                                    if($relationType == self::MANY_TO_MANY)
                                    {
                                        $foreign = $relatedTable->getAttribute('foreign');
                                        $res = self::$dbManager->find(new $relatedTable, array($foreign . ' ' . $frags[1] . ' ' . $frags[2]));
                                    }
                                }
                            }
                        }
                    }
                }
            }



            $name = get_called_class();
            $entities = array();
            $res = self::find($where, $orderBy) ;
            for($i=0; $i<count($res); $i++)
            {
                $entity = new $name;
                $entity->hydrate($res[$i]);
                $entities[] = $entity;
            }
        }
        else if(preg_match('/^[Cc]ount$/', $method))
        {
            $res = self::count($where);
        }
        
        return $res;
    }


    /** Méthode de mise à jour d'une entité à partir d'un tableau
     *  (Map) récupéré en base de données
     * @param array $attr un tableau comme array('id'=> 1, 'content' => 'HelloWorld');
     */
    public function hydrate($attr = array())
    {
        $keys = array_keys($attr);
        for($i=0; $i < count($keys); $i = $i+2)
        {
            $this->$keys[$i] = $attr[$keys[$i]];
        }
    }

    /** Méthode de sauvegarde de l'entité en base de données
     * La création d'un enregistrement ou la mise à jour dépends
     * de l'attribut $id. S'il vaut NULL alors on crée, sinon on met à jour.
     */
    public function persist()
    {
        $dbManager = Application::getComponent('PDOManager');
        // On enregistre d'abord cet objet en base de données
        if($this->id == NULL) {
            $this->id = $dbManager->create($this);
        }
        else
            $dbManager->update($this);

        // Ensuite on met à jour toutes ses relations
        // 
        // Exemple Post_Has_Tag
        // Teste si il y a des relations définies
        $entitiesNames = array_keys($this->relatedWith);
        for($i=0; $i<count($entitiesNames);$i++)
        {
            $entityName = $entitiesNames[$i];
            switch($this->relatedWith[$entitiesNames[$i]])
            {
                case self::MANY_TO_MANY:
                    //  Si il y a des nouveaux tags dans la liste
                    //  (i.e. pas d'id_tag ou id_tag null ou id_tag non def...)
                    //      Alors on persiste les Tags et on garde les id
                    //      On crée une entrée dans la table post_has_tag avec l'id_post et l'id_tag
                    $entityListName = strtolower($entityName);
                    
                    foreach($this->$entityListName as $entity)
                    {
                        // ... ... ... ???
                        // On souhaire éviter les doublons dans nos enregistrements
                        // Pour cela on se sert de la clé étangère de l'entité
                        // Dans notre cas, c'est la colonne 'name' de l'entité 'Tag'
                        $col =$this->foreignKeys[$entity->className];
                        // On cherche en base si il y a un Tag du meme nom
                        $res = $dbManager->find($entity, array($col .' = '. $entity->$col ));
                        if(is_array($res) && empty ($res)) { // Comme il n'y a pas de résultat
                            $entity->persist(); // on peut l'enregistrer sans craindre les doublons
                        } else {
                            $entity->id = $res[0]['id'];
                        }
                        // On utilise le helper Foo pour se faire passer
                        // pour une entité n'existant pas dans le système
                        // mais existante en base de données.
                        // Grâce à lui nous avons pu sauver le lien entre Post et Tag
                        $foo = new Foo(strtolower($this->className.'_has_'.$entity->className));
                        $id_this = strtolower('id_'.$this->className);
                        $id_other= strtolower('id_'.$entity->className);
                        $foo->$id_this = $this->id;
                        $foo->$id_other= $entity->id; 

                        // On peut maintenant interroger la table Post_has_Tag
                        // On regarde si un enregistrement contient les identifiants de nos deux objets
                        $res = $dbManager->find($foo, array($id_this .' = '.$this->id,
                                                     $id_other.' = '.$entity->id));
                        // Il n'y a pas de résultats
                        if(is_array($res) && empty ($res)) {
                            // On peut enregistrer le lien
                            $foo->persist();
                        }
                    }
                    break;
                case self::ONE_TO_MANY:
                    break;
                case self::ONE_TO_ONE:
                    break;

            }
        }
    }

    /** Supprime l'enregistrement associé à cette entité
     */
    public function delete()
    {
        $dbManager = Application::getComponent('PDOManager');
        $dbManager->delete($this);
    } 

    
    protected function setRelatedEntity($tableName, $foreignKey)
    {
        $this->relatedWith[$tableName] = $this->getRelation($tableName);
        $this->foreignKeys[$tableName]  = $foreignKey;
    }

    /** Cherche si il y a une relation entre l'entité appellante et l'entité cible
     *
     * @param string $other l'entité cible
     * @return RELATION_TYPE Le type de relation qui existe
     */
    public static function getRelation($other)
    {
        // On va avoir besoin d'accéder à la base de données
        $db = self::$dbManager;
        $foreignKey = strtolower('id_'. get_called_class());

        // Pour l'exemple, la fonction est appellée ainsi :
        //      Post::getRelation('Tags');
        try
        {
            // On recherche l'élément id_post dans la table Tag
            $res = $db->describe($other);
            $i=0;
            do
            {
                $hasForeignKey = ($res[$i]['Field'] == $foreignKey) ? 1 : 0 ;
            } while(++$i < count($res) && ! $hasForeignKey);

            // Si il y a une clé étrangère
            if($hasForeignKey) // alors on est dans du 1-1 ou 1-N
            {
                // On vérifie si c'est une relation 1-1 ou 1-N
                // Pour cela on regarde la description de cette table
                // contient une clé étrangère référencant l'autre table
                $foreignKey = strtolower('id_'.$other);
                $res = $db->describe(get_called_class());
                do
                {
                    $hasForeignKey = ($res[$i]['Field'] == $foreignKey) ? 1 : 0 ;
                } while(++$i < count($res) && ! $hasForeignKey);
                // Si oui alors relation 1-1, sinon relation 1-N
                return ($hasForeignKey) ? self::ONE_TO_ONE : self::ONE_TO_MANY;
            }
            else // Sinon on est dans du N-N ou pas de relations
            {
                // Si la table POST_HAS_TAG peut être décrite
                $this_has_other = strtoupper(get_called_class().'_has_'.$other);
                $res = $db->describe($this_has_other);
                // Alors elle existe et il y a une relation
                if(isset($res) && ! empty($res))
                    return self::MANY_TO_MANY;
                else
                    return self::NOT_RELATED; // sinon il n'y a pas de liens
            }
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
        }
    }

    /** Cherche toutes les relations avec l'entité appelante
     *  les stocke dans l'attribut $relatedWith
     *  et les retourne.
     *
     * @return le tableau des relations avec cette entité
     */
    public function getRelations()
    {
        $tables = array_keys($this->relatedWith);
        foreach($tables as $table)
        {
            $this->getRelatedEntitiesFrom($table);
        }
    }

    public function getRelatedEntitiesFrom($tableName)
    {
        $self_id = 'id_' . strtolower($this->className);
        $other_id = 'id_' . strtolower($tableName);

        switch (self::getRelation($tableName))
        {
            case self::NOT_RELATED:
                // What to do ?
                break;
            case self::ONE_TO_ONE:
                $res = self::$dbManager->find(new $tableName,
                                       array($self_id . ' = ' . $this->id));
                $varName = strtolower($tableName);
                $entity = new $tableName;
                $entity->hydrate($res[0]);
                $this->$varName = $entity;
                break;
            case self::ONE_TO_MANY:
                $res = self::$dbManager->find(new $tableName,
                                       array($self_id . ' = ' . $this->id));
                $varName = strtolower($tableName) . 's';
                $this->$varName = array();
                for($i=0; $i < count($res); $i++)
                {
                    $entity = new $tableName;
                    $entity->hydrate($res[$i]);
                    array_push($this->$varName, $entity);
                }
                break;
            case self::MANY_TO_MANY:
                // Foo est un helper magique qui peut se faire passer pour n'importe quelle autre entité
                $foo = new Foo(strtolower($this->className.'_has_'.$tableName));
                // On retrouve tous les enregistrements de post_has_tag avant cet identifiant de post
                $res = self::$dbManager->find($foo, array($self_id . ' = ' . $this->id));
                $varName = strtolower($tableName);
                $this->$varName = array();
                
                for($i=0; $i < count($res); $i++)
                {
                    $res2 = self::$dbManager->find(new $tableName,array('id = ' . $res[$i][$other_id]));
                    $entity = new $tableName;
                    $entity->hydrate($res2[0]);
                    array_push($this->$varName, $entity);
                }
                break;
            default:
                // What to do ?
                break;
        }
    }

    /** Getter pour $dbManager
     *
     * @return DBManager le gestionnaire de données
     */
    public static function getDbManager()
    {
        return self::$dbManager;
    }

    /** Setter pour $dbManager
     *
     * @param DBManager $manager un gestionnaire de BDD
     */
    public static function setDbManager($manager)
    {
        self::$dbManager = $manager;
    }

    /** compte le nombre d'enregistrement de l'entité en base
     *
     * @return int le nombre d'enregistrement de l'entité en base
     */
    public static function count($where = array())
    {
        $name = get_called_class();
        return self::$dbManager->count(new $name, $where);
    }

    /** Trouve le/les enregistrement(s) en fonction des paramètres données
     *
     * @param array $where les conditions (array('table1 = val1', 'table2 <= val2', ...))
     * @param array $order les colonnes selon lesquel les enregistrements sont triés (array('day', 'author', ...);
     * @param int $limit
     * @param int $offset
     * @param desc $desc
     *
     * @return un tableau contenant tous les enregistrements
     */
    public static function find($where = array(), $order = array(), $limit = -1, $offset = 0, $desc = false)
    {
        $name = get_called_class();
        $entity = new $name();
        return self::$dbManager->find($entity, $where, $order, $limit, $offset, $desc);
    }

    /** Trouve un enregistrement de l'entité avec son identifiant
     *
     * @param int $id l'identifiant de l'objet
     * @return DBentity l'entité portant l'identifiant passé en paramètre
     */
    public static function findById($id)
    {
        $name = get_called_class();
        $entity = new $name();
        $res = self::$dbManager->find($entity, array('id = ' . $id));
        $entity->hydrate($res[0]);
        $entity->getRelations();

        return $entity;
    }

    /** Touve toutes les enregistrements de l'entité appellante présents en base
     * et retourne un tableau d'objet entités hydraté à partir de ces données.
     * 
     * @param $order les colonnes selon lesquelles l'enregitrement est retourné
     * @param $limit le numéro d'intentifiant pour lequel la recherche doit s'arreter
     * @param $offset le numéro d'identifiant pour lequel la recherche doit commencer
     * @param $desc true si les enregistrements doivent être retournés dans l'ordre inverse duquel ils ont été trouvés
     * @return DBEntity=array() les entités corespondants à l'enregistrement
     */
    public static function findAll($order = array(), $limit = -1, $offset = 0, $desc = false)
    {
        $name = get_called_class();
        $res = self::$dbManager->find(new $name, array(), $order, $limit, $offset, $desc);
        
        $index = 0;
        $entities = array();
        while($index < count($res))
        {
            $entity = new $name;
            $entity->hydrate($res[$index]);
            $entities[$index++] = $entity;
            $entity->getRelations();
        }

        return $entities;
    }

}

?>
