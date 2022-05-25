<?php

use yii\helpers\Html;
use yii\grid\GridView;
use dosamigos\datepicker\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\WorkzatratySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Затраты');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="workzatraty-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
  
    <?php  echo $this->render('_search', ['model' => $searchModel]); ?> 
	
    </p>
	
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
		'showFooter' => true,
		'footerRowOptions'=>['style'=>'font-weight:bold;text-align:right'],
        'filterModel' => $searchModel,
        'columns' => [
      //      ['class' => 'yii\grid\SerialColumn'],

            ['attribute' =>'id',
			'headerOptions'=>['class'=>'text-center'],
			'contentOptions' => ['style' => 'width:80px;  min-width:80px;  ','class'=>'text-center']
			],
            ['attribute' => 'date',
            'headerOptions'=>['class'=>'text-center'],
            'format' => ['date', 'dd.MM.yyyy'],
            'contentOptions' => ['style' => 'width:180px;  min-width:180px;  ','class'=>'text-center'],
            'filter' =>DatePicker::widget([
                'model' => $searchModel,
                'value' => $searchModel->date,
                'attribute' => 'date',
                'language' => 'ru',
                
                'options' => ['placeholder' => 'Выбрать дату'],
                'template' => '{addon}{input}',
                'clientOptions' => [
                    'autoclose' => true,
                    'todayHighlight' => true,
                    'format' => 'yyyy-mm-dd'
             ],
			  ]),
			],
    
   			['attribute' =>'decl_id',
             'headerOptions'=>['class'=>'text-center'],
             'contentOptions' => ['style' => 'width:180px;  min-width:250px;  ','class'=>'text-left'],
            'value' =>'decl.decl_number',
            'filter' =>$arrDecl,
             ],

			['attribute' =>'client_id',
             'headerOptions'=>['class'=>'text-center'],
             'contentOptions' => ['style' => 'width:300px;  min-width:300px;  ','class'=>'text-left'],
             'value' =>'client.client',
             'filter' =>$arrClient,
			 'footer' => 'Всего:',
             ],
            ['attribute' =>'cost',
		     'headerOptions'=>['class'=>'text-center'],
			 'contentOptions' => ['style' => 'width:150px;  min-width:150px;  ','class'=>'text-right'],
			'footer' => number_format($sumCost,2,'.',''),
			],
            ['attribute' =>'workstatya_id',
			 'headerOptions'=>['class'=>'text-center'],
             'contentOptions' => ['style' => 'width:150px;  min-width:150px;  ','class'=>'text-left'],
             'value' =>'workstatya.statya',
             'filter' =>$arrStatya,
             ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
