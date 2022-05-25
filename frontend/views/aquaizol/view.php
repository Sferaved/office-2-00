<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

use frontend\models\Declaration;
use common\models\Contragent;

/* @var $this yii\web\View */
/* @var $model frontend\models\Aquaizol */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Акваизол'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>


<div class="aquaizol-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Исправить'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Удалить'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Вы уверены, что хотите удалить этот элемент?'),
                'method' => 'post',
            ],
        ]) ?>
		
			  
		 <?= Html::a(Yii::t('app', 'Скачать'), ['file','id' => $model->decl_number_id], ['class' => 'btn btn-success']) ?>
		
    </p>


    <?php $model_decl = Declaration::find()-> where (['=','id', $model->decl_number_id]) ->all();
	    foreach ($model_decl as $value) (
        $decl_number = $value->decl_number
    ); 
	?>

	<?php $model_contragent = Contragent::find()-> where (['=','id', $model->contragent_id]) ->all();
	    foreach ($model_contragent as $value) (
        $contragent = $value->contragent
    ); 
	?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            ['attribute' => 'date',
            'format' => ['date', 'dd.MM.yyyy']
            ],
            'ex_im',
            ['attribute' =>'decl_number_id',
             'value' =>$decl_number,
            ],
            ['attribute' =>'contragent_id', 
			'value' =>$contragent,
            ],
            'broker',
            'dosmotr',
            'custom',
            'fito',
        ],
    ]) ?>

</div>
