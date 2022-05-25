<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use dosamigos\datepicker\DatePicker;

use frontend\models\Declaration;
use frontend\models\User;
use common\models\Cabinetstatya;
use frontend\models\AuthAssignment;

/* @var $this yii\web\View */
/* @var $model frontend\models\Cabinet */
/* @var $form yii\widgets\ActiveForm */
?>

<?php    $arrDecl = Declaration::find()->asArray()
									   ->where(['date'=>date('Y-m-d'),'user_id'=>Yii::$app->user->id])
                                       ->orderBy(['id'=>SORT_DESC])->all();
  //Получили все декларации активного пользователя

?>


<?php    $arrUsers = AuthAssignment::find()->where(['item_name'=>'user'])->all();
foreach ($arrUsers as $value) (            //Получили отобранные id=User
           $arrIdUser[] = $value->user_id
		);
?>

<?php $arrUS = User::find()->asArray()->where(['id'=>$arrIdUser])->all();?>

<?php    $arrCom =Cabinetstatya::find()->asArray()->all();
  //Получили все варианты коментариев для отчета брокера

?>





<div class="cabinet-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'cost')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'coment_id')->textInput()->dropDownList(
                ArrayHelper::map($arrCom, 'id','statya'),
                ['prompt' => 'Сделайте выбор...']); ?>

    <?php     if (Yii::$app->user->can('admin')){ ?>
 
 
	<?=	  $form->field($model, 'user_id')->textInput()->dropDownList(
                ArrayHelper::map($arrUS, 'id','username'),
                ['prompt' => 'Выберите кто добавляет...']); 
    }?> 
   

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Сохранить'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
