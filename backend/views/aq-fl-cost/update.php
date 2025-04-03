<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\AqFlCost */

$this->title = 'Исправить запись №: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Доплата Аква-ФЛЄКСС', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="aq-fl-cost-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
