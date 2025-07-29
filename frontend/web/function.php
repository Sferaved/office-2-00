<?php

// баланс затрат

use frontend\controllers\DeclarationController;
use frontend\models\Cabinet;
use frontend\models\Cabinetstatya;
use frontend\models\User;
use frontend\models\AuthAssignment;
use common\models\Client;
use common\models\Contragent;
use frontend\models\Aquaizol;
use frontend\models\Flex;
use frontend\models\Invoice;
use frontend\models\Declaration;
use backend\models\Workzatraty;
use common\models\Workstatya;
use backend\models\AqFlCost;
use backend\models\ClientInfo;
use app\models\Homezatraty;
use common\models\HomestatyaModel;

use yii\helpers\ArrayHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Autoloader;
use PhpOffice\PhpSpreadsheet\Style\{Font, Border, Alignment};


function cabinet_bal () {
	
$arrUsers = AuthAssignment::find()->where(['item_name'=>'user'])->all();
 
	foreach ($arrUsers as $tabl) {
		$arrCabinet_bal[$tabl['user_id']]=0;
	}

foreach ($arrUsers as $value) (            //Получили отобранные id=User
           $arrIdUser[] = $value->user_id
		);

$arrZatraty_cab = Cabinet::find() ->asArray()
	                -> where (['user_id'=>$arrIdUser])->  all ();
					
if (isset($arrZatraty_cab)) {
  $statya = Cabinetstatya::find()->asArray()->  all ();

   
	foreach ($arrZatraty_cab as $tabl) {
			
			$cost_minus =0;
			$cost_plus=0;
			  
		foreach ($statya as $tablstatya) {
			 
			if ($tabl["coment_id"] == $tablstatya["id"]) {
				 $minus_plus= $tablstatya ['income'];
			};
		 };
		   
			if ($minus_plus == 'Нет') {
				$cost_minus += $tabl["cost"];
			}
			else {
				$cost_plus += $tabl["cost"];
			}
		   
			$arrCabinet_bal[$tabl['user_id']] +=$cost_plus - $cost_minus;

	}	
}
 
  
return ($arrCabinet_bal); 
};
   
// Просмотр массива

function debug ($name) {
	?>
	<pre>
	<?php
	print_r ($name);
	?>
	</pre>
	<?php
	}   

// Чтение из файла декларации

function decl_read ($filename) {
 
	$parser = new \Smalot\PdfParser\Parser();
	$pdf    = $parser->parseFile($filename);

    $text = $pdf->getText();
	

    //Поиск контрагента


    $arrContragent = Contragent::find()->asArray()->all(); 
    
	$tabl_pdf['custom']=800;
	
	
    foreach ($arrContragent as $tabl) {
    

		if ($tabl["contragent"] != null) {
			$pos = strpos($text,$tabl["contragent"]);

				if ($pos !== false) {    
			
					$tabl_pdf["contragent_id"] = $tabl["id"];
					
					$arrAqFlCost = AqFlCost::find()->asArray() 
	                -> where (['=','contragent_id',$tabl_pdf["contragent_id"]]) ->one();

	
	if ($arrAqFlCost != null) {
		$tabl_pdf['custom']=$arrAqFlCost['cost'];
	};

					
					/* if ($tabl["contragent"]== 'Difot International Trading Co') {
						$tabl_pdf['custom'] =1050;
					};
					if ($tabl["contragent"]== 'S.R.L. "MILANCONS"') {
						$tabl_pdf['custom'] =1050;
					};
					if ($tabl["contragent"]== 'Пигмент') {
						$tabl_pdf['custom'] =1050;
					};
					if ($tabl["contragent"]== 'Synthomer Deutschland GmbH') {
						$tabl_pdf['custom'] =1050;
					};
					if ($tabl["contragent"]== 'Graf + Cie AG') {
						$tabl_pdf['custom'] =1800;
					};
					if ($tabl["contragent"]== 'AziaGRIT') {
						$tabl_pdf['custom'] =950;
					};
					if ($tabl["contragent"]== 'Tectex Needle Boards srl') {
						$tabl_pdf['custom'] =1800;
					};
					if ($tabl["contragent"]== 'Kumho Petrochemical Co. Ltd.') {
						$tabl_pdf['custom'] =6150;
					};
					if ($tabl["contragent"]== 'Уралгрит') {
						$tabl_pdf['custom'] =1550;
					};
					if ($tabl["contragent"]== 'Mogensen GmbH & Co. KG') {
						$tabl_pdf['custom'] =1350;
					};					
					if ($tabl["contragent"]== 'MONTENERO') {
						$tabl_pdf['custom'] =2800;
					};					
					if ($tabl["contragent"]== 'CROSS LINE CORPORATION FOR IMPORT AND EXPORT') {
						$tabl_pdf['custom'] =6510;
					}; */
				} 
		}
        
    };
	
 	
	
	$keywords = preg_split("/[\s,]+/", $text);
	
	unlink($filename);
	
	$a = $keywords;

//debug ($a);

$a_i =0;
$i=0;
$i_date=0;

foreach ($a as $tabl)
    {
    //Поиск номера декларации
    if ($a[$i]=='РОЗРАХУНКІВ') {
        $i_decl=$i+1;
        }


    //Поиск даты декларации
    if ($a[$i]=='ОНП') {
        $i_date=$i+2;
        }
		
	//Поиск количества доп листов
    if ($a[$i]=='Вн.') {
        $i_dop_list=$i-3;
        }
		
    //Поиск типа декларации
    if ($a[$i]=='ЕК')  {
            $tabl_pdf["ex_im"]='Экспорт';
            $i_cod_inozem=$i+25; 
        }
    if ($a[$i]=='ІМ')  {
            $tabl_pdf["ex_im"]='Импорт';
            $i_cod_inozem=$i+4; 
        }

        $findme   = 'UA/';
        $pos = strpos($a[$i],   $findme );

     //Поиск кода ЕГРПОУ клиента
     if ($pos !== false) {
            if( substr($a[$i], -10) !== "2607014759"   & $a_i ==1) {
                $tabl_pdf["cod_EGRPOU"] =  substr($a[$i], -10); //Номер  кода ЕГРПОУ клиента
				$a_i++;
             } 
        }
      if ($pos !== false & $a_i !=1) {    
                $a_i++;
             }
        $i++;
    }
 
	$i=0;
    $tabl_pdf["dop_list"] = 0;
	
	foreach ($a as $tabl){
		if ($i == $i_decl & $i!=0){
			$tabl_pdf["decl"] = $tabl;//Номер декларации
		  } 

		if ($i == $i_date & $i!=0){
			$tabl_pdf["decl_date"] = date('Y-m-d',strtotime($tabl)); //Дата оформления
		  } 
              if ($i_date ==0){
			$tabl_pdf["decl_date"] = date('Y-m-d'); //Дата оформления
		  } 
		if ($i ==  $i_dop_list & $i!=0){
			$tabl_pdf["dop_list"] = $tabl-1; //Количество доп листов
		  }  
		 

		$i++;
	}

  	if ($tabl_pdf["decl_date"] ==  "1970-01-01" ) {
		$tabl_pdf["decl_date"] = date ("Y-m-d");
	}  
	
//	$tabl_pdf["client_id"] =null;	
	$arrClient = Client::findOne(['cod_EGRPOU'=>$tabl_pdf["cod_EGRPOU"]]);

	$tabl_pdf['client_id'] = $arrClient['id'];

//debug ($tabl_pdf);

	return ($tabl_pdf);
	
 
}

function decl_parsing_full ($filename) {

    $parser = new \Smalot\PdfParser\Parser();
    $pdf    = $parser->parseFile($filename);

    $text = $pdf->getText();


    //Поиск контрагента


    $arrContragent = Contragent::find()->asArray()->all();

    $tabl_pdf['custom']=800;


    foreach ($arrContragent as $tabl) {


        if ($tabl["contragent"] != null) {
            $pos = strpos($text,$tabl["contragent"]);

            if ($pos !== false) {

                $tabl_pdf["contragent_id"] = $tabl["id"];

                $arrAqFlCost = AqFlCost::find()->asArray()
                    -> where (['=','contragent_id',$tabl_pdf["contragent_id"]]) ->one();


                if ($arrAqFlCost != null) {
                    $tabl_pdf['custom']=$arrAqFlCost['cost'];
                };


                /* if ($tabl["contragent"]== 'Difot International Trading Co') {
                    $tabl_pdf['custom'] =1050;
                };
                if ($tabl["contragent"]== 'S.R.L. "MILANCONS"') {
                    $tabl_pdf['custom'] =1050;
                };
                if ($tabl["contragent"]== 'Пигмент') {
                    $tabl_pdf['custom'] =1050;
                };
                if ($tabl["contragent"]== 'Synthomer Deutschland GmbH') {
                    $tabl_pdf['custom'] =1050;
                };
                if ($tabl["contragent"]== 'Graf + Cie AG') {
                    $tabl_pdf['custom'] =1800;
                };
                if ($tabl["contragent"]== 'AziaGRIT') {
                    $tabl_pdf['custom'] =950;
                };
                if ($tabl["contragent"]== 'Tectex Needle Boards srl') {
                    $tabl_pdf['custom'] =1800;
                };
                if ($tabl["contragent"]== 'Kumho Petrochemical Co. Ltd.') {
                    $tabl_pdf['custom'] =6150;
                };
                if ($tabl["contragent"]== 'Уралгрит') {
                    $tabl_pdf['custom'] =1550;
                };
                if ($tabl["contragent"]== 'Mogensen GmbH & Co. KG') {
                    $tabl_pdf['custom'] =1350;
                };
                if ($tabl["contragent"]== 'MONTENERO') {
                    $tabl_pdf['custom'] =2800;
                };
                if ($tabl["contragent"]== 'CROSS LINE CORPORATION FOR IMPORT AND EXPORT') {
                    $tabl_pdf['custom'] =6510;
                }; */
            }
        }

    };



    $keywords = preg_split("/[\s,]+/", $text);

    unlink($filename);

    $a = $keywords;

//    debug ($a);

    $a_i =0;
    $i=0;
    $i_date=0;

    foreach ($a as $tabl)
    {
        //Поиск номера декларации
        if ($a[$i]=='РОЗРАХУНКІВ') {
            $i_decl=$i+1;
        }


        //Поиск даты декларации
        if ($a[$i]=='ОНП') {
            $i_date=$i+2;
        }

        //Поиск количества доп листов
        if ($a[$i]=='Вн.') {
            $i_dop_list=$i-3;
        }

        //Поиск типа декларации
        if ($a[$i]=='ЕК')  {
            $tabl_pdf["ex_im"]='Экспорт ' . $a[$i+1];


            $i_cod_inozem=$i+25;
        }
        if ($a[$i] == '11') {
            $tabl_pdf["costCurrency"]=$a[$i+64];
            $tabl_pdf["costValue"]=$a[$i+65];
            $tabl_pdf["costCurs"]=$a[$i+66];
        }
        if ($a[$i]=='ІМ')  {
            $tabl_pdf["ex_im"]='Импорт '  . $a[$i+1];
            if ($a[$i+1] == '31') {
                $tabl_pdf["costCurrency"]=$a[$i+63];
                $tabl_pdf["costValue"]=$a[$i+64];
                $tabl_pdf["costCurs"]=$a[$i+66];
            }

            $i_cod_inozem=$i+4;
        }
        if ($a[$i]=='ЄДРПОУ:')  {
            if ($a[$i-7] == "EUR" || $a[$i-7] == "USD") {
                $tabl_pdf["costCurrency"]=$a[$i-7];
                $tabl_pdf["costValue"]=$a[$i-6];
                $tabl_pdf["costCurs"]=$a[$i-5];
            } else {
                $tabl_pdf["costCurrency"]=$a[$i-6];
                $tabl_pdf["costValue"]=$a[$i-5];
                $tabl_pdf["costCurs"]=$a[$i-4];
            }
        }
//        if ($a[$i] == "14305909" &&  $tabl_pdf["costCurrency"] != 'EUR' || $tabl_pdf["costCurrency"] != 'USD') {
////            debug ($a[$i+1] . " " . $a[$i-6] . " " . $a[$i-5] . " " . $a[$i-4] );
//            $tabl_pdf["costCurrency"]=$a[$i-7];
//            $tabl_pdf["costValue"]=$a[$i-6];
//            $tabl_pdf["costCurs"]=$a[$i-5];
//        }
        if ($a[$i] == "039") {
            $tabl_pdf["costCurrency"]=$a[$i-3];
            $tabl_pdf["costValue"]=$a[$i-2];
            $tabl_pdf["costCurs"]=$a[$i-1];
        }

        if ($a[$i] == "039") {
            $tabl_pdf["costCurrency"]=$a[$i-3];
            $tabl_pdf["costValue"]=$a[$i-2];
            $tabl_pdf["costCurs"]=$a[$i-1];
        }
        $findme   = 'UA/';
        $pos = strpos($a[$i],   $findme );

        //Поиск кода ЕГРПОУ клиента
        if ($pos !== false) {
            if( substr($a[$i], -10) !== "2607014759"   & $a_i ==1) {
                $tabl_pdf["cod_EGRPOU"] =  substr($a[$i], -10); //Номер  кода ЕГРПОУ клиента
                $a_i++;
            }
        }
        if ($pos !== false & $a_i !=1) {
            $a_i++;
        }
        $i++;
    }

    $i=0;
    $tabl_pdf["dop_list"] = 0;

    foreach ($a as $tabl){
        if ($i == $i_decl & $i!=0){
            $tabl_pdf["decl"] = $tabl;//Номер декларации
        }

        if ($i == $i_date & $i!=0){
            $tabl_pdf["decl_date"] = date('Y-m-d',strtotime($tabl)); //Дата оформления
        }
        if ($i_date ==0){
            $tabl_pdf["decl_date"] = date('Y-m-d'); //Дата оформления
        }
        if ($i ==  $i_dop_list & $i!=0){
            $tabl_pdf["dop_list"] = $tabl-1; //Количество доп листов
        }


        $i++;
    }

    if ($tabl_pdf["decl_date"] ==  "1970-01-01" ) {
        $tabl_pdf["decl_date"] = date ("Y-m-d");
    }

//	$tabl_pdf["client_id"] =null;
    $arrClient = Client::findOne(['cod_EGRPOU'=>$tabl_pdf["cod_EGRPOU"]]);

    $tabl_pdf['client_id'] = $arrClient['id'];

//debug ($tabl_pdf);

    return ($tabl_pdf);


}
// Создание счета

function invoice_doc ($number_invoice,$date, $decl, $client, $cost ) {
	
 
 
 //номер и дата договора 
    $arrClient = Client::findOne(['client'=>$client]);
	   
	$dogovor= $arrClient["dogovor"];
    $dogovor_date= $arrClient["date_begin"];
	$dogovor_date =date('d.m.Y',strtotime($dogovor_date));
	
	$date = date('d.m.Y',strtotime($date));
 
// Создание счета

    $inputFileName = './templates/ranunok.xlsx';
    
	$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
    $sheet = $spreadsheet->getActiveSheet();

    $B17 = 'Рахунок на оплату № '.$number_invoice.' від '. $date.'р.';
    $H22 = $client;
    $H25 = "№".$dogovor." від ".$dogovor_date."р.";

	//Определение - номер декларации или все остальное 
	$flag_UA = 0;
	$mystring =   $decl;
	$findme   = 'U';
	$pos = strpos($mystring,   $findme );

	if ($pos !== false) { 
    
        $D29 = "Послуги митного брокера за МД №".$decl;
        $flag_U=1;
    }
    else {
        $D29 = "Послуги митного брокера.";
        $flag_U=0;
    }
 
    $B34 = num2text_ua($cost); // Общая стоимость словами
    $AH29 = number_format ($cost,2,',',' ');
    $B33 = 'Всього найменувань 1,'.' на суму '.$AH29.' грн';
           
            $new_invoice = 'ranunok_'.$number_invoice;
            
            
            $sheet->setCellValue("B17",   $B17); //Номер счета
            $sheet->setCellValue("H22",   $H22); //Клиент
            $sheet->setCellValue("H25",  $H25); //Клиент
            $sheet->setCellValue("D29",   $D29); //Товар
            $sheet->setCellValue("AD29", $AH29); //Цена за 1 декларацию
            $sheet->setCellValue("AH29", $AH29); //Стоимость за все
            $sheet->setCellValue("AH31", $AH29); //Стоимость за все (ИТОГО)
           
            $sheet->setCellValue("B33", $B33); //Всього найменувань на суму 

            $sheet->setCellValue("B34", $B34); //Всего прописью
   
         
            $invoice = new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
            $invoice->save('files/'.$new_invoice.'.xls');

            $drawing = new PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setPath('img/signature.jpg');
            $drawing->setWidth(255);
            $drawing->setCoordinates('K37');
            $drawing->setWorksheet($sheet);  
     
            $invoice_signature = new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
            $invoice_signature->save('files/'.$new_invoice.'_signature.xls');
         
return ($new_invoice);
}


function act_doc ($number_invoice,$date, $decl, $client, $cost ) {
	
 
 
 //номер и дата договора 
    $arrClient = Client::findOne(['client'=>$client]);
	$arrClientInfo = ClientInfo::find()->asArray()->where(['=','client_id',$arrClient['id']])->one();
 //  debug ($arrClientInfo);
	   
	$dogovor= $arrClient["dogovor"];
    $dogovor_date= $arrClient["date_begin"];
	$dogovor_date =date('d.m.Y',strtotime($dogovor_date));
	
	$date = date('d.m.Y',strtotime($date));
 
// Создание акта

	$inputFileName = './templates/act_aquaizol.xls';
    
	$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
    $sheet = $spreadsheet->getActiveSheet();

    $C11 = 'АКТ надання послуг № '.$number_invoice.' від '. $date.'р.';
	$C13 ="    Ми, що нижче підписалися, представник Замовника ".$client.",  з одного боку, і представник Виконавця  ФО-П Коржов Андрій Анатолійович , з іншого боку, склали цей акт про те, що на підставі наведених документів:";

    $T5 = $client;
	if (isset ($arrClientInfo)) {
	$T8 =	$arrClientInfo['director'];
	$T35 =$T5 ."код за ЕДРПОУ ".$arrClient['cod_EGRPOU']." адрес: ".$arrClientInfo['adress']." телефон ".$arrClientInfo['telephon'];	
	}
	else {
	$T8 =' ';
	$T35 =$T5 ."код за ЕДРПОУ ".$arrClient['cod_EGRPOU'];	
	}
	
    $L14 = "№".$dogovor." від ".$dogovor_date."р.";




	//Определение - номер декларации или все остальное 
	$flag_UA = 0;
	$mystring =   $decl;
	$findme   = 'U';
	$pos = strpos($mystring,   $findme );

	if ($pos !== false) { 
    
        $E19 = "Послуги митного брокера за МД №".$decl;
        $flag_U=1;
    }
    else {
        $E19 = "Послуги митного брокера.";
        $flag_U=0;
    }
 
    $B34 = num2text_ua($cost); // Общая стоимость словами
    $AK19 = number_format ($cost,2,',',' ');
    $C23 = 'Всього найменувань 1,'.' на суму '.$B34.' грн';
      
	$new_act = 'act_'.$number_invoice;	  
	
            $new_invoice = 'act_'.$number_invoice;
            $sheet->setCellValue("T5",   $T5); //Клиент
			$sheet->setCellValue("T8",   $T8); //Клиент
			$sheet->setCellValue("C13",   $C13); //Текст
            $sheet->setCellValue("L14",   $L14); //Номер договора
            $sheet->setCellValue("C11",   $C11); //Номер счета
            $sheet->setCellValue("E19",   $E19); //Список деклараций
            $sheet->setCellValue("Y19",  "1"); //Количество деклараций
            $sheet->setCellValue("AF19", $AK19); //Цена за 1 декларацию
            $sheet->setCellValue("AK19", $AK19); //Стоимость за все
            $sheet->setCellValue("AK21", $AK19); //Стоимость за все (ИТОГО)
            $sheet->setCellValue("C23", $C23); //Сумма прописью 
            $sheet->setCellValue("C34",  $date); //Дата подписи акта   
            $sheet->setCellValue("T34",  $date); //Дата подписи акта   
			$sheet->setCellValue("T35",  $T35); //Дата подписи акта  
         
            $act = new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
            $act->save('files/'.$new_act.'.xls');

       
   
   return ($new_act);     
}

