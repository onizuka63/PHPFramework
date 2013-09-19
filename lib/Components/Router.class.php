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


/** Classe Router
 *
 * Le routeur sert à associer une url à un module et une action présise.
 * Ainsi, quand l'utilisateur saisit une url, le routeur se charge d'indiquer
 * quel contrôleur il faut charger et quelle méthode exécuter
 *
 *
 * @author Emmanuel CORBEAU
 *
 */
class Router extends ApplicationComponent
{
    /** Retrouve le controlleur qui va effecter les traitements nécessaires pour
     * afficher la bonne page au client.
     *
     * @return Controller le contrôleur associé à la route
     */
    public function getController()
    {
        $filename = dirname($_SERVER['SCRIPT_FILENAME']).'/../apps/'.$this->app->name().'/config/routes.xml';
        // Création d'une instance de DOMDocument
        $dom = new DOMDocument;

        // Chargement du ficher /apps/<NomApp>/config/routes.xml
        if (false == $dom->load($filename)) {
           throw new RuntimeException('Echec de chargement du fichier : ' . $filename);
        }
        
        $routes = $dom->getElementsByTagName('route');
        // Parcours de chaque route
        foreach ($routes as $route)
        {
            $uri = $this->app->httpRequest()->requestURI();
            $pattern = $route->getAttribute('url');
            // Si l'URL correspond
            if (preg_match('#'.$pattern.'#', $uri, $matches))
            {
                // On prends le module et l'action correspondant à l'URL
                $module = $route->getAttribute('module');
                $action = $route->getAttribute('action');

                //echo "module/action " . $module . ' ' . $action . '<br/>';

                // On inclut le contrôleur dont le chemin est /apps/<NomApp>/modules/<NomModule>
                $classname = ucfirst($module).'Controller';
                $file = dirname($_SERVER['SCRIPT_FILENAME']).'/../apps/'.$this->app->name().'/modules/'.$module.'/'.$classname.'.class.php';

                // On vérifie l'existence du contrôleur puis on l'inclut
                if (!file_exists($file))
                {
                   //$this->app->httpResponse()->redirect404();
                   throw new RuntimeException('Le module ou pointe la route n\'existe pas');
                }
                else
                {
                    require dirname($_SERVER['SCRIPT_FILENAME']).'/../apps/'.$this->app->name().'/modules/'.$module.'/'.$classname.'.class.php';
                }
                // Instanciation du contrôleur
                $controller = new $classname($this->app, $module, $action);

                // Si l'attribue vars existe dans la route, c'est que l'on veut récupérer des variables
                if ($route->hasAttribute('vars'))
                {
                    $vars = explode(',', $route->getAttribute('vars'));

                    foreach ($matches as $key => $match)
                    {
                        if ($key !== 0)
                        {
                            $this->app->httpRequest()->addGetVar($vars[$key - 1], $match);
                        }
                    }
                }

                break;
            } 
        }

        // Si aucun contrôleur n'est instancié, alors on balance une 404
        if (!isset($controller))
        {
            $this->app->httpResponse()->redirect404();
        }

        // Enfin, on peut renvoyer le contrôleur
        return $controller;
    }
}
?>