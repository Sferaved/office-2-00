<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\models\Flex */

$this->title = Yii::t('app', 'Сделать новую запись');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Флекс'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="flex-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
