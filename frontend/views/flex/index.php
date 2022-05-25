<?php

use yii\helpers\Html;
use yii\grid\GridView;
use dosamigos\datepicker\DatePicker;
use common\models\Contragent;
use frontend\models\Declaration;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\FlexSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Флекс');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="flex-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php   echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
		'showFooter' => true,
		'footerRowOptions'=>['style'=>'font-weight:bold;text-align:right'],
        'filterModel' => $searchModel,
        'columns' => [
   //         ['class' => 'yii\grid\SerialColumn'],

   //         'id',
            ['attribute' => 'date',
            'headerOptions'=>['class'=>'text-center'],
            'format' => ['date', 'dd.MM.yyyy'],
            'contentOptions' => ['style' => 'width:160px;  min-width:160px;  ','class'=>'text-center'],
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
    
            ['attribute' =>'ex_im',
			'headerOptions'=>['class'=>'text-center'],
			'contentOptions' => ['style' => 'width:120px;  min-width:120px;  ','class'=>'text-right'],
			'filter' =>['Импорт'=>'Импорт','Экспорт'=> 'Экспорт'],
			],
            ['attribute' =>'decl_number_id',
             'headerOptions'=>['class'=>'text-center'],
             'contentOptions' => ['style' => 'width:200px;  min-width:200px;  ','class'=>'text-left'],
             'value' =>'declNumber.decl_number',
             'filter' =>$arrDecl,
             ],
            ['attribute' =>'contragent_id',
             'headerOptions'=>['class'=>'text-center'],
             'contentOptions' => ['style' => 'width:280px;  min-width:280px;  ','class'=>'text-left'],
             'value' =>'contragent.contragent',
             'filter' =>$arrContr,
			 'footer' => 'Всего:',
             ],
            ['attribute' =>'broker',
			'headerOptions'=>['class'=>'text-center'],
			'contentOptions' => ['style' => 'width:60px;  min-width:60px;  ','class'=>'text-right'],
			'footer' => number_format($sumBroker,2,'.',''),
			],
            ['attribute' =>'dosmotr',
			'headerOptions'=>['class'=>'text-center'],
			'contentOptions' => ['style' => 'width:60px;  min-width:60px;  ','class'=>'text-right'],
			'footer' => number_format($sumDosmotr,2,'.',''),
			],
            ['attribute' =>'custom',
			'headerOptions'=>['class'=>'text-center'],
			'contentOptions' => ['style' => 'width:60px;  min-width:60px;  ','class'=>'text-right'],
			'footer' => number_format($sumCustom,2,'.',''),
			],
            ['attribute' =>'fito',
			'headerOptions'=>['class'=>'text-center'],
			'contentOptions' => ['style' => 'width:60px;  min-width:60px;  ','class'=>'text-right'],
			'footer' => number_format($sumFito,2,'.',''),
			],

            ['class' => 'yii\grid\ActionColumn',
		 	'template' => '{view} {update} ',
			 'visibleButtons'=>[
 
			'update'=>  !Yii::$app->user->can('runo'),
			 ],
			],
        ],
    ]); ?>



</div>
