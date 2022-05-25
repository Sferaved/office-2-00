<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use dosamigos\datepicker\DateRangePicker;
use backend\models\Workzatraty;

/* @var $this yii\web\View */
/* @var $model backend\models\WorkzatratySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="workzatraty-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <?= $form->field($model, 'date_from') ->widget(DateRangePicker::className(), [
     'options' => ['placeholder' => 'Выбрать дату'],
     'attributeTo' => 'date_to', 
 //   'form' => $form, // best for correct client validation
    'language' => 'ru',
    'size' => 'ms',
    'clientOptions' => [
        'autoclose' => true,
        'format' => 'yyyy-mm-dd',
		'todayHighlight'=>true,
	 	'clearBtn'=>true,
	]
    ]);?>
 

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Найти'), ['class' => 'btn btn-primary']) ?> 
		<?= Html::a(Yii::t('app', 'Добавить'), ['create'], ['class' => 'btn btn-success']) ?>
		<?php	
		if (isset($model->date_from)) {
			if (Workzatraty::find()->where(['between', 'date', $model->date_from, $model->date_to])->all() != null) {  
			
			// Формирование отчета
	   ?>
         <?= Html::a(Yii::t('app', 'Отчет'), ['export','date_from'=>$model->date_from,'date_to'=>$model->date_to], ['class' => 'btn btn-danger']) ?>
		<?php
		
			};
		};	
		?>

    </div>

    <?php ActiveForm::end(); ?>

</div>
