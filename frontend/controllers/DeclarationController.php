<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Declaration;
use frontend\models\DeclarationSearch;
use frontend\models\User;
use common\models\Client;
use frontend\models\UploadForm;
use frontend\models\Cabinet;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use frontend\models\Invoice;
use backend\models\Workzatraty;
use common\models\Workshablon;
use frontend\models\Aquaizol;
use frontend\models\Flex;
use frontend\models\AuthAssignment;


/**
 * DeclarationController implements the CRUD actions for Declaration model.
 */
class DeclarationController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
		   'access' => [
                'class' => AccessControl::className(),
                'only' => ['index','create','update','delete','view'],
                'rules' => [
                    [
                        'actions' => ['index','create','update','delete','view'],
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
					[
                        'actions' => ['index','create','update','delete','view'],
                        'allow' => true,
                        'roles' => ['buh'],
                    ],
                    [
                        'actions' => ['index','create','update','delete','view'],
                        'allow' => true,
                        'roles' => ['user'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Declaration models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DeclarationSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $models = $dataProvider->getModels(); // Таблица отбранных записей

//Client

		$arrCl = Client::find()->all(); 	
		
        foreach ($arrCl as $value) (// пара ключ-значение для отобранных записей
           $arrClient[$value->id] = $value->client
        );
		
//User		

		
		$arr_W = ['user','admin'];
		$arrUsers = AuthAssignment::find()->where(['item_name'=>$arr_W])->all();
		
		foreach ($arrUsers as $value) (            //Получили отобранные id=User
				   $arrIdUser[] = $value->user_id
				);
		
		$arrUs = User::find()->where(['id' => $arrIdUser]) ->all(); 

        foreach ($arrUs as $value) (// пара ключ-значение для отобранных записей
           $arrUser[$value->id] = $value->username
        );
		
		
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        	'arrClient' =>$arrClient,
			'arrUser' =>$arrUser,
        ]);
    }

    /**
     * Displays a single Declaration model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
	  	    $model = $this->findModel($id);
			

		
        return $this->render('view', [
            'model' => $this->findModel($id)
        ]);
    }

    /**
     * Creates a new Declaration model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {

        $model = new Declaration();
		$model->decl_number = 'Операции за день';
		$model->date = date ('Y-m-d');
		
        $model->user_id = Yii::$app->user->id;
		$model->client_id = 1;
		 
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
			Yii::$app->session->setFlash ('success', 'Данные приняты');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }
 
    public function actionUpload()
    {
		$model = new UploadForm();
    
		if(Yii::$app->request->post())
		{
		  $model->file = UploadedFile::getInstance($model, 'file');
			if ($model->validate()) {
			 $model->file->saveAs( 'files/' . $model->file->baseName . '.' . $model->file->extension);
		
		    $filename='./files/'.$model->file;
            $img_binary = fread(fopen($filename, "r"), filesize($filename));
            $img_Base64 = base64_encode($img_binary);
            $tabl_pdf =decl_read ($filename);
	
			if ($tabl_pdf["client_id"] !=null) {
			
				$find_decl=Declaration::find()->where(['=','decl_number',$tabl_pdf['decl']])->all();
				
				if ($find_decl == null) { //Проверка на наличие декларации в базе
				$model_d = new Declaration();	//Новая декларация 	
			
				$model_d->decl_number = $tabl_pdf['decl'];
				$model_d->date = date('Y-m-d',strtotime($tabl_pdf['decl_date']));
				$model_d->user_id   = Yii::$app->user->id;
				$model_d->client_id = $tabl_pdf['client_id'];
				$model_d->decl_iso  =  $img_Base64;
				$model_d->save();


			// Отсекание не Харьковских оформлений
			$pos=0;
			$findme   = 'UA807';
			$pos_O = strpos($tabl_pdf["decl"],   $findme );
			if ($pos_O !== false) {
				$pos=1;
			}
			
		 
			if ($pos == 1)  {
				// Поиск шаблона затрат
				$find_zatraty=Workshablon::find()->where(['client_id'=>$tabl_pdf['client_id']])
												 ->andWhere(['ex_im'=>$tabl_pdf['ex_im']])->all();

					if ($find_zatraty!=null) {
						
						foreach ($find_zatraty as $value) (
								   $arrCost[$value->id] = $value->cost
							);
								
							foreach ($find_zatraty as $value) (
								   $arrStatyaId[$value->id] = $value->statya_id
							);

							$i=0;
							foreach ($find_zatraty as $value) {
								   $arrId[$i] = $value->id;
								   $i++;
							};

							$i=0;
							foreach ($arrStatyaId as $value) {
									$model_z = new Workzatraty();	//Новая запись затрат	
								
									$model_z->date =$model_d->date;
									$model_z->decl_id   = $model_d->id;
									$model_z->client_id = $tabl_pdf['client_id'];
									$model_z->cost   = $arrCost[$arrId[$i]];  $i++;
									$model_z->workstatya_id   = $value;
									$model_z->save();
							};
					}				
			}		
						
					if ($model_d->client_id == 3) {
					$model_A = new Aquaizol();	//Новая запись Акваизола
					
					$model_A->date =$model_d->date;
					$model_A->ex_im =$tabl_pdf['ex_im'];
					$model_A->decl_number_id  =$model_d->id;
                    $model_A->custom = 800;
                    $model_A->dosmotr =	800;
					$model_A->broker =	450.45*$tabl_pdf["dop_list"];
					
					if (isset($tabl_pdf['contragent_id']))  {
						$model_A->contragent_id = $tabl_pdf['contragent_id'];
					}
					else {
						$model_A->contragent_id =0;
					};
					
		  
					$model_A->save();	
					}
			   
					if ($model_d->client_id == 81) {
					$model_F = new Flex();	//Новая запись Флекса
					
					$model_F->date =$model_d->date;
					$model_F->ex_im =$tabl_pdf['ex_im'];
					$model_F->decl_number_id  =$model_d->id;
					$model_F->custom =	800;
                    $model_F->dosmotr =	800;
                    $model_F->broker =	450.45*$tabl_pdf["dop_list"];
					
					if (isset($tabl_pdf['contragent_id'])) {
						$model_F->contragent_id = $tabl_pdf['contragent_id'];
					}
					else {
						$model_F->contragent_id =0;
					};
					$model_F->save();	
					}
				
					if ($model_d->save()) {
					Yii::$app->session->setFlash ('success', 'Декларация успешно добавлена в базу');
					return $this->redirect(['view', 'id' => $model_d->id]);
					}
				 
				}
				else{
				 Yii::$app->session->setFlash ('error', 'Такая декларация уже есть в базе'); 
				}
			}	 
			else{
				 Yii::$app->session->setFlash ('error', 'Договора с клиентом нет в базе'); 
			}
		 }	
		
     
        }
        return $this->render('upload', [
            'model'   => $model
			
        ]);
    }
	
	 /**
     * Creates a new Invoice model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionInvoice($id)
    {
			$model_d = $this->findModel($id);
			
			$model = new Invoice(); // Счет на эту декларацию
			
			$model->date      = $model_d->date;
			if (Yii::$app->user->id != 1){
				$model->user_id   = $model_d->user_id;
			}
			else {
				$model->user_id   = 1;
			}
				
			$model->decl_id   = $model_d->id;
			$model->client_id = $model_d->client_id;
			
			$model->oplata    = 'Нет';
			

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
			Yii::$app->session->setFlash ('success', 'Данные приняты');
            
			if (Yii::$app->user->can('user') || Yii::$app->user->can('admin')) { // Отправка сообщения об выставленном счете
		
			$declArr= Declaration::find()->where(['=','id',$model->decl_id])->all();
			foreach ($declArr as $value) ($decl= $value->decl_number);
			
			$clientArr=Client::find()->where(['=','id',$model->client_id])->all();
			foreach ($clientArr as $value) 
			{
			 $client= $value->client;
			 $dogovor=$value->dogovor;
			 $date_begin= date('d.m.Y',strtotime($value->date_begin)) ;
			 $date_finish= date('d.m.Y',strtotime($value->date_finish));
			};
			
			
			$date = date('d.m.Y', strtotime($model->date));
			
			 
			$user_name = Yii::$app->user->identity->username; 
			
			$content   = '<b>Выставлен новый счет за '.$date.'</b></br>'.
						 '№: '.$model->id.'</br>'.
						 'Клиент: '.$client.'</br>'.
						 'Сумма: '.$model->cost.'грн</br>'.
						 'Декларация: '.$decl.'</br>'.
						 'Договор № '.$dogovor.' от '.$date_begin.' до '.$date_finish.'</br>'.
						 'Выставила: '.$user_name.'</br>'.
						 '--------------------------------</b></br>'.
						 '<b>Офис on-line. </b>';		

			if ($model->forma_oplat == 'Карта' && Yii::$app->user->can('user')) {
				 Yii::$app->mailer->compose()
				->setFrom(['sferaved@ukr.net' => 'Офис on-line'])
				->setTo(['andrey18051@gmail.com'])
				->setSubject('Новый счет на '.$client)
				->setHtmlBody($content)
			  ->send();	
			 };
			if ($model->forma_oplat != 'Карта' && Yii::$app->user->can('admin')) {
				 Yii::$app->mailer->compose()
                ->setFrom(['sferaved@ukr.net' => 'Офис on-line'])
				->setTo(['any26113@gmail.com'])
				->setSubject('Новый счет на '.$client)
				->setHtmlBody($content)
			  ->send();	
			 };
			 
			if ($model->forma_oplat != 'Карта' && Yii::$app->user->can('user')) {
				 Yii::$app->mailer->compose()
                ->setFrom(['sferaved@ukr.net' => 'Офис on-line'])
				->setTo(['andrey18051@gmail.com','any26113@gmail.com'])
				->setSubject('Новый счет на '.$client)
				->setHtmlBody($content)
			  ->send();	
			 }
    	}
            return $this->redirect(['view', 'id' => $model_d->id]);
        }

        return $this->render('invoice', [
            'model' => $model,
        ]);
    }

    public function actionZatraty($id)
    {
			$model_d = $this->findModel($id);
			
			$model = new Workzatraty(); // Новые затраты на эту декларацию
			
			$model->date      = $model_d->date;
			
				
			$model->decl_id   = $model_d->id;
			$model->client_id = $model_d->client_id;
			
				

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
			Yii::$app->session->setFlash ('success', 'Данные приняты');
        
            return $this->redirect(['view', 'id' => $model_d->id]);
        }

        return $this->render('zatraty', [
            'model' => $model,
        ]);
    }


    public function actionCabinet($id)
    {
		$model_d = $this->findModel($id);
	// 	debug ($model_d);
		
		
        $model = new Cabinet();
	 
	 
		/* $model->date = $model_d->date; //// исправить на дату внесения записи */
		
		$model->date =date ('Y-m-d');
		 
	    $model->decl_id = $model_d->id;
	    $model->user_id = Yii::$app->user->id;
        $model->client_id= $model_d->client_id;
		
		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			Yii::$app->session->setFlash ('success', 'Данные приняты');
			
			
			
			//Запись фито в базу Акваизола
			if ($model->coment_id == 104 && $model_d->client_id==3) {
				$model_a = Aquaizol::find() ->where(['decl_number_id'=>$id]) ->one();
				$model_a->fito = $model->cost;
				$model_a->save();
			}
		//Запись фито в базу Флекса
			if ($model->coment_id == 104 && $model_d->client_id==81) {
				$model_f = Flex::find() ->where(['decl_number_id'=>$id]) ->one();
				$model_f->fito = $model->cost;
				$model_f->save();
			}
			
			
            return $this->redirect(['view', 'id' => $model_d->id]);
        }

        return $this->render('cabinet', [
            'model' => $model,
        ]);
    }
	
    /**
     * Updates an existing Declaration model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
			Yii::$app->session->setFlash ('success', 'Данные успешно обновлены');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Declaration model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        
		
		//Поиск оплаченных счетов по этой работе
		$invIdArr= Invoice::find()->where(['decl_id'=>$id])
						->andWhere(['oplata'=>'Да'])		->all();
		
		if ($invIdArr !=null) {
		 Yii::$app->session->setFlash ('error', 'Нельзя удалить оплаченную работу');	
		}
		else {
			
		//Удаление записей о затратах
		$zatrIdArr= Workzatraty::find()->where(['decl_id'=>$id]) ->all();
		if ($zatrIdArr !=null) {
		foreach ($zatrIdArr as $value) (
		
			$arrIdZatr[$value->id] = $value->id
		
		);

		$i=0;
		foreach ($arrIdZatr as $value) {
	
			$arrId[$i] = $value;
			$i++;
			;
		}; 
		
		
		foreach ($arrId as $value) {
			$model = Workzatraty::find() ->where(['id'=>$value]) ->one() ->delete();
			};	
		}
		
		//Удаление записей из кабинета
		$cabIdArr= Cabinet::find()->where(['decl_id'=>$id]) ->all();
		if ($cabIdArr !=null) {
		foreach ($cabIdArr as $value) (
		
			$arrIdCab[$value->id] = $value->id
		
		);

		$i=0;
		foreach ($arrIdCab as $value) {
	
			$arrId[$i] = $value;
			$i++;
			;
		}; 
		
		
		foreach ($arrId as $value) {
			$model = Cabinet::find() ->where(['id'=>$value]) ->one() ->delete();
			};	
		}
		
		
        //Удаление не оплаченных счетов по декларации
	   
	    $invIdArr= Invoice::find()->where(['decl_id'=>$id])->all();
		
		
	    if ($invIdArr !=null) {
		foreach ($invIdArr as $value) (
		
			$arrIdInv[$value->id] = $value->id
		
		);

		$i=0;
		foreach ($arrIdInv as $value) {
	
			$arrIdI[$i] = $value;
			$i++;
			;
		}; 
		
	
	    foreach ($arrIdI as $val) {
						// Отправка сообщения об удаленном счете
		
			$declArr= Declaration::find()->where(['=','id',$id])->all();
	
		    foreach ($declArr as $value) 
			(
			$client_id= $value->client_id
			
			);
			
	
	        $clientArr=Client::find()->where(['=','id',$client_id])->all();
			
		
			foreach ($declArr as $value) 
			(
			$decl= $value->decl_number
			
			);
            
			foreach ($clientArr as $value) 
			{
			 $client= $value->client;
			 $dogovor=$value->dogovor;
			 $date_begin= date('d.m.Y',strtotime($value->date_begin)) ;
			 $date_finish= date('d.m.Y',strtotime($value->date_finish));
			};
			
		    $invIdArr= Invoice::find()->asArray()->where(['decl_id'=>$id])->andWhere(['id'=>$val])->one();
		
			$date = date('d.m.Y', strtotime($invIdArr['date']));		
			
			$cost= $invIdArr['cost'];
			
			$user_name = Yii::$app->user->identity->username; 
			
			$content   = '<b>Удален счет </b>'.$val.' от '.$date.'</b></br>'.
						 'Клиент: '.$client.'</br>'.
						 'Сумма: '.$cost.'грн</br>'.
						 'Декларация: '.$decl.'</br>'.
						 'Договор № '.$dogovor.' от '.$date_begin.' до '.$date_finish.'</br>'.
						 'Удалила: '.$user_name.'</br>'.
						 '--------------------------------</b></br>'.
						 '<b>Офис on-line. </b>';		

		
			Yii::$app->mailer->compose()
            ->setFrom(['sferaved@ukr.net' => 'Офис on-line'])
			->setTo(['andrey18051@gmail.com','any26113@gmail.com'])
			->setSubject('Удаление счета на '.$client)
			->setHtmlBody($content)
		  ->send();	
			$model = Invoice::find() ->where(['id'=>$val]) ->one() ->delete();
			
			
			};
		}
		
    	 //Удаление декларации	
	    $find_decl=Declaration::find()->where(['=','id',$id])->one();
         

		if  ($find_decl->client_id == 3) { //Удаление из базы Акваизола
			$model = Aquaizol::find() ->where(['decl_number_id'=>$id]) ->one();
			if ($model != null) {
			$model = Aquaizol::find() ->where(['decl_number_id'=>$id]) ->one() ->delete();
			}
        }	  
	  
	    if  ($find_decl->client_id == 81) { //Удаление из базы Флекса
		    $model = Flex::find() ->where(['decl_number_id'=>$id]) ->one();
		    if ($model != null) {
			$model = Flex::find() ->where(['decl_number_id'=>$id]) ->one() ->delete(); 
			}
		}
	
		
		
		
		$this->findModel($id)->delete();
     
    	 Yii::$app->session->setFlash ('success', 'Запись успешно удалена из базы');
		 
		 
	
		}
			
		        return $this->redirect(['index']);
    }

    /**
     * Finds the Declaration model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Declaration the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Declaration::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
	
	public function actionReport($date_from,$date_to)// Скачивание списка работ
    {
 	 	
        DeclReport($date_from,$date_to); 
		
		$file = 'files/report.xls';
		
		if (file_exists($file)) {
			 return \Yii::$app->response->sendFile($file)->on(\yii\web\Response::EVENT_AFTER_SEND, function($event) {
			unlink($event->data);
			}, $file); 
		} 
		throw new \Exception('File not found'); 
	
	}
	
    public function actionFile($id) // Скачивание изображений декларации из базы
    {
 
		$model = $this->findModel($id);
		
		$pdf_decoded=base64_decode($model['decl_iso']);
		//Write data back to pdf file

		$img_file = 'files/'.'decl_iso_'.$id.'.pdf';

		$pdf = fopen ($img_file,'w');
		fwrite ($pdf,$pdf_decoded);
		//close output file
		fclose ($pdf);
		
		$file = $img_file;
	 
		if (file_exists($file)) {
			/* return \Yii::$app->response->sendFile($file)->on(\yii\web\Response::EVENT_AFTER_SEND, function($event) {
			unlink($event->data);
			}, $file); */
			return \Yii::$app->response->sendFile($file);
		} 
		throw new \Exception('File not found'); 
	
	}
	
	public function actionExport($id)// Скачивание расчета по декларации
    {
 	 
	    declaration_report($id);
		$file = 'files/report.xls';
	 
		if (file_exists($file)) {
			 return \Yii::$app->response->sendFile($file)->on(\yii\web\Response::EVENT_AFTER_SEND, function($event) {
			unlink($event->data);
			}, $file); 
		} 
		throw new \Exception('Нужно найти заново'); 

	
	}
	
}
