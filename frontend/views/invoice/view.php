<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use frontend\models\Declaration;
use common\models\Client;
use frontend\models\User;

/* @var $this yii\web\View */
/* @var $model frontend\models\Invoice */
                
/* Html::a(Yii::t('app', 'Скачать'), ['invoice','id' => $model->id], ['class' => 'btn btn-success'])  */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Счета'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="invoice-view">

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
		
   
    <?php $model_decl = Declaration::find()-> where (['=','id', $model->decl_id]) ->all();
	    foreach ($model_decl as $value) (
        $decl_number = $value->decl_number
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
	
<?php	
// Формирование счета
$invoice= invoice_doc ( $model->id,$model->date, $decl_number, $client, $model->cost);
// Формирование акта
$act= act_doc ( $model->id,$model->date, $decl_number, $client, $model->cost);
?>

<?= Html::a(Yii::t('app', 'Счет'), ['export','file'=>$invoice], ['class' => 'btn btn-success']) ?>

<?php
$invoice = $invoice.'_signature';
?>
<?= Html::a(Yii::t('app', 'Счет c подписью'), ['export','file'=>$invoice], ['class' => 'btn btn-primary']) ?>


<?= Html::a(Yii::t('app', 'Акт'), ['export','file'=>$act], ['class' => 'btn btn-success']) ?>				
	 </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            ['attribute' => 'date',
            'format' => ['date', 'dd.MM.yyyy']
            ],
			['attribute' =>'decl_id',
             'value' =>$decl_number,
            ],
      		['attribute' =>'client_id',
             'value' =>$client,
            ],
            'cost',
            ['attribute' =>'user_id',
			 'value' =>$user,
            ],
            'oplata',
            'forma_oplat',
        ],
		'template' => "<tr><th style='width: 15%;'>{label}</th><td>{value}</td></tr>"
    ]) ?>



	

</div>
