<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UsersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="users-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Users', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'x_user',
            't_user',
            't_name',
            't_password',
            't_lastname',
            // 't_dni',
            // 't_mail',
            // 'created',
            // 'creation_date',
            // 'modified',
            // 'modification_date',
            // 't_internal_password',
            // 'group_origin',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