function actDocSignature ($number_invoice,$date, $decl, $client, $cost ) {



 //номер и дата договора
    $arrClient = Client::findOne(['client'=>$client]);
	$arrClientInfo = ClientInfo::find()->asArray()->where(['=','client_id',$arrClient['id']])->one();
 //  debug ($arrClientInfo);

	$dogovor= $arrClient["dogovor"];
    $dogovor_date= $arrClient["date_begin"];
	$dogovor_date =date('d.m.Y',strtotime($dogovor_date));

	$date = date('d.m.Y',strtotime($date));

// Создание акта

	$inputFileName = './templates/act_aquaizol.xls';

	$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
    $sheet = $spreadsheet->getActiveSheet();

    $C11 = 'АКТ надання послуг № '.$number_invoice.' від '. $date.'р.';
	$C13 ="    Ми, що нижче підписалися, представник Замовника ".$client.",  з одного боку, і представник Виконавця  ФО-П Коржов Андрій Анатолійович , з іншого боку, склали цей акт про те, що на підставі наведених документів:";

    $T5 = $client;
	if (isset ($arrClientInfo)) {
	$T8 =	$arrClientInfo['director'];
	$T35 =$T5 ."код за ЕДРПОУ ".$arrClient['cod_EGRPOU']." адрес: ".$arrClientInfo['adress']." телефон ".$arrClientInfo['telephon'];
	}
	else {
	$T8 =' ';
	$T35 =$T5 ."код за ЕДРПОУ ".$arrClient['cod_EGRPOU'];
	}

    $L14 = "№".$dogovor." від ".$dogovor_date."р.";




	//Определение - номер декларации или все остальное
	$flag_UA = 0;
	$mystring =   $decl;
	$findme   = 'U';
	$pos = strpos($mystring,   $findme );

	if ($pos !== false) {

        $E19 = "Послуги митного брокера за МД №".$decl;
        $flag_U=1;
    }
    else {
        $E19 = "Послуги митного брокера.";
        $flag_U=0;
    }

    $B34 = num2text_ua($cost); // Общая стоимость словами
    $AK19 = number_format ($cost,2,',',' ');
    $C23 = 'Всього найменувань 1,'.' на суму '.$B34.' грн';

	$new_act = 'act_'.$number_invoice;

            $new_invoice = 'act_'.$number_invoice;
            $sheet->setCellValue("T5",   $T5); //Клиент
			$sheet->setCellValue("T8",   $T8); //Клиент
			$sheet->setCellValue("C13",   $C13); //Текст
            $sheet->setCellValue("L14",   $L14); //Номер договора
            $sheet->setCellValue("C11",   $C11); //Номер счета
            $sheet->setCellValue("E19",   $E19); //Список деклараций
            $sheet->setCellValue("Y19",  "1"); //Количество деклараций
            $sheet->setCellValue("AF19", $AK19); //Цена за 1 декларацию
            $sheet->setCellValue("AK19", $AK19); //Стоимость за все
            $sheet->setCellValue("AK21", $AK19); //Стоимость за все (ИТОГО)
            $sheet->setCellValue("C23", $C23); //Сумма прописью
            $sheet->setCellValue("C34",  $date); //Дата подписи акта
            $sheet->setCellValue("T34",  $date); //Дата подписи акта
			$sheet->setCellValue("T35",  $T35); //Дата подписи акта



            $drawing = new PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setPath('img/signature.jpg');
            $drawing->setWidth(255);
            $drawing->setCoordinates('C36');
            $drawing->setWorksheet($sheet);

    $act = new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
    $act->save('files/'.$new_act.'.xls');



   return ($new_act);
}
 

function dogovor_doc ($id){
	
	//Информация о реквизитах договора 
    $arrClient = Client::findOne(['id'=>$id]);

	$dogovor_full = $id."-КБ-".date("m", strtotime($arrClient["date_begin"])).'/'.date("Y", strtotime($arrClient["date_begin"]));  
	$company = $arrClient["client"];   
 	$dogovor= $arrClient["dogovor"];
    $date_begin= $arrClient["date_begin"];
	$date_begin =date('d.m.Y',strtotime($date_begin));
	$date_finish= $arrClient["date_finish"];
	$date_finish =date('d.m.Y',strtotime($date_finish));
	


	// Creating the new document...
	

	$phpWord = new \PhpOffice\PhpWord\PhpWord();
	
	

	// Adding an empty Section to the document...


	$section = $phpWord->addSection();
	 
	$phpWord->addParagraphStyle('Paragraph', array('bold' => TRUE , 'align' => 'center' ));
	$fontStyle = array('name' => 'Times New Roman', 'size'=>12,'bold' => TRUE );
	$section->addText('ДОГОВОР ДОРУЧЕННЯ №'.$dogovor_full, $fontStyle, 'Paragraph');

	$date = new DateTime($date_begin); 

	$fontStyle = array('name' => 'Times New Roman', 'size'=>10);
	$section->addText(
	   'про надання послуг з декларування товарів і транспортних засобів',
	   $fontStyle, 'Paragraph'
	);

    $fontStyle = array('name' => 'Times New Roman', 'size'=>10);
    $section->addText(
    'м.Харків                                                                                                                 '.$date->format('d.m.Y').'р.', 
    $fontStyle, 'Paragraph'
    );
    
    $phpWord->addParagraphStyle('Paragraph_2', array('bold' => TRUE , 'align' => 'both' ));
 
    $section->addText('           '.$company.', яке є платником податку _______________________ за ___________ ставкою, що визначена п._________ Податкового кодексу України та є (ні є) платником ПДВ, далі за текстом – «ДОРУЧИТЕЛЬ», в особі ________________ , який діє на підставі Статуту, з однієї сторони, і фізична особа-підприємець Коржов Андрій Анатолійович, який є платником єдиного податку за ставкою 5% та ні є платником ПДВ, далі за текстом – «ПОВІРЕНИЙ» (номер запису в Єдиному державному реєстрі  2 480 017 0000 009334 від 23.08.2002 р., номер авторизації на провадження митної брокерської діяльності UACBR125000066 від 28.07.2025), з іньшої сторони, уклали Договор про наведене нижче:',
        $fontStyle, 'Paragraph_2' );

   
        $section->addText('1. ПРЕДМЕТ ДОГОВОРУ.', $fontStyle, 'Paragraph');
        $section->addText(' 1.1. Доручитель доручає Повіреному,  згідно з Загальними умовами надання послуг з декларування товарів і транспортних засобів (п.8), здійснення  самостійно або з залученням третіх осіб усіх необхідних юридичних дій з декларування товарів і транспортних засобів  Доручителя таких, як: оформлення вантажу у митному відношенні (митно-брокерьскі послуги),  організація робіт по сертифікації у системі УкрСЕПРО, проведення фітосанітарного, санітарно-епідеміологічного та інших видів державного контролю, проведення усіх необхідних дій, пов"язаних з організацією, розміщенням та зберіганням  цих товарів  і майна на складах тимчасового зберігання, а також здійснення акредитації у митних органах. ',
        $fontStyle, 'Paragraph_2' );

        $section->addText(' 1.2. Згідно з цим договором ПОВІРЕНИЙ діє як прямий митний представник в інтересах і від імені ДОРУЧИТЕЛЯ',
        $fontStyle, 'Paragraph_2' );
        
        $section->addText('2. ОБОВ`ЯЗКИ СТОРІН.', $fontStyle, 'Paragraph');

        $section->addText('2.1. Обов`язки ПОВІРЕНОГО.', $fontStyle, 'Paragraph_2' );
        $section->addText('2.1.1. Надає  консультаційно-інформаційні послуги з декларування товарів і транспортних засобів Доручителя, відповідно до п.1.1. Договору.', $fontStyle, 'Paragraph_2' );
        $section->addText('2.1.2. Зберігає комерційну таємницю, яка знаходиться в наданих Доручителем документах.', $fontStyle, 'Paragraph_2' );
        $section->addText('2.1.3. Повідомляє Доручителю усі відомості стосовно виконання доручення.', $fontStyle, 'Paragraph_2' );

        $section->addText('2.2. Обов`язки ДОРУЧИТЕЛЯ.', $fontStyle, 'Paragraph_2' );
        $section->addText('2.2.1. Надає Повіреному  заяву на оформлення вантажної митної декларації не пізніше двох діб до моменту митного оформлення вантажів на митниці.', $fontStyle, 'Paragraph_2' );
        $section->addText('2.2.2. Надає Повіреному усі необхідні для оформлення документи (підтвердження реєстрації суб"єкта ЗЕД на митниці, контракт, рахунок-фактура, товарно-транспортні документи, технічні документи, підтвердження країни походження товару, сертифікати щодо відповідності стандартам та показникам безпеки та якості, які оформлені та видані виробнику товару в країні виробництва та інших країнах, а також інші документи, необхідні для декларування товарів і транспортних засобів Доручителя, які будуть повернуті Доручітелю після проведення митного оформлення.', 
        $fontStyle, 'Paragraph_2' );
        $section->addText('2.2.3. Доручитель гарантує збереження вантажу, забезпечення цілісності митного забезпечення при його наявності і відповідність вантажу відомостям, вказаним у наданих Повіреному документах, а також надає до огляду вантаж.', $fontStyle, 'Paragraph_2' );
      

        $section->addText('3. ПОРЯДОК  РОЗРАХУНКІВ.', $fontStyle, 'Paragraph');    
        $section->addText('3.1. Сплата за послуги, які надані Повіріним Доручителю згідно цього договору, а також за усі фактичні витрати, які пов`язані з виконанням Договору, здійснюється згідно рахунків Повіренного та /або Актів виконаних робіт, підписаних між сторонами.', $fontStyle, 'Paragraph_2' );
        $section->addText('3.2. Доручитель завчасно переказує митні платежі згідно розрахункам, наданим Повіреним, у вигляді попереднього платежу шляхом 100% передоплати на рахунок Повіреного або депозитний рахунок митниці оформлення. Сплата митних платежів може бути  проведена  Повіреним та/або Доручителем.', $fontStyle, 'Paragraph_2' );

                
        $section->addText('4. ПОРЯДОК ЗДАЧІ РОБІТ.', $fontStyle, 'Paragraph');
        $section->addText('4.1. Здача робіт підтверджується оформленим Актом виконаних робіт. Якщо рахунок сплачен, роботи фактично виконані, а підписаний Акт виконаних робіт не повернуто Доручителю впродовж 10 днів з часу його виписки,послуги митного брокера з  декларування товарів і транспортних засобів вважаються виконаними у повному обсязі.', $fontStyle, 'Paragraph_2' );

        $section->addText('5. ВІДПОВІДАЛЬНІСТЬ СТОРІН.', $fontStyle, 'Paragraph');
     
        $section->addText('5.1. Загальні положення:', $fontStyle, 'Paragraph_2' );
        $section->addText('5.1.2. Сторона, яка порушила свої договірні обов`язки, повинна швидко ліквідувати ці порушення.', $fontStyle, 'Paragraph_2' );
        $section->addText('5.1.3. Сторона, яка притягує третю особу до виконання своїх обов`язків за договором, несе перед іншою стороною відповідальність за невиконання або неналежне виконання обов`язків, як за особисті дії.', $fontStyle, 'Paragraph_2' );

        $section->addText('5.2. Відповідальність Повіреного:', $fontStyle, 'Paragraph_2' );
        $section->addText('5.2.1. Повірений несе матеріальну відповідальність у розмірі документально підтверджених збитків, заподіяних Доручителю внаслідок неналежного виконання Повіреним умов цього договору.', $fontStyle, 'Paragraph_2' );
        $section->addText('5.2.2. Повірений не несе відповідальність за невиконання умов договору в разі мотивованої відмови державних органів у наданні дозволу, необхідного для митного оформлення вантажу, який належить Доручителю.', $fontStyle, 'Paragraph_2' );
        $section->addText('5.2.3. Співробітник Повіреного (представник митного брокера) на підставі аналізу на відповідність діючому законодавству наданих Доручителем документів має право на прийняття самостійного та мотивованого рішення про можливості чи неможливість надання послуг з декларування товарів і транспортних засобів Доручителя.', $fontStyle, 'Paragraph_2' );

        $section->addText('5.3. Відповідальність Доручителя:', $fontStyle, 'Paragraph_2' );
        $section->addText('5.3.1. Доручитель несе матеріальну відповідальність у розмірі документально підтверджених збитків, заподіяних Повіреному внаслідок неналежного виконання Доручителем умов цього договору.', $fontStyle, 'Paragraph_2' );
        $section->addText('5.3.2. Доручитель несе матеріальну відповідальність у разі невчасного надання Повіреному відомостей, необхідних для складання митної декларації.', $fontStyle, 'Paragraph_2' );
        $section->addText('5.3.3. Доручитель несе матеріальну відповідальність за достовірність і точність відомостей, наданих Повіреному, а також за відповідність документів фактичній номенклатурі вантажів, наданих до митного оформлення.', $fontStyle, 'Paragraph_2' );
                      
        $section->addText('6. ІНШІ УМОВИ.', $fontStyle, 'Paragraph');     
     
        $section->addText('6.1. В усіх випадках при виконанні умов цього договору Сторони керуються положеннями цього договору та нормами діючого законодавства.', $fontStyle, 'Paragraph_2' );
        $section->addText('6.2.Цей Договор діє з моменту його підписання уповноваженими представниками Сторін до '. $date_finish.'р.  та не припиняється до повного виконання обов"язків між сторонами, які виникли до цієї дати.', $fontStyle, 'Paragraph_2' );
        $section->addText('6.3. Умови договору змінюються за письмовим узгодженням сторін шляхом підписання додаткових угод до договору.', $fontStyle, 'Paragraph_2' );
        $section->addText('6.4.	Усі додатки до договору є його невід"ємною частиною.', $fontStyle, 'Paragraph_2' );
                                  
        $section->addText('7. ПОРЯДОК  РОЗВ`ЯЗУВАННЯ СУПЕРЕЧОК.', $fontStyle, 'Paragraph');
        $section->addText('7.1. Усі суперечки по суті виконання сторонами умов договору підлягають регулюванню згідно з діючим законодавством України.', $fontStyle, 'Paragraph_2' );
       
        $section->addText('8. ЗАГАЛЬНІ  УМОВИ  НАДАННЯ ПОСЛУГ З ДЕКЛАРУВАННЯ ТОВАРІВ І ТРАНСПОРТНИХ ЗАСОБІВ.', $fontStyle, 'Paragraph');
        $section->addText('8.1. Вартисть послуг з декларування товарів і транспортних засобів  не враховує суми митних платежів, нарахованих по митної декларації, а також усі витрати, які підлягають сплаті згідно рахункам для отримання дозволів, висновків, сертифікатів та інших документів, необхідних для виконання Договору.', $fontStyle, 'Paragraph_2' );
        $section->addText('8.2. Вартість послуг по доставці інспектора митниці до місця догляду вантажу або відбору зразків не зараховується до вартості послуг та компенсується Доручителем  окремо.', $fontStyle, 'Paragraph_2');     
        $section->addText('8.3. Сума ПДВ, нарахованого за ВМД, сплачується Доручителем самостійно на депозитний рахунок митниці оформлення попередньо готівкою або банківським переказом завчасно до початку митного оформлення.', $fontStyle, 'Paragraph_2' );
        $section->addText('8.4. Доручитель компенсує Повіреному упродовж 3 (трьох) банківських днів з дати  оформлення Актів виконаних робіт (або шляхом передплати згідно рахунків Повіренного) усі надані послуги та фактичні витрати, пов"язані з виконанням своїх обов"язків по цьому  Договору  доручення (у тому числі конвертація іноземної валюти, банківські проценти по обслуговуванню, та інші витрати, які пов"язані з виконанням цього Договору  доручення),  а також у випадку, коли Повірений самостійно сплачує митні платежі, нараховані за митною декларацією.', $fontStyle, 'Paragraph_2' );
        $section->addText('8.5.	Вартість послуг з оформлення митної декларації на митниці у вихідні та офіційні державні свята узгоджується між сторонами окремо.', $fontStyle, 'Paragraph_2' );

        $section->addText('9. ФОРС-МАЖОР.', $fontStyle, 'Paragraph');     
        $section->addText('9.1. Сторони звільняються від відповідальності за часткове або повне невиконання своїх обов"язків згідно цього договору, коли воно є наслідком  обставин  непоборної  сили, тобто: пожежі, землетрусу, паводку та інше. Сторона, для котрої  склалася неспроможність виконання обов"язків по даному договору, повинна  повідомити про начало та закінчення таких обставин негайно  іншу сторону. Коли ці обставини будуть продовжуватися більш як один місяць, то кожна із сторін має  право відмовитися від подальшого виконання обов"язків по договору, та у цьому випадку  кожна із сторін не має права вимагати від іншої сторони відшкодування  збитків. Достатнім  доказом  вказаних обставин та їх  терміну  дії будуть служити свідоцтва Торгово-промислової палати відповідної країни.', $fontStyle, 'Paragraph_2');
        $section->addText('10. ЮРИДИЧНІ АДРЕСИ, РЕКВІЗИТИ ТА ПІДПИСИ  СТОРІН.', $fontStyle, 'Paragraph');



    $table = $section->addTable();
     
    $table->addRow(200);
    $table_styleFont_bold=array('name' => 'Times New Roman', 'size' => 12,'bold'=>TRUE);
    $table_styleFont_normal=array('name' => 'Times New Roman', 'size' => 12,'bold'=>false);
     
    $table->addCell(5000)->addText("ПОВІРЕНИЙ",$table_styleFont_bold);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText("ДОРУЧИТЕЛЬ",$table_styleFont_bold);
    $table->addRow(200);
    $table->addCell(5000)->addText("ФО-П Коржов А.А.",$table_styleFont_normal);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText($company,$table_styleFont_normal);
    $table->addRow(200);
    $table->addCell(5000)->addText("Юридична адреса: 61176, Україна, м.Харків, вул.Єдності, буд. 177, корп. Б, кв.58",$table_styleFont_normal);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText("Юридична адреса:",$table_styleFont_normal);
    $table->addRow(200);
    $table->addCell(5000)->addText("Код ДРФО:   2607014759",$table_styleFont_normal);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText("Код ЄДРПОУ: ",$table_styleFont_normal);
    $table->addRow(200);
    $table->addCell(5000)->addText("Свідоцтво про сплату единого податку  Б № 390917",$table_styleFont_normal);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText("ІПН ",$table_styleFont_normal);
    $table->addRow(200);
	$table->addCell(5000)->addText("АТ ПРАВЕКС БАНК, м. Київ МФО 380838 ",$table_styleFont_normal);
    $table->addRow(200);

	$table->addCell(5000)->addText("IBAN UA  46 3808 3800 0002 6003 7999 70031",$table_styleFont_normal);
    $table->addRow(200);

    $table->addCell(5000)->addText("Телефон: +38 (093) 673-44-88",$table_styleFont_normal);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText("Телефон: ",$table_styleFont_normal);
    $table->addRow(200);
    $table->addCell(5000)->addText("ФО-П А.А. Коржов",$table_styleFont_normal);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText("",$table_styleFont_normal); 

 
	$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007'); 
	$new_ugoda = 'files/ugoda.docx';
	$objWriter->save($new_ugoda);  
	
	$dogovor =$new_ugoda;
	return ($dogovor);
}

// Договор Продление

