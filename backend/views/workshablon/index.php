<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\WorkshablonSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Типовые затраты по клиентам');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="workshablon-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Добавить'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
         //   ['class' => 'yii\grid\SerialColumn'],

/*             ['attribute' =>'id',
			'headerOptions'=>['class'=>'text-center'],
		    'contentOptions' => ['style' => 'width:50px;  min-width:50px;  ','class'=>'text-right'],
		    ], */
            ['attribute' =>'client_id',
            'headerOptions'=>['class'=>'text-center'],
            'contentOptions' => ['style' => 'width:450px;  min-width:450px;  ','class'=>'text-center'],
            'value' =>'client0.client',
            'filter' =>$arrClient,
            ],
            
			['attribute' =>'cost',
   		    'headerOptions'=>['class'=>'text-center'],
			'contentOptions' => ['style' => 'width:200px;  min-width:200px;  ','class'=>'text-right'],
		    ],
			
	        ['attribute' =>'statya_id',
            'headerOptions'=>['class'=>'text-center'],
            'contentOptions' => ['style' => 'width:200px;  min-width:200px;  ','class'=>'text-center'],
            'value' =>'statya0.statya',
            'filter' =>$arrStatya,
	 
             ],
			
            ['attribute' =>'ex_im',
			'headerOptions'=>['class'=>'text-center'],
		    'contentOptions' => ['style' => 'width:200px;  min-width:200px;  ','class'=>'text-center'],
			'filter' =>['Импорт'=>'Импорт','Экспорт'=> 'Экспорт']
			],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
