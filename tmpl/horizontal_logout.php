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

<div class="usuario-registrado" >
  	<div class="login-greeting" >
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