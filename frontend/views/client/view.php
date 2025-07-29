<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Client */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Клиенты'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="client-view">

    <h1><?= Html::encode($this->title) ?></h1>
	
    <p>
        <?= Html::a(Yii::t('app', 'Исправить'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Удалить'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Вы уверены, что хотите удалить этот элемент?'),
                'method' => 'post',
            ],
        ]) ?>
		<?php	
		// Формирование договора
	    $dogovor= dogovor_doc ($model->id);

     	?>

		<?= Html::a(Yii::t('app', 'Договор'), ['export','file'=>$dogovor], ['class' => 'btn btn-success']) ?>

		<?php
		// Формирование продления договора
		$dogovor_long= dogovor_long_doc ( $model->id);
        $dogovor_mp= dogovor_mp_doc ( $model->id);
		?>
		<?= Html::a(Yii::t('app', 'Продление'), ['export','file'=>$dogovor_long], ['class' => 'btn btn-primary']) ?>
		<?= Html::a(Yii::t('app', 'Авторизация'), ['export','file'=>$dogovor_mp], ['class' => 'btn btn-success']) ?>

	
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'cod_EGRPOU',
            'client:ntext',
            'dogovor',
            ['attribute' => 'date_begin',
            'format' => ['date', 'dd.MM.yyyy']
			 ],
            ['attribute' => 'date_finish',
            'format' => ['date', 'dd.MM.yyyy']
			 ],
        ],
		'template' => "<tr><th style='width: 15%;'>{label}</th><td>{value}</td></tr>"
    ]) ?>

</div>
