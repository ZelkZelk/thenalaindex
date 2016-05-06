<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
    <head>
        <meta charset="utf-8"/>
        <title>Acceso <?=Configure::read('App.name')?></title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8">
        <meta content="" name="description"/>
        <meta content="" name="author"/>
        
        <!-- BEGIN GLOBAL MANDATORY STYLES -->
        <link href="<?=$this->webroot?>global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?=$this->webroot?>global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?=$this->webroot?>global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?=$this->webroot?>global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
        <link href="<?=$this->webroot?>global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?=$this->webroot?>global/plugins/bootstrap-toastr/toastr.min.css" rel="stylesheet" type="text/css"/>
        <!-- END GLOBAL MANDATORY STYLES -->
        
        <!-- BEGIN PAGE LEVEL STYLES -->
        <link href="<?=$this->webroot?>global/plugins/select2/select2.css" rel="stylesheet" type="text/css"/>
        <link href="<?=$this->webroot?>admin/pages/css/login.css" rel="stylesheet" type="text/css"/>
        
        <!-- END PAGE LEVEL SCRIPTS -->
        
        <!-- BEGIN THEME STYLES -->
        <link href="<?=$this->webroot?>global/css/components.css" rel="stylesheet" type="text/css"/>
        <link href="<?=$this->webroot?>global/css/plugins.css" rel="stylesheet" type="text/css"/>
        <link href="<?=$this->webroot?>admin/layout2/css/layout.css" rel="stylesheet" type="text/css"/>
        <link id="style_color" href="<?=$this->webroot?>admin/layout2/css/themes/default.css" rel="stylesheet" type="text/css"/>
        <link href="<?=$this->webroot?>admin/layout2/css/custom.css" rel="stylesheet" type="text/css"/>
        <!-- END THEME STYLES -->
        
        <!--[if lt IE 9]>
        <script src="/global/plugins/respond.min.js"></script>
        <script src="/global/plugins/excanvas.min.js"></script> 
        <![endif]-->
        <script src="<?=$this->webroot?>global/plugins/jquery-1.11.0.min.js" type="text/javascript"></script>
        <script src="<?=$this->webroot?>global/plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>
        <!-- IMPORTANT! Load jquery-ui-1.10.3.custom.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
        <script src="<?=$this->webroot?>global/plugins/jquery-ui/jquery-ui-1.10.3.custom.min.js" type="text/javascript"></script>
        <script src="<?=$this->webroot?>global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="<?=$this->webroot?>global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript"></script>
        <script src="<?=$this->webroot?>global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
        <script src="<?=$this->webroot?>global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
        <script src="<?=$this->webroot?>global/plugins/jquery.cokie.min.js" type="text/javascript"></script>
        <script src="<?=$this->webroot?>global/plugins/uniform/jquery.uniform.min.js" type="text/javascript"></script>
        <script src="<?=$this->webroot?>global/plugins/bootstrap-toastr/toastr.min.js" type="text/javascript"></script>
        <!-- END CORE PLUGINS -->
        
        <!-- BEGIN PAGE LEVEL PLUGINS -->
        <script src="<?=$this->webroot?>global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
        <script type="text/javascript" src="<?=$this->webroot?>global/plugins/select2/select2.min.js"></script>
        <!-- END PAGE LEVEL PLUGINS -->
        
        <!-- BEGIN PAGE LEVEL SCRIPTS -->
        <script src="<?=$this->webroot?>global/scripts/metronic.js" type="text/javascript"></script>
        <script src="<?=$this->webroot?>admin/layout2/scripts/layout.js" type="text/javascript"></script>
        <!-- END PAGE LEVEL SCRIPTS -->
        
        <script>            
            jQuery(document).ready(function() {     
                Metronic.init(); // init metronic core components
                Layout.init(); // init current layout
            });
        </script>
        
        <link rel="shortcut icon" href="<?=$this->webroot?>favicon.ico"/>            
    </head>
    <body class="login">
        <div class="logo"><img src="<?=$this->webroot?>img/logo.png" width="140px" height="auto"/></div>
        
        <div class="menu-toggler sidebar-toggler"></div>
        
        <div class="content">
            <?=$this->fetch('content'); ?>
        </div>
        
        <?=$this->Session->flash(); ?>
    </body>
</html>