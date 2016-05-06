<div class="page-sidebar navbar-collapse collapse">
    <ul class="page-sidebar-menu page-sidebar-menu-hover-submenu " data-auto-scroll="true" data-slide-speed="200">
        <?php foreach($Menu as $menuOption){ ?>
            <?php if(!empty($menuOption['actions'])){ ?>
                <li>
                    <a href="javascript:;" rel="mlk-ignore">
                        <i class="fa fa-<?=$menuOption['icon']?>"></i><span class="title"><?=$menuOption['title']?></span>
                    </a>

                    <ul class="sub-menu" style="width:250px;">
                        <?php foreach($menuOption['actions'] as $action){ ?>
                            <li style="width:250px;">
                                <a href="<?=$this->Html->url(array('controller' => $action['controller'],'action' => $action['action']))?>">
                                    <i class="fa fa-<?=$action['icon']?>"></i>
                                    <?=$action['title']?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
        <?php } ?>
    </ul>
</div>