<?php

use yii\helpers\Html;
use yii\grid\GridView;
use dosamigos\datepicker\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel app\models\HomezatratySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Затраты по дому');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="homezatraty-index">

    <h1><?= Html::encode($this->title) ?></h1>

  

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php echo 'Баланс: <b>',Yii::$app->formatter->asCurrency($cost_all_after_day),'</b>'; ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
		'showFooter' => true,
		'footerRowOptions'=>['style'=>'font-weight:bold;text-align:right'],
        'filterModel' => $searchModel,
        'columns' => [
      //      ['class' => 'yii\grid\SerialColumn'],

        /*     ['attribute' => 'id',
            'headerOptions'=>['class'=>'text-center'],
            'contentOptions' => ['style' => 'width:100px;  min-width:50px;  ', 'class'=>'text-right'],
      //      'filter' =>$arrIdHome,
            ], */
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
             ] 
              ]),
            ],
            ['attribute' =>'statya',
            'headerOptions'=>['class'=>'text-center'],
             'contentOptions' => ['style' => 'width:200px;  min-width:200px;  ','class'=>'text-center'],
             'value' =>'statya0.statya',
            'filter' =>$arrHomestatya,
			 'footer' => 'Всего:',
             ],
             ['attribute' =>'cost',
             'headerOptions'=>['class'=>'text-center'],
             'contentOptions' => ['style' => 'width:100px;  min-width:100px;  ','class'=>'text-right'],
             'footer' => number_format($sumCost,2,'.',''),
             ],
             ['attribute' =>'comment',
             'headerOptions'=>['class'=>'text-center'],
             ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
