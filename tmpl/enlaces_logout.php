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
<script type="text/javascript">
function mostrarMicuenta() {
	document.getElementById('Pop-up').style.display = 'table';
	}
function OcultarMicuenta() {
	document.getElementById('Pop-up').style.display = 'none';
	}
</script>
<div class="usuario-registrado" onmouseover="mostrarMicuenta()">
    <form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure', 0)); ?>" method="post" id="login-form" class="form-vertical">
        <div class="login-greeting" >
            <?php if ($params->get('name') == 0) : {
                echo JText::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('name')));
            } else : {
                echo JText::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('username')));
            } endif; ?>
            <span class="glyphicon glyphicon-chevron-down" style="font-size: 10px;"> </span>
            <span class="glyphicon glyphicon-user"></span>
            <input type="submit" name="Submit" class="button" value="<?php echo JText::_('JLOGOUT'); ?>" />
        </div>
        <div class="logout-button">
            <input type="hidden" name="option" value="com_users" />
            <input type="hidden" name="task" value="user.logout" />
            <input type="hidden" name="return" value="<?php echo $return; ?>" />
            <?php echo JHtml::_('form.token'); ?>
        </div>
    </form>
   
</div>
<div class="Pop-up" id="Pop-up">
    <?php
        echo '<pre>';
        print_r($menuLogin);
        echo '</pre>';
    ?>
</div>
