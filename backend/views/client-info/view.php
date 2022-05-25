<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\Client;


/* @var $this yii\web\View */
/* @var $model backend\models\ClientInfo */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Реквизиты для акта', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

	<?php $model_client = Client::find()-> where (['=','id', $model->client_id]) ->all();
	    foreach ($model_client as $value) (
        $client = $value->client
    ); 
	?>

<div class="client-info-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Исправить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите удалить этот элемент?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            ['attribute' =>'client_id',
             'value' =>$client,
            ],
            'telephon',
            'adress:ntext',
            'director',
        ],
    ]) ?>

</div>
