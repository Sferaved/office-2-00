<?php

use yii\helpers\Html;
use yii\grid\GridView;
use dosamigos\datepicker\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel app\models\DeclarationSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Выполненная работа');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php //Очистка папки 
$files = glob('files/*'); // get all file names
 foreach($files as $file){ // iterate files
  if(is_file($file))
    unlink($file); // delete file
 }  
?> 


<div class="declaration-index">

    <h1><?= Html::encode($this->title) ?></h1>
	<?php 
	if (Yii::$app->user->can('admin') || Yii::$app->user->can('buh')){ 	
	echo $this->render('_search', ['model' => $searchModel]);
	}?>
    <p>
        <?= Html::a(Yii::t('app', 'Добавить запись'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Добавить декларацию'), ['upload'], ['class' => 'btn btn-success']) ?>
    </p>
    
   

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
        //    ['class' => 'yii\grid\SerialColumn'],

           /* ['attribute' => 'id',
            'headerOptions'=>['class'=>'text-center'],
            'contentOptions' => ['style' => 'width:100px;  min-width:50px;  ', 'class'=>'text-right'],
            'filter' =>$arrIdDecl,
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
             ],
			  ]),
            ],
            ['attribute' =>'decl_number',
			 'headerOptions'=>['class'=>'text-center'],
			],
			['attribute' =>'client_id',
             'headerOptions'=>['class'=>'text-center'],
             'contentOptions' => ['style' => 'width:200px;  min-width:200px;  ','class'=>'text-left'],
             'value' =>'client0.client',
             'filter' =>$arrClient,
             ],
   			['attribute' =>'user_id',
             'headerOptions'=>['class'=>'text-center'],
             'contentOptions' => ['style' => 'width:200px;  min-width:200px;  ','class'=>'text-left'],
             'value' =>'user0.username',
             'filter' =>$arrUser,
			 'visible' => Yii::$app->user->can('admin') || Yii::$app->user->can('buh') ,
             ],
 

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
