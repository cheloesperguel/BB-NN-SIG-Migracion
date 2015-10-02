<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Users */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="users-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 't_user')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 't_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 't_password')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 't_lastname')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 't_dni')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 't_mail')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'creation_date')->textInput() ?>

    <?= $form->field($model, 'modified')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'modification_date')->textInput() ?>

    <?= $form->field($model, 't_internal_password')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'group_origin')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
