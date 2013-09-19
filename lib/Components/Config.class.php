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


/** Classe Config
 *
 * Charge et stocke les données de configuration
 * depuis le fichier apps/<NomApp>/config/app.xml
 *
 *
 * @author Emmanuel CORBEAU
 *
 */
class Config extends ApplicationComponent
{
    /** Le tableau qui contient les données de configuration */
    protected $vars = array();

    /** Revoie les données du tableau $vars pour la clé en paramètre
     *
     * @param string $var la clé des données auquelles on veut accéder
     * @return string les données ou null si la clé ne correspond à aucun enregistrement
     */
    public function get($var)
    {
        if (!$this->vars)
        {
            $filename = dirname($_SERVER['SCRIPT_FILENAME']).'/../apps/'.$this->app->name().'/config/app.xml';
            $xml = new DOMDocument;
            $xml->load($filename);

            $elements = $xml->getElementsByTagName('define');

            foreach ($elements as $element)
            {
                $this->vars[$element->getAttribute('var')] = $element->getAttribute('value');
            }
        }
        if (isset($this->vars[$var]))
        {
            return $this->vars[$var];
        }

        return null;
    }
}
?>
