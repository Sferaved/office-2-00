<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

use common\models\Client;
use common\models\Workstatya;

/* @var $this yii\web\View */
/* @var $model common\models\Workshablon */
/* @var $form yii\widgets\ActiveForm */
?>

<?php $arrCL = Client::find()->asArray()->all();?>
<?php $arrST = Workstatya::find()->asArray()->all();?>

<div class="workshablon-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'client_id')->textInput()->dropDownList(
                ArrayHelper::map($arrCL, 'id','client'),
                ['prompt' => 'Выберите клиента...']); ?>

    <?= $form->field($model, 'cost')->textInput(['maxlength' => true])?>

    <?= $form->field($model, 'statya_id')->textInput()->dropDownList(
                ArrayHelper::map($arrST, 'id','statya'),
                ['prompt' => 'Выберите статью...']);  ?>

    <?= $form->field($model, 'ex_im')->textInput(['maxlength' => true])->dropDownList([
		'Экспорт' => 'Экспорт',
		'Импорт' => 'Импорт',
	]); ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
