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


/** Classe ApplicationComponent
 *
 * Cette classe définit un composant pour notre application.
 * Un composant est un élément indispensable au fonctionnement du site (HTTPRequest par exemple)
 * Elle se charge de stocker l'application exécutée.
 *
 *
 * @author Emmanuel CORBEAU
 *
 */
abstract class ApplicationComponent
{
    /** L'application qui est exécutée */
    protected $app;

    /** Constructeur d'un composant
     *
     * @param Application $app l'application qui est exécutée
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        Application::registerComponent($this);

    }

    /** Permet d'accéder à l'application exécutée
     *
     * @return Application l'application exécutée
     */
    public function app()
    {
        return $this->app;
    }

    public function  __toString() 
    {
        $reflexion = new ReflectionClass($this);
        $name = $reflexion->getName();
        return $name;
    }
}
?>