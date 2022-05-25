<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use dosamigos\datepicker\DateRangePicker;
use frontend\models\Declaration;

/* @var $this yii\web\View */
/* @var $model app\models\DeclarationSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="declaration-search">

  
	    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'date_from') ->widget(DateRangePicker::className(), [
     'options' => ['placeholder' => 'Выбрать дату'],
     'attributeTo' => 'date_to', 
 //   'form' => $form, // best for correct client validation
    'language' => 'ru',
    'size' => 'ms',
    'clientOptions' => [
        'autoclose' => true,
        'format' => 'yyyy-mm-dd',
		'todayHighlight'=>true,
	 	'clearBtn'=>true,
	]
    ]);?>


    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Найти'), ['class' => 'btn btn-primary']) ?>
   
	<?php     
	
	
	if (Yii::$app->user->can('admin')|| Yii::$app->user->can('buh')|| Yii::$app->user->can('runo')){ ?>
       <?php	
		if (isset($model->date_from)) {
		
		$arrDecl = Declaration::find()->asArray()->where(['between', 'date', $model->date_from, $model->date_to])->all(); 
			if ($arrDecl!= null) {  
		
			?>
			
			<?= Html::a(Yii::t('app', 'Отчет'), ['report','date_from'=>$model->date_from,'date_to'=>$model->date_to], ['class' => 'btn btn-danger']) ?>
			<?php	
		
			};
		};	
	};
		?>	


   </div>

    <?php ActiveForm::end(); ?>
	

</div>
