<h1>500</h1>
<h2>Error de Servidor.</h2>

<p>
    <?php if (Configure::read('debug') > 0 ){ ?>
        <?=$this->element('exception_stack_trace'); ?>
    <?php } ?>
</p>

<p>
    <a href="<?=$this->Html->url(Configure::read('App.rootUrl'))?>"> Regresar </a>
    <br>
</p>