<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use dosamigos\datepicker\DatePicker;

use frontend\models\User;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\CabinetSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Кабинет');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cabinet-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
	 <?php  /*   if (Yii::$app->user->can('user')){ ?>
        <?= Html::a(Yii::t('app', 'Добавить'), ['create'], ['class' => 'btn btn-success']) ?>
	 <?php    } */?>	
    </p>

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
	
	<?php
    $arrCabinet_bal =cabinet_bal();
	if ($arrCabinet_bal !=null) {
	 $arrUserId = array_keys ($arrCabinet_bal); //Список ID имен брокеров у которых есть записи в кабинете
     $arrUS = User::find()->asArray()->where(['id'=>$arrUserId])->all();
	 $arrUserNames = ArrayHelper::map($arrUS, 'id', 'username');
		
	$arrKeys = array_keys ($arrUserNames);

	if (Yii::$app->user->id != '1' && Yii::$app->user->id != '2') {
		echo 'Баланс: <b>',Yii::$app->formatter->asCurrency($arrCabinet_bal[Yii::$app->user->id]),' </b>';
	}
	else {
			$i=0;
			echo 'Баланс: <br>'; 
			foreach ($arrCabinet_bal as $tabl) {
				echo $arrUserNames [$arrKeys[$i]],': <b>',Yii::$app->formatter->asCurrency($tabl),' </b>';
				$i++;
	}
	};
	}
	 

	 ?>


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
			'value' =>'decl0.decl_number',
            'filter' =>$arrDecl,
	         ],
			['attribute' =>'client_id',
             'headerOptions'=>['class'=>'text-center'],
             'contentOptions' => ['style' => 'width:200px;  min-width:200px;  ','class'=>'text-left'],
             'value' =>'client0.client',
             'filter' =>$arrClient,
			 'footer' => 'Всего:',
             ],
            ['attribute' =>'cost',
			'headerOptions'=>['class'=>'text-center'],
			'headerOptions'=>['class'=>'text-center'],
			'contentOptions' => ['style' => 'width:150px;  min-width:150px;  ','class'=>'text-right'],
			'footer' => number_format($sumCost,2,'.',''),
			],
            ['attribute' =>'coment_id',
			'headerOptions'=>['class'=>'text-center'],
			'value' =>'coment0.statya',
            'filter' =>$arrCab,
	         ],
            ['attribute' =>'user_id',
            'headerOptions'=>['class'=>'text-center'],
             'contentOptions' => ['style' => 'width:200px;  min-width:200px;  ','class'=>'text-center'],
             'value' =>'user0.username',
             'filter' =>$arrUser,
			 'visible' => Yii::$app->user->can('admin') || Yii::$app->user->can('buh') ,
             ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
