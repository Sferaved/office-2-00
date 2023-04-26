<?php

/* @var $this yii\web\View */
use frontend\models\Invoice;


$this->title = 'Офис on-line';
?>
<div class="site-index">

    <div class="jumbotron">
        <h1>Офис on-line</h1>

        <p class="lead">Система учета работы и затрат (версия 2.0)</p>
		<p class="lead">Период с 1 января по 31 декабря 2022</p>
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
   <?php 
	$invoice=0;
	if (Yii::$app->user->can('user') ) {
		
		$decl_inv = invoice_absent();

		if ($decl_inv[0] !=0){
			
			$Inv_abs=null;
			foreach ($decl_inv as $arrDcl) {
				$Inv_abs = $Inv_abs.$arrDcl['decl_number'].' ';
			};
		
		    Yii::$app->session->addFlash('error', 'Выход заблокирован!!!! Не выставлены счета на декларации: '.$Inv_abs);
	    }
		else {
			$invoice= Invoice::find()->where (['=','oplata','Нет'])->andWhere (['=','user_id',Yii::$app->user->id])->count();
			$arrClient= dogovor_report();
			
			if ($invoice !=0 && $arrClient !=null) {
				Yii::$app->session->addFlash('error', 'Не оплачено счетов: '. $invoice.'. Часть договоров нужно продлить');
			}
			
			if ($invoice ==0 && $arrClient !=null) {
				Yii::$app->session->addFlash('error', 'Часть договоров нужно продлить');
//				Yii::$app->session->removeFlash('error');
			}
			
			if ($invoice !=0 && $arrClient ==null) {
				Yii::$app->session->addFlash('error', 'Не оплачено счетов: '. $invoice);

			}
		}
		
		
		
	
		
	}
	
	if (Yii::$app->user->can('admin') || Yii::$app->user->can('buh')) {
		$invoice= Invoice::find()->where (['=','oplata','Нет'])->count();
		$arrClient= dogovor_report();
		
		if ($invoice !=0 && $arrClient !=null) {
			Yii::$app->session->addFlash('error', 'Не оплачено счетов: '. $invoice.'. Часть договоров нужно продлить');
		}
		
		if ($invoice ==0 && $arrClient !=null) {
			Yii::$app->session->addFlash('error', 'Часть договоров нужно продлить');
		}
		
		if ($invoice !=0 && $arrClient ==null) {
			Yii::$app->session->addFlash('error', 'Не оплачено счетов: '. $invoice);
		}
	}
   

   ?>
   
   
    <div class="body-content">

        <div class="row">
	
	<?php if (Yii::$app->user->can('admin')) { ?>
		    <div class="col-lg-3">
                <h3>Отчеты</h3>

                <p>Панель администратора.</p>

                <p><a class="btn btn-default" href="https://sferaved-office-online.ru/admin">перейти &raquo;</a></p>
            </div>
			<div class="col-lg-3">
                <h3>Электронный документооборот</h3>

                <p>Отправка документов.</p>

                <p><a class="btn btn-default" href="https://sota-buh.com.ua/edo">перейти &raquo;</a></p>
            </div>
	<?php }?>
	
	

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
