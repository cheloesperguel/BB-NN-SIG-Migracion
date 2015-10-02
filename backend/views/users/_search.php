<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\UsersSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="users-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'x_user') ?>

    <?= $form->field($model, 't_user') ?>

    <?= $form->field($model, 't_name') ?>

    <?= $form->field($model, 't_password') ?>

    <?= $form->field($model, 't_lastname') ?>

    <?php // echo $form->field($model, 't_dni') ?>

    <?php // echo $form->field($model, 't_mail') ?>

    <?php // echo $form->field($model, 'created') ?>

    <?php // echo $form->field($model, 'creation_date') ?>

    <?php // echo $form->field($model, 'modified') ?>

    <?php // echo $form->field($model, 'modification_date') ?>

    <?php // echo $form->field($model, 't_internal_password') ?>

    <?php // echo $form->field($model, 'group_origin') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
