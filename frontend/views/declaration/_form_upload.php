<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use dosamigos\datepicker\DatePicker;

use common\models\Client;
use frontend\models\User;
use frontend\models\AuthAssignment;
use frontend\models\Invoice;

/* @var $this yii\web\View */
/* @var $model app\models\Declaration */
/* @var $form yii\widgets\ActiveForm */
?>

  
<div class="declaration-form">


 <?php $form = ActiveForm::begin([
    'id' => 'form-input-homezatraty',
    'options' => [
        'class' => 'form-horizontal col-lg-3',
        'enctype' => 'multipart/form-data'
        ],
    ]); ?>


	
    <?= $form->field($model, 'file')->fileInput()->label (''); ?>
	
  
	
	

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
