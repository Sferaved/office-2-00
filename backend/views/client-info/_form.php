<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Client;

/* @var $this yii\web\View */
/* @var $model backend\models\ClientInfo */
/* @var $form yii\widgets\ActiveForm */
?>

<?php $arrCL = Client::find()->asArray()->all();?>


<div class="client-info-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'client_id')->textInput()->dropDownList(
                ArrayHelper::map($arrCL, 'id','client'),
                ['prompt' => 'Выберите клиента...']);  ?>

    <?= $form->field($model, 'telephon')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'adress')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'director')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
