<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

use common\models\Client;
use common\models\Workstatya;


/* @var $this yii\web\View */
/* @var $model common\models\Workshablon */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Шаблоны'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="workshablon-view">

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
    </p>

	<?php $model_client = Client::find()-> where (['=','id', $model->client_id]) ->all();
	    foreach ($model_client as $value) (
        $client = $value->client
    ); 
	?>
	
	<?php $model_statya = Workstatya::find()-> where (['=','id', $model->statya_id]) ->all();
	    foreach ($model_statya as $value) (
        $statya = $value->statya
    ); 
	?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            ['attribute' =>'client_id',
             'value' =>$client,
            ],
            'cost',
            ['attribute' =>'statya_id',
             'value' =>$statya,
            ],
            'ex_im',
        ],
    ]) ?>

</div>
