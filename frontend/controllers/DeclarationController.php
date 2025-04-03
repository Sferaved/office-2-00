<?php

namespace frontend\controllers;

use Smalot\PdfParser\Parser;
use Yii;
use frontend\models\Declaration;
use frontend\models\DeclarationSearch;
use frontend\models\User;
use common\models\Client;
use frontend\models\UploadForm;
use frontend\models\Cabinet;
use frontend\models\ParsedDeclarations;

use yii\base\BaseObject;
use yii\db\Query;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
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
        $decl= Declaration::find()->where(['=','user_id',Yii::$app->user->id])
            ->andWhere(['=','date',date('Y-m-d')])
            ->andWhere(['=','decl_number','Операции за день'])->count();
        if ($decl == 0) {
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
                'model' => $model,]);
        } else {
            Yii::$app->session->setFlash ('error', 'Операции за день - рарешено 1 раз в день');
            return $this->redirect('index');
        }




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
                            if ($pos != 1) {

                                 $model_A->custom = $tabl_pdf['custom'];
                                 $model_A->broker =	450.45 * $tabl_pdf["dop_list"] + 100;
                            } else {
                                $model_A->custom = $tabl_pdf['custom'] + 550;
                                $model_A->broker =450.45 * $tabl_pdf["dop_list"] + 100;
                            }
                        $model_A->dosmotr = 800;

					if (isset($tabl_pdf['contragent_id']))  {
						$model_A->contragent_id = $tabl_pdf['contragent_id'];
					}
					else {
						$model_A->contragent_id =0;
					};


					$model_A->save();
					}

					if ($model_d->client_id == 81) {
					$model_F = new Flex();	//Новая запись ФЛЄКССа

					$model_F->date =$model_d->date;
					$model_F->ex_im =$tabl_pdf['ex_im'];
					$model_F->decl_number_id  =$model_d->id;
                            if ($pos != 1) {
                                $model_F->custom = $tabl_pdf['custom'];
                                $model_F->broker =	450.45*$tabl_pdf["dop_list"] + 100;
                            } else {
                                $model_F->custom = $tabl_pdf['custom'] + 550;
                                $model_F->broker =	450.45*$tabl_pdf["dop_list"] + 100;
                            }
                        $model_F->dosmotr =	800;

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
     * @param int $id
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function actionInvoice($id)
    {
        $model_d = $this->findModel($id);

        $model = new Invoice();
        $model->date = $model_d->date;
        $model->user_id = (Yii::$app->user->id != 1) ? $model_d->user_id : 1;
        $model->decl_id = $model_d->id;
        $model->client_id = $model_d->client_id;
        $model->oplata = 'Нет';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Данные приняты');

            // Получаем нужные данные напрямую
            $decl = Declaration::findOne($model->decl_id)->decl_number;
            $clientModel = Client::findOne($model->client_id);
            $client = $clientModel->client;
            $dogovor = $clientModel->dogovor;
            $date_begin = date('d.m.Y', strtotime($clientModel->date_begin));
            $date_finish = date('d.m.Y', strtotime($clientModel->date_finish));

            $date = date('d.m.Y', strtotime($model->date));
            $user_name = Yii::$app->user->identity->username;

            // Генерация сообщения для отправки по email и боту
            $content = $this->generateInvoiceMessage($model, $client, $decl, $dogovor, $date_begin, $date_finish, $user_name);
//            $this->sendInvoiceEmail($model, $client, $content);

            $message = "$user_name выставил(а) счет за $date №: {$model->id} Клиент: $client Сумма: {$model->cost} грн";
            self::messageToBot($message, 120352595);
            self::messageToBot($message, 474748019);

            return $this->redirect(['view', 'id' => $model_d->id]);
        }

        return $this->render('invoice', [
            'model' => $model,
        ]);
    }

    /**
     * Generates the content for the invoice email.
     *
     * @param Invoice $model
     * @param string $client
     * @param string $decl
     * @param string $dogovor
     * @param string $date_begin
     * @param string $date_finish
     * @param string $user_name
     * @return string
     */
    private function generateInvoiceMessage($model, $client, $decl, $dogovor, $date_begin, $date_finish, $user_name)
    {
        return "<b>Выставлен новый счет за {$model->date}</b><br>" .
            "№: {$model->id}<br>" .
            "Клиент: $client<br>" .
            "Сумма: {$model->cost} грн<br>" .
            "Декларация: $decl<br>" .
            "Договор № $dogovor от $date_begin до $date_finish<br>" .
            "Выставила: $user_name<br>" .
            "--------------------------------<br>" .
            "<b>Офис on-line.</b>";
    }

    /**
     * Sends invoice email based on payment form and user role.
     *
     * @param Invoice $model
     * @param string $client
     * @param string $content
     */
    private function sendInvoiceEmail($model, $client, $content)
    {
        $recipients = [];
        if ($model->forma_oplat == 'Карта' && Yii::$app->user->can('user')) {
            $recipients = ['andrey18051@gmail.com'];
        } elseif ($model->forma_oplat != 'Карта' && Yii::$app->user->can('admin')) {
            $recipients = ['any26113@gmail.com'];
        } elseif ($model->forma_oplat != 'Карта' && Yii::$app->user->can('user')) {
            $recipients = ['andrey18051@gmail.com', 'any26113@gmail.com'];
        }

        if (!empty($recipients)) {
            Yii::$app->mailer->compose()
                ->setFrom(['sferaved@ukr.net' => 'Офис on-line'])
                ->setTo($recipients)
                ->setSubject('Новый счет на ' . $client)
                ->setHtmlBody($content)
                ->send();
        }
    }


    public function messageToBot($message, $chat_id)
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

    public function buttonsToBot($chat_id, $message, $button)
    {
        $bot = '6235702872:AAFW6QzdfvAILGe0oA9_X7lgx-I9O2w_Vg4';

        $array = array(
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'html',
            'reply_markup' => $button
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
		//Запись фито в базу ФЛЄКССа
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

            $message = "$user_name удалил(а) счет за $date №: $val Клиент: $client Сумма: $cost грн";

            self::messageToBot($message, 120352595);
            self::messageToBot($message, 474748019);
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

	    if  ($find_decl->client_id == 81) { //Удаление из базы ФЛЄКССа
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
//        $tabl_pdf = decl_parsing_full($file);
//        debug ($tabl_pdf);
//        exit;

		if (file_exists($file)) {
			/* return \Yii::$app->response->sendFile($file)->on(\yii\web\Response::EVENT_AFTER_SEND, function($event) {
			unlink($event->data);
			}, $file); */
			return \Yii::$app->response->sendFile($file);
		}
		throw new \Exception('File not found');

	}

//    public function actionParsing($id) // Скачивание изображений декларации из базы
//    {
//
//        $model = $this->findModel($id);
//
//        $pdf_decoded=base64_decode($model['decl_iso']);
//        //Write data back to pdf file
//
//        $img_file = 'files/'.'decl_iso_'.$id.'.pdf';
//
//        $pdf = fopen ($img_file,'w');
//        fwrite ($pdf,$pdf_decoded);
//        //close output file
//        fclose ($pdf);
//
//        $file = $img_file;
//        $tabl_pdf = decl_parsing_full($file);
//        debug ($tabl_pdf);
//        exit;
//
//        throw new \Exception('File not found');
//
//    }

    public function actionParsing($id) // Скачивание изображений декларации из базы
    {
        try {
            // Находим модель по ID
            $model = $this->findModel($id);

            // Декодируем PDF из базы данных
            $pdf_decoded = base64_decode($model['decl_iso']);

            // Путь для сохранения PDF файла
            $img_file = 'files/' . 'decl_iso_' . $id . '.pdf';

            // Открываем файл для записи
            $pdf = fopen($img_file, 'w');
            fwrite($pdf, $pdf_decoded);
            fclose($pdf); // Закрываем файл

            // Путь к файлу для парсинга
            $file = $img_file;

            // Парсим PDF
            $tabl_pdf = decl_parsing_full($file);
//            $tabl_pdf = $this->parse($file);
//            debug($tabl_pdf);

            if (is_array($tabl_pdf) && !empty($tabl_pdf)) {
                Yii::info("Результаты парсинга: " . print_r($tabl_pdf, true), __METHOD__);
//                debug($tabl_pdf);

                $parsedData = [

                    'contragent_id' => $tabl_pdf['contragent_id'],
                    'ex_im' => $tabl_pdf['ex_im'],
                    'cod_EGRPOU' => $tabl_pdf['cod_EGRPOU'],
                    'costCurrency' => $tabl_pdf['costCurrency'],
                    'costValue' => $tabl_pdf['costValue'],
                    'costCurs' => $tabl_pdf['costCurs'],

                    'decl' => $tabl_pdf['decl'],
                    'decl_date' => $tabl_pdf['decl_date'],
                    'client_id' => $tabl_pdf['client_id'],
                ];
                Yii::info('Данные, переданные в модель ParsedDeclarations: ' . print_r($parsedData, true), __METHOD__);

                $parsedModel = new ParsedDeclarations();

                $parsedModel->contragent_id = $tabl_pdf['contragent_id'];
                $parsedModel->ex_im = $tabl_pdf['ex_im'];
                $parsedModel->cod_EGRPOU = $tabl_pdf['cod_EGRPOU'];
                $parsedModel->costCurrency = $tabl_pdf['costCurrency'];
                $parsedModel->costValue = $tabl_pdf['costValue'];
                $parsedModel->costCurs = $tabl_pdf['costCurs'];

                $parsedModel->decl = $tabl_pdf['decl'];
                $parsedModel->decl_date = $tabl_pdf['decl_date'];
                $parsedModel->client_id = $tabl_pdf['client_id'];
                $parsedModel->save();

                Yii::info("Данные для декларации с ID " . $model['id'] . " успешно сохранены.", __METHOD__);

            }
            // Логируем результат парсинга
            Yii::info("Результаты парсинга для декларации с ID $id: " . print_r($tabl_pdf, true), __METHOD__);

            // Проверка на случай, если парсинг не вернул данных
            if (empty($tabl_pdf)) {
                Yii::error("Ошибка: данные парсинга для декларации с ID $id пусты.", __METHOD__);
            } else {
                Yii::debug("Парсинг для декларации с ID $id прошел успешно.", __METHOD__);
            }

            // Удаляем временный файл после обработки
            unlink($img_file);

        } catch (\Exception $e) {
            // Логируем ошибку
            Yii::error("Ошибка при обработке декларации с ID $id: " . $e->getMessage(), __METHOD__);
        }
    }


    public function parse($filePath)
    {
        // Укажите путь к вашему файлу


        if (!file_exists($filePath)) {
            throw new \yii\web\NotFoundHttpException('Файл не найден');
        }

        // Инициализация парсера
        $parser = new Parser();

        // Парсим файл
        $pdf = $parser->parseFile($filePath);

        // Извлекаем текст
        $text = $pdf->getText();
debug($text);
        // Выводим текст или сохраняем в базу данных
        return $this->render('parse', [
            'text' => $text,
        ]);
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

    public function actionParseDeclarations($page = 1)
    {
        ini_set('memory_limit', '4G');

        $pageSize = 10;
        $offset = ($page - 1) * $pageSize;

        // Выполняем запрос с учетом LIMIT и OFFSET
        Yii::debug("Запрос к базе данных: выбираем записи с LIMIT = $pageSize и OFFSET = $offset", __METHOD__);
        $declarations = \Yii::$app->db->createCommand('SELECT * FROM declaration WHERE decl_iso IS NOT NULL LIMIT :limit OFFSET :offset')
            ->bindValues([':limit' => $pageSize, ':offset' => $offset])
            ->queryAll();

        // Проверка результата запроса
        Yii::debug("Полученные данные для страницы $page: " . print_r($declarations, true), __METHOD__);

        if (!empty($declarations)) {
            foreach ($declarations as $model) {
                try {
                    // Логируем начало обработки записи
                    Yii::info("Начинаем обработку декларации с ID " . $model['id'], __METHOD__);

                    // Скачиваем изображение декларации, используя вашу функцию
                    $this->actionParsing($model['id']);

                    Yii::info("Обработка декларации с ID " . $model['id'] . " завершена.", __METHOD__);
                } catch (\Exception $e) {
                    Yii::info("Ошибка при обработке декларации с ID " . $model['id'] . ": " . $e->getMessage(), __METHOD__);
                }
            }

            // Переход к следующей странице
            Yii::debug("Продолжаем обработку на следующей странице: " . ($page + 1), __METHOD__);
            $this->actionParseDeclarations($page + 1);
        } else {
            Yii::debug("Данных для обработки больше нет, завершение.", __METHOD__);
            echo "Обработка завершена";
        }
    }

    public function actionCompareDeclarations()
    {
        // Пример "Операции за день" (можете заменить на нужное значение)
        $operationForTheDay = 'Операции за день'; // Установите значение для "Операции за день"

        // Получаем все значения из таблицы declaration, исключая "Операции за день"
        $parsedDeclarationsSubquery = (new Query())
            ->select('decl')
            ->from('parsed_declarations');

        // Получаем все значения из таблицы declaration, исключая "Операции за день"
        $declarationQuery = (new Query())
            ->select('decl_number')
            ->from('declaration')
            ->where(['not', ['decl_number' => $operationForTheDay]])  // Исключаем "Операцию за день"
            ->andWhere(['not in', 'decl_number', $parsedDeclarationsSubquery]); // Исключаем из подзапроса

        // Получаем результат запроса
        $declarations = $declarationQuery->all();

        // Преобразуем результат в массив с номерами деклараций
        $declarations = array_column($declarations, 'decl_number');

        // Возвращаем результат - номера деклараций, которых нет в parsed_declarations
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $declarations;
    }
//    public function actionParseDeclarations($page = 1)
//    {
//        ini_set('memory_limit', '4G');
//
//        $pageSize = 10;
//        $offset = ($page - 1) * $pageSize;
//
//        // Выполняем запрос с учетом LIMIT и OFFSET
//        Yii::debug("Запрос к базе данных: выбираем записи с LIMIT = $pageSize и OFFSET = $offset", __METHOD__);
//        $declarations = \Yii::$app->db->createCommand('SELECT * FROM declaration WHERE decl_iso IS NOT NULL LIMIT :limit OFFSET :offset')
//            ->bindValues([':limit' => $pageSize, ':offset' => $offset])
//            ->queryAll();
//
//        // Проверка результата запроса
//        Yii::debug("Полученные данные для страницы $page: " . print_r($declarations, true), __METHOD__);
//
//        if (!empty($declarations)) {
//            foreach ($declarations as $model) {
//                try {
//                    Yii::debug("Обработка декларации с ID " . $model['id'], __METHOD__);
//
//                    $isValid = $this->validatePdf($model['decl_iso']);
//                    if (!$isValid) {
//                        throw new \Exception('Файл не является валидным PDF или поврежден');
//                    }
//
//                    $pdf_decoded = base64_decode($model['decl_iso']);
//                    $img_file = 'files/' . 'decl_iso_' . $model['id'] . '.pdf';
//                    Yii::debug("Сохранение файла: $img_file", __METHOD__);
//                    $pdf = fopen($img_file, 'w');
//                    fwrite($pdf, $pdf_decoded);
//                    fclose($pdf);
//
//                    // Парсим PDF
//                    Yii::debug("Парсинг данных из PDF файла для декларации с ID " . $model['id'], __METHOD__);
//                    $tabl_pdf = decl_parsing_full($img_file);
//
//                    if (is_array($tabl_pdf) && !empty($tabl_pdf)) {
//                        debug ($tabl_pdf);
//                        Yii::debug("Результаты парсинга: " . print_r($tabl_pdf, true), __METHOD__);
//
//                        $parsedData = [
//                            'custom' => $tabl_pdf['custom'],
//                            'contragent_id' => $tabl_pdf['contragent_id'],
//                            'ex_im' => $tabl_pdf['ex_im'],
//                            'cod_EGRPOU' => $tabl_pdf['cod_EGRPOU'],
//                            'costCurrency' => $tabl_pdf['costCurrency'],
//                            'costValue' => $tabl_pdf['costValue'],
//                            'costCurs' => $tabl_pdf['costCurs'],
//                            'dop_list' => $tabl_pdf['dop_list'],
//                            'decl' => $tabl_pdf['decl'],
//                            'decl_date' => $tabl_pdf['decl_date'],
//                            'client_id' => $tabl_pdf['client_id'],
//                        ];
//
//                        $parsedModel = new ParsedDeclarations();
//                        $parsedModel->setAttributes($parsedData);
//
//                        if (!$parsedModel->save()) {
//                            Yii::error('Ошибка при сохранении данных для декларации с ID ' . $model['id'], __METHOD__);
//                            throw new \Exception('Ошибка при сохранении данных');
//                        }
//
//                        Yii::debug("Данные для декларации с ID " . $model['id'] . " успешно сохранены.", __METHOD__);
//                    } else {
//                        throw new \Exception('Ошибка парсинга для декларации с ID ' . $model['id']);
//                    }
//
//                    // Удаляем временный файл
//                    Yii::debug("Удаление временного файла для декларации с ID " . $model['id'], __METHOD__);
//                    unlink($img_file);
//                } catch (\Exception $e) {
//                    Yii::error('Ошибка при парсинге декларации с ID ' . $model['id'] . ': ' . $e->getMessage(), __METHOD__);
//                }
//            }
//
//            // Обработка завершена для первых 10 записей
//            Yii::debug("Обработка завершена для первых 10 деклараций.", __METHOD__);
//            echo "Обработка завершена для первых 10 деклараций.";
//        } else {
//            Yii::debug("Данных для обработки больше нет, завершение.", __METHOD__);
//            echo "Обработка завершена";
//        }
//    }

    public function actionParseFirstTenDeclarations()
    {
        // Запрашиваем первые 10 записей из таблицы declaration
        $declarations = \Yii::$app->db->createCommand('SELECT * FROM declaration WHERE decl_iso IS NOT NULL LIMIT 10')
            ->queryAll();
//        $this->actionParsing(2);
//        debug ($declarations);

//         Проверяем, если данные найдены
        if (!empty($declarations)) {
            foreach ($declarations as $model) {
                try {
                    // Логируем начало обработки записи
                    Yii::info("Начинаем обработку декларации с ID " . $model['id'], __METHOD__);

                    // Скачиваем изображение декларации, используя вашу функцию
                    $this->actionParsing($model['id']);

                    Yii::info("Обработка декларации с ID " . $model['id'] . " завершена.", __METHOD__);
                } catch (\Exception $e) {
                    Yii::info("Ошибка при обработке декларации с ID " . $model['id'] . ": " . $e->getMessage(), __METHOD__);
                }
            }

            Yii::info("Обработка первых 10 деклараций завершена.", __METHOD__);
        } else {
            Yii::info("Нет данных для обработки.", __METHOD__);
        }
    }


// Пример метода для проверки валидности PDF
    public function validatePdf($filePath)
    {
        // Проверка, что файл существует
        if (!file_exists($filePath)) {
            return false;
        }

        // Попробуем открыть файл и проверить его как PDF
        $file = fopen($filePath, 'rb');
        $bin = fread($file, 4);
        fclose($file);

        // Проверим, что файл начинается с "%PDF" (стандартный заголовок для PDF)
        if ($bin === '%PDF') {
            return true;
        }

        return false;
    }


}
