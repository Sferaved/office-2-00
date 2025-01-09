<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\Client;
use frontend\models\User;

/* @var $this yii\web\View */
/* @var $model app\models\Declaration */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Выполненная работа'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="declaration-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Исправить'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Удалить'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Вы уверены, что хотите удалить этот элемент?'),
                'method' => 'post',
            ],
        ]) ?>
		  <?= Html::a(Yii::t('app', 'Cчет'), ['invoice','id' => $model->id], ['class' => 'btn btn-success']) ?>
		  <?= Html::a(Yii::t('app', 'Расходы'), ['cabinet', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
		  
		  <?= Html::a(Yii::t('app', 'Скачать'), ['file','id' => $model->id], ['class' => 'btn btn-success']) ?>
		  
    
	
	<?php     
	if (Yii::$app->user->can('admin')){ ?>
       
			
			<?= Html::a(Yii::t('app', 'Калькуляция'), ['export','id' => $model->id], ['class' => 'btn btn-danger']) ?>
			<?= Html::a(Yii::t('app', 'Затраты'), ['zatraty', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
			<?= Html::a(Yii::t('app', 'Парсинг'), ['parsing', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
			<?= Html::a(Yii::t('app', 'Парсинг в базу данных'), ['parse-declarations'], ['class' => 'btn btn-primary']) ?>
			<?= Html::a(Yii::t('app', 'Парсинг в базу данных 10'), ['parse-first-ten-declarations'], ['class' => 'btn btn-primary']) ?>
	<?php
	};
	?>	
	
	</p>
	
	<?php $model_client = Client::find()-> where (['=','id', $model->client_id]) ->all();
	    foreach ($model_client as $value) (
        $client = $value->client
    ); 
	?>
	
	<?php $model_user = User::find()-> where (['=','id', $model->user_id]) ->all();
	    foreach ($model_user as $value) (
        $user = $value->username
    ); 
	?>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            ['attribute' => 'date',
            'format' => ['date', 'dd.MM.yyyy']
            ],
            'decl_number',
            ['attribute' =>'client_id',
             'value' =>$client,
            ],
            ['attribute' =>'user_id',
			 'value' =>$user,
            ],
         //  'decl_iso',
        ],
		'template' => "<tr><th style='width: 15%;'>{label}</th><td>{value}</td></tr>"
    ]) ?>

</div>
