<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use frontend\models\User;
use frontend\models\Cabinetstatya;
use frontend\models\Declaration;
use common\models\Client;

/* @var $this yii\web\View */
/* @var $model frontend\models\Cabinet */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Кабинет'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="cabinet-view">

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
		 <?= Html::a(Yii::t('app', 'Скачать'), ['file','id' => $model->decl_id], ['class' => 'btn btn-success']) ?>
    </p>

    <?php $model_declaration = Declaration::find()-> where (['=','id', $model->decl_id]) ->all();
	    foreach ($model_declaration as $value) (
        $declaration = $value->decl_number
    ); 
	?>

    <?php $model_cabinetstatya = Cabinetstatya::find()-> where (['=','id', $model->coment_id]) ->all();
	    foreach ($model_cabinetstatya as $value) (
        $cabinetstatya = $value->statya
    ); 
	?>
	
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
            ['attribute' =>'decl_id',
			'value' =>$declaration,
			],
			['attribute' =>'client_id',
             'value' =>$client,
            ],
            'cost',
            ['attribute' =>'coment_id',
			'value' =>$cabinetstatya,
			],
            ['attribute' =>'user_id',
     //       'headerOptions'=>['class'=>'text-center'],
    //         'contentOptions' => ['style' => 'width:200px;  min-width:200px;  ','class'=>'text-center'],
             'value' =>$user,
	//		 'visible' => Yii::$app->user->can('admin') || Yii::$app->user->can('buh') ,
             ],
        ],
    ]) ?>

</div>
