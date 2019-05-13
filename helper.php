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
     * @return mixto Array con los rlementos del menú que elige el módulo o false si hay error.
     */
    private static function obtenerOpcionesMenu($params)
    {
        $app = JFactory::getApplication();
        $menu = $app->getMenu(); // menú del sitio

        $base = $params->get('base'); // parámetro que indica elemento del menú o todo el menú
        if ($base == 0) {
            $itemBase = null;         // todo el menú
        } else {
            // los elementos que tienen al elegido como padre
            $itemBase = $menu->getItem($params->get('base'));
        }

        if ($base == 0 || $params->get('menutype') == $itemBase->menutype) {
            // elementos de un menú. El que marcan los parámetros
            $itemsMenu = $menu->getItems('menutype', $params->get('menutype'));

            $mimenu = [];
            $encontrado = ($base == 0);
            $indiceMenu = 0;

            // Se posiciona en el elemento elegido, o el primero de la lista si 
            // es todo el menú
            while ($indiceMenu < count($itemsMenu) && !$encontrado) {
                $encontrado = $itemsMenu[$indiceMenu]->id == $itemBase->id;
                if (!$encontrado) {
                    $indiceMenu++;
                }
            }

            if ($encontrado) {
                $arbol = $itemsMenu[$indiceMenu]->tree;

                $indiceArbolItemBase = -1;
                $encontradoIndiceArbol = ($base == 0);
                // se busca en que posición de la propiedad tree está el elemento.
                // Sus hijos tendrán el mismo número en la misma posición.
                // No se recorre si es "todo el menu"
                while ($indiceArbolItemBase < count($arbol) && !$encontradoIndiceArbol) {
                    $encontradoIndiceArbol = $arbol[$indiceArbolItemBase] == $itemBase->id;
                    if (!$encontradoIndiceArbol) {
                        $indiceArbolItemBase++;
                    }
                }
                
                if ($encontradoIndiceArbol) {
                    $nivel = $itemsMenu[$indiceMenu]->level;
                    $mostrarHijos = $params->get('showAllChildren');
                    $mimenu[] = $itemsMenu[$indiceMenu];
                    $nivelarbol = $nivel;
                    while (++$indiceMenu < count($itemsMenu) && $encontrado) {
                        if (($base == 0) 
                            || (isset($itemsMenu[$indiceMenu]->tree[$indiceArbolItemBase]) 
                                && $itemsMenu[$indiceMenu]->tree[$indiceArbolItemBase] == $itemBase->id)) {
                            if (($mostrarHijos == 1 && $itemsMenu[$indiceMenu]->level > $nivel) 
                                || ($base == 0 && $nivel == $itemsMenu[$indiceMenu]->level) 
                                || ($base != 0 && $nivel + 1 == $itemsMenu[$indiceMenu]->level)) {
                                $mimenu[] = $itemsMenu[$indiceMenu];
                            }
                        } else {
                            $encontrado = false;
                        }
                    }
                    $itemsMenu = $mimenu;
                }
            }
        } else {
            $itemsMenu = false;
        }
        return $itemsMenu;
    }

    public static function getElegidos($params)
    {
        $subMenu = self::obtenerOpcionesMenu($params);
        if ($subMenu === false) {
            $menuHTML = 'Error: el item de menú no pertenece al menú. Hable con el administrador';
        } else {
            $menuHTML = '';
            if (count($subMenu) > 0) {
                $nivel = $subMenu[0]->level;
                $indice = 0;
                $menuHTML .= '<ul><li>';
                $menuHTML .= $subMenu[$indice]->title;
                while (++$indice < count($subMenu)) {
                    $elemento = $subMenu[$indice];
                    if ($nivel == $elemento->level) {
                        $menuHTML .= '</li><li>';
                    } elseif ($nivel > $elemento->level) {
                        $menuHTML .= '</li></ul><br/><li>';
                    } elseif ($nivel < $elemento->level) {
                        $menuHTML .= '<br/><ul><li>';
                    }
                    $menuHTML .= '<a href="'.$elemento->link.'">'. $elemento->title.'</a>';
                    $nivel = $elemento->level;
                }
                $menuHTML .= '</li></ul>';
            }
        }
        return $menuHTML;
    }
}
