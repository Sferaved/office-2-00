<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\AqFlCost */

$this->title = 'Добавить';
$this->params['breadcrumbs'][] = ['label' => 'Доплата', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="aq-fl-cost-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
