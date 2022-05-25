<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use dosamigos\datepicker\DatePicker;
use common\models\HomestatyaModel;

/* @var $this yii\web\View */
/* @var $model app\models\Homezatraty */
/* @var $form yii\widgets\ActiveForm */
?>
<?php $arrHS = HomestatyaModel::find()->asArray()->all();?>

<div class="homezatraty-form">

    <?php $form = ActiveForm::begin([
    'id' => 'form-input-homezatraty',
    'options' => [
        'class' => 'form-horizontal col-lg-3',
        'enctype' => 'multipart/form-data'
        ],
    ]); ?>

    <?= $form->field($model, 'date')->widget(
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
    ]);?>

    <?= $form->field($model, 'statya')->dropDownList(
                ArrayHelper::map($arrHS, 'id','statya'),
                ['prompt' => 'Выберите статью...']);  ?>

    <?= $form->field($model, 'cost')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'comment')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
