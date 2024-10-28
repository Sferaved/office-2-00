<?php
//
//use yii\helpers\Html;
//
///* @var $this yii\web\View */
///* @var $model frontend\models\Invoice */
//
//$this->title = Yii::t('app', 'Исправить запись №: {name}', [
//    'name' => $model->id,
//]);
//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Счета'), 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
//$this->params['breadcrumbs'][] = Yii::t('app', 'Исправить');
//?>
<!--<div class="invoice-update">-->
<!---->
<!--    <h1>--><?//= Html::encode($this->title) ?><!--</h1>-->
<!---->
<!--    --><?//= $this->render('_form', [
//        'model' => $model,
//    ]) ?>
<!---->
<!--</div>-->
<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\models\Invoice */

$this->title = Yii::t('app', 'Подтвердить оплату по счету  №: {name}', [
    'name' => $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Счета'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Подтвердить');
?>
<div class="invoice-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Обновить статус оплаты'), ['invoice/update', 'id' => $model->id], [
            'class' => 'btn btn-success',
            'data-method' => 'post', // Отправка запроса как POST
            'data-confirm' => Yii::t('app', 'Вы уверены, что хотите обновить статус оплаты?'),
            'onclick' => 'return confirmUpdate();', // вызов пользовательского подтверждения
        ]) ?>
    </p>

    <p>Вы можете обновить статус оплаты для счета, нажав кнопку выше. Это действие установит статус оплаты в "Да" без загрузки полной формы.</p>

</div>

<?php
$script = <<<JS
    function confirmUpdate() {
        if (confirm("Вы уверены, что хотите обновить статус оплаты?")) {
            return true; // Подтверждение обновления
        }
        return false; // Отмена действия
    }
JS;

$this->registerJs($script);
?>

