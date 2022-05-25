<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\models\Invoice */

$this->title = Yii::t('app', 'Добавить затраты');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Работа'), 'url' => ['declaration/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="zatraty-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form_zatraty', [
        'model' => $model,
    ]) ?>

</div>
