<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Homezatraty */

$this->title = Yii::t('app', 'Сделать новую запись');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Затраты по дому'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="homezatraty-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
