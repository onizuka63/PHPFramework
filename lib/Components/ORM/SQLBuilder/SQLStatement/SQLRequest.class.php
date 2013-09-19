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


abstract class SQLRequest
{
    /** La requete sous forme de chaine de caractères */
    protected $request;
    /** Sauvegarde l'état de la requete. Initialement à faux */
    protected  $isBuild = false;

    /** Méthode de construction de la requête
     * L'état final de la requête est à vrai
     */
    protected function build()
    {
        $this->isBuild = true;

    }

    /** Donne l'état de la requête
     * @return bool vrai si la requete est prête, faux sinon
     */
    public function isBuild()
    {
        return $this->isBuild;
    }

    /** Affiche la requête
     * @return string la requête
     */
    public function  __toString()
    {
        return $this->request;
    }

    //    abstract function select($column);
    
}

?>
