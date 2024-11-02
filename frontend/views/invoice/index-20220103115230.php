<?php

use yii\helpers\Html;
use yii\grid\GridView;
use dosamigos\datepicker\DatePicker;


/* @var $this yii\web\View */
/* @var $searchModel app\models\InvoiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Счета');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php //Очистка папки 
$files = glob('files/*'); // get all file names
 foreach($files as $file){ // iterate files
  if(is_file($file))
    unlink($file); // delete file
 }  
?> 

<div class="invoice-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Добавить'), ['create', 'mod' => null], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
		'showFooter' => true,
        'filterModel' => $searchModel,
		'footerRowOptions'=>['style'=>'font-weight:bold;text-align:right'],
        'columns' => [
        //    ['class' => 'yii\grid\SerialColumn'],

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
             'contentOptions' => ['style' => 'width:180px;  min-width:180px;  ','class'=>'text-left'],
             'value' =>'declaration0.decl_number',
            'filter' =>$arrDecl,
             ],

			['attribute' =>'client_id',
             'headerOptions'=>['class'=>'text-center'],
            'contentOptions' => ['style' => 'width:300px;  min-width:300px;  ','class'=>'left'],
             'value' =>'client0.client',
             'filter' =>$arrClient,
			 'footer' => 'Всего:',
             ],
            ['attribute' =>'cost',
			'headerOptions'=>['class'=>'text-center'],
			'contentOptions' => ['style' => 'width:100px;  min-width:100px;  ','class'=>'text-right'],
			'footer' => number_format($sumInvoice,2,'.',''),
			],

			['attribute' =>'user_id',
             'headerOptions'=>['class'=>'text-center'],
             'contentOptions' => ['style' => 'width:100px;  min-width:100px;  ','class'=>'text-left'],
             'value' =>'user0.username',
             'filter' =>$arrUser,
	 		 'visible' => Yii::$app->user->can('admin') || Yii::$app->user->can('buh') ,
             ],
            ['attribute' =>'oplata',
			 'contentOptions' => ['style' => 'width:50px;  min-width:50px;  ','class'=>'text-center'],
			 'filter' =>['Нет'=>'Нет','Да'=> 'Да']
			 ],
             ['attribute' =>'forma_oplat', 'contentOptions' => ['style' => 'width:150px;  min-width:150px;  ','class'=>'text-center'],
	 		 'contentOptions' => ['style' => 'width:50px;  min-width:50px;  ','class'=>'text-center'],
			 'filter' =>['Безнал'=>'Безнал','Карта'=> 'Карта']
			 ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
