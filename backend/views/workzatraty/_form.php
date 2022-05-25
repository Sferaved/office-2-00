<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use dosamigos\datepicker\DatePicker;

use frontend\models\Declaration;
use common\models\Client;
use frontend\models\User;
use common\models\Workstatya;

/* @var $this yii\web\View */
/* @var $model backend\models\Workzatraty */
/* @var $form yii\widgets\ActiveForm */
?>


<?php $arrDE = Declaration::find()->asArray()->orderBy( 'date DESC' )->where(['!=','decl_number','Операции за день'])->all(); ?>
<?php $arrCL = Client::find()->asArray()->all();?>
<?php $arrSt = Workstatya::find()->asArray()->all();?>

<div class="workzatraty-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'date')->textInput()->widget(
    DatePicker::class, 
    [
        'model' => $model,
                'value' => $model->date,
                'attribute' => 'date',
                'language' => 'ru',
                
                'options' => ['placeholder' => 'Выбрать дату'],
                'template' => '{addon}{input}',
                'clientOptions' => [
                    'autoclose' => true,
                    'todayHighlight' => true,
                    'format' => 'yyyy-mm-dd'
    ]
    ]); ?>

    <?= $form->field($model, 'decl_id')->textInput()->dropDownList(
                ArrayHelper::map($arrDE, 'id','decl_number'),
                ['prompt' => 'Выберите декларацию...']);  ?>

    <?= $form->field($model, 'client_id')->textInput()->dropDownList(
                ArrayHelper::map($arrCL, 'id','client'),
                ['prompt' => 'Выберите клиента...']);  ?>

    <?= $form->field($model, 'cost')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'workstatya_id')->textInput()->dropDownList(
                ArrayHelper::map($arrSt, 'id','statya'),
                ['prompt' => 'Выберите статью...']);   ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
