<!DOCTYPE html>
<html>
    <head>
        <title>Bootstrap 101 Template</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <!-- Bootstrap -->
        <link href="<?php echo "/".$this->app->name()."/bootstrap/css/bootstrap.css" ?>" rel="stylesheet" media="screen"/>
        <style>
          body {
            padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
          }
        </style>
        <script src="<?php echo "/".$this->app->name()."/jquery/jquery-1.10.2.min.js" ?>"></script>
        <script src="<?php echo "/".$this->app->name()."/bootstrap/js/bootstrap.js" ?>"></script>
    </head>
    <body>
    <?php
        echo $this->getPartial('header');
        echo $content;
    ?>
    </body>
</html>