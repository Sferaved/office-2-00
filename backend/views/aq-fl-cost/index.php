<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\AqFlCostSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Доплаты по контрагентам Аква-Флексс';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="aq-fl-cost-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Добавить', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
   //         ['class' => 'yii\grid\SerialColumn'],

            'id',
            ['attribute' =>'contragent_id',
             'headerOptions'=>['class'=>'text-center'],
             'contentOptions' => ['style' => 'width:280px;  min-width:280px;  ','class'=>'text-left'],
             'value' =>'contragent.contragent',
             'filter' =>$arrContr,
			 'footer' => 'Всего:',
             ],
            'cost',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
