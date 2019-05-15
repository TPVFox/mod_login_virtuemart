<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
?>
<!-- Estoy default logout -->
<div class="usuario-registrado">
<form action="<?php echo JRoute::_(htmlspecialchars(JUri::getInstance()->toString()), true, $params->get('usesecure')); ?>" method="post" id="login-form" class="form-vertical">
<script type="text/javascript">
function mostrarMicuenta() {
	document.getElementById('Pop-up').style.display = 'table';
	}
function OcultarMicuenta() {
	document.getElementById('Pop-up').style.display = 'none';
	}
</script>
<div class="usuarioRegistrado" onmouseover="mostrarMicuenta()">
<?php if ($params->get('greeting')) : ?>
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
<?php endif; ?>
	<div class="Pop-up" id="Pop-up" onmouseout="OcultarMicuenta(this)">
		<h3 class="LetraVerde">Mi cuenta</h3>
		<ul>
		<li><a href="index.php?option=com_virtuemart&amp;view=user&amp;layout=edit&amp;Itemid=327" title="Mantenimiento de cuenta">Informacion de cliente</a></li>	
		<li><a href="index.php?option=com_virtuemart&amp;view=user&amp;layout=editaddress&amp;Itemid=329" title="Direcciones de envi&oacute;">Direcciones de envío</a></li>
		<li><a href="index.php?option=com_virtuemart&amp;view=orders&amp;layout=list&amp;Itemid=328" title="Listado pedidos">Historial de pedidos</a></li>
		</ul>
	</div>
</div>

	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="user.logout" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
</div>
