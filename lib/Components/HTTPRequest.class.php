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


/** Classe HTTPRequest
 *
 * Représente la requête HTTP envoyée par le client
 * Elle permet de :
 *      -> Obtenir une variable POST
 *      -> Obtenir une variable GET
 *      -> Obtenir un cookie
 *      -> Obtenir l'URL saisie par le client
 *
 *
 * @author Emmanuel CORBEAU
 *
 */
class HTTPRequest extends ApplicationComponent
{
    /** Accède au données du cookie dont la clé est fournie en paramètre
     *
     * @param string $key la clé du cookie à rechercher
     * @return Le contenu du cookie si il existe, null s(il n'existe pas
     */
    public function cookieData($key)
    {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
    }

    /** Teste si un cookie existe
     *
     * @param string $key la clé du cookie à tester
     * @return TRUE si le cookie existe, FALSE sinon
     */
    public function cookieExists($key)
    {
        return isset($_COOKIE[$key]);
    }

    /** Accède au contenu de la variable GET dont la clé est fournie en paramètre
     *
     * @param string $key la clé de la variable GET dont on veut les données
     * @return Les données de la variable GET si elle existe, null sinon
     */
    public function getData($key)
    {
        return isset($_GET[$key]) ? $_GET[$key] : null;
    }

    /** Teste si une variable GET existe
     *
     * @param string $key la clé de la variable GET à tester
     * @return TRUE si la variable GET existe, FALSE sinon
     */
    public function getExists($key)
    {
        return isset($_GET[$key]);
    }

    /** Accède au contenu de la variable POST dont la clé est fournie en paramètre
     *
     * @param string $key la clé de la variable POST dont on veut les données
     * @return Les données de la variable POST si elle existe, null sinon
     */
    public function postData($key)
    {
        return isset($_POST[$key]) ? $_POST[$key] : null;
    }

    /** Teste si une variable POST existe
     *
     * @param string $key la clé de la variable POST à tester
     * @return TRUE si la variable POST existe, FALSE sinon
     */
    public function postExists($key)
    {
        return isset($_POST[$key]);
    }

    /** Renvoie l'url demandée par le client
     *
     * @return string l'url saisie par le client
     */
    public function requestURI()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /** Ajoute une nouvelle valeur au tableau $_GET
     *
     * @param <type> $key la clé de la variable
     * @param <type> $value la valeur de la variable
     */
    public function addGetVar($key, $value)
    {
        $_GET[$key] = $value;
    }
}

?>