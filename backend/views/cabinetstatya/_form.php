<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Cabinetstatya */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="cabinetstatya-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'statya')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'income')->textInput(['maxlength' => true])->dropDownList([
		'Нет' => 'Нет',
		'Да' => 'Да',
	]); ?>

   
    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
