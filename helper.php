<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Helper for mod_login
 *
 * @since  1.5
 */
class ModLoginVirtuemartHelper
{

    /**
     * Retrieve the URL where the user should be returned after logging in
     *
     * @param   \Joomla\Registry\Registry  $params  module parameters
     * @param   string                     $type    return type
     *
     * @return string
     */
    public static function getReturnUrl($params, $type)
    {
        $app = JFactory::getApplication();
        $item = $app->getMenu()->getItem($params->get($type));

        // Stay on the same page
        $url = JUri::getInstance()->toString();

        if ($item) {
            $lang = '';

            if ($item->language !== '*' && JLanguageMultilang::isEnabled()) {
                $lang = '&lang=' . $item->language;
            }

            $url = 'index.php?Itemid=' . $item->id . $lang;
        }

        return base64_encode($url);
    }

    /**
     * Returns the current users type
     *
     * @return string
     */
    public static function getType()
    {
        $user = JFactory::getUser();

        return (!$user->get('guest')) ? 'logout' : 'login';
    }

    /**
     * Get list of available two factor methods
     *
     * @return array
     *
     * @deprecated  4.0  Use JAuthenticationHelper::getTwoFactorMethods() instead.
     */
    public static function getTwoFactorMethods()
    {
        JLog::add(__METHOD__ . ' is deprecated, use JAuthenticationHelper::getTwoFactorMethods() instead.', JLog::WARNING, 'deprecated');

        return JAuthenticationHelper::getTwoFactorMethods();
    }

    public static function getListMenu(&$params)
    {
        $app = JFactory::getApplication();
        $menu = $app->getMenu();

        // Get active menu item
        $base = self::getBase($params);
        $user = JFactory::getUser();
        $levels = $user->getAuthorisedViewLevels();
        asort($levels);
        $key = 'menu_items' . $params . implode(',', $levels) . '.' . $base->id;
        $cache = JFactory::getCache('mod_menu', '');

        if (!($items = $cache->get($key))) {
            $path = $base->tree;
            $start = (int) $params->get('startLevel');
            $end = (int) $params->get('endLevel');
            $showAll = $params->get('showAllChildren');
            $items = $menu->getItems('menutype', $params->get('menutype'));

            $lastitem = 0;
            $padres = array();  // Creo array que cuenta cuantos hijo tiene cada padre.

            if ($items) {
                foreach ($items as $i => $item) {
                    // Buscamos si existe el nivel sino lo creamos.
                    if (isset($padres[$item->parent_id])) {
                        $hijos = $padres[$item->parent_id] + 1;
                        $padres[$item->parent_id] = $hijos;
                    } else {
                        $padres[$item->parent_id] = 1;
                    }
                    if (($start && $start > $item->level) || ($end && $item->level > $end) || (!$showAll && $item->level > 1 && !in_array($item->parent_id, $path)) || ($start > 1 && !in_array($item->tree[$start - 2], $path))) {
                        // Eliminamos items si no queremos mostrar ese nivel.
                        unset($items[$i]);
                        continue;
                    }

                    $item->deeper = false;
                    $item->shallower = false;
                    $item->level_diff = 0;

                    if (isset($items[$lastitem])) {
                        $items[$lastitem]->deeper = ($item->level > $items[$lastitem]->level);
                        $items[$lastitem]->shallower = ($item->level < $items[$lastitem]->level);
                        $items[$lastitem]->level_diff = ($items[$lastitem]->level - $item->level);
                    }

                    $item->parent = (boolean) $menu->getItems('parent_id', (int) $item->id, true);

                    $lastitem = $i;
                    $item->active = false;
                    $item->flink = $item->link;

                    // Reverted back for CMS version 2.5.6
                    switch ($item->type) {
                        case 'separator':
                        case 'heading':
                            // No further action needed.
                            continue;

                        case 'url':
                            if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false)) {
                                // If this is an internal Joomla link, ensure the Itemid is set.
                                $item->flink = $item->link . '&Itemid=' . $item->id;
                            }
                            break;

                        case 'alias':
                            $item->flink = 'index.php?Itemid=' . $item->params->get('aliasoptions');
                            break;

                        default:
                            $item->flink = 'index.php?Itemid=' . $item->id;
                            break;
                    }

                    if (strcasecmp(substr($item->flink, 0, 4), 'http') && (strpos($item->flink, 'index.php?') !== false)) {
                        $item->flink = JRoute::_($item->flink, true, $item->params->get('secure'));
                    } else {
                        $item->flink = JRoute::_($item->flink);
                    }

                    // Evitamos la doble codificación porque por alguna razón el $item es compartido para los módulos de menú y obtenemos doble codificación
                    // Cuando se encuenta la causa de que el argumento debe ser eliminado
                    $item->title = htmlspecialchars($item->title, ENT_COMPAT, 'UTF-8', false);
                    $item->anchor_css = htmlspecialchars($item->params->get('menu-anchor_css', ''), ENT_COMPAT, 'UTF-8', false);
                    $item->anchor_title = htmlspecialchars($item->params->get('menu-anchor_title', ''), ENT_COMPAT, 'UTF-8', false);
                    $item->menu_image = $item->params->get('menu_image', '') ?
                        htmlspecialchars($item->params->get('menu_image', ''), ENT_COMPAT, 'UTF-8', false) : '';
                } // fin recorido de items
                //~ echo '<pre>';
                //~ print_r($padres);
                //~ echo '<pre>';
                if (isset($items[$lastitem])) {
                    $items[$lastitem]->deeper = (($start ? $start : 1) > $items[$lastitem]->level);
                    $items[$lastitem]->shallower = (($start ? $start : 1) < $items[$lastitem]->level);
                    $items[$lastitem]->level_diff = ($items[$lastitem]->level - ($start ? $start : 1));
                }
            }

