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

/* Los iconos que utilizamos de Joomla con prefijo: icon-
 *  arrow-down-3
 *  user
 *  lock 
 *
 * Los iconos que utilzamos de GLYPHICON con prefijo: glyphicon glyphicon-
 *  chevron-down
 *  user
 *  lock
 *   
 * */
if ($params->get('iconos_lista') == 1){
    // Utiliza los icons de Joomla
    $prefijo = 'icon-';
    $icon_fecha = $prefijo.'arrow-down-3';
} else {
    $prefijo ='glyphicon glyphicon-';
    $icon_fecha = $prefijo.'chevron-down';
}

if ($params->get('popup_menu') ==1){
?>
<script type="text/javascript">
function mostrarMicuenta() {
	document.getElementById('Pop-up').style.display = 'table';
	}
function OcultarMicuenta() {
	document.getElementById('Pop-up').style.display = 'none';
	}
</script>
<?php
    $eventoMostrar = 'onmouseover="mostrarMicuenta()"';
    $eventoOcultar = 'onmouseout="OcultarMicuenta(this)"';
} else {
    $eventoMostrar = '';
    $eventoOcultar = '';
}
?>
<div class="usuario-registrado" <?php echo $eventoMostrar;?>>
  	<div class="login-greeting" >
    <form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure', 0)); ?>" method="post" id="login-form" class="form-vertical">
        <div class="login-greeting" >
            <?php if ($params->get('name') == 0) : {
                echo JText::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('name')));
            } else : {
                echo JText::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('username')));
            } endif; ?>
            <span class="<?php echo $icon_fecha;?>" style="font-size: 10px;"> </span>
            <span class="<?php echo $prefijo.'user';?>"></span>
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
    <div class="Pop-up" id="Pop-up" <?php echo $eventoOcultar;?>>
        <h3 class="LetraVerde"><?php echo $menuLogin['titulo'];?></h3>
        <?php // Obtenemos item de menu o mostramos error
        if (isset($menuLogin['error'])){
            echo '<div>'.$menuLogin['error'].'</div>';

        } else {
             $items = $menuLogin['items'];
            echo '<ul>';
            foreach ($items as $i=>$item){
                $nivel = $item->level;
                echo '<li>';
                    echo '<a href="'.$item->link.'">'.$item->title.'</a>';
                    $i_siguiente = $i +1;
                if ($nivel === $items[$i_siguiente]->level){
                    echo '</li>';
                } else {
                    if ($nivel < $items[$i_siguiente]->level){
                        // Es un hijo.
                        echo '<ul class="descendiente">';
                    } else {
                        echo '</li></ul>';

                    }
                }
                
            }
        }
        ?>
    </div>
</div>
