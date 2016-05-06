<li class="dropdown dropdown-user">
    <a rel="nofollow" href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
        <img alt="" class="img-avatar img-circle hide1"/>
        <span class="username username-hide-on-mobile"><i class="fa fa-user"></i><b>Administrador:</b> <?=$Admin->Data()->read('user_name');?> </span>
        <i class="fa fa-angle-down"></i>
    </a>
    
    <ul class="dropdown-menu">
        <?php foreach($AdminActions as $adminAction){ ?>        
            <li>
                <a href="<?=$this->Html->url(array('controller' => $adminAction['controller'],'action' => $adminAction['action']))?>">
                    <i class="fa fa-<?=$adminAction['icon']?>"></i> <?=$adminAction['title'] ?>
                </a>
            </li>
        <?php } ?>
    </ul>
</li>