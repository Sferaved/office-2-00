<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Contragent;

/* @var $this yii\web\View */
/* @var $model backend\models\AqFlCost */
/* @var $form yii\widgets\ActiveForm */
?>
<?php $arrCtr = Contragent::find()->asArray()->all();?>

<div class="aq-fl-cost-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'contragent_id')->textInput()->dropDownList(
                ArrayHelper::map($arrCtr, 'id','contragent'),
                ['prompt' => 'Выберите клиента...']); ?>

    <?= $form->field($model, 'cost')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