function dogovor_long_doc ($id){
	
	//Информация о реквизитах договора 
    $arrClient = Client::findOne(['id'=>$id]);

	$dogovor_full = $id."-КБ-".date("m", strtotime($arrClient["date_begin"])).'/'.date("Y", strtotime($arrClient["date_begin"]));  
	$company = $arrClient["client"];   
 	$dogovor= $arrClient["dogovor"];
    $date_begin= $arrClient["date_begin"];
	$date_begin =date('d.m.Y',strtotime($date_begin));
	$date_finish= $arrClient["date_finish"];
	$date_finish =date('d.m.Y',strtotime($date_finish));
	

	
	// Creating the new document...
	$phpWord = new \PhpOffice\PhpWord\PhpWord();


	// Adding an empty Section to the document...


	$section = $phpWord->addSection();


 
	$phpWord->addParagraphStyle('Paragraph', array('bold' => TRUE , 'align' => 'center' ));
	$fontStyle = array('name' => 'Times New Roman', 'size'=>10,'bold' => TRUE );
	$section->addText('ДОДАТКОВА УГОДА ', $fontStyle, 'Paragraph');



	$fontStyle = array('name' => 'Times New Roman', 'size'=>10);
	$section->addText(
	   'до ДОГОВОРУ ДОРУЧЕННЯ  №'.$dogovor_full.' від '. $date_begin .'р. (далі за текстом – «Договор») про надання послуг з декларування товарів і транспортних засобів',
	   $fontStyle, 'Paragraph'
	);



    $fontStyle = array('name' => 'Times New Roman', 'size'=>10);
    $section->addText(
    'м.Харків                                                                                                                 ' . $date_finish ,
    $fontStyle, 'Paragraph'
    );
    
    $phpWord->addParagraphStyle('Paragraph_2', array('bold' => TRUE , 'align' => 'both' ));
 
    $section->addText('           '.$company.', яке є платником податку _______________________ за ___________ ставкою, що визначена п._________ Податкового кодексу України та є (ні є) платником ПДВ, далі за текстом – «ДОРУЧИТЕЛЬ»,  в особі ________________ , який діє на підставі Статуту, з однієї сторони, і фізична особа-підприємець Коржов Андрій Анатолійович, який є платником єдиного податку за ставкою 5% та ні є платником ПДВ, далі за текстом – «ПОВІРЕНИЙ»   (номер запису в Єдиному державному реєстрі  2 480 017 0000 009334 від 23.08.2002 р., номер авторизації на провадження митної брокерської діяльності UACBR125000066 від 28.07.2025), з іньшої сторони, уклали дану Додаткову угоду до Договору про наведене нижче:',
    $fontStyle, 'Paragraph_2' );

 
    
        $section->addText(
         '     1. Сторони вирішили змінити строк дії Договору та домовилися викласти пункт 6.2. Договору в наступній редакції: ',
        $fontStyle, 'Paragraph_2'  );
    
   // Новый срок договора ( продление на 3 года )  
        $date = new DateTime($date_finish);
        $date->modify('+3 year');
       
        $section->addText(
         '    "6.2. Цей Договор діє з моменту його підписання уповноваженими представниками Сторін до '. $date->format('d.m.Y').'р. та не припиняється до повного виконання обов`язків між сторонами, які виникли до цієї дати."',
        $fontStyle, 'Paragraph_2'   );  
 

        $section->addText(
         '    2. Інші умови Договору залишаються  незмінними і Сторони підтверджують по ним свої зобов’язання.',
        $fontStyle, 'Paragraph_2'   );  
    
  
        $section->addText(
         '    3. Дану Додаткову угоду укладено у двох оригінальних примірниках, по одному для кожної із Сторін.',
        $fontStyle, 'Paragraph_2'   );
       
		// Начало действия допсоглашения 
		$date = new DateTime($date_finish);
		$date->modify('+1 day');

        $section->addText(
         '    4. Дана Додаткова угода набирає чинності з '. $date->format('d.m.Y').'р. та є невід’ємною частиною Договору.',
        $fontStyle, 'Paragraph_2'   );




    $table = $section->addTable();
  
    $table->addRow(200);
    $table_styleFont_bold=array('name' => 'Times New Roman', 'size' => 10,'bold'=>TRUE);
    $table_styleFont_normal=array('name' => 'Times New Roman', 'size' => 10,'bold'=>false);
     
    $table->addCell(5000)->addText("ПОВІРЕНИЙ",$table_styleFont_bold);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText("ДОРУЧИТЕЛЬ",$table_styleFont_bold);
    $table->addRow(200);
    $table->addCell(5000)->addText("ФО-П Коржов А.А.",$table_styleFont_normal);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText($company,$table_styleFont_normal);
    $table->addRow(200);
    $table->addCell(5000)->addText("Юридична адреса: 61176, Україна, м.Харків, вул.Єдності, буд. 177, корп. Б, кв.58",$table_styleFont_normal);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText("Юридична адреса:",$table_styleFont_normal);
    $table->addRow(200);
    $table->addCell(5000)->addText("Код ДРФО:   2607014759",$table_styleFont_normal);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText("Код ЄДРПОУ: ",$table_styleFont_normal);
    $table->addRow(200);
    $table->addCell(5000)->addText("Свідоцтво про сплату единого податку  Б № 390917",$table_styleFont_normal);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText("ІПН ",$table_styleFont_normal);
    $table->addRow(200);
	$table->addCell(5000)->addText("АТ ПРАВЕКС БАНК, м. Київ МФО 380838 ",$table_styleFont_normal);
    $table->addRow(200);
	$table->addCell(5000)->addText("IBAN UA  46 3808 3800 0002 6003 7999 70031",$table_styleFont_normal);
    $table->addRow(200);
    $table->addCell(5000)->addText("Телефон: +38 (093) 673-44-88",$table_styleFont_normal);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText("Телефон: ",$table_styleFont_normal);
    $table->addRow(200);
    $table->addCell(5000)->addText("ФО-П А.А. Коржов",$table_styleFont_normal);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText("",$table_styleFont_normal); 
 
	$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007'); 
	$new_ugoda = 'files/ugoda_long.docx';
	$objWriter->save($new_ugoda);  
		
	
	$dogovor_long =$new_ugoda;
	return ($dogovor_long);
}// Договор Продление

function dogovor_mp_doc ($id){

	//Информация о реквизитах договора
    $arrClient = Client::findOne(['id'=>$id]);

	$dogovor_full = $id."-КБ-".date("m", strtotime($arrClient["date_begin"])).'/'.date("Y", strtotime($arrClient["date_begin"]));
	$company = $arrClient["client"];
 	$dogovor= $arrClient["dogovor"];
    $date_begin= $arrClient["date_begin"];
	$date_begin =date('d.m.Y',strtotime($date_begin));
	$date_finish= $arrClient["date_finish"];
	$date_finish =date('d.m.Y',strtotime($date_finish));



	// Creating the new document...
	$phpWord = new \PhpOffice\PhpWord\PhpWord();


	// Adding an empty Section to the document...


	$section = $phpWord->addSection();



	$phpWord->addParagraphStyle('Paragraph', array('bold' => TRUE , 'align' => 'center' ));
	$fontStyle = array('name' => 'Times New Roman', 'size'=>10,'bold' => TRUE );
	$section->addText('ДОДАТКОВА УГОДА ', $fontStyle, 'Paragraph');



	$fontStyle = array('name' => 'Times New Roman', 'size'=>10);
	$section->addText(
	   'до ДОГОВОРУ ДОРУЧЕННЯ  №'.$dogovor_full.' від '. $date_begin .'р. (далі за текстом – «Договор») про надання послуг з декларування товарів і транспортних засобів',
	   $fontStyle, 'Paragraph'
	);

    // Начало действия допсоглашения
    $date = new DateTime();
    $dateFormatted = $date->format('d.m.Y');

    $fontStyle = array('name' => 'Times New Roman', 'size'=>10);
    $section->addText(
    'м.Харків                                                                                                                 ' . $dateFormatted ,
    $fontStyle, 'Paragraph'
    );

    $phpWord->addParagraphStyle('Paragraph_2', array('bold' => TRUE , 'align' => 'both' ));

    $section->addText('           '.$company.', яке є платником податку _______________________ за ___________ ставкою, що визначена п._________ Податкового кодексу України та є (ні є) платником ПДВ, далі за текстом – «ДОРУЧИТЕЛЬ»,  в особі ________________ , який діє на підставі Статуту, з однієї сторони, і фізична особа-підприємець Коржов Андрій Анатолійович, який є платником єдиного податку за ставкою 5% та ні є платником ПДВ, далі за текстом – «ПОВІРЕНИЙ»   (номер запису в Єдиному державному реєстрі  2 480 017 0000 009334 від 23.08.2002 р., номер авторизації на провадження митної брокерської діяльності UACBR125000066 від 28.07.2025), з іньшої сторони, уклали дану Додаткову угоду до Договору про наведене нижче:',
    $fontStyle, 'Paragraph_2' );



        $section->addText(
         '     1. Сторони домовилися:  ',
        $fontStyle, 'Paragraph_2'  );

        $section->addText(
         '    "Згідно з цим договором ПОВІРЕНИЙ діє як прямий митний представник в інтересах і від імені ДОРУЧИТЕЛЯ."',
        $fontStyle, 'Paragraph_2'   );


        $section->addText(
         '    2. Інші умови Договору залишаються  незмінними і Сторони підтверджують по ним свої зобов’язання.',
        $fontStyle, 'Paragraph_2'   );


        $section->addText(
         '    3. Дану Додаткову угоду укладено у двох оригінальних примірниках, по одному для кожної із Сторін.',
        $fontStyle, 'Paragraph_2'   );

        $section->addText(
         '    4. Дана Додаткова угода набирає чинності з '. $dateFormatted .'р. та є невід’ємною частиною Договору.',
        $fontStyle, 'Paragraph_2'   );




    $table = $section->addTable();

    $table->addRow(200);
    $table_styleFont_bold=array('name' => 'Times New Roman', 'size' => 10,'bold'=>TRUE);
    $table_styleFont_normal=array('name' => 'Times New Roman', 'size' => 10,'bold'=>false);

    $table->addCell(5000)->addText("ПОВІРЕНИЙ",$table_styleFont_bold);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText("ДОРУЧИТЕЛЬ",$table_styleFont_bold);
    $table->addRow(200);
    $table->addCell(5000)->addText("ФО-П Коржов А.А.",$table_styleFont_normal);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText($company,$table_styleFont_normal);
    $table->addRow(200);
    $table->addCell(5000)->addText("Юридична адреса: 61176, Україна, м.Харків, вул.Єдності, буд. 177, корп. Б, кв.58",$table_styleFont_normal);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText("Юридична адреса:",$table_styleFont_normal);
    $table->addRow(200);
    $table->addCell(5000)->addText("Код ДРФО:   2607014759",$table_styleFont_normal);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText("Код ЄДРПОУ: ",$table_styleFont_normal);
    $table->addRow(200);
    $table->addCell(5000)->addText("Свідоцтво про сплату единого податку  Б № 390917",$table_styleFont_normal);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText("ІПН ",$table_styleFont_normal);
    $table->addRow(200);
	$table->addCell(5000)->addText("АТ ПРАВЕКС БАНК, м. Київ МФО 380838 ",$table_styleFont_normal);
    $table->addRow(200);
	$table->addCell(5000)->addText("IBAN UA  46 3808 3800 0002 6003 7999 70031",$table_styleFont_normal);
    $table->addRow(200);
    $table->addCell(5000)->addText("Телефон: +38 (093) 673-44-88",$table_styleFont_normal);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText("Телефон: ",$table_styleFont_normal);
    $table->addRow(200);
    $table->addCell(5000)->addText("ФО-П А.А. Коржов",$table_styleFont_normal);
    $table->addCell(200)->addText("");
    $table->addCell(5000)->addText("",$table_styleFont_normal);

	$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
	$new_ugoda = 'files/ugoda_dop_mp.docx';
	$objWriter->save($new_ugoda);


	$dogovor_long =$new_ugoda;
	return ($dogovor_long);
}


//Отчеты Аква ФЛЄКСС

function aq_fl_report($aq_fl_id,$date_from,$date_to) {

//Формирование отчета


//Создаем экземпляр класса электронной таблицы
    $spreadsheet = new Spreadsheet();
	$spreadsheet->setActiveSheetIndex(0);
	
    $sheet = $spreadsheet->getActiveSheet();

	$sheet->getColumnDimension('A')->setAutoSize(true);
  	$sheet->getColumnDimension("B")->setAutoSize(true);
	$sheet->getColumnDimension("C")->setAutoSize(true);
	$sheet->getColumnDimension("D")->setAutoSize(true);

					  $A="A"."1";
                      $B="B"."1";
                      $C="C"."1";
                      $D="D"."1";


   
    $sheet->setCellValue($A,   "Тип" ); 
    $sheet->setCellValue($B,   "Количество"); 
    $sheet->setCellValue($C,   "Цена"); 
    $sheet->setCellValue($D,   "Сумма"); 


	//Стиль ячеек
				$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					  /* 'color' => [
						  'rgb' => 'FF0000'
						] */
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );


// Фон ячеек
$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($C)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($D)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');

if ($aq_fl_id==3) {
	$arrAqFl=Aquaizol::find()->asArray()->where(['between', 'date', $date_from, $date_to])->all();
	$sheet = $spreadsheet->getActiveSheet()->setTitle("Акваизол");
}

if ($aq_fl_id==81) {
	$arrAqFl=Flex::find()->asArray()->where(['between', 'date', $date_from, $date_to])->all();
	$sheet = $spreadsheet->getActiveSheet()->setTitle("ФЛЄКСС");
}

$export=0;
$i_e=0;
$import=0;
$i_i=0;
$broker=0;
$dosmotr=0;
$custom=0;
$fito=0;

foreach ($arrAqFl as $tabl) {
	if ($tabl['ex_im'] =='Экспорт') {
		$export+=$tabl['broker']+$tabl['dosmotr']+$tabl['custom']+$tabl['fito'];
		$broker+=$tabl['broker'];
		$dosmotr+=$tabl['dosmotr'];
		$custom+=$tabl['custom'];
		$fito+=$tabl['fito'];
		$i_e++;
	};
	if ($tabl['ex_im'] =='Импорт') {
	$import+=$tabl['broker']+$tabl['dosmotr']+$tabl['custom']+$tabl['fito'];
		$broker+=$tabl['broker'];
		$dosmotr+=$tabl['dosmotr'];
		$custom+=$tabl['custom'];
		$fito+=$tabl['fito'];
	    $i_i++;
	}
};

$i_All = $i_e+$i_i; // Количество оформлений общее за период

$cost_1= round(($export+$import+$i_All*800)*1.21/$i_All,2); //Цена

$export =$cost_1*$i_e; // Счет за экспорт
$import =$cost_1*$i_i; // Счет за импорт

$ex_im = $export+$import; // Счет общий

$sheet = $spreadsheet->getActiveSheet();
 
if ($i_e !=null) {
					  $A="A"."2";
                      $B="B"."2";
                      $C="C"."2";
                      $D="D"."2";

    $sheet->setCellValue($A,   "Экспорт" ); 
    $sheet->setCellValue($B,   $i_e); 
    $sheet->setCellValue($C,   $cost_1); 
    $sheet->setCellValue($D,   $export); 
	
	//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	

				
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );

	
};

if ($i_i !=null) {
    $col =3;
    if ($i_e == null) {
        $col =2;
    };
    $A="A".$col;
    $B="B".$col;
    $C="C".$col;
    $D="D".$col;


    $sheet->setCellValue($A,   "Импорт" ); 
    $sheet->setCellValue($B,   $i_i); 
    $sheet->setCellValue($C,   $cost_1); 
    $sheet->setCellValue($D,   $import); 
	
		//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	

				
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );

	
	
	
};

if ($i_All !=null){
    $col =3;
    if ($i_e !=null && $i_i !=null) {
        $col =4;
    };
					  $A="A".$col;
                      $B="B".$col;
                      $C="C".$col;
                      $D="D".$col;
					  

    $sheet->setCellValue($A,   "Итого" ); 
    $sheet->setCellValue($B,   $i_All); 
    $sheet->setCellValue($D,   $ex_im); 
	
		//Стиль ячеек
				$styleArray = ([
					 'font' => [
					  //    'name' => 'Arial',
						   'bold' => true,
					   //   'italic' => true,
					  //    'underline' => Font::UNDERLINE_DOUBLE,
						  'strikethrough' => false,
						   'color' => [
							  'rgb' => 'FF0000'
							]  
						],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );	

				
				$styleArray = ([
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				
				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );	
	
}
else {
    $sheet->setCellValue($A,   "Итого" ); 
    $sheet->setCellValue($B,   $i_All);
	
		//Стиль ячеек
				$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					  'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );	
								
				$styleArray = ([
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				
				$sheet->getStyle($B)->applyFromArray( $styleArray );


}

					  $A="A"."6";
                      $B="B"."6";
                      $C="C"."6";
                      $D="D"."6";
					  $E="E"."6";
					  $F="F"."6";
					  $G="G"."6";

	$sheet->setCellValue($A,   "Брокер" ); 
    $sheet->setCellValue($B,   "Досморт"); 
    $sheet->setCellValue($C,   "Таможня"); 
    $sheet->setCellValue($D,   "Фито");
	$sheet->setCellValue($E,   "Расходы");
	$sheet->setCellValue($F,   "Услуги");
	$sheet->setCellValue($G,   "Счет");

//Стиль ячеек
				$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					  /* 'color' => [
						  'rgb' => 'FF0000'
						] */
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );
				$sheet->getStyle($E)->applyFromArray( $styleArray );	
				$sheet->getStyle($F)->applyFromArray( $styleArray );
				$sheet->getStyle($G)->applyFromArray( $styleArray );	
		 
		 


// Фон ячеек
$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($C)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($D)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($E)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($F)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($G)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');

					  $A="A"."7";
                      $B="B"."7";
                      $C="C"."7";
                      $D="D"."7";
					  $E="E"."7";
					  $F="F"."7";
					  $G="G"."7";
					  
	$zatraty =	$broker+$dosmotr+ $custom+ $fito;
	$usligi= 800*($i_e+$i_i);
	$itogo =($zatraty +$usligi)*1.21;

	$sheet->setCellValue($A,   $broker ); 
    $sheet->setCellValue($B,   $dosmotr); 
    $sheet->setCellValue($C,   $custom); 
    $sheet->setCellValue($D,   $fito); 
	$sheet->setCellValue($E,   $zatraty);
	$sheet->setCellValue($F,   $usligi);
	$sheet->setCellValue($G,   $itogo);
	
	//Стиль ячеек
				$styleArray = ([

					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );
				$sheet->getStyle($E)->applyFromArray( $styleArray );	
				$sheet->getStyle($F)->applyFromArray( $styleArray );
				$sheet->getStyle($G)->applyFromArray( $styleArray );	
 
 	
	
	
		// Create a new worksheet called "My Data"
	$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'По контрагентам');

// Attach the "My Data" worksheet as the first worksheet in the Spreadsheet object
	$spreadsheet->addSheet($myWorkSheet, 1);
	$sheet = $spreadsheet->getSheet(1);
	$sheet->getColumnDimension('A')->setAutoSize(true);
  	$sheet->getColumnDimension("B")->setAutoSize(true);
	
	$i=1;
    
					  $A="A".$i;
                      $B="B".$i;
                    
	
	$sheet->setCellValue($A,   "Контрагент" ); 
    $sheet->setCellValue($B,   "Количество оформлений"); 
	//Стиль ячеек
				$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					  /* 'color' => [
						  'rgb' => 'FF0000'
						] */
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );

		 


// Фон ячеек
$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');

$arrC=Contragent::find()->asArray()->all();
	
	$i=2;
	$itogo=0;
	
		foreach ($arrC as $tabl ) {  
			
			if ($aq_fl_id==3) {
				$sum_contr=Aquaizol::find()->where(['between', 'date', $date_from, $date_to])->
				andWhere(['=', 'contragent_id', $tabl['id']])->count();
			}

			if ($aq_fl_id==81) {
				$sum_contr=Flex::find()->where(['between', 'date', $date_from, $date_to])->
				andWhere(['=', 'contragent_id', $tabl['id']])->count();
			}
						
		 if ($sum_contr !=0) {
			 
					   $A="A".$i;
                      $B="B".$i;
			  $sheet->setCellValue($A,   $tabl['contragent'] ); 
			  $sheet->setCellValue($B,   $sum_contr); 
			  
			  	//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );

			  $itogo+=$sum_contr;
			  $i++; 
			  $sum_contr =0;
		 }   
	   
		}
					  $A="A".$i;
                      $B="B".$i;
                    
	
	$sheet->setCellValue($A,   "Итого:" ); 
    $sheet->setCellValue($B,   $itogo); 
		
		//Стиль ячеек
				$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					    'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );

		
		
	// Create a new worksheet called "My Data"
	$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Список деклараций');

// Attach the "My Data" worksheet as the first worksheet in the Spreadsheet object
	$spreadsheet->addSheet($myWorkSheet, 1);
	$sheet = $spreadsheet->getSheet(1);

	$sheet->getColumnDimension('A')->setAutoSize(true);
  	$sheet->getColumnDimension("B")->setAutoSize(true);
	$sheet->getColumnDimension("C")->setAutoSize(true);
	$sheet->getColumnDimension("D")->setAutoSize(true);
	$sheet->getColumnDimension("E")->setAutoSize(true);
	$sheet->getColumnDimension("F")->setAutoSize(true);
	$sheet->getColumnDimension("G")->setAutoSize(true);
	$sheet->getColumnDimension("H")->setAutoSize(true);
	
	 $i=1;
    
					  $A="A".$i;
                      $B="B".$i;
                      $C="C".$i;
                      $D="D".$i;
					  $E="E".$i;
					  $F="F".$i;
					  $G="G".$i;
					  $H="H".$i;
					  
	$sheet->setCellValue($A,   "Дата");	
	$sheet->setCellValue($B,   "Декларация");	
	$sheet->setCellValue($C,   "Контрагент");
	$sheet->setCellValue($D,   "Брокер" ); 
    $sheet->setCellValue($E,   "Досморт"); 
    $sheet->setCellValue($F,   "Таможня"); 
    $sheet->setCellValue($G,   "Фито");
	$sheet->setCellValue($H,   "Экспорт/импорт");
	
	//Стиль ячеек
				$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					  /* 'color' => [
						  'rgb' => 'FF0000'
						] */
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );
				$sheet->getStyle($E)->applyFromArray( $styleArray );	
				$sheet->getStyle($F)->applyFromArray( $styleArray );
				$sheet->getStyle($G)->applyFromArray( $styleArray );	
				$sheet->getStyle($H)->applyFromArray( $styleArray );
		 


// Фон ячеек
$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($C)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($D)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($E)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($F)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($G)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($H)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
	
	
	
	
	Yii\helpers\ArrayHelper::multisort($arrAqFl, ['date'], [SORT_ASC]);
	foreach ($arrAqFl as $tabl) {
	
	    $i++;
					  $A="A".$i;
                      $B="B".$i;
                      $C="C".$i;
                      $D="D".$i;
					  $E="E".$i;
					  $F="F".$i;
					  $G="G".$i;
					  $H="H".$i;
	$arrD=Declaration::find()->asArray()->where(['=', 'id', $tabl["decl_number_id"]])->one();
	$arrC=Contragent::find()->asArray()->where(['=', 'id', $tabl["contragent_id"]])->one();
	
	$sheet->setCellValue($A,   date("d.m.Y", strtotime($tabl['date'])));	
	$sheet->setCellValue($B,   $arrD['decl_number']);
	$sheet->setCellValue($C,   $arrC['contragent']);
	$sheet->setCellValue($D,   $tabl['broker'] ); 
    $sheet->setCellValue($E,   $tabl['dosmotr']); 
    $sheet->setCellValue($F,   $tabl['custom']); 
    $sheet->setCellValue($G,   $tabl['fito']); 
	$sheet->setCellValue($H,   $tabl['ex_im']);
		//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
				$sheet->getStyle($H)->applyFromArray( $styleArray );
				
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				
				$sheet->getStyle($D)->applyFromArray( $styleArray );
				$sheet->getStyle($E)->applyFromArray( $styleArray );	
				$sheet->getStyle($F)->applyFromArray( $styleArray );
				$sheet->getStyle($G)->applyFromArray( $styleArray );	
				
		 

	
					  
					  
};
	    $i++;
                      $C="C".$i;
                      $D="D".$i;
					  $E="E".$i;
					  $F="F".$i;
					  $G="G".$i;
					  
	$sheet->setCellValue($C,   'Итого:');			 
	$sheet->setCellValue($D,   $broker ); 
    $sheet->setCellValue($E,   $dosmotr); 
    $sheet->setCellValue($F,   $custom); 
    $sheet->setCellValue($G,   $fito); 
	
	
	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($C)->applyFromArray( $styleArray );
				$sheet->getStyle($D)->applyFromArray( $styleArray );
				$sheet->getStyle($E)->applyFromArray( $styleArray );	
				$sheet->getStyle($F)->applyFromArray( $styleArray );
				$sheet->getStyle($G)->applyFromArray( $styleArray );	
	
$writer = new Xlsx($spreadsheet);
$writer->save('files/report.xls'); //Расчет за период
}

