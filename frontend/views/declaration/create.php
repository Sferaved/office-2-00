<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Declaration */

$this->title = Yii::t('app', 'Записать информацию в базу');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Выполненная работа'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="declaration-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
