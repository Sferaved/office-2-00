<?php


use frontend\models\Cabinet;
use frontend\models\Cabinetstatya;
use frontend\models\User;
use frontend\models\AuthAssignment;
use frontend\models\Declaration;
use common\models\Client;
use common\models\Contragent;
use backend\models\Workzatraty;
use common\models\Workstatya;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Autoloader;
use PhpOffice\PhpSpreadsheet\Style\{Font, Border, Alignment};


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


//Отчет по затратам

function workzatraty_report($date_from,$date_to) {
	
	$statya_tabl =Workstatya::find()->asArray()-> where(['=', 'report','Да'])->all();

  
    $cost_ALL=0;

    $statya_sum = 0;
   
    $i=2;
    
          
  //Создаем экземпляр класса электронной таблицы
    $spreadsheet = new Spreadsheet(); 
	 
	
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

	$sheet= $spreadsheet->getActiveSheet()->setTitle("За месяц");	 
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue($A,   "Дата" ); 
    $sheet->setCellValue($B,   "Номер декларации"); 
    $sheet->setCellValue($C,   "Клиент"); 
    $sheet->setCellValue($D,   "Сумма"); 
 //   $sheet->setCellValue($E,   "Коментарий"); 




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
//$sheet->getStyle($E)->applyFromArray( $styleArray );





 // Фон ячеек
$sheet->getStyle($A)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($B)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($C)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');
$sheet->getStyle($D)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FAFAD2');


foreach  ($statya_tabl as $tabl_st) {
	$tabl_zatraty_info=Workzatraty::find()->asArray()->where(['between', 'date', $date_from, $date_to])
	->AndWhere(['=', 'workstatya_id', $tabl_st['id']])->all();
	
	Yii\helpers\ArrayHelper::multisort($tabl_zatraty_info, ['client_id', 'date'], [SORT_ASC, SORT_ASC]);

	
	if ($tabl_zatraty_info !=null) {
	$statya_sum =0;
	
	foreach ($tabl_zatraty_info  as $tabl){
        $statya =$tabl["workstatya_id"];
		
		$model_statya = Workstatya::find()-> where (['=','id', $statya]) ->all();
	    foreach ($model_statya as $value) (
        $statya_write = $value->statya
        ); 



		
                  $date =date("d.m.Y", strtotime($tabl["date"])); 
                  $cost = $tabl["cost"];
                  $cost_num = number_format ($cost,2,'.','');
               
                  $model_decl = Declaration::find()-> where (['=','id', $tabl["decl_id"]]) ->all();
					foreach ($model_decl as $value) (
						$decl_number = $value->decl_number
					); 
                
				  
				  $model_client = Client::find()-> where (['=','id', $tabl["client_id"]]) ->all();
					foreach ($model_client as $value) (
					$client = $value->client
				  ); 
					// Отсекание лишних записей
					$pos=0;
					$findme   = 'UA807';
					$pos_O = strpos($decl_number,   $findme );
					if ($pos_O !== false) {
						$pos=1;
					}
					
				 
					if ($pos == 1)  {
	
								$A="A".$i;
                                $B="B".$i;
                                $C="C".$i;
                                $D="D".$i;
                                $E="E".$i;

                                $sheet->setCellValue($A,   $date ); 
                                $sheet->setCellValue($B,   $decl_number); 
                                $sheet->setCellValue($C,   $client); 
                                $sheet->setCellValue($D,   $cost_num); 
                            //    $sheet->setCellValue($E,   $statya_write); 
								
								
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
					//$sheet->getStyle($E)->applyFromArray( $styleArray );
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

 

 
							
								$statya_sum+=$cost;
								
								$cost_ALL=$cost_ALL +$cost;
								$i++;
				 }			
								
								
		}
		
		if ($statya_sum !=0) {
		$C="C".$i;
        $D="D".$i;		
		$C_info="Всего по ". $statya_write;
        $sheet->setCellValue($C,   $C_info); 
        $sheet->setCellValue($D,   $statya_sum); 
		
		 //Стиль ячеек

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
		   
			'alignment' => [
				'horizontal' => Alignment::HORIZONTAL_RIGHT,
				'vertical' => Alignment::VERTICAL_CENTER,
				'wrapText' => true,
			]
		]);




		$sheet->getStyle($C)->applyFromArray( $styleArray );	
		$sheet->getStyle($D)->applyFromArray( $styleArray );
		
// Фон ячеек

/* $sheet->getStyle($C)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
$sheet->getStyle($D)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');	 */	
		
		
		
        $i++;
		}
		
	}
	
}

					$C="C".$i;
                    $D="D".$i;
        

                      $sheet->setCellValue($C,   "ИТОГО"); 
                      $sheet->setCellValue($D,   $cost_ALL);	
					 //Стиль ячеек

					$styleArray = ([
						'font' => [
						       'name' => 'Arial',
							   'bold' => true,
							//  'italic' => true,
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
		



        
foreach  ($statya_tabl as $tabl_st) {
	$tabl_zatraty_info=Workzatraty::find()->asArray()->where(['between', 'date', $date_from, $date_to])
	->AndWhere(['=', 'workstatya_id', $tabl_st['id']])->all();
	
	Yii\helpers\ArrayHelper::multisort($tabl_zatraty_info, ['client_id', 'date'], [SORT_ASC, SORT_ASC]);
	
	if ($tabl_zatraty_info !=null) {
		if ($tabl_st['report'] == 'Да') {
			// Create a new worksheet called "My Data"
				$myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $tabl_st['statya']);

			// Attach the "My Data" worksheet as the first worksheet in the Spreadsheet object
				$spreadsheet->addSheet($myWorkSheet, 0);
				$sheet = $spreadsheet->getSheet(0);
				
				$cost_ALL=0;

				$statya_sum = 0;
			   
				$i=2;
				
					
				$sheet->getColumnDimension('A')->setAutoSize(true);
				$sheet->getColumnDimension("B")->setAutoSize(true);
				$sheet->getColumnDimension("C")->setAutoSize(true);
				$sheet->getColumnDimension("D")->setAutoSize(true);
				
				
								  $A="A"."1";
								  $B="B"."1";
								  $C="C"."1";
								  $D="D"."1";
								


			   /*  $sheet = $spreadsheet->getActiveSheet(); */
				$sheet->setCellValue($A,   "Дата" ); 
				$sheet->setCellValue($B,   "Номер декларации"); 
				$sheet->setCellValue($C,   "Клиент"); 
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



		}
		
	
	
	
	$statya_sum =0;
	
	foreach ($tabl_zatraty_info  as $tabl){
        $statya =$tabl["workstatya_id"];
		
		$model_statya = Workstatya::find()-> where (['=','id', $statya]) ->all();
	    foreach ($model_statya as $value) (
        $statya_write = $value->statya
        ); 

        foreach ($statya_tabl  as $tabl_s)  // Определение надо ли запоминать статью
                      
                  $date =date("d.m.Y", strtotime($tabl["date"])); 
                  $cost = $tabl["cost"];
                  $cost_num = number_format ($cost,2,'.','');
               
                  $model_decl = Declaration::find()-> where (['=','id', $tabl["decl_id"]]) ->all();
					foreach ($model_decl as $value) (
						$decl_number = $value->decl_number
					); 
                
				  
				  $model_client = Client::find()-> where (['=','id', $tabl["client_id"]]) ->all();
					foreach ($model_client as $value) (
					$client = $value->client
				  ); 
					// Отсекание лишних записей
					$pos=0;
					$findme   = 'UA807';
					$pos_O = strpos($decl_number,   $findme );
					if ($pos_O !== false) {
						$pos=1;
					}
					
				 
					if ($pos == 1)  {
	
								$A="A".$i;
                                $B="B".$i;
                                $C="C".$i;
                                $D="D".$i;
                           
                                $sheet->setCellValue($A,   $date ); 
                                $sheet->setCellValue($B,   $decl_number); 
                                $sheet->setCellValue($C,   $client); 
                                $sheet->setCellValue($D,   $cost_num); 
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
						//$sheet->getStyle($E)->applyFromArray( $styleArray );
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
								
								$statya_sum+=$cost;
																
								$i++;
				 }			
								
		}
		
		if ($statya_sum !=0) {
		$C="C".$i;
        $D="D".$i;		
		$C_info="Всего по ". $statya_write;
        $sheet->setCellValue($C,   $C_info); 
        $sheet->setCellValue($D,   $statya_sum); 
		
				 //Стиль ячеек

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
		   
			'alignment' => [
				'horizontal' => Alignment::HORIZONTAL_RIGHT,
				'vertical' => Alignment::VERTICAL_CENTER,
				'wrapText' => true,
			]
		]);




		$sheet->getStyle($C)->applyFromArray( $styleArray );	
		$sheet->getStyle($D)->applyFromArray( $styleArray );
		
		
		}
		
	}
	
}




    $report = new PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
    $report->save('files/'.'report.xls');

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



