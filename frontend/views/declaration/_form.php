<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use dosamigos\datepicker\DatePicker;

use common\models\Client;
use frontend\models\User;
use frontend\models\AuthAssignment;


/* @var $this yii\web\View */
/* @var $model app\models\Declaration */
/* @var $form yii\widgets\ActiveForm */
?>

<?php $arrCL = Client::find()->asArray()->all();?>

<?php    $arrUsers = AuthAssignment::find()->where(['item_name'=>'user'])->all();
foreach ($arrUsers as $value) (            //Получили отобранные id=User
           $arrIdUser[] = $value->user_id
		); 
?>    
 
<?php $arrUS = User::find()->asArray()->where(['id'=>$arrIdUser])->all();?> 
  
  
<div class="declaration-form">

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
    ]); ?>

    <?= $form->field($model, 'decl_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'client_id')->textInput()->dropDownList(
                ArrayHelper::map($arrCL, 'id','client'),
                ['prompt' => 'Выберите клиента...']); ?>

  <?php 
  if (Yii::$app->user->id == 1 || Yii::$app->user->id == 2) { ?>
	<?=  $form->field($model, 'user_id')->textInput()->dropDownList(
                ArrayHelper::map($arrUS, 'id','username'),
				['prompt' => 'Выберите кто добавляет...']); ?>
   <?php  } ?>
	
    <?php /* $form->field($model, 'decl_iso')->textInput() */?>


    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
