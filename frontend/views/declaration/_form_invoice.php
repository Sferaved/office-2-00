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

<?php $arrDE = Declaration::find()->asArray()->all();?>
<?php $arrCL = Client::find()->asArray()->all();?>
<?php $arrUS = User::find()->asArray()->all();?>


<div class="invoice-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'cost')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'forma_oplat')->textInput(['maxlength' => true])->dropDownList([
		'Безнал' => 'Безнал',
		'Карта' => 'Карта',
	]);  ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