//Список оформленных деклараций

function DeclReport($date_from,$date_to) {
	

//Формирование отчета


//Создаем экземпляр класса электронной таблицы
    $spreadsheet = new Spreadsheet();

    $spreadsheet->setActiveSheetIndex(0);
	
    $sheet = $spreadsheet->getActiveSheet()->setTitle("Работа");
	
    $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
  	$spreadsheet->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
	$spreadsheet->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
	$spreadsheet->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
	$spreadsheet->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);

					  $A="A"."1";
                      $B="B"."1";
                      $C="C"."1";
                      $D="D"."1";
					  $E="E"."1";
		

	

    $sheet->setCellValue($A,   "Дата"); 
    $sheet->setCellValue($B,   "Номер декларации"); 
    $sheet->setCellValue($C,   "Клиент"); 
	$sheet->setCellValue($D,   "Брокер"); 
	$sheet->setCellValue($E,   "Счет"); 
//Стиль ячеек
				$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					  /* 'color' => [
						  'rgb' => 'FF0000'
						] */
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );
				$sheet->getStyle($E)->applyFromArray( $styleArray );	



// Фон ячеек
$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($C)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($D)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($E)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');


	 $arrClient =Client::find()->asArray()->all();

	$i=1;
	
	foreach (	$arrClient as $value) {
	
		$arrDecl=Declaration::find()->asArray()->where(['between', 'date', $date_from, $date_to])
		->AndWhere(['=', 'client_id', $value['id']])->all();



		

		if ($arrDecl != null){
		 
		 $i_decl=0;
		 foreach ($arrDecl as $tabl) {
		
			// Отсекание лишних записей
			$pos=0;
			/* $findme   = 'UA807'; */
			$findme   = 'UA';
			$pos_O = strpos($tabl["decl_number"],   $findme );
			if ($pos_O !== false) {
				$pos=1;
			}
			
		 
		 if ($pos == 1)  {
			
			$i_decl++;			  
			$i++;			
							  $A="A".$i;
							  $B="B".$i;
							  $C="C".$i;
							  $D="D".$i;
							  $E="E".$i;


			$arrC =Client::find()->asArray()->where(['=', 'id', $tabl["client_id"]])->one();
			$arrU =User::find()->asArray()->where(['=', 'id', $tabl["user_id"]])->one();
			
			$arrD=Declaration::find()->asArray()->where(['=', 'decl_number', $tabl["decl_number"]])->one();
	
			$arrI =Invoice::find()->asArray() ->where(['=', 'decl_id',$arrD['id']])->one();
	
		
			$sheet->setCellValue($A,   date("d.m.Y", strtotime($tabl["date"]))); 
			$sheet->setCellValue($B,   $tabl["decl_number"]); 
			$sheet->setCellValue($C,   $arrC["client"]); 
			$sheet->setCellValue($D,   $arrU["username"]); 
			$sheet->setCellValue($E,   $arrI["id"]); 
			
			//Стиль ячеек

						$styleArray = ([
							'borders' => [
								'allBorders' => [
									'borderStyle' => Border::BORDER_THIN,
									'color' => [
										'rgb' => '808080'
									]
								],
							],
							'alignment' => [
								'horizontal' => Alignment::HORIZONTAL_CENTER,
								'vertical' => Alignment::VERTICAL_CENTER,
								'wrapText' => true,
							]
						]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );
				$sheet->getStyle($E)->applyFromArray( $styleArray );	
				

			
			
			 
		 }
			
			
			
				 

		};

							 
                     

		   if ( $i_decl !=0) { 
							  $i++;
							
							  $B="B".$i;
							  $C="C".$i;
			$sheet->setCellValue($B,   "Итого"); 
			$sheet->setCellValue($C,   $i_decl." оформлений");
			
			//Стиль ячеек

						$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					  /* 'color' => [
						  'rgb' => 'FF0000'
						] */
					],
					
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);

				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
			
				
			
		   }
		 		
		}

	}
	
// Create a new worksheet called "My Data"
	$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'ПРАВЕКС');

// Attach the "My Data" worksheet as the first worksheet in the Spreadsheet object
	$spreadsheet->addSheet($myWorkSheet, 1);
	$sheet = $spreadsheet->getSheet(1);

	$sheet->getColumnDimension('A')->setAutoSize(true);
  	$sheet->getColumnDimension("B")->setAutoSize(true);
	$sheet->getColumnDimension("C")->setAutoSize(true);
	$sheet->getColumnDimension("D")->setAutoSize(true);
	$sheet->getColumnDimension("E")->setAutoSize(true);
	$sheet->getColumnDimension("F")->setAutoSize(true);
    
	$i=1; 
					  $A="A".$i;
                      $B="B".$i;
                      $C="C".$i;
                      $D="D".$i;
					  $E="E".$i;
					  $F="F".$i;
					  $G="G".$i;
			


    $sheet->setCellValue($A,   "Номер"); 
    $sheet->setCellValue($B,   "Дата"); 
    $sheet->setCellValue($C,   "Номер декларации"); 
    $sheet->setCellValue($D,   "Клиент"); 
	$sheet->setCellValue($E,   "Сумма"); 
	$sheet->setCellValue($F,   "Брокер"); 
	$sheet->setCellValue($G,   "Оплата"); 

//Стиль ячеек
				$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					  /* 'color' => [
						  'rgb' => 'FF0000'
						] */
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );
				$sheet->getStyle($E)->applyFromArray( $styleArray );	
				$sheet->getStyle($F)->applyFromArray( $styleArray );
				$sheet->getStyle($G)->applyFromArray( $styleArray );	
		 


// Фон ячеек
$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($C)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($D)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($E)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($F)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($G)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');



	$arrI =Invoice::find()->asArray() ->where(['between', 'date', $date_from, $date_to])->all();
	
   foreach ($arrI as $tabl) {
	 if ($tabl['forma_oplat'] == 'Безнал') {
		$i++; 
					  $A="A".$i;
                      $B="B".$i;
                      $C="C".$i;
                      $D="D".$i;
					  $E="E".$i;
					  $F="F".$i;
					  $G="G".$i;
			

$arrC =Client::find()->asArray()->where(['=', 'id', $tabl["client_id"]])->one();
$arrU =User::find()->asArray()->where(['=', 'id', $tabl["user_id"]])->one();
$arrD=Declaration::find()->asArray()->where(['=', 'id', $tabl["decl_id"]])->one();



    $sheet->setCellValue($A,   $tabl["id"]); 
    $sheet->setCellValue($B,   date("d.m.Y", strtotime($tabl["date"]))); 
    $sheet->setCellValue($C,   $arrD["decl_number"]); 
    $sheet->setCellValue($D,   $arrC["client"]); 
	$sheet->setCellValue($E,   $tabl["cost"]); 
	$sheet->setCellValue($F,   $arrU["username"]); 
	$sheet->setCellValue($G,   $tabl["oplata"]); 

//Стиль ячеек

						$styleArray = ([
							'borders' => [
								'allBorders' => [
									'borderStyle' => Border::BORDER_THIN,
									'color' => [
										'rgb' => '808080'
									]
								],
							],
							'alignment' => [
								'horizontal' => Alignment::HORIZONTAL_CENTER,
								'vertical' => Alignment::VERTICAL_CENTER,
								'wrapText' => true,
							]
						]);




						$sheet->getStyle($A)->applyFromArray( $styleArray );	
						$sheet->getStyle($B)->applyFromArray( $styleArray );
						$sheet->getStyle($C)->applyFromArray( $styleArray );
						$sheet->getStyle($D)->applyFromArray( $styleArray );
						$sheet->getStyle($F)->applyFromArray( $styleArray );
						$sheet->getStyle($G)->applyFromArray( $styleArray );
			
						$styleArray = ([
							'borders' => [
								'allBorders' => [
									'borderStyle' => Border::BORDER_THIN,
									'color' => [
										'rgb' => '808080'
									]
								],
							],
							'alignment' => [
								'horizontal' => Alignment::HORIZONTAL_RIGHT,
								'vertical' => Alignment::VERTICAL_CENTER,
								'wrapText' => true,
							]
						]);	
						$sheet->getStyle($E)->applyFromArray( $styleArray );
						
	 }		 
   };
   
    $i++;
   
   $sum_itog = '=SUM(E2:'.'E'.$i.')';
   $sheet->setCellValue('D'.$i,  'Всего:'); 
						$styleArray = ([	
							'font' => [
							  //    'name' => 'Arial',
								   'bold' => true,
							   //   'italic' => true,
							  //    'underline' => Font::UNDERLINE_DOUBLE,
								  'strikethrough' => false,
								   'color' => [
									  'rgb' => 'FF0000'
									]  
								],
							'borders' => [
								'allBorders' => [
									'borderStyle' => Border::BORDER_THIN,
									'color' => [
										'rgb' => '808080'
									]
								],
							],
							'alignment' => [
								'horizontal' => Alignment::HORIZONTAL_RIGHT,
								'vertical' => Alignment::VERTICAL_CENTER,
								'wrapText' => true,
							]
						]);	
						$sheet->getStyle('D'.$i)->applyFromArray( $styleArray );
						$sheet->getStyle('E'.$i)->applyFromArray( $styleArray );
   
   $sheet->setCellValue('E'.$i, $sum_itog);
 
   

// Create a new worksheet called "My Data"
	$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Наличные');

// Attach the "My Data" worksheet as the first worksheet in the Spreadsheet object
	$spreadsheet->addSheet($myWorkSheet, 2);
	$sheet = $spreadsheet->getSheet(2);
	
   	$sheet->getColumnDimension('A')->setAutoSize(true);
  	$sheet->getColumnDimension("B")->setAutoSize(true);
	$sheet->getColumnDimension("C")->setAutoSize(true);
	$sheet->getColumnDimension("D")->setAutoSize(true);
	$sheet->getColumnDimension("E")->setAutoSize(true);
	$sheet->getColumnDimension("F")->setAutoSize(true);
	
	$i=1; 
					  $A="A".$i;
                      $B="B".$i;
                      $C="C".$i;
                      $D="D".$i;
					  $E="E".$i;
					  $F="F".$i;
					  $G="G".$i;



    $sheet->setCellValue($A,   "Номер"); 
    $sheet->setCellValue($B,   "Дата"); 
    $sheet->setCellValue($C,   "Номер декларации"); 
    $sheet->setCellValue($D,   "Клиент"); 
	$sheet->setCellValue($E,   "Сумма"); 
	$sheet->setCellValue($F,   "Брокер"); 
	$sheet->setCellValue($G,   "Оплата"); 
//Стиль ячеек
				$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					  /* 'color' => [
						  'rgb' => 'FF0000'
						] */
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );
				$sheet->getStyle($E)->applyFromArray( $styleArray );	
				$sheet->getStyle($F)->applyFromArray( $styleArray );
				$sheet->getStyle($G)->applyFromArray( $styleArray );	
		 


// Фон ячеек
$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($C)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($D)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($E)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($F)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($G)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');

      foreach ($arrI as $tabl) {
	 if ($tabl['forma_oplat'] == 'Карта') {
		$i++; 	
					  $A="A".$i;
                      $B="B".$i;
                      $C="C".$i;
                      $D="D".$i;
					  $E="E".$i;
					  $F="F".$i;
					  $G="G".$i;


$arrC =Client::find()->asArray()->where(['=', 'id', $tabl["client_id"]])->one();
$arrU =User::find()->asArray()->where(['=', 'id', $tabl["user_id"]])->one();
$arrD=Declaration::find()->asArray()->where(['=', 'id', $tabl["decl_id"]])->one();



    $sheet->setCellValue($A,   $tabl["id"]); 
    $sheet->setCellValue($B,   date("d.m.Y", strtotime($tabl["date"]))); 
    $sheet->setCellValue($C,   $arrD["decl_number"]); 
    $sheet->setCellValue($D,   $arrC["client"]); 
	$sheet->setCellValue($E,   $tabl["cost"]); 
	$sheet->setCellValue($F,   $arrU["username"]); 
	$sheet->setCellValue($G,   $tabl["oplata"]); 
//Стиль ячеек

						$styleArray = ([
							'borders' => [
								'allBorders' => [
									'borderStyle' => Border::BORDER_THIN,
									'color' => [
										'rgb' => '808080'
									]
								],
							],
							'alignment' => [
								'horizontal' => Alignment::HORIZONTAL_CENTER,
								'vertical' => Alignment::VERTICAL_CENTER,
								'wrapText' => true,
							]
						]);




						$sheet->getStyle($A)->applyFromArray( $styleArray );	
						$sheet->getStyle($B)->applyFromArray( $styleArray );
						$sheet->getStyle($C)->applyFromArray( $styleArray );
						$sheet->getStyle($D)->applyFromArray( $styleArray );
						$sheet->getStyle($F)->applyFromArray( $styleArray );
						$sheet->getStyle($G)->applyFromArray( $styleArray );
			
						$styleArray = ([
							'borders' => [
								'allBorders' => [
									'borderStyle' => Border::BORDER_THIN,
									'color' => [
										'rgb' => '808080'
									]
								],
							],
							'alignment' => [
								'horizontal' => Alignment::HORIZONTAL_RIGHT,
								'vertical' => Alignment::VERTICAL_CENTER,
								'wrapText' => true,
							]
						]);	
						$sheet->getStyle($E)->applyFromArray( $styleArray );


	 }		 
   };
   
   $i++;
   $sum_itog = '=SUM(E2:'.'E'.$i.')';
   
   $sheet->setCellValue('D'.$i,  'Всего:'); 
						$styleArray = ([	
							'font' => [
							  //    'name' => 'Arial',
								   'bold' => true,
							   //   'italic' => true,
							  //    'underline' => Font::UNDERLINE_DOUBLE,
								  'strikethrough' => false,
								   'color' => [
									  'rgb' => 'FF0000'
									]  
								],
							'borders' => [
								'allBorders' => [
									'borderStyle' => Border::BORDER_THIN,
									'color' => [
										'rgb' => '808080'
									]
								],
							],
							'alignment' => [
								'horizontal' => Alignment::HORIZONTAL_RIGHT,
								'vertical' => Alignment::VERTICAL_CENTER,
								'wrapText' => true,
							]
						]);	
						$sheet->getStyle('D'.$i)->applyFromArray( $styleArray );
						$sheet->getStyle('E'.$i)->applyFromArray( $styleArray );
   
   $sheet->setCellValue('E'.$i, $sum_itog);
   
$writer = new Xlsx($spreadsheet);
$writer->save('files/report.xls'); //Расчет за период
}

