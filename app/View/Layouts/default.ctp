<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
    <head>
        <meta charset="utf-8"/>
        <title><?=$Action['title']?></title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8">
        <meta content="" name="description"/>
        <meta content="" name="author"/>
        
        
        <!-- BEGIN GLOBAL MANDATORY STYLES -->
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
        <link href="/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
        <link href="/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
        <link href="/global/plugins/bootstrap-toastr/toastr.min.css" rel="stylesheet" type="text/css"/>
        <link href="/global/plugins/gritter/css/jquery.gritter.css" rel="stylesheet"/>
        <link href="/css/jquery-ui-custom.css" rel="stylesheet"/>
        <!-- END GLOBAL MANDATORY STYLES -->
        
        <!-- BEGIN THEME STYLES -->
        <link href="/global/css/components.css" rel="stylesheet" type="text/css"/>
        <link href="/global/css/plugins.css" rel="stylesheet" type="text/css"/>
        <link href="/admin/layout2/css/layout.css" rel="stylesheet" type="text/css"/>
        <link id="style_color" href="/admin/layout2/css/themes/default.css" rel="stylesheet" type="text/css"/>
        <link href="/admin/layout2/css/custom.css" rel="stylesheet" type="text/css"/>
        <!-- END THEME STYLES -->
        
        <script src="/global/plugins/jquery-1.11.0.min.js" type="text/javascript"></script>
        <script src="/global/plugins/jquery-ui/jquery-ui-1.10.3.custom.min.js" type="text/javascript"></script>
        <script src="/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
        <script src="/global/plugins/gritter/js/jquery.gritter.js" type="text/javascript"></script>
        <script src="/global/plugins/bootstrap-toastr/toastr.min.js" type="text/javascript"></script>
        
        <!--[if lt IE 9]>
        <script src="/global/plugins/respond.min.js"></script>
        <script src="/global/plugins/excanvas.min.js"></script> 
        <![endif]-->
        <script src="/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript"></script>
        <script src="/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
        <script src="/global/scripts/metronic.js" type="text/javascript"></script>
        <script src="/admin/layout2/scripts/layout.js" type="text/javascript"></script>
       
        <link rel="shortcut icon" href="<?=$this->webroot?>favicon.ico"/>
        
        <script>      
            $(function(){
                Metronic.init(); // init metronic core components
                Layout.init(); // init current layout
            });
        </script>
        
        <?php $this->Head->dumpCss($CUSTOM_CSS)?>
        <?php $this->Head->dumpJsVars($CUSTOM_JS_VARS)?>
        <?php $this->Head->dumpJs($CUSTOM_JS)?>
    </head>
    <body class="page-boxed page-header-fixed page-container-bg-solid page-sidebar-closed-hide-logo ">
        <div class="page-header navbar navbar-fixed-top">
            <div class="page-header-inner container">
                <?= $this->element('layout/logo'); ?>
                
		<div class="page-top">
                    <?= $this->element('layout/top-menu'); ?>
		</div>
            </div>
        </div>

        <div class="clearfix"></div>
        
	<!-- BEGIN CONTAINER -->    
        <div class="container">                
            <div class="page-container">
                <div class="page-sidebar-wrapper">
                    <?= $this->element('layout/sidemenu'); ?>
                </div>	

                <div class="page-content-wrapper">
                    <div class="page-content">
                        <?= $this->element('layout/breadcrumb'); ?>
                        <?=$this->fetch('content'); ?>
                    </div>
                </div>
            </div>
            
            <!-- BEGIN FOOTER -->
            <div class="page-footer">
                <div class="page-footer-inner">
                    
                </div>
                <div class="scroll-to-top">
                    <i class="fa fa-level-up"></i>
                </div>
            </div>
        </div>
        
        <?=$this->Session->flash(); ?>
    </body>
</html>