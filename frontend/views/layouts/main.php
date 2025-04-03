<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use common\widgets\Alert;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
	
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
   
    if (Yii::$app->user->isGuest) {
     
        $menuItems[] = ['label' => 'Вход', 'url' => ['/site/login']];
        
    } else {
        if (Yii::$app->user->can('admin')){
			$menuItems=[
			['label' => 'Главная', 'url' => ['/site/index']],
			['label' => 'Регистрация', 'url' => ['/site/signup']],
            ['label' => 'Дом', 'url' => ['/homezatraty'],'active' => $this->context->route == 'homezatraty/index'],
            ['label' => 'Работа', 'url' => ['/declaration'],'active' => $this->context->route == 'declaration/index'],
			['label' => 'Счета', 'url' => ['/invoice'],'active' => $this->context->route == 'invoice/index'],
			['label' => 'Кабинет', 'url' => ['/cabinet'],'active' => $this->context->route == 'cabinet/index'],
		    ['label' => 'Клиенты', 'url' => ['/client'],'active' => $this->context->route == 'client/index'],
			['label' => 'Акваизол', 'url' => ['/aquaizol'],'active' => $this->context->route == 'aquaizol/index'],
			['label' => 'ФЛЄКСС', 'url' => ['/flex'],'active' => $this->context->route == 'flex/index']
			];
						
        }  
		if (Yii::$app->user->can('buh')){
			$menuItems=[
			['label' => 'Главная', 'url' => ['/site/index']],
		    ['label' => 'Дом', 'url' => ['/homezatraty'],'active' => $this->context->route == 'homezatraty/index'],
            ['label' => 'Работа', 'url' => ['/declaration'],'active' => $this->context->route == 'declaration/index'],
			['label' => 'Счета', 'url' => ['/invoice'],'active' => $this->context->route == 'invoice/index'],
		    ['label' => 'Клиенты', 'url' => ['/client'],'active' => $this->context->route == 'client/index']
			];
					
        }
		if (Yii::$app->user->can('user')){
			$menuItems=[
			['label' => 'Главная', 'url' => ['/site/index']],
	        ['label' => 'Работа', 'url' => ['/declaration'],'active' => $this->context->route == 'declaration/index'],
			['label' => 'Счета', 'url' => ['/invoice'],'active' => $this->context->route == 'invoice/index'],
			['label' => 'Кабинет', 'url' => ['/cabinet'],'active' => $this->context->route == 'cabinet/index'],
		    ['label' => 'Клиенты', 'url' => ['/client'],'active' => $this->context->route == 'client/index']
			];
        }
		
		if (Yii::$app->user->can('runo')){
			$menuItems=[
			['label' => 'Главная', 'url' => ['/site/index']],
			['label' => 'Акваизол', 'url' => ['/aquaizol'],'active' => $this->context->route == 'aquaizol/index'],
			['label' => 'ФЛЄКСС', 'url' => ['/flex'],'active' => $this->context->route == 'flex/index']
			];
						
        }
            $menuItems[] = '<li>'
            . Html::beginForm(['/site/logout'], 'post')
            . Html::submitButton(
                'Выход (' . Yii::$app->user->identity->username . ')',
                ['class' => 'btn btn-link logout']
            )
            . Html::endForm()
            . '</li>';
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'activateItems' => true,
		'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
