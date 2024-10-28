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

<?php //
//if (Yii::$app->user->id !=1 && Yii::$app->user->id !=2) {
//	$arrDE = Declaration::find()->asArray()->orderBy( 'date DESC' )->where(['=','user_id',Yii::$app->user->id])->all();
//}
//else {
//	$arrDE = Declaration::find()->asArray()->orderBy( 'date DESC' )->all();
//}
//
//?>
<?php //$arrCL = Client::find()->asArray()->all();?>
<?php //$arrUS = User::find()->asArray()->all();?>


<div class="invoice-form">

    <?php $form = ActiveForm::begin(); ?>

<!--    --><?//= $form->field($model, 'date')->textInput()->widget(
//    DatePicker::class,
//    [
//        'model' => $model,
//                'value' => $model->date,
//                'attribute' => 'date',
//                'language' => 'ru',
//
//                'options' => ['placeholder' => 'Выбрать дату'],
//                'template' => '{addon}{input}',
//                'clientOptions' => [
//                    'autoclose' => true,
//                    'todayHighlight' => true,
//                    'format' => 'yyyy-mm-dd'
//    ]
//    ]); ?>

<!--    --><?//= $form->field($model, 'decl_id')->textInput()->dropDownList(
//                ArrayHelper::map($arrDE, 'id','decl_number'),
//                ['prompt' => 'Выберите декларацию...']); ?>
<!---->
<!--    --><?//= $form->field($model, 'client_id')->textInput()->dropDownList(
//                ArrayHelper::map($arrCL, 'id','client'),
//                ['prompt' => 'Выберите клиента...']); ?>
<!---->
<!--    --><?//= $form->field($model, 'cost')->textInput(['maxlength' => true]) ?>
<!---->
<!--    --><?//= $form->field($model, 'user_id')->textInput()->dropDownList(
//                ArrayHelper::map($arrUS, 'id','username'),
//                ['prompt' => 'Выберите кто выставляет счет...']); ?>

    <?php if (Yii::$app->user->can('buh') || Yii::$app->user->can('admin')) { ?>
	<?=     $form->field($model, 'oplata')->textInput(['maxlength' => true])->dropDownList([
		'Нет' => 'Нет',
		'Да' => 'Да',
	]);?> 
	<?php
	};
	?>

<!--    --><?//= $form->field($model, 'forma_oplat')->textInput(['maxlength' => true])->dropDownList([
//		'Безнал' => 'Безнал',
//		'Карта' => 'Карта',
//	]);  ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
