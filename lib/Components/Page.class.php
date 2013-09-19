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


/** Classe Page
 *
 * La page permet de :
 *      -> Ajouter une variable à la page (pour que le contrôleur puisse passer
 *    des données à la vue)
 *      -> Assigner une vue à la page
 *      -> Générer la page avec le layout de l'application
 *
 * 
 * @author Emmanuel CORBEAU
 *
 */
class Page extends ApplicationComponent
{
    protected $contentFile;
    protected $vars = array();

    /** Ajoute une variable à la page ans le tableau $vars
     *
     * @param string $var la clé
     * @param mixed $value la valeur
     */
    public function addVar($var, $value)
    {
        if (!is_string($var) || is_numeric($var) || empty($var))
        {
            throw new InvalidArgumentException('Le nom de la variable doit être une chaine de caractère non nulle');
        }

        $this->vars[$var] = $value;
    }

    /** Génère la page avec le layout de l'application
     *
     * @return string la page générée
     */
    public function getGeneratedPage()
    {
        if (!file_exists($this->contentFile))
        {
            throw new RuntimeException('La vue spécifiée (' . $this->contentFile . ') n\'existe pas');
        }

        $session = $this->app->session();

        extract($this->vars);
        
        ob_start();
            require $this->contentFile;
        $content = ob_get_clean();

        ob_start();
            require dirname($_SERVER['SCRIPT_FILENAME']).'/../apps/'.$this->app->name().'/templates/layout.php';
        return ob_get_clean();
    }

    /** Assigne une vue à la page
     *
     * @param string $contentFile
     */
    public function setContentFile($contentFile)
    {
        if (!is_string($contentFile) || empty($contentFile))
        {
            throw new InvalidArgumentException('La vue spécifiée est invalide');
        }

        $this->contentFile = $contentFile;
    }

    /** Retourne une vue partielle
     *
     * @param string $partial le nom de la vue partielle
     * @return string le contenu de la vue partielle
     */
    public function getPartial($partial) {
        $filename = dirname($_SERVER['SCRIPT_FILENAME']).'/../apps/'.$this->app->name().'/templates/partials/'.$partial.'.php';
        ob_start();
            require $filename;
        return ob_get_clean();
    }
}
?>
