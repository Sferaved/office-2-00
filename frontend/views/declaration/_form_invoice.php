<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use dosamigos\datepicker\DatePicker;

use frontend\models\Declaration;
use common\models\Client;
use frontend\models\User;

/* @var $this yii\web\View */
/* @var $model frontend\models\Invoice */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="invoice-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'cost')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'forma_oplat')->dropDownList([
        'Безнал' => 'Безнал',
        'Карта' => 'Карта',
    ], ['prompt' => 'Выберите форму оплаты']) ?>

    <?= $form->field($model, 'decl_id')->dropDownList(
        ArrayHelper::map(
            Declaration::find()->select(['id', 'decl_number'])->asArray()->all(),
            'id',
            'decl_number'
        ),
        ['prompt' => 'Выберите декларацию']
    ) ?>

    <?= $form->field($model, 'client_id')->dropDownList(
        ArrayHelper::map(
            Client::find()->select(['id', 'client'])->asArray()->all(),
            'id',
            'client'
        ),
        ['prompt' => 'Выберите клиента']
    ) ?>

    <?= $form->field($model, 'user_id')->dropDownList(
        ArrayHelper::map(
            User::find()->select(['id', 'username'])->asArray()->all(),
            'id',
            'username'
        ),
        ['prompt' => 'Выберите пользователя']
    ) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