function messageToBot($message, $chat_id)
{
    $bot = '6235702872:AAFW6QzdfvAILGe0oA9_X7lgx-I9O2w_Vg4';

    $array = array(
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'html'
    );

    $url = 'https://api.telegram.org/bot' . $bot . '/sendMessage';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($array, '', '&'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_exec($ch);
    curl_close($ch);
}

    function aq_fl_documents($aq_fl_id,$date_from,$date_to) {
// Расчет для формирования документов
    if ($aq_fl_id==3) {
        $arrAqFl=Aquaizol::find()->where(['between', 'date', $date_from, $date_to])->all();
        $client = "Акваизол";
    }

    if ($aq_fl_id==81) {
        $arrAqFl=Flex::find()->where(['between', 'date', $date_from, $date_to])->all();
        $client = "ФЛЄКСС";
    }

    foreach ($arrAqFl as $value) (
        $arrId[$value->id] = $value->decl_number_id
    );

 

    $arrDeclaration = Declaration::find()->asArray()->where(['id' => $arrId])->all();


    if ($aq_fl_id==3) {
        $arrAqFl=Aquaizol::find()->where(['between', 'date', $date_from, $date_to])->all();
    }

    if ($aq_fl_id==81) {
        $arrAqFl=Flex::find()->where(['between', 'date', $date_from, $date_to])->all();
    }
    //debug ($arrAquaizol);
    $export=0;
    $i_e=0;
    $md_export=null;


    $import=0;
    $i_i=0;
    $md_import=null;

    foreach ($arrAqFl as $tabl) {
        if ($tabl['ex_im'] =='Экспорт') {
            $export+=$tabl['broker']+$tabl['dosmotr']+$tabl['custom']+$tabl['fito'];
            foreach ($arrDeclaration as $arr) {
                if ($arr['id'] == $tabl['decl_number_id']) {
                    $md_export = $md_export.$arr['decl_number'].' ';
                };
            }

            $i_e++;
        };
        if ($tabl['ex_im'] =='Импорт') {
            $import+=$tabl['broker']+$tabl['dosmotr']+$tabl['custom']+$tabl['fito'];
            foreach ($arrDeclaration as $arr){
                if ($arr['id'] == $tabl['decl_number_id']) {
                    $md_import = $md_import.$arr['decl_number'].' ';
                };
            }
            $i_i++;
        }
    }

    $i_All = $i_e+$i_i; // Количество оформлений общее за период

    if ($i_All !=0) {  // Если есть декларации за период начало условия
        $cost_1 = round(($export+$import+$i_All*800)*1.21/$i_All, 2); //Цена

        $export = $cost_1*$i_e; // Счет за экспорт
        $import = $cost_1*$i_i; // Счет за импорт

//// Счет


//номер и дата договора

        $arrClient = Client::findOne(['id'=>$aq_fl_id]);

        $client = $arrClient["client"];
        $dogovor = $arrClient["dogovor"];
        $dogovor_date = $arrClient["date_begin"];
        $dogovor_date = date('d.m.Y', strtotime($dogovor_date));



// Создание счета экспорт
        if ($i_e !=0) {
            $inputFileName = './templates/ranunok.xlsx';

            $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            $sheet = $spreadsheet->getActiveSheet();

            $decl_text="Экспорт c ". date('d',strtotime($date_from))." по ".date('d',strtotime($date_to));

//Новая декларация
            $model_d = new Declaration();
            $model_d->date = $date_to;
            $model_d->decl_number = $decl_text;

            $model_d->user_id   = Yii::$app->user->id;
            $model_d->client_id = $aq_fl_id;
//		$model_d->decl_iso  = $decl_text;
            $model_d->save();

            Yii::info('Saving Declaration: ' . json_encode($model_d->attributes));
            if ($model_d->save()) {
                Yii::info('Declaration saved with ID: ' . $model_d->id);
                $model_i = new Invoice();
                $model_i->date = $date_to;
                $model_i->decl_id = $model_d->id;
                Yii::info('Setting decl_id for Invoice: ' . $model_d->id);
                $model_i->client_id = $aq_fl_id;
                $model_i->cost = $export;
                $model_i->user_id = 1;
                $model_i->oplata = 'Нет';
                $model_i->forma_oplat = 'Безнал';
                $model_i->save();
                Yii::info('Invoice saved with ID: ' . $model_i->id);
            } else {
                Yii::error('Error saving Declaration: ' . json_encode($model_d->getErrors()));
            }


            $date_from_T= date('d.m.Y',strtotime($date_from));
            $date_to_T = date('d.m.Y',strtotime($date_to));

            $message = "Андрей выставил счет за $date_to_T №: $model_i->id Клиент: $client Сумма: $export грн";
            messageToBot($message, 120352595);
            messageToBot($message, 474748019);

            $B17 = 'Рахунок на оплату № '.$model_i->id.' від '. $date_to_T.'р.';
            $H22 = $client;
            $H25 = "№".$dogovor." від ".$dogovor_date."р.";
            $D29 = "Послуги митного брокера з оформлення експорту товару у період з ".$date_from_T."р" ." по ".$date_to_T."р за МД №№:".$md_export;


            $W29=$i_e;
            $B34 = num2text_ua($export); // Общая стоимость словами
            $AD29 = number_format ($cost_1,2, ',', ' ');//Цена за 1 декларацию
            $AH29 = number_format ($export,2, ',', ' ');//Стоимость за все
            $B33 = 'Всього найменувань 1,'.' на суму '.$AH29.' грн';

            $new_invoice = 'ranunok_'.$model_i->id;


            $sheet->setCellValue("B17", $B17); //Номер счета
            $sheet->setCellValue("H22", $H22); //Клиент
            $sheet->setCellValue("H25", $H25); //Клиент

            $hight_e=$i_e*6+8; // высота строк

            if ($hight_e <=32) {
                $hight_e=32;
            }

            $sheet->getRowDimension('29')->setRowHeight($hight_e);
            $sheet->setCellValue("D29",  $D29); //Товар
            $sheet->setCellValue("W29",  $W29); //Количество
            $sheet->setCellValue("AD29", $AD29); //Цена за 1 декларацию
            $sheet->setCellValue("AH29", $AH29); //Стоимость за все
            $sheet->setCellValue("AH31", $AH29); //Стоимость за все (ИТОГО)

            $sheet->setCellValue("B33", $B33); //Всього найменувань на суму

            $sheet->setCellValue("B34", $B34); //Всего прописью

            $invoice = new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
            $invoice->save($new_invoice.'.xls');

            $drawing = new PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setPath('img/signature.jpg');
            $drawing->setWidth(255);
            $drawing->setCoordinates('K37');
            $drawing->setWorksheet($sheet);

            $invoice_signature = new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
            $invoice_signature->save($new_invoice.'_signature.xls');


    // Акт выполненных работ
    //// Без подписи

            if ($aq_fl_id==3) {
                $inputFileName = './templates/act_aquaizol.xls';
                $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            }

            if ($aq_fl_id==81) {
                $inputFileName = './templates/act_flexx.xls';
                $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            }

            $sheet = $spreadsheet->getActiveSheet();


            $C23 = 'Загальна вартість робіт (послуг) склала '.$B34; // Общая стоимость  словами
            $C11 = 'АКТ надання послуг № '.$model_i->id.' від '. $date_to_T.'р.';

            $new_act = 'act_'.$model_i->id;


            $sheet->setCellValue("C11", $C11); //Номер счета
            $sheet->getRowDimension('19')->setRowHeight($hight_e);
            $sheet->setCellValue("E19", $D29); //Список деклараций

            $sheet->setCellValue("Y19",  $W29); //Количество деклараций
            $sheet->setCellValue("AF19", $AD29); //Цена за 1 декларацию
            $sheet->setCellValue("AK19", $AH29); //Стоимость за все
            $sheet->setCellValue("AK21", $AH29); //Стоимость за все (ИТОГО)
            $sheet->setCellValue("C23", $C23); //Сумма прописью
            $sheet->setCellValue("C34",  $date_to_T); //Дата подписи акта
            $sheet->setCellValue("T34",  $date_to_T); //Дата подписи акта


            $act= new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
            $act->save($new_act.'.xls');

    ////Подписанный

            if ($aq_fl_id==3) {
                $inputFileName =  './templates/act_aquaizol_sign.xls';
                $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            }

            if ($aq_fl_id==81) {
                $inputFileName = './templates/act_flexx_sign.xls';
                $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            }

                $sheet = $spreadsheet->getActiveSheet();


                $C23 = 'Загальна вартість робіт (послуг) склала '.$B34; // Общая стоимость  словами
                $C11 = 'АКТ надання послуг № '.$model_i->id.' від '. $date_to_T.'р.';

               $new_act = 'act_'.$model_i->id;


                $sheet->setCellValue("C11",   $C11); //Номер счета


                $sheet->getRowDimension('19')->setRowHeight($hight_e);
                $sheet->setCellValue("E19",   $D29); //Список деклараций

                $sheet->setCellValue("Y19",   $W29); //Количество деклараций
                $sheet->setCellValue("AF19", $AD29); //Цена за 1 декларацию
                $sheet->setCellValue("AK19", $AH29); //Стоимость за все
                $sheet->setCellValue("AK21", $AH29); //Стоимость за все (ИТОГО)
                $sheet->setCellValue("C23", $C23); //Сумма прописью
                $sheet->setCellValue("C34",  $date_to_T); //Дата подписи акта
                $sheet->setCellValue("T34",  $date_to_T); //Дата подписи акта


                $act= new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
                $act->save($new_act.'_signature.xls');

      //Запись в архив

                          $zip = new ZipArchive();
                          $filename = "files/documents.zip";
                          if ($zip->open($filename, ZipArchive::CREATE)!==TRUE)
                          {
                          exit("Невозможно открыть <$filename>\n");
                          }

                          $zip->addFile($new_invoice.'.xls');
                          $zip->addFile($new_invoice.'_signature.xls');
                          $zip->addFile($new_act.'.xls');
                          $zip->addFile($new_act.'_signature.xls');

                          $zip->close();

                          unlink($new_invoice.'.xls');
                          unlink($new_invoice.'_signature.xls');
                          unlink($new_act.'.xls');
                          unlink($new_act.'_signature.xls');
    //
        $content   = '<b>Выставлен новый счет за '.$date_to_T.'</b></br>'.
                             $B17.'</br>'.
                             'Клиент: '.$client.'</br>'.
                             'Сумма: '.$AH29.'грн</br>'.
                             'Декларация: '.$D29.'</br>'.
                             'Договор '.$H25.'</br>'.
                             '--------------------------------</b></br>'.
                             '<b>Офис on-line. </b>';


                     Yii::$app->mailer->compose()
                    ->setFrom(['sferaved@ukr.net' => 'Офис on-line'])
                    ->setTo(['any26113@gmail.com'])
                    ->setSubject('Новый счет на '.$client)
                    ->setHtmlBody($content)
                  ->send();
    }

// Создание счета импорт
    if ($i_i !=0) {
            $inputFileName = './templates/ranunok.xlsx';
    
	        $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            $sheet = $spreadsheet->getActiveSheet();
   
	        $decl_text="Импорт c ". date('d',strtotime($date_from))." по ".date('d',strtotime($date_to));
	
  	        $model_d = new Declaration();	//Новая декларация
		    $model_d->date = $date_to;
   		    $model_d->decl_number = $decl_text;
			
			$model_d->user_id   = Yii::$app->user->id;
			$model_d->client_id = $aq_fl_id;
	//		$model_d->decl_iso  = $decl_text;
			$model_d->save();

            if ($model_d->save()) {
                $model_i = new Invoice();
                $model_i->date = $date_to;
                $model_i->decl_id = $model_d->id;
                $model_i->client_id = $aq_fl_id;
                $model_i->cost = $import;
                $model_i->user_id = 1;
                $model_i->oplata = 'Нет';
                $model_i->forma_oplat = 'Безнал';
                $model_i->save();
            }


            $date_from_T = date('d.m.Y',strtotime($date_from));
            $date_to_T = date('d.m.Y',strtotime($date_to));

            $message = "Андрей выставил счет за $date_to_T №: $model_i->id Клиент: $client Сумма: $import грн";
            messageToBot($message, 120352595);
            messageToBot($message, 474748019);

            $B17 = 'Рахунок на оплату № '.$model_i->id.' від '. $date_to_T.'р.';
            $H22 = $client;
            $H25 = "№".$dogovor." від ".$dogovor_date."р.";
            $D29 = "Послуги митного брокера з оформлення імпорту товару у період з ".$date_from_T."р" ." по ".$date_to_T."р за МД №№:".$md_import;
    
 
            $W29=$i_i;
            $B34 = num2text_ua($import); // Общая стоимость словами
            $AD29 = number_format ($cost_1,2,',',' ');//Цена за 1 декларацию
            $AH29 = number_format ($import,2,',',' ');//Стоимость за все
            $B33 = 'Всього найменувань 1,'.' на суму '.$AH29.' грн';
           
            $new_invoice = 'ranunok_'.$model_i->id;
            
            
            $sheet->setCellValue("B17",   $B17); //Номер счета
            $sheet->setCellValue("H22",   $H22); //Клиент
            $sheet->setCellValue("H25",  $H25); //Клиент
			$hight_i=$i_i*6+8; // высота строк
			
		 	if ($hight_i <=32) {
				$hight_i=32;
				}
				

			$sheet->getRowDimension('29')->setRowHeight($hight_i); 	
            $sheet->setCellValue("D29",   $D29); //Товар
			$sheet->setCellValue("W29",   $W29); //Количество
            $sheet->setCellValue("AD29", $AD29); //Цена за 1 декларацию
            $sheet->setCellValue("AH29", $AH29); //Стоимость за все
            $sheet->setCellValue("AH31", $AH29); //Стоимость за все (ИТОГО)
           
            $sheet->setCellValue("B33", $B33); //Всього найменувань на суму 

            $sheet->setCellValue("B34", $B34); //Всего прописью
            
            $invoice = new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
            $invoice->save($new_invoice.'.xls');

            $drawing = new PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setPath('img/signature.jpg');
            $drawing->setWidth(255);
            $drawing->setCoordinates('K37');
            $drawing->setWorksheet($sheet);  
     
            $invoice_signature = new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
            $invoice_signature->save($new_invoice.'_signature.xls');

// Акт выполненных работ
//// Без подписи	
	
	if ($aq_fl_id==3) {
		$inputFileName = './templates/act_aquaizol.xls';
		$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
	}

	if ($aq_fl_id==81) {
		$inputFileName = './templates/act_flexx.xls';
		$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
	}	
	
			$sheet = $spreadsheet->getActiveSheet();
            
       
            $C23 = 'Загальна вартість робіт (послуг) склала '.$B34; // Общая стоимость  словами
            $C11 = 'АКТ надання послуг № '.$model_i->id.' від '. $date_to_T.'р.';

           $new_act = 'act_'.$model_i->id;
     
        
            $sheet->setCellValue("C11",   $C11); //Номер счета
			$sheet->getRowDimension('19')->setRowHeight($hight_i); 	
            $sheet->setCellValue("E19",   $D29); //Список деклараций
            $sheet->setCellValue("Y19",   $W29); //Количество деклараций
            $sheet->setCellValue("AF19", $AD29); //Цена за 1 декларацию
            $sheet->setCellValue("AK19", $AH29); //Стоимость за все
            $sheet->setCellValue("AK21", $AH29); //Стоимость за все (ИТОГО)
            $sheet->setCellValue("C23", $C23); //Сумма прописью 
            $sheet->setCellValue("C34",  $date_to_T); //Дата подписи акта   
            $sheet->setCellValue("T34",  $date_to_T); //Дата подписи акта   

 
            $act= new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
            $act->save($new_act.'.xls');
	
////Подписанный	
	
	if ($aq_fl_id==3) {
		$inputFileName =  './templates/act_aquaizol_sign.xls';
		$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
	}

	if ($aq_fl_id==81) {
		$inputFileName = './templates/act_flexx_sign.xls';
		$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
	}	
	
			$sheet = $spreadsheet->getActiveSheet();
            
       
            $C23 = 'Загальна вартість робіт (послуг) склала '.$B34; // Общая стоимость  словами
            $C11 = 'АКТ надання послуг № '.$model_i->id.' від '. $date_to_T.'р.';

           $new_act = 'act_'.$model_i->id;
            
        
            $sheet->setCellValue("C11",   $C11); //Номер счета
			$sheet->getRowDimension('19')->setRowHeight($hight_i); 	
            $sheet->setCellValue("E19",   $D29); //Список деклараций
            $sheet->setCellValue("Y19",   $W29); //Количество деклараций
            $sheet->setCellValue("AF19", $AD29); //Цена за 1 декларацию
            $sheet->setCellValue("AK19", $AH29); //Стоимость за все
            $sheet->setCellValue("AK21", $AH29); //Стоимость за все (ИТОГО)
            $sheet->setCellValue("C23", $C23); //Сумма прописью 
            $sheet->setCellValue("C34",  $date_to_T); //Дата подписи акта   
            $sheet->setCellValue("T34",  $date_to_T); //Дата подписи акта   

 
            $act= new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
            $act->save($new_act.'_signature.xls');	
	
  //Запись в архив

                      $zip = new ZipArchive();
                      $filename = "files/documents.zip";
                      if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) 
                      {
                      exit("Невозможно открыть <$filename>\n");
                      }

                      $zip->addFile($new_invoice.'.xls');
					  $zip->addFile($new_invoice.'_signature.xls');
					  $zip->addFile($new_act.'.xls');
					  $zip->addFile($new_act.'_signature.xls');					  

                      $zip->close();
					  
                      unlink($new_invoice.'.xls'); 
					  unlink($new_invoice.'_signature.xls'); 
					  unlink($new_act.'.xls');
					  unlink($new_act.'_signature.xls');
					  
					  
	
//	$content   = '<b>Выставлен новый счет за '.$date_to_T.'</b></br>'.
//						 $B17.'</br>'.
//						 'Клиент: '.$client.'</br>'.
//						 'Сумма: '.$AH29.'грн</br>'.
//						 'Декларация: '.$D29.'</br>'.
//						 'Договор '.$H25.'</br>'.
//						 '--------------------------------</b></br>'.
//						 '<b>Офис on-line. </b>';
//
//
				 Yii::$app->mailer->compose()
				->setFrom(['sferaved@ukr.net' => 'Офис on-line'])
				->setTo(['any26113@gmail.com'])
				->setSubject('Новый счет на '.$client)
				->setHtmlBody($content)
			  ->send();
			 	
				
				
}

// Доп соглашение по цене

// Creating the new document...
        $phpWord = new \PhpOffice\PhpWord\PhpWord();

/* Note: any element you append to a document must reside inside of a Section. */

// Adding an empty Section to the document...
        $section = $phpWord->addSection();

        $B34 = num2text_ua($cost_1); // Цена за 1 декларацию словами

        if ($aq_fl_id==3) {
            $phpWord->addParagraphStyle('Paragraph', array('bold' => TRUE , 'align' => 'center' ));
            $fontStyle = array('name' => 'Times New Roman', 'size'=>12,'bold' => TRUE );
            $section->addText(
                'ДОДАТКОВА УГОДА', $fontStyle, 'Paragraph'
            );

            $fontStyle = array('name' => 'Times New Roman', 'size'=>12);
            $section->addText(
               'до ДОГОВОРУ ДОРУЧЕННЯ про надання послуг з декларування товарів і транспортних засобів №3-КБ-06/2012 від 06.06.2012',
               $fontStyle, 'Paragraph'
            );


            $fontStyle = array('name' => 'Times New Roman', 'size'=>12);
            $section->addText(
            'м.Харків                                                                                                                    '.$date_to_T,
            $fontStyle, 'Paragraph'
            );
            $phpWord->addParagraphStyle('Paragraph_2', array('bold' => TRUE , 'align' => 'both' ));
            $fontStyle = array('name' => 'Times New Roman', 'size'=>12);
            $section->addText(
                '       ТОВ  “Акваізол” (надалі “ДОРУЧИТЕЛЬ”) в особі директора Файнера Д.І., діючого на підставі Статуту, і ФО-П Коржов А.А. (надалі “ПОВІРЕНИЙ”), що діє на підставі запису в Єдиному державному реєстрі 2 480 017 0000 009334 від 23.08.2002 р. номер авторизації на провадження митної брокерської діяльності UACBR125000066 від 28.07.2025, уклали додаткову угоду про наведене нижче:',
                $fontStyle, 'Paragraph_2'
            );

            $fontStyle = array('name' => 'Times New Roman', 'size'=>12, 'align'=>'both');
                $section->addText(
                 '      1.Вартість послуг з оформлення однієї митної декларації (МД) на митниці  за період з '.$date_from_T.' по '.$date_to_T.' складає '.$AD29.' ('.$B34.') без ПДВ. Всі інші умови договору залишаються незмінними.',
                $fontStyle, 'Paragraph_2'
            );

        $table = $section->addTable();

        $table->addRow(200);
        $table_styleFont_bold=array('name' => 'Times New Roman', 'size' => 12,'bold'=>TRUE);
        $table_styleFont_normal=array('name' => 'Times New Roman', 'size' => 12,'bold'=>false);

        $table->addCell(5000)->addText("ПОВІРЕНИЙ",$table_styleFont_bold);
        $table->addCell(200)->addText("");
        $table->addCell(5000)->addText("ДОРУЧИТЕЛЬ",$table_styleFont_bold);
        $table->addRow(200);
        $table->addCell(5000)->addText("ФО-П Коржов А.А.",$table_styleFont_normal);
        $table->addCell(200)->addText("");
        $table->addCell(5000)->addText('ТОВ  “Акваізол"',$table_styleFont_normal);
        $table->addRow(200);
        $table->addCell(5000)->addText("Юридична адреса: 61176, Україна, м.Харків, вул.Єдності, буд. 177, корп. Б, кв.58",$table_styleFont_normal);
        $table->addCell(200)->addText("");
        $table->addCell(5000)->addText("Юридична адреса: 62371, Харківська обл., с.Подвірки, вул.Сумський шлях ,47-Б",$table_styleFont_normal);
        $table->addRow(200);
        $table->addCell(5000)->addText("Код ДРФО:   2607014759",$table_styleFont_normal);
        $table->addCell(200)->addText("");
        $table->addCell(5000)->addText("Код ЄДРПОУ:   31466053",$table_styleFont_normal);
        $table->addRow(200);
        $table->addCell(5000)->addText("Свідоцтво про сплату единого податку  Б № 390917",$table_styleFont_normal);
        $table->addCell(200)->addText("");
        $table->addCell(5000)->addText("ІПН № 314660520113",$table_styleFont_normal);
        $table->addRow(200);
        $table->addCell(5000)->addText("Телефон: 093 673-44-88",$table_styleFont_normal);
        $table->addCell(200)->addText("");
        $table->addCell(5000)->addText("Телефон: 050 323-71-10",$table_styleFont_normal);
        $table->addRow(200);
        $table->addCell(5000)->addText("ФО-П А.А.Коржов",$table_styleFont_normal);
        $table->addCell(200)->addText("");
        $table->addCell(5000)->addText("Директор Д.І.Файнер",$table_styleFont_normal);
    } else {
            if ($aq_fl_id==81) {
                $phpWord->addParagraphStyle('Paragraph', array('bold' => TRUE , 'align' => 'center' ));
                $fontStyle = array('name' => 'Times New Roman', 'size'=>12,'bold' => TRUE );
                $section->addText(
                    'ДОДАТКОВА УГОДА', $fontStyle, 'Paragraph'
                );

                $fontStyle = array('name' => 'Times New Roman', 'size'=>12);
                $section->addText(
                   'до ДОГОВОРУ ДОРУЧЕННЯ про надання послуг з декларування товарів і транспортних засобів №81-КБ-08/2014 від 05.08.2014',
                   $fontStyle, 'Paragraph'
                );


                $fontStyle = array('name' => 'Times New Roman', 'size'=>12);
                $section->addText(
                'м.Харків                                                                                                                    '.$date_to_T,
                $fontStyle, 'Paragraph'
                );
                $phpWord->addParagraphStyle('Paragraph_2', array('bold' => TRUE , 'align' => 'both' ));
                $fontStyle = array('name' => 'Times New Roman', 'size'=>12);
                $section->addText(
                    '       ТОВ  “ФЛЄКСС” (надалі “ДОРУЧИТЕЛЬ”) в особі директора Бондаренка М.П., діючого на підставі Статуту, і ФО-П Коржов А.А. (надалі “ПОВІРЕНИЙ”), що діє на підставі запису в Єдиному державному реєстрі 2 480 017 0000 009334 від 23.08.2002 р. номер авторизації на провадження митної брокерської діяльності UACBR125000066 від 28.07.2025, уклали додаткову угоду про наведене нижче:',
                    $fontStyle, 'Paragraph_2'
                );

                $fontStyle = array('name' => 'Times New Roman', 'size'=>12, 'align'=>'both');
                $section->addText(
                    '      1.Вартість послуг з оформлення однієї митної декларації (МД) на митниці  за період з '.$date_from_T.' по '.$date_to_T.' складає '.$AD29.'('.$B34.') без ПДВ. Всі інші умови договору залишаються незмінними.',
                    $fontStyle, 'Paragraph_2'
                );

                $table = $section->addTable();

                $table->addRow(200);
                $table_styleFont_bold=array('name' => 'Times New Roman', 'size' => 12, 'bold'=>TRUE);
                $table_styleFont_normal=array('name' => 'Times New Roman', 'size' => 12,'bold'=>false);

                $table->addCell(5000)->addText("ПОВІРЕНИЙ", $table_styleFont_bold);
                $table->addCell(200)->addText("");
                $table->addCell(5000)->addText("ДОРУЧИТЕЛЬ", $table_styleFont_bold);
                $table->addRow(200);
                $table->addCell(5000)->addText("ФО-П Коржов А.А.", $table_styleFont_normal);
                $table->addCell(200)->addText("");
                $table->addCell(5000)->addText('ТОВ “ФЛЄКСС”', $table_styleFont_normal);
                $table->addRow(200);
                $table->addCell(5000)->addText("Юридична адреса: 61176, Україна, м.Харків, вул.Єдності, буд. 177, корп. Б, кв.58",$table_styleFont_normal);
                $table->addCell(200)->addText("");
                $table->addCell(5000)->addText("Юридична адреса: 62371, Харківська обл., с.Подвірки, вул.Сумський шлях ,45-Д, офіс 307",$table_styleFont_normal);
                $table->addRow(200);
                $table->addCell(5000)->addText("Код ДРФО:   2607014759", $table_styleFont_normal);
                $table->addCell(200)->addText("");
                $table->addCell(5000)->addText("Код ЄДРПОУ:   38734484", $table_styleFont_normal);
                $table->addRow(200);
                $table->addCell(5000)->addText("Свідоцтво про сплату единого податку  Б № 390917", $table_styleFont_normal);
                $table->addCell(200)->addText("");
                $table->addCell(5000)->addText("ІПН № 387344820119", $table_styleFont_normal);
                $table->addRow(200);
                $table->addCell(5000)->addText("Телефон: 093 673-44-88", $table_styleFont_normal);
                $table->addCell(200)->addText("");
                $table->addCell(5000)->addText("Телефон: 050 400-94-85", $table_styleFont_normal);
                $table->addRow(200);
                $table->addCell(5000)->addText("ФО-П А.А. Коржов", $table_styleFont_normal);
                $table->addCell(200)->addText("");
                $table->addCell(5000)->addText("Директор М.П.Бондаренко", $table_styleFont_normal);
            }
        };

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $new_ugoda = 'ugoda.docx';
        $objWriter->save($new_ugoda);

        $section->addImage('img/signature.jpg', array('width'=>180, 'align'=>'left'));

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $new_ugoda_signature = 'ugoda_signature.docx';
        $objWriter->save($new_ugoda_signature);

//Запись в архив

        $zip = new ZipArchive();
        $filename = "files/documents.zip";
        if ($zip->open($filename,ZipArchive::CREATE)!==TRUE) {
            exit("Невозможно открыть <$filename>\n");
        }

        $zip->addFile($new_ugoda);
        $zip->addFile($new_ugoda_signature);

        $zip->close();
        unlink($new_ugoda);
        unlink($new_ugoda_signature);
    } // Если есть декларации за период - конец условия
}

