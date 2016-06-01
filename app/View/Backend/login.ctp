<?=$this->Form->create('Administrator',array('url' => $loginURL)); ?>
    <h3 class="form-title"> Inicie Sesión </h3>
    
    <div class="form-group">        
        <div class="input-icon">
            <i class="fa fa-user"></i>
            <?=$this->Form->input($loginField,array('label' => false,'class' => 'form-control','placeholder' => $Admin->getSchema()[$loginField]['label'],'div' => null));?>
        </div>
    </div>
    <div class="form-group">
        <div class="input-icon">
            <i class="fa fa-lock"></i>
            <?=$this->Form->input($passwordField,array('label' => false,'class' => 'form-control','placeholder' => $Admin->getSchema()[$passwordField]['label'],'div' => null,'type' => 'password'));?>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-info green pull-right btn-submit">Login</button>
    </div>
<?=$this->Form->end(); ?>