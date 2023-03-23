<?php

/* @var $this yii\web\View */

$this->title = 'Офис on-line';
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Офис on-line</h1> 
		<p class="lead">Система учета работы и затрат (версия 2.0)</p>
		<p class="lead">Период с 1 января по 31 декабря 2022</p>
		 <h2>Панель администратора</h2>

       
  <?php if (Yii::$app->user->isGuest) {
     ?>
        <p><a class="btn btn-lg btn-success" href="/site/login">Начать работу</a></p>
  <?php }
  else {
	?>  
	  <p class="lead">Авторизация успешна.</p>
	  
  <?php 
// echo Yii::$app->user->id;
  }  
     ?>
    </div>
   
    <div class="body-content">

        <div class="row">
            <div class="col-lg-3">
                <h3>Офис on-line</h3>

                <p>Рабочая панель</p>

                <p><a class="btn btn-default" href="https://sferaved-office-online.ru">перейти &raquo;</a></p>
            </div>
            <div class="col-lg-3">
                <h3>Курсы валют</h3>

                <p>Официальные курсы валют НБУ.</p>

                <p><a class="btn btn-default" href="https://bank.gov.ua/ua/markets/exchangerates?date=10.02.2021&period=daily">посмотреть &raquo;</a></p>
            </div>
            <div class="col-lg-3">
                <h3>Таможенный кодекс</h3>

                <p>Таможенный кодекс Украины.</p>

                <p><a class="btn btn-default" href="https://www.mdoffice.com.ua/ua/aMDONormDocs.CustCodecs2012">перейти &raquo;</a></p>
            </div>
			<div class="col-lg-3">
                <h3>УкрТНВЭД</h3>

                <p>Товарная номенклатура Украины.</p>

                <p><a class="btn btn-default" href="https://www.mdoffice.com.ua/ua/aMDOTNVD2020.GetTNVUA">перейти &raquo;</a></p>
            </div>
        </div>

    </div>
	
</div>