//Калькуляция

 function declaration_report($id) {
//Создаем экземпляр класса электронной таблицы
    $spreadsheet = new Spreadsheet();
  
   

    $sheet = $spreadsheet->getActiveSheet();
    $sheet = $spreadsheet->getActiveSheet()->setTitle("Калькуляция");
	
    $sheet->getColumnDimension('A')->setAutoSize(true);
  	$sheet->getColumnDimension('B')->setAutoSize(true);
	
$arrDeclaration=Declaration::find()->asArray()->where(['=', 'id', $id])->one();
$arrClient = Client::find()->asArray()->where(['=', 'id', $arrDeclaration["client_id"]])->one();
    
// Получаем ячейку для которой будем устанавливать стили
$sheet->getStyle('A1:A3')->applyFromArray([
    'font' => [
      'name' => 'Georgia',
      'bold' => true,
      'italic' => false,
 //     'underline' => Font::UNDERLINE_DOUBLE,
      'strikethrough' => false,
    /*   'color' => [
          'rgb' => '808080'
        ] */
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => [
                'rgb' => '808080'
            ]
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true,
    ]
]);


$sheet->getStyle('B1:B3')->applyFromArray([
/*     'font' => [
 //     'name' => 'Century',
     'bold' => true,
      'italic' => false,
 //     'underline' => Font::UNDERLINE_DOUBLE,
      'strikethrough' => false,
      'color' => [
          'rgb' => '808080'
        ]
    ], */
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => [
                'rgb' => '808080'
            ]
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true,
    ]
]);


// Фон ячеек
$sheet->getStyle('A1:A3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('7FFFD4');
$sheet->getStyle('B1:B3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('7FFFD4');


	
    $sheet->setCellValue('A1',   "Декларация"); 
	$sheet->setCellValue('A2',   "Дата"); 
	$sheet->setCellValue('A3',   "Клиент" ); 
	
	
 
    $sheet->setCellValue('B1',   $arrDeclaration["decl_number"]); 
    $sheet->setCellValue('B2',   date('d.m.Y',strtotime($arrDeclaration["date"]))); 
    $sheet->setCellValue('B3',   $arrClient["client"] ); 




	
$col=4; //Счетчик строк
$zatraty=0; //Подсчет затрат
$one_nalog=0; //Подсчет единого налога

$styleArray = ([
    'font' => [
  //    'name' => 'Arial',
       'bold' => true,
      'italic' => true,
  //    'underline' => Font::UNDERLINE_DOUBLE,
      'strikethrough' => false,
      'color' => [
          'rgb' => '0000FF'
        ]
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => [
                'rgb' => '808080'
            ]
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true,
    ]
]);




//Поиск выставленных счетов
$arrInvoice = Invoice::find()->asArray()->where(['=', 'decl_id', $arrDeclaration["id"]])->all();
if ($arrInvoice !=null) {
	foreach ($arrInvoice as $arr) {
		$A="A".$col;
		$B="B".$col;
	


// Фон ячеек
$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');

$sheet->getStyle($A)->applyFromArray( $styleArray );	
$sheet->getStyle($B)->applyFromArray( $styleArray );

	    
        $sheet->setCellValue($B,   $arr["cost"] ); 

		
		$col++;
		
		if ($arr["forma_oplat"]== 'Безнал') {
		$sheet->setCellValue($A,   'Безнал'); 	
		$zatraty+= $arr["cost"]*0.95;
		$one_nalog+= $arr["cost"]*0.05;
		}
		else {
		$sheet->setCellValue($A,   'Карта'); 		
		$zatraty+= $arr["cost"];
		}
	}
}
		$A="A".$col;
		$B="B".$col;
		// Фон ячеек
		$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
		$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
		
	    $sheet->setCellValue($A,   'Единый налог'); 
        $sheet->setCellValue($B,   '-'.$one_nalog ); 
		$col++;

//Поиск расходов в кабинете брокера
$arrCabinet = Cabinet::find()->asArray()->where(['=', 'decl_id', $arrDeclaration["id"]])->all();
if ($arrCabinet !=null) {
	foreach ($arrCabinet as $arr) {
		$A="A".$col;
		$B="B".$col;
		
		// Фон ячеек
		$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
		$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');

		$arrCabinetstatya = Cabinetstatya::find()->asArray()->where(['=', 'id', $arr["coment_id"]])->one();
        $statya = $arrCabinetstatya["statya"];

	    $sheet->setCellValue($A,   $statya); 
        $sheet->setCellValue($B,   '-'.$arr["cost"] ); 
		$col++;
		$zatraty-= $arr["cost"];
	}
	

}
	
//Поиск расходов в отчетах
$arrWorkzatraty = Workzatraty::find()->asArray()->where(['=', 'decl_id', $arrDeclaration["id"]])->all();
if ($arrWorkzatraty !=null) {
	foreach ($arrWorkzatraty as $arr) {
		$A="A".$col;
		$B="B".$col;
		// Фон ячеек
		$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
		$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
		
		$arrWorkstatya = Workstatya::find()->asArray()->where(['=', 'id', $arr["workstatya_id"]])->one();
        $statya = $arrWorkstatya["statya"];

	    $sheet->setCellValue($A,   $statya); 
        $sheet->setCellValue($B,   '-'.$arr["cost"] ); 
		$col++;
		$zatraty-= $arr["cost"];
	}
}

$styleArray = ([

    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => [
                'rgb' => '808080'
            ]
        ],
    ]
]);
		$A = 'A4'.':'.'A'.$col;
		$B = 'B4'.':'.'B'.$col;
		
		$sheet->getStyle($A)->applyFromArray( $styleArray );
		$sheet->getStyle($B)->applyFromArray( $styleArray );

	

//Прибыль

$styleArray = ([
    'font' => [
  //    'name' => 'Arial',
       'bold' => true,
      'italic' => true,
  //    'underline' => Font::UNDERLINE_DOUBLE,
      'strikethrough' => false,
      'color' => [
          'rgb' => 'FF0000'
        ]
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => [
                'rgb' => '808080'
            ]
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true,
    ],

]);



        $A="A".$col;
		$B="B".$col;
		
		$sheet->getStyle($A)->applyFromArray( $styleArray );
		$sheet->getStyle($B)->applyFromArray( $styleArray );
	
 
		
		$sheet->setCellValue($A,   'Прибыль'); 
        $sheet->setCellValue($B,   $zatraty ); 
		
		// Фон ячеек
		$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
		$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');

$writer = new Xlsx($spreadsheet);
$writer->save('files/report.xls'); //Расчет за период	 
 };


//Отчет по затратам

function broker_report($date_from,$date_to,$id) {
	
if ($id != '1' && $id != '2') {
		//Баланс затрат на начало периода 

   $arrZatraty_cab = Cabinet::find() ->asArray()
	                -> where (['=','user_id',$id])
					  -> andWhere(['<', 'date', $date_from])  ->  all ();

$broker =0;
		 
if (isset($arrZatraty_cab)) {
  $statya = Cabinetstatya::find()->asArray()->  all ();

   
	foreach ($arrZatraty_cab as $tabl) {
			
			$cost_minus =0;
			$cost_plus=0;
			  
		foreach ($statya as $tablstatya) {
			 
			if ($tabl["coment_id"] == $tablstatya["id"]) {
				 $minus_plus= $tablstatya ['income'];
			};
		 };
		   
			if ($minus_plus == 'Нет') {
				$cost_minus += $tabl["cost"];
			}
			else {
				$cost_plus += $tabl["cost"];
			}
		   
			$broker +=$cost_plus - $cost_minus;

	}
}
 
	
  //Создаем экземпляр класса электронной таблицы
    $spreadsheet = new Spreadsheet(); 
	$sheet = $spreadsheet->getActiveSheet()->setTitle('Отчет');   
	
	$sheet->getColumnDimension('A')->setAutoSize(true);
  	$sheet->getColumnDimension("B")->setAutoSize(true);
	$sheet->getColumnDimension("C")->setAutoSize(true);
	$sheet->getColumnDimension('D')->setAutoSize(true);
  	$sheet->getColumnDimension("E")->setAutoSize(true);
 
	
	
    $sheet->setCellValue('A1', "Период:");
	$sheet->setCellValue('B1', " с ".date('d.m.Y',strtotime($date_from)));
	$sheet->setCellValue('C1', ' по '.date('d.m.Y',strtotime($date_to)));
	
					  $A="A"."1";
                      $B="B"."1";
                      $C="C"."1";
	
	
	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'ff00f8'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($A)->applyFromArray( $styleArray );
				$sheet->getStyle($B)->applyFromArray( $styleArray );
	
	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'ff00f8'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_LEFT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);			
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
	
	
	
	$sheet->getColumnDimension('A')->setAutoSize(true);
  	$sheet->getColumnDimension("B")->setAutoSize(true);
	$sheet->getColumnDimension("C")->setAutoSize(true);
	$sheet->getColumnDimension("F")->setAutoSize(true);
					$C="C"."3";
                    $D="D"."3";
					$E="E"."3";
    $sheet->setCellValue($C,   "Начало дня: " ); 
    $sheet->setCellValue($D,   $broker); 
	$sheet->setCellValue($E,   "грн"); 
	
	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($C)->applyFromArray( $styleArray );
		 		$sheet->getStyle($D)->applyFromArray( $styleArray );
	
	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_LEFT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);			
				$sheet->getStyle($E)->applyFromArray( $styleArray );	
			
					  $A="A"."4";
                      $B="B"."4";
                      $C="C"."4";
                      $D="D"."4";
                      $E="E"."4";
					  $F="F"."4";


   
    $sheet->setCellValue($A,   "Дата" ); 
    $sheet->setCellValue($B,   "Номер декларации"); 
    $sheet->setCellValue($C,   "Клиент"); 
    $sheet->setCellValue($D,   "Пополнение"); 
	$sheet->setCellValue($E,   "Списание"); 
    $sheet->setCellValue($F,   "Коментарий"); 

//Стиль ячеек
				$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					  /* 'color' => [
						  'rgb' => 'FF0000'
						] */
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );
				$sheet->getStyle($E)->applyFromArray( $styleArray );
				$sheet->getStyle($F)->applyFromArray( $styleArray );	



// Фон ячеек
$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($C)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($D)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($E)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($F)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');


//Затраты за период 

   $arrZatraty_cab = Cabinet::find() ->asArray()
	                -> where (['=','user_id',Yii::$app->user->id])
					  -> andWhere(['between', 'date', $date_from, $date_to])  ->  all ();
	//  	 debug ($arrZatraty_cab);	

  $cost_plus_itogo=0; 
  $cost_minus_itogo=0;

if (isset($arrZatraty_cab)) {
  $statya = Cabinetstatya::find()->asArray()->  all ();

    $i=5;
   
	foreach ($arrZatraty_cab as $tabl) {
			
			$cost_minus =0;
			$cost_plus=0;
			  
					$A="A".$i;
                    $B="B".$i;
                    $C="C".$i;
                    $D="D".$i;
                    $E="E".$i;
					$F="F".$i;


   	$arrDCl = Declaration::find()->asArray ()->where(['=','id', $tabl["decl_id"]])->one(); 
	$arrCl = Client::find()->where(['=','id', $tabl["client_id"]])->one(); 
	$arrCS = Cabinetstatya::find()->asArray()->where(['=','id', $tabl["coment_id"]])->one(); 

    $sheet->setCellValue($A,   date('d.m.Y',strtotime($tabl["date"])) ); 
    $sheet->setCellValue($B,   $arrDCl["decl_number"]); 
    $sheet->setCellValue($C,   $arrCl["client"]); 
	if ($arrCS['income'] == "Да") {
		$sheet->setCellValue($D,   $tabl["cost"]); 
	}
    else {
		$sheet->setCellValue($E,  "-". $tabl["cost"]); 
	}
	
    $sheet->setCellValue($F,   $arrCS["statya"]); 
	  
	//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
		    	$sheet->getStyle($E)->applyFromArray( $styleArray );	
				$sheet->getStyle($F)->applyFromArray( $styleArray );
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				
				$sheet->getStyle($D)->applyFromArray( $styleArray );
				$sheet->getStyle($E)->applyFromArray( $styleArray );


		
		foreach ($statya as $tablstatya) {
			 
			if ($tabl["coment_id"] == $tablstatya["id"]) {
				 $minus_plus= $tablstatya ['income'];
			};
		 };
		   
			if ($minus_plus == 'Нет') {
				$cost_minus += $tabl["cost"];
				$cost_minus_itogo+=$tabl["cost"];
			}
			else {
				$cost_plus += $tabl["cost"];
				$cost_plus_itogo+=$tabl["cost"];
			}
		   
			$broker +=$cost_plus - $cost_minus;
	$i++;
	}
	
	
}  				
				$C="C".$i;
                $D="D".$i;
				$E="E".$i;
    $sheet->setCellValue($C,   "итого за период: " ); 
    $sheet->setCellValue($D,    $cost_plus_itogo); 
	$sheet->setCellValue($E,   "-".$cost_minus_itogo); 
	
	$styleArray = (['font' => [
					   'bold' => true,
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
	$sheet->getStyle($C)->applyFromArray( $styleArray );
				
				
				

	
	$i++;
	
				$C="C".$i;
                $D="D".$i;
				$E="E".$i;
    $sheet->setCellValue($C,   "Конец дня: " ); 
    $sheet->setCellValue($D,   $broker); 
	$sheet->setCellValue($E,   "грн"); 


	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($C)->applyFromArray( $styleArray );
				$sheet->getStyle($D)->applyFromArray( $styleArray );
	
	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_LEFT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);			
				$sheet->getStyle($E)->applyFromArray( $styleArray );	



//Подсчет итогов по статьям

if (isset($arrZatraty_cab)) {
  $statya = Cabinetstatya::find()->asArray()->  all ();

				$i++;$i++;
				$A="A".$i;
				$B="B".$i;
	$sheet->setCellValue($A,   "Итоги по статьям" ); 
	$sheet->setCellValue($B,   "грн" ); 
	//Стиль ячеек
				$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					  /* 'color' => [
						  'rgb' => 'FF0000'
						] */
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );




// Фон ячеек
$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');

	foreach ( $statya as $tabl) {
		$Zatraty_cab_statya=0;	
		$arrZatraty_cab_statya = Cabinet::find() ->asArray()
					-> where (['=','user_id',Yii::$app->user->id])
					-> andWhere(['between', 'date', $date_from, $date_to])
					-> andWhere(['=', 'coment_id', $tabl['id']])->  all();
		
		foreach ( $arrZatraty_cab_statya as $tabl_z) {
			$Zatraty_cab_statya+= $tabl_z['cost'];
		}	
		
		if ($Zatraty_cab_statya !=0) {
			$i++;
				$A="A".$i;
				$B="B".$i;
			$sheet->setCellValue($A,   $tabl['statya'] );
			$sheet->setCellValue($B,   $Zatraty_cab_statya); 	
			//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);

				$sheet->getStyle($A)->applyFromArray( $styleArray );	

				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($B)->applyFromArray( $styleArray );
	
			
    	}		
	
	}
	}	
	}
	else {
		
		$arrCabinet_bal =cabinet_bal();	
		if ($arrCabinet_bal !=null) {
			$arrUserId = array_keys ($arrCabinet_bal); //Список ID имен брокеров у которых есть записи в кабинете
			$arrUS = User::find()->asArray()->where(['id'=>$arrUserId])->all();
			$arrUserNames = ArrayHelper::map($arrUS, 'id', 'username');
	 
			
			  //Создаем экземпляр класса электронной таблицы
			 	$spreadsheet = new Spreadsheet(); 
	
		};
 
		$i=0;
		foreach ($arrUserId as $tabl_r) {
		
	  if ($i!=0) {
		  // Create a new worksheet called "My Data"
				$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet,$arrUserNames [$tabl_r] );

			// Attach the "My Data" worksheet as the first worksheet in the Spreadsheet object
				$spreadsheet->addSheet($myWorkSheet, 0);
				$sheet = $spreadsheet->getSheet(0);
	  }
	  else
	  {
		  $sheet = $spreadsheet->getActiveSheet()->setTitle($arrUserNames [$tabl_r]);  
	  }
		
				$i++;

				$sheet->getColumnDimension('A')->setAutoSize(true);
				$sheet->getColumnDimension("B")->setAutoSize(true);
				$sheet->getColumnDimension("C")->setAutoSize(true);
				$sheet->getColumnDimension('D')->setAutoSize(true);
				$sheet->getColumnDimension("E")->setAutoSize(true);
		
		
		//Баланс затрат на начало периода 

   $arrZatraty_cab = Cabinet::find() ->asArray() 
	                -> where (['=','user_id',$tabl_r]) 
					   -> andWhere(['<', 'date', $date_from])   ->  all ();
//debug ($arrZatraty_cab);
$broker =0;
		 
if (isset($arrZatraty_cab)) {
  $statya = Cabinetstatya::find()->asArray()->  all ();

   
	foreach ($arrZatraty_cab as $tabl) {
			
			$cost_minus =0;
			$cost_plus=0;
			  
		foreach ($statya as $tablstatya) {
			 
			if ($tabl["coment_id"] == $tablstatya["id"]) {
				 $minus_plus= $tablstatya ['income'];
			};
		 };
		   
			if ($minus_plus == 'Нет') {
				$cost_minus += $tabl["cost"];
			}
			else {
				$cost_plus += $tabl["cost"];
			}
		   
			$broker +=$cost_plus - $cost_minus;

	}
}
 
	

	
	
    $sheet->setCellValue('A1', "Период:");
	$sheet->setCellValue('B1', " с ".date('d.m.Y',strtotime($date_from)));
	$sheet->setCellValue('C1', ' по '.date('d.m.Y',strtotime($date_to)));
	
					  $A="A"."1";
                      $B="B"."1";
                      $C="C"."1";
	
	
	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'ff00f8'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($A)->applyFromArray( $styleArray );
				$sheet->getStyle($B)->applyFromArray( $styleArray );
	
	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'ff00f8'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_LEFT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);			
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
	
	
	
	$sheet->getColumnDimension('A')->setAutoSize(true);
  	$sheet->getColumnDimension("B")->setAutoSize(true);
	$sheet->getColumnDimension("C")->setAutoSize(true);
	$sheet->getColumnDimension("F")->setAutoSize(true);
					$C="C"."3";
                    $D="D"."3";
					$E="E"."3";
    $sheet->setCellValue($C,   "Начало дня: " ); 
    $sheet->setCellValue($D,   $broker); 
	$sheet->setCellValue($E,   "грн"); 
	
	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($C)->applyFromArray( $styleArray );
				$sheet->getStyle($D)->applyFromArray( $styleArray );
	
	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_LEFT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);			
				$sheet->getStyle($E)->applyFromArray( $styleArray );	
	
	
	
	
					  $A="A"."4";
                      $B="B"."4";
                      $C="C"."4";
                      $D="D"."4";
                      $E="E"."4";
					  $F="F"."4";

   
    $sheet->setCellValue($A,   "Дата" ); 
    $sheet->setCellValue($B,   "Номер декларации"); 
    $sheet->setCellValue($C,   "Клиент"); 
    $sheet->setCellValue($D,   "Пополнение"); 
	$sheet->setCellValue($E,   "Списание"); 
    $sheet->setCellValue($F,   "Коментарий"); 
	
