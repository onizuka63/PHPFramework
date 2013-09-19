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


/** Classe HTTPResponse
 *
 * Représente la réponse que le serveur va fournir au client
 * Elle permet de :
 *      -> Assigner une page à la réponse
 *      -> Envoyer la réponse en générant la page
 *      -> Rediriger l'utilisateur
 *      -> Rediriger l'utilisateur vers une page 404 quand sa demande ne peut aboutir
 *      -> Ajouter un cookie
 *      -> Ajouter un header spécifique
 *
 *
 * @author Emmanuel CORBEAU
 *
 */
class HTTPResponse extends ApplicationComponent
{
    /** La page assignée à la réponse */
    protected $page;

    /** Ajoute une header spécifique à la réponse
     *
     * @param string le header à ajouter
     */
    public function addHeader($header)
    {
        header($header);
    }

    /** Redirige l'utilisateur vers l'url qui corespond à sa requête
     *
     * @param string $location l'url vers laquelle l'utilisateur va être redirigé
     */
    public function redirect($location)
    {
        header('Location: '. $location);
        exit;
    }

    /** Redirige l'utilisateur vers la page d'erreur 404
     *
     */
    public function redirect404()
    {
        $this->page = new Page($this->app);
        $this->page->setContentFile(dirname($_SERVER['SCRIPT_FILENAME']).'/../apps/'.$this->app->name().'/templates/errors/404.php');

        $this->addHeader('HTTP/1.0 404 Not Found');

        $this->send();
    }

    /**
     * Envoie la page que le navigateur du client va afficher
     */
    public function send()
    {
        exit($this->page->getGeneratedPage());
    }

    /** Définit la page à afficher chez le client
     *
     * @param Page $page la page à afficher chez le client
     */
    public function setPage(Page $page)
    {
        $this->page = $page;
    }

    /** Définit le cookie qui va être envoyé au cliet
     *
     * @param <type> $name le nom du cookie
     * @param <type> $value le contenu du cookie
     * @param <type> $expire la date d'expiration du cookie
     * @param <type> $path le chemin ou sera stocké le cookie
     * @param <type> $domain 
     * @param <type> $secure
     * @param <type> $httpOnly
     */
    public function setCookie($name, $value = '', $expire = 0, $path = null, $domain = null, $secure = false, $httpOnly = true)
    {
        setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }
}
?>