<?php

/* @var $this yii\web\View */

$this->title = 'Trabajando con Yii2: Inicio';
?>
<div class="site-index">

    <div class="jumbotron">
        <!--Llamado de mi component mensaje -->
        <h1>Congratulations! <?=Yii::$app->message->display('Chelo');?> </h1>

        <p class="lead">You have successfully created your Yii-powered application.</p>

        <a class="btn btn-lg btn-success" href="http://www.yiiframework.com">Get started with Yii</a>
        <a class="btn btn-lg btn-success" href="http://localhost/basic/web/index.php?r=site/entry">Formulario</a>
        <a class="btn btn-lg btn-success" href="http://localhost/basic/web/index.php?r=country/index">Muestra BD</a>
        <a class="btn btn-lg btn-success" href="http://localhost/basic/web/index.php?r=site/documentacion">Documentacion</a>
        <a class="btn btn-lg btn-success" style="background-color:rgb(82, 150, 183); border-color:rgb(82, 150, 183)"href="http://localhost/basic/web/index.php?r=gii">Iniciar Gii</a>


    </div>


    <div class="body-content">

        <div class="row">
            <div class="col-lg-4">
                <h2>Heading</h2>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-default" href="http://www.yiiframework.com/doc/">Yii Documentation &raquo;</a></p>
            </div>
            <div class="col-lg-4">
                <h2>Heading</h2>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-default" href="http://www.yiiframework.com/forum/">Yii Forum &raquo;</a></p>
            </div>
            <div class="col-lg-4">
                <h2>Heading</h2>

                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
                    dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                    ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
                    fugiat nulla pariatur.</p>

                <p><a class="btn btn-default" href="http://www.yiiframework.com/extensions/">Yii Extensions &raquo;</a></p>
            </div>
        </div>

    </div>
</div>
