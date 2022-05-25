<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ClientInfoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Реквизиты для акта';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="client-info-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Добавить', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

         //   'id',
            ['attribute' =>'client_id',
             'headerOptions'=>['class'=>'text-center'],
             'contentOptions' => ['style' => 'width:200px;  min-width:200px;  ','class'=>'text-left'],
             'value' =>'client0.client',
             'filter' =>$arrClient,
             ],
            'telephon',
            'adress:ntext',
            'director',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
