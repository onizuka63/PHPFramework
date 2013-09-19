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


/** Classe BackController
 *
 * Classe mère de chaque contrôleur de l'application
 * Elle permet de :
 *      -> Exécuter une action (méthode)
 *      -> Obtenir la page associée au contrôleur
 *      -> Modifier le Module, l'action et la vue associé au contrôleur
 *
 *
 * @author Emmanuel CORBEAU
 *
 */
abstract class BackController extends ApplicationComponent
{
    /** Le nom de l'action à exécuter */
    protected $action = '';
    /** Le module de associé au contrôleur */
    protected $module = '';
    /** La page à afficher */
    protected $page = null;
    /** Le contenu de la page */
    protected $view = '';

    /** Constucteur d'un BackController
     *
     * @param Application $app l'application exécutée
     * @param <type> $module le module à charger
     * @param <type> $action l'action à exécuter
     */
    public function __construct(Application $app, $module, $action)
    {
        parent::__construct($app);

        $this->page = new Page($app);
        $this->setModule($module);
        $this->setAction($action);
        $this->setView($action);
    }

    /**
     * Invoque la méthode correspondant à l'action assignée à notre objet
     */
    public function execute()
    {
        $method = ucfirst($this->action).'Action';

        if (!is_callable(array($this, $method)))
        {
            throw new RuntimeException('L\'action "'.$this->action.'" n\'est pas définie sur ce module');
        }

        $this->$method($this->app->httpRequest());
    }

    /** Getter pour $page
     *
     * @return Page la page a afficher
     */
    public function page()
    {
        return $this->page;
    }
    /** Setter pour $module
     *
     * @param string $module le nom du module
     */
    public function setModule($module)
    {
        if (!is_string($module) || empty($module))
        {
            throw new InvalidArgumentException('Le module doit être une chaine de caractères valide');
        }

        $this->module = $module;
    }
    /** Setter pour $action
     *
     * @param string $action le nom de l'action à executer
     */
    public function setAction($action)
    {
        if (!is_string($action) || empty($action))
        {
            throw new InvalidArgumentException('L\'action doit être une chaine de caractères valide');
        }

        $this->action = $action;
    }
    /** Setter pour $view
     *
     * @param string $view le nom du fichier contenant la vue
     */
    public function setView($view)
    {
        if (!is_string($view) || empty($view))
        {
            throw new InvalidArgumentException('La vue doit être une chaine de caractères valide');
        }

        $this->view = $view;

        $this->page->setContentFile(dirname($_SERVER['SCRIPT_FILENAME']).'/../apps/'.$this->app->name().'/modules/'.$this->module.'/views/'.$this->view.'.php');
    }
}

?>
