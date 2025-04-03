<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\Contragent;

/* @var $this yii\web\View */
/* @var $model backend\models\AqFlCost */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Доплата Аква-ФЛЄКСС', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="aq-fl-cost-view">

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
	<?php $model_contragent = Contragent::find()-> where (['=','id', $model->contragent_id]) ->all();
	    foreach ($model_contragent as $value) (
        $contragent = $value->contragent
    ); 
	?>
	
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            ['attribute' =>'contragent_id', 
			'value' =>$contragent,
            ],
            'cost',
        ],
    ]) ?>

</div>