//Стиль ячеек
				$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					  /* 'color' => [
						  'rgb' => 'FF0000'
						] */
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );
				$sheet->getStyle($E)->applyFromArray( $styleArray );	
				$sheet->getStyle($F)->applyFromArray( $styleArray );


// Фон ячеек
$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($C)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($D)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($E)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($F)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');


//Затраты за период 

   $arrZatraty_cab = Cabinet::find() ->asArray()
	                -> where (['=','user_id',$tabl_r])
					  -> andWhere(['between', 'date', $date_from, $date_to])  ->  all ();
	//  	 debug ($arrZatraty_cab);	

  $cost_plus_itogo=0; 
  $cost_minus_itogo=0;
  
if (isset($arrZatraty_cab)) {
  $statya = Cabinetstatya::find()->asArray()->  all ();

    $i=5;
   
	foreach ($arrZatraty_cab as $tabl) {
			
			$cost_minus =0;
			$cost_plus=0;
			  
					$A="A".$i;
                    $B="B".$i;
                    $C="C".$i;
                    $D="D".$i;
                    $E="E".$i;
					$F="F".$i;

   	$arrDCl = Declaration::find()->asArray ()->where(['=','id', $tabl["decl_id"]])->one(); 
	$arrCl = Client::find()->where(['=','id', $tabl["client_id"]])->one(); 
	$arrCS = Cabinetstatya::find()->asArray()->where(['=','id', $tabl["coment_id"]])->one(); 

	
    $sheet->setCellValue($A,   date('d.m.Y',strtotime($tabl["date"])) ); 
    $sheet->setCellValue($B,   $arrDCl["decl_number"]); 
    $sheet->setCellValue($C,   $arrCl["client"]); 
    	if ($arrCS['income'] == "Да") {
		$sheet->setCellValue($D,   $tabl["cost"]); 
	}
    else {
		$sheet->setCellValue($E,  "-". $tabl["cost"]); 
	}
	
    $sheet->setCellValue($F,   $arrCS["statya"]); 
	  
	//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
		    	$sheet->getStyle($E)->applyFromArray( $styleArray );	
				$sheet->getStyle($F)->applyFromArray( $styleArray );				
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				
				$sheet->getStyle($D)->applyFromArray( $styleArray );
				$sheet->getStyle($E)->applyFromArray( $styleArray );

			  
		foreach ($statya as $tablstatya) {
			 
			if ($tabl["coment_id"] == $tablstatya["id"]) {
				 $minus_plus= $tablstatya ['income'];
			};
		 };
		   
			if ($minus_plus == 'Нет') {
				$cost_minus += $tabl["cost"];
				$cost_minus_itogo+=$tabl["cost"];
			}
			else {
				$cost_plus += $tabl["cost"];
				$cost_plus_itogo+=$tabl["cost"];
			}
		   
			$broker +=$cost_plus - $cost_minus;
	$i++;
	}
	
	
}

				$C="C".$i;
                $D="D".$i;
				$E="E".$i;
    $sheet->setCellValue($C,   "итого за период: " ); 
    $sheet->setCellValue($D,    $cost_plus_itogo); 
	$sheet->setCellValue($E,   "-".$cost_minus_itogo); 
	
	$styleArray = (['font' => [
					   'bold' => true,
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
	$sheet->getStyle($C)->applyFromArray( $styleArray );
				
				
				

	
	$i++;
				$C="C".$i;
                $D="D".$i;
				$E="E".$i;
    $sheet->setCellValue($C,   "Конец дня: " ); 
    $sheet->setCellValue($D,   $broker); 
	$sheet->setCellValue($E,   "грн"); 


	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($C)->applyFromArray( $styleArray );
				$sheet->getStyle($D)->applyFromArray( $styleArray );
	
	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_LEFT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);			
				$sheet->getStyle($E)->applyFromArray( $styleArray );	



//Подсчет итогов по статьям

if (isset($arrZatraty_cab)) {
  $statya = Cabinetstatya::find()->asArray()->  all ();

				$i++;$i++;
				$A="A".$i;
				$B="B".$i;
	$sheet->setCellValue($A,   "Итоги по статьям" ); 
	$sheet->setCellValue($B,   "грн" ); 
	//Стиль ячеек
				$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					  /* 'color' => [
						  'rgb' => 'FF0000'
						] */
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );




// Фон ячеек
$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');

	foreach ( $statya as $tabl) {
		$Zatraty_cab_statya=0;	
		$arrZatraty_cab_statya = Cabinet::find() ->asArray()
					-> where (['=','user_id',$tabl_r])
					-> andWhere(['between', 'date', $date_from, $date_to])
					-> andWhere(['=', 'coment_id', $tabl['id']])->  all();
		
		foreach ( $arrZatraty_cab_statya as $tabl_z) {
			$Zatraty_cab_statya+= $tabl_z['cost'];
		}	
		
		if ($Zatraty_cab_statya !=0) {
			$i++;
				$A="A".$i;
				$B="B".$i;
			$sheet->setCellValue($A,   $tabl['statya'] );
			$sheet->setCellValue($B,   $Zatraty_cab_statya); 	
			//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);

				$sheet->getStyle($A)->applyFromArray( $styleArray );	

				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($B)->applyFromArray( $styleArray );
	
			
    	}		
	
	}
	}
		
			
		}
		
	}


    $report = new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
    $report->save('files/broker.xls');

}


//Отчет по затратам ДОМ

function home_report($date_from,$date_to) {
	

		//Баланс затрат на начало периода 

   $arrZatraty_cab = Homezatraty::find() ->asArray()-> andWhere(['<', 'date', $date_from])  ->  all ();

 //debug ($arrZatraty_cab);
$broker =0;
		 
if (isset($arrZatraty_cab)) {
  $statya = HomestatyaModel::find()->asArray()->  all ();
//debug ($statya);
   
	foreach ($arrZatraty_cab as $tabl) {
			
			$cost_minus =0;
			$cost_plus=0;
			  
		foreach ($statya as $tablstatya) {
			 
			if ($tabl["statya"] == $tablstatya["id"]) {
				 $minus_plus= $tablstatya ['income'];
			};
		 };
		   
			if ($minus_plus == 'Нет') {
				$cost_minus += $tabl["cost"];
			}
			else {
				$cost_plus += $tabl["cost"];
			}
		   
			$broker +=$cost_plus - $cost_minus;

	}
}
 
	
  //Создаем экземпляр класса электронной таблицы
    $spreadsheet = new Spreadsheet(); 
	$sheet = $spreadsheet->getActiveSheet()->setTitle('ДОМ');   
	
	$sheet->getColumnDimension('A')->setAutoSize(true);
  	$sheet->getColumnDimension("B")->setAutoSize(true);
	$sheet->getColumnDimension("C")->setAutoSize(true);
	$sheet->getColumnDimension('D')->setAutoSize(true);
  	$sheet->getColumnDimension("E")->setAutoSize(true);
 
	
	
    $sheet->setCellValue('A1', "Период:");
	$sheet->setCellValue('B1', " с ".date('d.m.Y',strtotime($date_from)));
	$sheet->setCellValue('C1', ' по '.date('d.m.Y',strtotime($date_to)));
	
					  $A="A"."1";
                      $B="B"."1";
                      $C="C"."1";
	
	
	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'ff00f8'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($A)->applyFromArray( $styleArray );
				$sheet->getStyle($B)->applyFromArray( $styleArray );
	
	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'ff00f8'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_LEFT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);			
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
	
	
	
	$sheet->getColumnDimension('A')->setAutoSize(true);
  	$sheet->getColumnDimension("B")->setAutoSize(true);
	$sheet->getColumnDimension("C")->setAutoSize(true);
	$sheet->getColumnDimension("F")->setAutoSize(true);
					$C="C"."3";
                    $D="D"."3";
					$E="E"."3";
    $sheet->setCellValue($C,   "Начало дня: " ); 
    $sheet->setCellValue($D,   $broker); 
	$sheet->setCellValue($E,   "грн"); 
	
	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($C)->applyFromArray( $styleArray );
		 		$sheet->getStyle($D)->applyFromArray( $styleArray );
	
	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_LEFT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);			
				$sheet->getStyle($E)->applyFromArray( $styleArray );	
			
					  $A="A"."4";
                      $B="B"."4";
                      $C="C"."4";
                      $D="D"."4";
                      $E="E"."4";
	   
    $sheet->setCellValue($A,   "Дата" ); 
    $sheet->setCellValue($B,   "Статья"); 
    $sheet->setCellValue($C,   "Пополнение"); 
	$sheet->setCellValue($D,   "Списание"); 
    $sheet->setCellValue($E,   "Коментарий"); 

//Стиль ячеек
				$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					  /* 'color' => [
						  'rgb' => 'FF0000'
						] */
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );
				$sheet->getStyle($E)->applyFromArray( $styleArray );
 
// Фон ячеек
$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($C)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($D)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($E)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');


//Затраты за период 

   $arrZatraty_cab =Homezatraty::find() ->asArray()
					  -> andWhere(['between', 'date', $date_from, $date_to])  ->  all ();
	//  	 debug ($arrZatraty_cab);	

  $cost_plus_itogo=0; 
  $cost_minus_itogo=0;

if (isset($arrZatraty_cab)) {
  $statya = HomestatyaModel::find()->asArray()->  all ();

    $i=5;
   
	foreach ($arrZatraty_cab as $tabl) {
			
			$cost_minus =0;
			$cost_plus=0;
			  
					$A="A".$i;
                    $B="B".$i;
                    $C="C".$i;
                    $D="D".$i;
                    $E="E".$i;
	
  $arrCS = HomestatyaModel::find()->asArray()->where(['=','id', $tabl["statya"]])->one(); 
 	

    $sheet->setCellValue($A,   date('d.m.Y',strtotime($tabl["date"])) ); 
    $sheet->setCellValue($B,   $arrCS["statya"]); 

	if ($arrCS['income'] == "Да") {
		$sheet->setCellValue($C,   $tabl["cost"]); 
	}
    else {
		$sheet->setCellValue($D,  "-". $tabl["cost"]); 
	}
	
    $sheet->setCellValue($E,   $tabl["comment"]); 
	  
	//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
		    	$sheet->getStyle($D)->applyFromArray( $styleArray );	
		//		$sheet->getStyle($E)->applyFromArray( $styleArray );
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($C)->applyFromArray( $styleArray );
				$sheet->getStyle($D)->applyFromArray( $styleArray );
				


		
		foreach ($statya as $tablstatya) {
			 
			if ($tabl["statya"] == $tablstatya["id"]) {
				 $minus_plus= $tablstatya ['income'];
			};
		 };
		   
			if ($minus_plus == 'Нет') {
				$cost_minus += $tabl["cost"];
				$cost_minus_itogo+=$tabl["cost"];
			}
			else {
				$cost_plus += $tabl["cost"];
				$cost_plus_itogo+=$tabl["cost"];
			}
		   
			$broker +=$cost_plus - $cost_minus;
	$i++;
	}
	
	
}  				
				$B="B".$i;
                $C="C".$i;
				$D="D".$i;
    $sheet->setCellValue($B,   "итого за период: " ); 
    $sheet->setCellValue($C,    $cost_plus_itogo); 
	$sheet->setCellValue($D,   "-".$cost_minus_itogo); 
	
	$styleArray = (['font' => [
					   'bold' => true,
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
	$sheet->getStyle($B)->applyFromArray( $styleArray );
				
				
				

	
	$i++;
	
				$C="C".$i;
                $D="D".$i;
				$E="E".$i;
    $sheet->setCellValue($C,   "Конец дня: " ); 
    $sheet->setCellValue($D,   $broker); 
	$sheet->setCellValue($E,   "грн"); 


	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($C)->applyFromArray( $styleArray );
				$sheet->getStyle($D)->applyFromArray( $styleArray );
	
	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_LEFT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);			
				$sheet->getStyle($E)->applyFromArray( $styleArray );	



//Подсчет итогов по статьям

if (isset($arrZatraty_cab)) {
  $statya = HomestatyaModel::find()->asArray()->  all ();

				$i++;$i++;
				$A="A".$i;
				$B="B".$i;
	$sheet->setCellValue($A,   "Итоги по статьям" ); 
	$sheet->setCellValue($B,   "грн" ); 
	//Стиль ячеек
				$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					  /* 'color' => [
						  'rgb' => 'FF0000'
						] */
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );




// Фон ячеек
$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');

	foreach ( $statya as $tabl) {
		$Zatraty_cab_statya=0;	
		$arrZatraty_cab_statya = Homezatraty::find() ->asArray()
					-> andWhere(['between', 'date', $date_from, $date_to])
					-> andWhere(['=', 'statya', $tabl['id']])->  all();
		
		foreach ( $arrZatraty_cab_statya as $tabl_z) {
			$Zatraty_cab_statya+= $tabl_z['cost'];
		}	
		
		if ($Zatraty_cab_statya !=0) {
			$i++;
				$A="A".$i;
				$B="B".$i;
			$sheet->setCellValue($A,   $tabl['statya'] );
			$sheet->setCellValue($B,   $Zatraty_cab_statya); 	
			//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);

				$sheet->getStyle($A)->applyFromArray( $styleArray );	

				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($B)->applyFromArray( $styleArray );
	
			
    	}		
	
	}
	}	
	 

    $report = new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
    $report->save('files/home.xls');

}









//Отчет по договорам

function dogovor_report() {
	
	$date = date_create();
	date_date_set($date,  date('Y'),date('m')+ 1, date('d'));  

	$date_to=date_format($date, 'Y-m-d');

	$arrClient = Client::find() ->asArray()
	                -> where(['<=', 'date_finish', $date_to]) ->  all ();

return ($arrClient);

}

function dogovor_file() {
 
 $arrClient= dogovor_report();
 
  //Создаем экземпляр класса электронной таблицы
    $spreadsheet = new Spreadsheet(); 
	$sheet = $spreadsheet->getActiveSheet()->setTitle('Продление');   
	
    $sheet->setCellValue('A1', "Необходимо продлить:");
	
					  $A="A"."3";
                      $B="B"."3";
                      $C="C"."3";
                      $D="D"."3";
                      $E="E"."3";


   
    $sheet->setCellValue($A,   "Код" ); 
    $sheet->setCellValue($B,   "Клиент"); 
    $sheet->setCellValue($C,   "Договор"); 
    $sheet->setCellValue($D,   "Начало"); 
    $sheet->setCellValue($E,   "Конец"); 
	
	//Стиль ячеек
				$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					  /* 'color' => [
						  'rgb' => 'FF0000'
						] */
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );
				$sheet->getStyle($E)->applyFromArray( $styleArray );	



// Фон ячеек
$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($C)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($D)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($E)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');

	
	
	
	$i=4;
	
    $sheet->getColumnDimension('A')->setAutoSize(true);
  	$sheet->getColumnDimension("B")->setAutoSize(true);
	$sheet->getColumnDimension("C")->setAutoSize(true);
	$sheet->getColumnDimension("D")->setAutoSize(true);
	$sheet->getColumnDimension("E")->setAutoSize(true);
	
	foreach ($arrClient as $tabl) {
		  
					$A="A".$i;
                    $B="B".$i;
                    $C="C".$i;
                    $D="D".$i;
                    $E="E".$i;

	
    $sheet->setCellValue($A,   $tabl["cod_EGRPOU"]); 
    $sheet->setCellValue($B,   $tabl["client"]); 
    $sheet->setCellValue($C,   $tabl["dogovor"]); 
    $sheet->setCellValue($D,   date('d.m.Y',strtotime($tabl["date_begin"]))); 
    $sheet->setCellValue($E,   date('d.m.Y',strtotime($tabl["date_finish"]))); 

	//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );
				$sheet->getStyle($D)->applyFromArray( $styleArray );				
		    	$sheet->getStyle($E)->applyFromArray( $styleArray );	



	$i++;
	}
	

    $report = new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
    $report->save('files/dogovor.xls');

}

