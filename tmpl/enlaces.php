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


?>
<div>
    <a href="<?php echo JRoute::_($items[0]);?>">Registrate</a>
    <span class="glyphicon glyphicon-user"></span>
    <a href="<?php echo JRoute::_($items[0]);?>">Mi cuenta</a>

</div>
