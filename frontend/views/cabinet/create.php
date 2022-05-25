<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\models\Cabinet */

$this->title = Yii::t('app', 'Записать движение денег');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Кабинет'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cabinet-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
