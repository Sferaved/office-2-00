<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\HomestatyaModel;

/* @var $this yii\web\View */
/* @var $model app\models\Homezatraty */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Затраты по дому'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="homezatraty-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Исправить'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Удалить'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Вы точно хотите удалить эту запись?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <?php $model_statya = HomestatyaModel::find()-> where (['=','id', $model->statya]) ->all();

    foreach ($model_statya as $value) (
        $statya = $value->statya
    ); ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            ['attribute' => 'date',
            'format' => ['date', 'dd.MM.yyyy']
            ],
            ['attribute' =>'statya',
             'value' =>$statya,
            ],
            'cost',
            'comment',
        ],
    ]) ?>

</div>
