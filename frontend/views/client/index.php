<?php

use yii\helpers\Html;
use yii\grid\GridView;
use dosamigos\datepicker\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ClientSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Клиенты');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php //Очистка папки 
$files = glob('files/*'); // get all file names
 foreach($files as $file){ // iterate files
  if(is_file($file))
    unlink($file); // delete file
 }  
?> 



<div class="client-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Добавить'), ['create'], ['class' => 'btn btn-success']) ?>
		<?= Html::a(Yii::t('app', 'Проверить'), ['file'], ['class' => 'btn btn-primary']) ?>		
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
    

            'id',
            'cod_EGRPOU',
            'client:ntext',
            'dogovor',
    
			['attribute' => 'date_begin',
            'headerOptions'=>['class'=>'text-center'],
            'format' => ['date', 'dd.MM.yyyy'],
            'contentOptions' => ['style' => 'width:180px;  min-width:180px;  ','class'=>'text-center'],
            'filter' =>DatePicker::widget([
                'model' => $searchModel,
                'value' => $searchModel->date_begin,
                'attribute' => 'date_begin',
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
			['attribute' => 'date_finish',
            'headerOptions'=>['class'=>'text-center'],
            'format' => ['date', 'dd.MM.yyyy'],
            'contentOptions' => ['style' => 'width:180px;  min-width:180px;  ','class'=>'text-center'],
            'filter' =>DatePicker::widget([
                'model' => $searchModel,
                'value' => $searchModel->date_finish,
                'attribute' => 'date_finish',
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

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
