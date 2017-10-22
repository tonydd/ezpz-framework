<?php
/**
 * @var Renderer $this
 */

/** @var LoginController $loginCtl */
$loginCtl = Controller::getInstance('login');
?>
<ul class="nav navbar-nav navbar-right">
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            <?php echo $loginCtl->getNom();?>
            <span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
            <?php if($loginCtl->isAdmin()) : ?>
                <li class="dropdown-header">Administration</li>
                <li>
                    <a href="<?php echo $this->buildUrl('admin', 'index');?>">
                        Administration
                    </a>
                </li>
                <li role="separator" class="divider"></li>
            <?php endif; ?>

            <li class="dropdown-header">Mon compte</li>
            <li>
                <a href="<?php echo $this->buildUrl('login','account');?>">
                    Mes infos
                </a>
            </li>
        </ul>
    </li>
    <li><a href="<?php echo $this->buildUrl('login','logout');?>">Déconnexion</a></li>
</ul>