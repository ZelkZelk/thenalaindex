<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
    <head>
        <meta charset="utf-8"/>
        <title>The Nala Index - Web Archive</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8">
        <meta content="Web Archive" name="description"/>
        <meta content="klez" name="author"/>
        
        <link rel="shortcut icon" href="<?=$this->webroot?>favicon.ico"/>
        <link rel="stylesheet" href="<?=$this->webroot?>css/frontend/main.css"/>
        
        <?php $this->Head->dumpCss($CUSTOM_CSS)?>
        <?php $this->Head->dumpJsVars($CUSTOM_JS_VARS)?>
        <?php $this->Head->dumpJs($CUSTOM_JS)?>
    </head>
    <body>
        <?=$this->fetch('content'); ?>
    </body>
</html>