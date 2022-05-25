<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Homezatraty */

$this->title = Yii::t('app', 'Исправить запись №: {name}', [
    'name' => $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Затраты по дому'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view',  'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Исправить');
?>
<div class="homezatraty-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
