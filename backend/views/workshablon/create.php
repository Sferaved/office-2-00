<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Workshablon */

$this->title = Yii::t('app', 'Добавить');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Шаблоны'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="workshablon-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
