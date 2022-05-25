<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use dosamigos\datepicker\DatePicker;

use frontend\models\Declaration;
use common\models\Contragent;

/* @var $this yii\web\View */
/* @var $model frontend\models\Aquaizol */
/* @var $form yii\widgets\ActiveForm */
?>

<?php $arrDE = Declaration::find()->asArray()->orderBy( 'date DESC' )->where(['=','client_id','3'])->all(); ?>
<?php $arrCtr = Contragent::find()->asArray()->all();?>

<div class="aquaizol-form">

    <?php $form = ActiveForm::begin(); ?>

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
    ]); ?>

    <?= $form->field($model, 'ex_im')->textInput(['maxlength' => true])->dropDownList([
		'Экспорт' => 'Экспорт',
		'Импорт' => 'Импорт',
	]); ?>

    <?= $form->field($model, 'decl_number_id')->textInput()->dropDownList(
                ArrayHelper::map($arrDE, 'id','decl_number'),
                ['prompt' => 'Выберите декларацию...']); ?>

    <?= $form->field($model, 'contragent_id')->textInput()->dropDownList(
                ArrayHelper::map($arrCtr, 'id','contragent'),
                ['prompt' => 'Выберите клиента...']); ?>

    <?= $form->field($model, 'broker')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'dosmotr')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'custom')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'fito')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
