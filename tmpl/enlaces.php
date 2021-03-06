<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Mostramos solo link de registro y acceso.
 *
 * 
 */

defined('_JEXEC') or die;

JLoader::register('UsersHelperRoute', JPATH_SITE . '/components/com_users/helpers/route.php');

JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');
if ($params->get('iconos_lista') == 1){
    $prefijo = 'icon-';
}else{
    $prefijo ='glyphicon glyphicon-';
}

?>
<div>
    <?php echo '<a href="'.JRoute::_($items[1]).'">'.JText::_('MOD_LOGIN_TEXTO_LINK_REGISTRO').'</a>';?>
    <span class="<?php echo $prefijo.'user';?>"></span>
    <?php echo '<a href="'.JRoute::_($items[0]).'">'.JText::_('MOD_LOGIN_TEXTO_LINK_MICUENTA').'</a>';?>

</div>
