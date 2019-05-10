<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the login functions only once
JLoader::register('ModLoginVirtuemartHelper', __DIR__ . '/helper.php');

$params->def('greeting', 1);
$list             = ModLoginVirtuemartHelper::getListMenu($params);
$type             = ModLoginVirtuemartHelper::getType();
$return           = ModLoginVirtuemartHelper::getReturnUrl($params, $type);
$twofactormethods = JAuthenticationHelper::getTwoFactorMethods();
$user             = JFactory::getUser();
$layout           = $params->get('layout', 'vertical');

// Logged users must load the logout sublayout
if (!$user->guest)
{
	$layout = 'default_logout';
}

require JModuleHelper::getLayoutPath('mod_login_virtuemart', $layout);
