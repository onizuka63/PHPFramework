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


/** Classe Application
 *
 * Représente l'application en cours d'execution
 * Elle permet de :
 *      - Executer l'application
 *      - Obtenir la requete envoyée par le client
 *      - Obtenir la réponse que l'on envoie au client
 *      - Renvoyer sont nom
 *
 *
 * @author Emmanuel CORBEAU
 *
 */
abstract class Application
{
    /********* Liste des composants **********/

    /** La liste des composants de l'application */
    private static $components = array();

    public static function getComponent($componentName)
    {
        if( isset(self::$components[$componentName]))
        {
            return self::$components[$componentName];
        }
        $reflexion = new ReflectionClass($componentName);

        //pr($reflexion);

        $component = $reflexion->newInstance($this);
        
        //pr($component);

        self::$components[$componentName] = $component ;

    }

    public static function registerComponent( $component)
    {
        if(!isset(self::$components[$component->__toString()]))
        {
             self::$components[$component->__toString()] = $component;
        }
                
    }

    /*********  **********/

    /** La requête envoyée par le client */
    protected $httpRequest;
    /** La réponse à envoyer au client */
    protected $httpResponse;
    /** Le nom de l'application */
    protected $name;
    /** La session de l'utilisateur de l'application */
    protected $session;
    /** Le module de configuration */
    protected $config;

    /**
     *  Constructeur d'une application
     */
    public function __construct($name)
    {
        $this->httpRequest = new HTTPRequest($this);
        $this->httpResponse = new HTTPResponse($this);
        $this->name = $name;
        $this->session = new Session($this);
        $this->config = new Config($this);

        // On instancie le gestionnaire de base de donnée savec les bonnes valeurs
        // Ainsi, on y aurra accès dans toute l'application grace à Application::getComponent('PDOManager');
        $dsn = $this->config->get('SQL_DSN');
        $username = $this->config->get('SQL_USERNAME');
        $password = $this->config->get('SQL_PASSWORD');

        // On crée un nouveau gestionaire de base de données
        // Et on l'affecte à notre ORM

        try {
            DBEntity::setDbManager(new PDOManager($this, $dsn, $username, $password));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Fonction de l'application à executer 
     */
    abstract public function run();

    /** Getter pour l'attibut $httpRequest
     *
     * @return HttpRequest la requête envoyée par le client
     */
    public function httpRequest()
    {
        return $this->httpRequest;
    }
    /** Getter pour l'attibut httpResponse
     *
     * @return HttpResponse la réponse à envoyer au client
     */
    public function httpResponse()
    {
        return $this->httpResponse;
    }

    /** Getter pour l'attibut $name
     *
     * @return string le nom de l'application
     */
    public function name()
    {
        return $this->name;
    }

    /** Getter pour l'attribut $session
     *
     * @return Session La session de l'utilisateur de l'application
     */
    public function session()
    {
        return $this->session;
    }

    /** Accesseur pour le module de configuration
     *
     * @return Config le module de configuration
     */
    public function config()
    {
        return $this->config;
    }
}

 ?>