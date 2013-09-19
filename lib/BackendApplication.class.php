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

/** Classe FrontendApplication
 *
 * Le frontend est la partie visible par tout le monde.
 * Il représente ce qu'un utilisateur annonyme pourra consulter
 *
 * @author Emmanuel CORBEAU
 *
 */
class BackendApplication extends Application
{
    public function run()
    {
        //echo 'Hello Backend ! <br/>';
        // Si l'utilisateur est connecté, il peut accéder à la partie administation
        if($this->session()->isAuthenticated() === true)
        {
            // Instantiation d'un routeur
            $router = new Router($this);
            // Obtention du contrôleur
            $controller = $router->getController();
        }
        else // Sinon on le redirige gentilement vers le formulaire de connexion
        {   require_once dirname($_SERVER['SCRIPT_FILENAME']).'/../apps/'. $this->name() . '/modules/connection/ConnectionController.class.php';
            $controller = new ConnectionController($this, 'connection', 'index');
        }

        // Exécution du contrôleur
        $controller->execute();

        // Assignation puis envoi de la page créée par le contrôleur à la réponse
        $this->httpResponse->setPage($controller->page());
        $this->httpResponse->send();
    }
}
?>