            $cache->store($items, $key);
        }
        // Ahora añadimos los hermanos que hay en cada item.
        foreach ($items as $i => $item) {
            // teniendo en cuenta que el valor 1 , es que hijo unico... :-)
            $items[$i]->hermanos = $padres[$item->parent_id];
        }

        return $items;
    }

    public static function getBase(&$params)
    {
        // Get base menu item from parameters
        if ($params->get('base')) {
            $base = JFactory::getApplication()->getMenu()->getItem($params->get('base'));
        } else {
            $base = false;
        }

        // Use active menu item if no base found
        if (!$base) {
            $base = self::getActive($params);
        }

        return $base;
    }

    public static function getActive(&$params)
    {
        $menu = JFactory::getApplication()->getMenu();
        $lang = JFactory::getLanguage();

        // Look for the home menu
        if (JLanguageMultilang::isEnabled()) {
            $home = $menu->getDefault($lang->getTag());
        } else {
            $home = $menu->getDefault();
        }

        return $menu->getActive() ? $menu->getActive() : $home;
    }

    /**
     * Obtiene las opciones del menú que pide el módulo a partir de los menús del sitio
     * @param objeto $params parámetros del módulo
     * @return mixto
     *      Opcion correcta:
     *          Array [items]  -> con los elementos del menú
     *                [titulo] -> con el nombre del menu o item padre
     *      Opcion incorrecta
     *           String con error a mostrar.
     */
    public static function obtenerOpcionesMenu($params)
    {
        $respuesta = array();
        $app = JFactory::getApplication();
        $menu = $app->getMenu(); // menú del sitio
        $error ='NO';
        $base = $params->get('base',0); // parámetro que indica elemento del menú o todo el menú
        if ($base > 0) {
             // Si hay base entonces obtenemos elemento y nombre menu
            $itemBase = $menu->getItem($params->get('base'));
            // Si menu selecionado no es el mismo que menu de item base tampoco tiene sentido.
            if ($params->get('menutype') !== $itemBase->menutype ){
                // El itembase seleccionado no es correcto no esta dentro del menu seleccionado.
                $error= JText::_('MOD_LOGIN_TEXTO_ERROR_OBTENEROPCIONESMENU');
            }
        } 

        if ($error ==='NO') {
            // Solo pasa si es mismo menu que el itembase seleccionado o si el itembase es 0
            $itemsMenu = $menu->getItems('menutype',$params->get('menutype'));
            // @ variable
            // $itemMenu = > Obtenemos array de todos los items menu ordenados padre hijos nietos...
            $mimenu = array ();
            $arbol = array ();
            $titulo = 'Pendiente obtener titulo modulo';
            if ($base == 0){
                // Si no hay $item base empieza desde el primer item.
                $empezar = 'Si';
                $nivel = 0 ;
            } else {
                $empezar = 'No';
            }
            foreach ($itemsMenu as $item){
                if ($base >0 && $item->id === $base) {
                    // Estamos en item que es el indicado por base.
                    $titulo = $item->title; // Para mostrar como cabecera .
                    $empezar = 'Si';
                    $nivel = $item->level;
                } 
                $control = true; // Bandera control para finalizar foreach si fuera necesario.
                if ($item->id !== $base && $empezar === 'Si') {
                    // Empezamos a montar si base es 0 o $item->id distinto de base..
                    if ($base > 0){
                        // Tenemos que comprobar si un descendiente de base,sino es NO continuamos el bucle
                        $control = in_array($base,$item->tree);
                    }
                    if ($control === true){
                        if ($params->get('showAllChildren') == 0 ){
                            // No queremos los descendientes.
                            if ( (intval($nivel) +1) == $item->level ) {
                                $mimenu[] = $item;
                            }
                        } else {
                            $mimenu[] = $item;
                        }
                    }
                }
                if ($control === false){
                    // Si control es false es porque base > 0 y item->tree no indica que sea pariente de base.
                    break;
                }
            }
            $respuesta['titulo'] = $titulo; //(Pendiente por resolver como obtener titulo menu.... )
            $respuesta['items']  = $mimenu;

        } else {
            $respuesta['error'] = $error;
        }
        return $respuesta;
    }



    public static function getLinksLogin($params)
    {
        $app = JFactory::getApplication();
        $items = array();
        $urls = array();
        $items['micuenta'] = $app->getMenu()->getItem($params->get('micuenta'));
        $items['registro'] = $app->getMenu()->getItem($params->get('registro'));

        // Stay on the same page
        $url = JUri::getInstance()->toString();

        if (gettype($items['micuenta'])=== 'object' && gettype($items['registro'])=== 'object' ) {
            foreach ($items as $item){
                $lang = '';

                if ($item->language !== '*' && JLanguageMultilang::isEnabled()) {
                    $lang = '&lang=' . $item->language;
                }
                

                $urls[] = 'index.php?Itemid=' . $item->id . $lang;
            }
        }
        //~ $url = base64_encode($url);
        return $urls;
    }

}
