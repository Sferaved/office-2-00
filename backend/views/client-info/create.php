<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\ClientInfo */

$this->title = 'Добавить';
$this->params['breadcrumbs'][] = ['label' => 'Реквизиты для акта', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="client-info-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
