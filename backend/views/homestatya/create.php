<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\HomestatyaModel */

$this->title = Yii::t('app', 'Добавить статью');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Статьи дом'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="homestatya-model-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