//Список неоплаченных счетов
function invoice_file($id) {
 
 if ($id==1 || $id==2) {
	$arrInvoice=Invoice::find()->asArray()->where(['=','oplata','Нет'])->all();
	
	if ($arrInvoice != null) {
// Обший список неоплаченных счетов

$invoice_itogo =0;
	
 // debug ($invoice);
 
  //Создаем экземпляр класса электронной таблицы
    $spreadsheet = new Spreadsheet(); 
	$sheet = $spreadsheet->getActiveSheet()->setTitle('Нет оплаты');   
	
    $sheet->setCellValue('A1', "Неоплаченные счета:");
	
					  $A="A"."3";
                      $B="B"."3";
                      $C="C"."3";
                      $D="D"."3";
                      $E="E"."3";
					  $F="F"."3";
                      $G="G"."3";

   
    $sheet->setCellValue($A,   "Номер счета" ); 
    $sheet->setCellValue($B,   "Дата"); 
    $sheet->setCellValue($C,   "Номер декларации"); 
    $sheet->setCellValue($D,   "Клиент"); 
    $sheet->setCellValue($E,   "Сумма"); 
	$sheet->setCellValue($F,   "Брокер"); 
    $sheet->setCellValue($G,   "Форма оплаты"); 
	
	//Стиль ячеек
				$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					  /* 'color' => [
						  'rgb' => 'FF0000'
						] */
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );
	 			$sheet->getStyle($E)->applyFromArray( $styleArray );	
				$sheet->getStyle($F)->applyFromArray( $styleArray );
				$sheet->getStyle($G)->applyFromArray( $styleArray );	



// Фон ячеек
$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($C)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($D)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($E)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($F)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($G)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');

	
	
	
	$i=4;
	
    $sheet->getColumnDimension('A')->setAutoSize(true);
  	$sheet->getColumnDimension("B")->setAutoSize(true);
	$sheet->getColumnDimension("C")->setAutoSize(true);
	$sheet->getColumnDimension("D")->setAutoSize(true);
	$sheet->getColumnDimension("E")->setAutoSize(true);
 	$sheet->getColumnDimension("F")->setAutoSize(true);
	$sheet->getColumnDimension("G")->setAutoSize(true);
	
	foreach ($arrInvoice as $tabl) {
		  
					$A="A".$i;
                    $B="B".$i;
                    $C="C".$i;
                    $D="D".$i;
                    $E="E".$i;
					$F="F".$i;
                    $G="G".$i;

	$arrD=Declaration::find()->asArray()->where(['=', 'id', $tabl["decl_id"]])->one();
	$arrC=Client::find()->asArray()->where(['=', 'id', $tabl["client_id"]])->one();
	$arrU=User::find()->asArray()->where(['=', 'id', $tabl["user_id"]])->one();
	
	$invoice_itogo += $tabl["cost"];
	
    $sheet->setCellValue($A,   $tabl["id"]); 
    $sheet->setCellValue($B,    date('d.m.Y',strtotime($tabl["date"]))); 
    $sheet->setCellValue($C,   $arrD['decl_number']); 
    $sheet->setCellValue($D,   $arrC['client']); 
    $sheet->setCellValue($E,   number_format($tabl["cost"], 2, ',', ' ')); 
	$sheet->setCellValue($F,   $arrU['username']); 
    $sheet->setCellValue($G,   $tabl["forma_oplat"]); 

	//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );
		//		$sheet->getStyle($D)->applyFromArray( $styleArray );				
	
			    $sheet->getStyle($F)->applyFromArray( $styleArray );				
		    	$sheet->getStyle($G)->applyFromArray( $styleArray );					
	
	//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);

				$sheet->getStyle($E)->applyFromArray( $styleArray );
	//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_LEFT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($D)->applyFromArray( $styleArray );			

	$i++;
	}
	
				$D="D".$i;
                $E="E".$i;
				$F="F".$i;
				
    $sheet->setCellValue($D,   "Итого: " ); 
    $sheet->setCellValue($E,   number_format($invoice_itogo, 2, ',', ' ')); 
	$sheet->setCellValue($F,   "грн"); 


	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($D)->applyFromArray( $styleArray );
				$sheet->getStyle($E)->applyFromArray( $styleArray );
	
	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_LEFT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);			
				$sheet->getStyle($F)->applyFromArray( $styleArray );	




// Список по брокерам	
	$arrUser=array();
	$i=0;
	
	foreach ($arrInvoice as $tabl_i) {	
		$arrUser[$i] =$tabl_i['user_id'];
	 
		$i++;
	};
	
	$arrUser = array_unique($arrUser);
	
	//debug ($arrUser);
	
	foreach ($arrUser as $tabl_U) {	  
	 
	
	// Create a new worksheet called "My Data"
	
	$arrU=User::find()->asArray()->where(['=', 'id', $tabl_U])->one();
	
	$list_name= $arrU['username'];
	
	$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $list_name);

// Attach the "My Data" worksheet as the first worksheet in the Spreadsheet object
	$spreadsheet->addSheet($myWorkSheet, 1);
	$sheet = $spreadsheet->getSheet(1);
//		$sheet = $spreadsheet->getActiveSheet()->setTitle($arrUser['id']);   

	  $arrInvoice=Invoice::find()->asArray()->where(['=','oplata','Нет'])
	  ->andWhere(['=','user_id',$tabl_U])->all();
	
	$invoice_itogo =0;
	
 
	
    $sheet->setCellValue('A1', "Неоплаченные счета:");
	
					  $A="A"."3";
                      $B="B"."3";
                      $C="C"."3";
                      $D="D"."3";
                      $E="E"."3";
					  $F="F"."3";

   
    $sheet->setCellValue($A,   "Номер счета" ); 
    $sheet->setCellValue($B,   "Дата"); 
    $sheet->setCellValue($C,   "Номер декларации"); 
    $sheet->setCellValue($D,   "Клиент"); 
    $sheet->setCellValue($E,   "Сумма"); 
	$sheet->setCellValue($F,   "Форма оплаты"); 
	
	//Стиль ячеек
				$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					  /* 'color' => [
						  'rgb' => 'FF0000'
						] */
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );
	 			$sheet->getStyle($E)->applyFromArray( $styleArray );	
				$sheet->getStyle($F)->applyFromArray( $styleArray );
				


// Фон ячеек
$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($C)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($D)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($E)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($F)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');

	
	
	
	$i=4;
	
    $sheet->getColumnDimension('A')->setAutoSize(true);
  	$sheet->getColumnDimension("B")->setAutoSize(true);
	$sheet->getColumnDimension("C")->setAutoSize(true);
	$sheet->getColumnDimension("D")->setAutoSize(true);
	$sheet->getColumnDimension("E")->setAutoSize(true);
 	$sheet->getColumnDimension("F")->setAutoSize(true);
	
	foreach ($arrInvoice as $tabl) {
		  
					$A="A".$i;
                    $B="B".$i;
                    $C="C".$i;
                    $D="D".$i;
                    $E="E".$i;
					$F="F".$i;

	$arrD=Declaration::find()->asArray()->where(['=', 'id', $tabl["decl_id"]])->one();
	$arrC=Client::find()->asArray()->where(['=', 'id', $tabl["client_id"]])->one();
	$arrU=User::find()->asArray()->where(['=', 'id', $tabl["user_id"]])->one();
	
	$invoice_itogo += $tabl["cost"];
	
    $sheet->setCellValue($A,   $tabl["id"]); 
    $sheet->setCellValue($B,    date('d.m.Y',strtotime($tabl["date"]))); 
    $sheet->setCellValue($C,   $arrD['decl_number']); 
    $sheet->setCellValue($D,   $arrC['client']); 
    $sheet->setCellValue($E,   number_format($tabl["cost"], 2, ',', ' ')); 
	$sheet->setCellValue($F,   $tabl["forma_oplat"]); 

	//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );
			    $sheet->getStyle($F)->applyFromArray( $styleArray );				
		    	
	//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($E)->applyFromArray( $styleArray );
	
	//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_LEFT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($D)->applyFromArray( $styleArray );
	$i++;
	}
	
				$D="D".$i;
                $E="E".$i;
				$F="F".$i;
				
    $sheet->setCellValue($D,   "Итого: " ); 
    $sheet->setCellValue($E,   number_format($invoice_itogo, 2, ',', ' ')); 
	$sheet->setCellValue($F,   "грн"); 


	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($D)->applyFromArray( $styleArray );
				$sheet->getStyle($E)->applyFromArray( $styleArray );
	
	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_LEFT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);			
				$sheet->getStyle($F)->applyFromArray( $styleArray );	
 }

    $report = new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
    $report->save('files/invoice.xls');
		
	}
 
 }
 else {
	  $arrInvoice=Invoice::find()->asArray()->where(['=','oplata','Нет'])
	  ->andWhere(['=','user_id',$id])->all();
	  
	   $invoice_itogo =0;
	
 // debug ($invoice);
 
  //Создаем экземпляр класса электронной таблицы
    $spreadsheet = new Spreadsheet(); 
	$sheet = $spreadsheet->getActiveSheet()->setTitle('Нет оплаты');   
	
    $sheet->setCellValue('A1', "Неоплаченные счета:");
	
					  $A="A"."3";
                      $B="B"."3";
                      $C="C"."3";
                      $D="D"."3";
                      $E="E"."3";
					  $F="F"."3";
                   
   
    $sheet->setCellValue($A,   "Номер счета" ); 
    $sheet->setCellValue($B,   "Дата"); 
    $sheet->setCellValue($C,   "Номер декларации"); 
    $sheet->setCellValue($D,   "Клиент"); 
    $sheet->setCellValue($E,   "Сумма"); 
	$sheet->setCellValue($F,   "Форма оплаты"); 
	
	//Стиль ячеек
				$styleArray = ([
				 'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					  /* 'color' => [
						  'rgb' => 'FF0000'
						] */
					],
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );	
				$sheet->getStyle($D)->applyFromArray( $styleArray );
	 			$sheet->getStyle($E)->applyFromArray( $styleArray );	
				$sheet->getStyle($F)->applyFromArray( $styleArray );
				


// Фон ячеек
$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($C)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($D)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($E)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($F)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');

	
	
	
	$i=4;
	
    $sheet->getColumnDimension('A')->setAutoSize(true);
  	$sheet->getColumnDimension("B")->setAutoSize(true);
	$sheet->getColumnDimension("C")->setAutoSize(true);
	$sheet->getColumnDimension("D")->setAutoSize(true);
	$sheet->getColumnDimension("E")->setAutoSize(true);
 	$sheet->getColumnDimension("F")->setAutoSize(true);
	
	foreach ($arrInvoice as $tabl) {
		  
					$A="A".$i;
                    $B="B".$i;
                    $C="C".$i;
                    $D="D".$i;
                    $E="E".$i;
					$F="F".$i;

	$arrD=Declaration::find()->asArray()->where(['=', 'id', $tabl["decl_id"]])->one();
	$arrC=Client::find()->asArray()->where(['=', 'id', $tabl["client_id"]])->one();
	$arrU=User::find()->asArray()->where(['=', 'id', $tabl["user_id"]])->one();
	
	$invoice_itogo += $tabl["cost"];
	
    $sheet->setCellValue($A,   $tabl["id"]); 
    $sheet->setCellValue($B,    date('d.m.Y',strtotime($tabl["date"]))); 
    $sheet->setCellValue($C,   $arrD['decl_number']); 
    $sheet->setCellValue($D,   $arrC['client']); 
    $sheet->setCellValue($E,   number_format($tabl["cost"], 2, ',', ' ')); 
	$sheet->setCellValue($F,   $tabl["forma_oplat"]); 

	//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_CENTER,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);




				$sheet->getStyle($A)->applyFromArray( $styleArray );	
				$sheet->getStyle($B)->applyFromArray( $styleArray );
				$sheet->getStyle($C)->applyFromArray( $styleArray );
				$sheet->getStyle($F)->applyFromArray( $styleArray );				
		    	
	//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);

				$sheet->getStyle($E)->applyFromArray( $styleArray );
				
	//Стиль ячеек
				$styleArray = ([
					'borders' => [
						'allBorders' => [
							'borderStyle' => Border::BORDER_THIN,
							'color' => [
								'rgb' => '808080'
							]
						],
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_LEFT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($D)->applyFromArray( $styleArray );			

	$i++;
	}
	
				$D="D".$i;
                $E="E".$i;
				$F="F".$i;
				
    $sheet->setCellValue($D,   "Итого: " ); 
    $sheet->setCellValue($E,   number_format($invoice_itogo, 2, ',', ' ')); 
	$sheet->setCellValue($F,   "грн"); 


	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_RIGHT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);
				
				$sheet->getStyle($D)->applyFromArray( $styleArray );
				$sheet->getStyle($E)->applyFromArray( $styleArray );
	
	$styleArray = ([
				'font' => [
				  //    'name' => 'Arial',
					   'bold' => true,
				   //   'italic' => true,
				  //    'underline' => Font::UNDERLINE_DOUBLE,
					  'strikethrough' => false,
					   'color' => [
						  'rgb' => 'FF0000'
						]  
					],
					'alignment' => [
						'horizontal' => Alignment::HORIZONTAL_LEFT,
						'vertical' => Alignment::VERTICAL_CENTER,
						'wrapText' => true,
					]
				]);			
				$sheet->getStyle($F)->applyFromArray( $styleArray );	


    $report = new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
    $report->save('files/invoice.xls');
	  
	  
 };



}

//Проверка на отсутствие счета
function invoice_absent() {


		$decl= Declaration::find()->asArray()->where(['=','user_id',Yii::$app->user->id])
		                        ->andWhere(['!=','decl_number','Операции за день'])
								->andWhere(['!=','client_id',3])
								->andWhere(['!=','client_id',81])
						     	/*->andWhere(['=','date',date('Y-m-d')])*/ ->all();
		
		$invoice=Invoice::find()->asArray()->where(['=','user_id',Yii::$app->user->id])
		                          /* ->andWhere(['=','date',date('Y-m-d')]) */->all();
		
		$i=0;
		
		$arrInvoice_absent[$i] =0;
		

		if ($decl!=null){
			
			if ($invoice!=null) {
				foreach ($decl as $arrDcl){
					$flag=0;
						
					 foreach ($invoice as $arrInv) {
						if ($arrInv['decl_id']==$arrDcl['id']) {
							$flag =1; 
						};
				    };
				
					if ($flag == 0) {
							$arrInvoice_absent[$i]= $arrDcl['id'];
							
							$i++;	
						};
				}
			}
			
				
				
		}

	 
		if ($arrInvoice_absent[0] !=0) {
					$decl_inv= Declaration::find()->asArray()->where(['id'=>$arrInvoice_absent])->all();
		}
		else {
			$decl_inv[0]=0;
		}
//debug ($decl_inv);
return($decl_inv);						  
}
//////////////////////////////////////////////////////////////////////////////////////////// Перевод чисел в строку////////////////////////////////////////////////////////////////////////////////////////////////////////////

function num2text_ua($num) {
    $num = trim(preg_replace('~s+~s', '', $num)); // отсекаем пробелы
    if (preg_match("/, /", $num)) {
        $num = preg_replace("/, /", ".", $num);
    } // преобразует запятую
    if (is_numeric($num)) {
        $num = round($num, 2); // Округляем до сотых (копеек)
        $num_arr = explode(".", $num);
        $amount = $num_arr[0]; // переназначаем для удобства, $amount - сумма без копеек
        if (strlen($amount) <= 3) {
            $res = implode(" ", Triada($amount)) . Currency($amount);
        } else {
            $amount1 = $amount;
            while (strlen($amount1) >= 3) {
                $temp_arr[] = substr($amount1, -3); // засовываем в массив по 3
                $amount1 = substr($amount1, 0, -3); // уменьшаем массив на 3 с конца
            }
            if ($amount1 != '') {
                $temp_arr[] = $amount1;
            } // добавляем то, что не добавилось по 3
            $i = 0;
            foreach ($temp_arr as $temp_var) { // переводим числа в буквы по 3 в массиве
                $i++;
                if ($i == 3 || $i == 4) { // миллионы и миллиарды мужского рода, а больше миллирда вам все равно не заплатят
                    if ($temp_var == '000') {

                        $temp_res[] = '';
                    } else {
                        $temp_res[] = implode(" ", Triada($temp_var, 1)) . GetNum($i, $temp_var);
                    } # if
                } else {
                    if ($temp_var == '000') {
                        $temp_res[] = '';
                    } else {
                        $temp_res[] = implode(" ", Triada($temp_var)) . GetNum($i, $temp_var);
                    } # if
                } # else
            } # foreach
            $temp_res = array_reverse($temp_res); // разворачиваем массив
            $res = implode(" ", $temp_res) . Currency($amount);
        }
        if (!isset($num_arr[1]) || $num_arr[1] == '') {
            $num_arr[1] = '00';
        }
        return $res . ' ' . $num_arr[1] . ' коп.';
    } # if
}

function Triada($amount, $case = null) {
    global $_1_2, $_1_19, $des, $hang; // объявляем массив переменных
    $count = strlen($amount);
    for ($i = 0; $i < $count; $i++) {
        $triada[] = substr($amount, $i, 1);
    }
    $triada = array_reverse($triada); // разворачиваем массив для операций
    if (isset($triada[1]) && $triada[1] == 1) { // строго для 10-19
        $triada[0] = $triada[1] . $triada[0]; // Объединяем в единицы
        $triada[1] = ''; // убиваем десятки
        $triada[0] = $_1_19[$triada[0]]; // присваиваем
    } else { // а дальше по обычной схеме
        if (isset($case) && ($triada[0] == 1 || $triada[0] == 2)) { // если требуется м.р.
            $triada[0] = $_1_2[$triada[0]]; // единицы, массив мужского рода
        } else {
            if ($triada[0] != 0) {
                $triada[0] = $_1_19[$triada[0]];
            } else {
                $triada[0] = '';
            } // единицы
        } # if
        if (isset($triada[1]) && $triada[1] != 0) {
            $triada[1] = $des[$triada[1]];
        } else {
            $triada[1] = '';
        } // десятки
    }
    if (isset($triada[2]) && $triada[2] != 0) {
        $triada[2] = $hang[$triada[2]];
    } else {
        $triada[2] = '';
    } // сотни
    $triada = array_reverse($triada); // разворачиваем массив для вывода
    foreach ($triada as $triada_) { // вычищаем массив от пустых значений
        if ($triada_ != '') {
            $triada1[] = $triada_;
        }
    } # foreach
    return $triada1;
}

function Currency($amount) {
    global $namecurr; // объявляем масиив переменных
    $last2 = substr($amount, -2); // последние 2 цифры
    $last1 = substr($amount, -1); // последняя 1 цифра
    $last3 = substr($amount, -3); //последние 3 цифры
    if ((strlen($amount) != 1 && substr($last2, 0, 1) == 1) || $last1 >= 5 || $last3 == '000') {
        $curr = $namecurr[3];
    } // от 10 до 19
    else if ($last1 == 1) {
        $curr = $namecurr[1];
    } // для 1-цы
    else {
        $curr = $namecurr[2];
    } // все остальные 2, 3, 4
    return ' ' . $curr;
}

function GetNum($level, $amount) {
    global $nametho, $namemil, $namemrd; // объявляем массив переменных
    if ($level == 1) {
        $num_arr = null;
    } else if ($level == 2) {
        $num_arr = $nametho;
    } else if ($level == 3) {
        $num_arr = $namemil;
    } else if ($level == 4) {
        $num_arr = $namemrd;
    } else {
        $num_arr = null;
    }
    if (isset($num_arr)) {
        $last2 = substr($amount, -2);
        $last1 = substr($amount, -1);
        if ((strlen($amount) != 1 && substr($last2, 0, 1) == 1) || $last1 >= 5) {
            $res_num = $num_arr[3];
        } // 10-19
        else if ($last1 == 1) {
            $res_num = $num_arr[1];
        } // для 1-цы
        else {
            $res_num = $num_arr[2];
        } // все остальные 2, 3, 4
        return ' ' . $res_num;
    } # if
}

$_1_2[1] = "один";
$_1_2[2] = "два";

$_1_19[1] = "одна";
$_1_19[2] = "дві";
$_1_19[3] = "три";
$_1_19[4] = "чотири";
$_1_19[5] = "п'ять";
$_1_19[6] = "шість";
$_1_19[7] = "сім";
$_1_19[8] = "вісім";
$_1_19[9] = "дев'ять";
$_1_19[10] = "десять";

$_1_19[11] = "одинадцять";
$_1_19[12] = "дванадцять";
$_1_19[13] = "тринадцять";
$_1_19[14] = "чотирнадцять";
$_1_19[15] = "п'ятнадцять";
$_1_19[16] = "шістнадцять";
$_1_19[17] = "сімнадцять";
$_1_19[18] = "вісімнадцять";
$_1_19[19] = "дев'ятнадцять";


$des[2] = "двадцять";
$des[3] = "тридцять";
$des[4] = "сорок";
$des[5] = "п'ятдесят";
$des[6] = "шістдесят";
$des[7] = "сімдесят";
$des[8] = "вісімдесят";
$des[9] = "дев'яносто";

$hang[1] = "сто";
$hang[2] = "двісті";
$hang[3] = "триста";
$hang[4] = "чотириста";
$hang[5] = "п'ятсот";
$hang[6] = "шістсот";
$hang[7] = "сімсот";
$hang[8] = "вісімсот";
$hang[9] = "дев'ятьсот";

$namecurr[1] = "грн"; // 1
$namecurr[2] = "грн"; // 2, 3, 4
$namecurr[3] = "грн"; // >4

$nametho[1] = "тисяча"; // 1
$nametho[2] = "тисячі"; // 2, 3, 4
$nametho[3] = "тисяч"; // >4

$namemil[1] = "мільйон"; // 1
$namemil[2] = "мільйона"; // 2, 3, 4
$namemil[3] = "мільйонів"; // >4

$namemrd[1] = "мільярд"; // 1
$namemrd[2] = "мільярда"; // 2, 3, 4
$namemrd[3] = "мільярдів"; // >4



