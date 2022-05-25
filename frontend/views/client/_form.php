<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use dosamigos\datepicker\DatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\Client */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="client-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'cod_EGRPOU')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'client')->textarea(['rows' => 1]) ?>

    <?= $form->field($model, 'dogovor')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'date_begin')->textInput()->widget(
    DatePicker::class, 
    [
        'model' => $model,
                'value' => $model->date_begin,
                'attribute' => 'date_begin',
                'language' => 'ru',
                
                'options' => ['placeholder' => 'Выбрать дату'],
                'template' => '{addon}{input}',
                'clientOptions' => [
                    'autoclose' => true,
                    'todayHighlight' => true,
                    'format' => 'yyyy-mm-dd'
    ]
    ]); 
    ?>
	
	<?php     if (Yii::$app->user->can('admin')){ ?>
	

    <?= $form->field($model, 'date_finish')->textInput()->widget(
    DatePicker::class, 
    [
        'model' => $model,
                'value' => $model->date_finish,
                'attribute' => 'date_finish',
                'language' => 'ru',
                
                'options' => ['placeholder' => 'Выбрать дату'],
                'template' => '{addon}{input}',
                'clientOptions' => [
                    'autoclose' => true,
                    'todayHighlight' => true,
                    'format' => 'yyyy-mm-dd'
    ]
    ]);
	} 
	
	else { ?>
	
    <?= $form->field($model, 'date_finish')->textInput(['disabled' => 'true'])?>
	<?php
	}   
	?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
