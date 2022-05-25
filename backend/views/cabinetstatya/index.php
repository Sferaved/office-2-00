<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\CabinetstatyaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Комментарии в кабинете брокера');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cabinetstatya-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Добавить'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
   //         ['class' => 'yii\grid\SerialColumn'],

            'id',
            'statya',
            'income',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
