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


if (false == session_id()) {
    session_start();
}

/** Class User
 *
 * L'utilisateur est une personne utilisant le site.
 * Cette classe sert à enregistrer des informations le concernant grâce
 * aux sessions.
 * Elle permet de gérer facilement la session d'un utilisateur en :
 *      -> Assignant un attribut à l'utilisateur
 *      -> Obtenir la valeur d'un attribut
 *      -> Authentifier l'utilisateur
 *      -> Savoir si l'utilisateur est authentifié
 *      -> Assigner un message informatif à l'utilisateur que l'on affichera sur la page
 *      -> Savoir si l'utilisateur a un tel message
 *      -> Récupérer ce message
 *
 *
 * @author Emmanuel CORBEAU
 *
 */
class Session extends ApplicationComponent
{
    /** Obtient la valeur d'un attribut depuis le tableau $_SESSION
     *
     * @param mixed $attr la clé de l'attribut dans le tableau $_SESSION
     * @return mixed le contenu de l'attribut
     */
    public function getAttribute($attr)
    {
        return isset($_SESSION[$attr]) ? $_SESSION[$attr] : null;
    }

    /** Récupérer un message temporaire
     *
     * @return string le contenu du message
     */
    public function getFlash()
    {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return $flash;
    }

    /** Vérifie si l'utilisateur a un message temporaire
     *
     * @return bool true si l'utilisateur a un message, false sinon
     */
    public function hasFlash()
    {
        return isset($_SESSION['flash']);
    }

    /** Indique si l'utilisateur est connecté
     *
     * @return bool true si l'utilisateur est connecté, false sinon
     */
    public function isAuthenticated()
    {
        return isset($_SESSION['auth']) && $_SESSION['auth'] === true;
    }

    /** Définit un nouvel attribut à la session de l'utilisateur
     *
     * @param mixed $attr la clé de l'attribut du tableau $_SESSION
     * @param mixed $value la valeur de l'attribut
     */
    public function setAttribute($attr, $value)
    {
        $_SESSION[$attr] = $value;
    }

    /** Définit si l'utilisateur est connecté
     *
     * @param bool $authenticated true s'il est connecté, false s'il ne l'est plus
     */
    public function setAuthenticated($authenticated = true)
    {
        if (!is_bool($authenticated))
        {
            throw new InvalidArgumentException('La valeur spécifiée à la méthode User::setAuthenticated() doit être un boolean');
        }

        $_SESSION['auth'] = $authenticated;
    }

    /** Définit un message temporaire pour l'utilisateur
     *
     * @param string $value le contenu du message
     */
    public function setFlash($value)
    {
        $_SESSION['flash'] = $value;
    }
}
?>