<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Invoice;
use app\models\InvoiceSearch;
use frontend\models\User;
use common\models\Client;
use frontend\models\Declaration;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

use yii2tech\spreadsheet\Spreadsheet;
use yii\data\ActiveDataProvider;
use frontend\models\AuthAssignment;


/**
 * InvoiceController implements the CRUD actions for Invoice model.
 */
class InvoiceController extends Controller
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
     * Lists all Invoice models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new InvoiceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $models = $dataProvider->getModels(); // Таблица отбранных записей
		
//Decl		
		 foreach ($models  as $value) (            //Получили отобранные id деклараций
           $arrIdDecl[$value->id] = $value->decl_id
		);
		if(isset ($arrIdDecl)) {
			$arrIdDecl = array_unique($arrIdDecl);   //Убрали повторяющиеся статьи отобранных записей
		
		$arrDCl = Declaration::find()->where(['id' => $arrIdDecl])->all(); 
		
		foreach ($arrDCl as $value) (//пара ключ-значение для отобранных записей
           $arrDecl[$value->id] = $value->decl_number
        );
		}
		else {
		$arrDecl[0] = '';	
		};
 
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
		
//Client		
		$arrCl = Client::find()->all(); 	
	
        foreach ($arrCl as $value) (// пара ключ-значение для отобранных записей
           $arrClient[$value->id] = $value->client
        );

		

//Итоги по Invoice	
	    $sumInvoice=0;
		foreach ($models  as $value) (           
           $sumInvoice += $value->cost
		);
		
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
			'arrClient' =>$arrClient,
			'arrUser' =>$arrUser,
			'arrDecl' =>$arrDecl,
			'sumInvoice' =>$sumInvoice
		  ]);
    }

    /**
     * Displays a single Invoice model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Invoice model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    public function actionCreate()
    {
	
		$model = new Invoice();
		
        

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
			Yii::$app->session->setFlash ('success', 'Данные приняты');
			
		 // Отправка сообщения об выставленном счете
		
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
						 '№ счета: '.$model->id.'</br>'.
						 'Клиент: '.$client.'</br>'.
						 'Сумма: '.$model->cost.'грн</br>'.
						 'Декларация: '.$decl.'</br>'.
						 'Договор № '.$dogovor.' от '.$date_begin.' до '.$date_finish.'</br>'.
						 'Выставил(а) счет: '.$user_name.'</br>'.
						 '--------------------------------</b></br>'.
						 '<b>Офис on-line. </b>';		


		//echo $content;
		
			Yii::$app->mailer->compose()
			->setFrom(['sferaved@ukr.net' => 'Офис on-line'])
	//		->setReplyTo('sferaved@gmail.com')
			->setTo(['andrey18051@gmail.com','any26113@gmail.com'])
			->setSubject('Новый счет на '.$client)
			->setHtmlBody($content)
		  ->send();

              $message = "$user_name выставил(а) счет за $date №: $model->id Клиент: $client Сумма: $model->cost грн";
              self::messageToBot($message, 120352595);
              self::messageToBot($message, 474748019);



            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }
    public function messageToBot($message, $chat_id)
    {
        $bot = '6235702872:AAFW6QzdfvAILGe0oA9_X7lgx-I9O2w_Vg4';

        $array = array(
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'html'
        );

        $url = 'https://api.tlgr.org/bot' . $bot . '/sendMessage';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($array, '', '&'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * Updates an existing Invoice model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
			Yii::$app->session->setFlash ('success', 'Счет успешно исправлен');
			
			// Отправка сообщения об исправленном счете
		
            $declArr= Declaration::find()->where(['=','id',$model->decl_id])->all();
			foreach ($declArr as $value) 
			(
			$decl= $value->decl_number
			
			);
			
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
			
			$content   = '<b>Исправлен счет №'.$id.' от '.$date.'</b></br>'.
						 'Клиент: '.$client.'</br>'.
						 'Сумма: '.$model->cost.'грн</br>'.
						 'Декларация: '.$decl.'</br>'.
						 'Договор № '.$dogovor.' от '.$date_begin.' до '.$date_finish.'</br>'.
						 'Исправила: '.$user_name.'</br>'.
						 '--------------------------------</b></br>'.
						 '<b>Офис on-line. </b>';		

		
			Yii::$app->mailer->compose()
			->setFrom(['sferaved@ukr.net' => 'Офис on-line'])
	//		->setReplyTo('sferaved@gmail.com')
			->setTo(['andrey18051@gmail.com','any26113@gmail.com'])
			->setSubject('Изменения в счете на '.$client)
			->setHtmlBody($content)
		  ->send();
            $message = "$user_name исправил(а) счет за $date №: $model->id Клиент: $client Сумма: $model->cost грн";
            self::messageToBot($message, 120352595);
            self::messageToBot($message, 474748019);

//
        $invArr= Invoice::find()->where(['=','id',$id])->one();
		  
		 $arrEmail = User:: find()->where(['=','id',$invArr['user_id']])->one();
		
		
		
		if ($invArr['oplata'] == 'Да') {
			if (Yii::$app->user->can('buh')) { // Отправка сообщения об оплате счета
		
            $declArr= Declaration::find()->where(['=','id',$model->decl_id])->all();
			foreach ($declArr as $value) 
			(
			$decl= $value->decl_number
			
			);
			
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
			
			$content   = '<b>Оплачен счет №'.$id.' от '.$date.'</b></br>'.
						 'Клиент: '.$client.'</br>'.
						 'Сумма: '.$model->cost.'грн</br>'.
						 'Декларация: '.$decl.'</br>'.
						 'Договор № '.$dogovor.' от '.$date_begin.' до '.$date_finish.'</br>'.
						 '--------------------------------</b></br>'.
						 '<b>Офис on-line. </b>';		

	
			Yii::$app->mailer->compose()
			->setFrom(['sferaved@ukr.net' => 'Офис on-line'])
		//	->setReplyTo('sferaved@gmail.com')
			->setTo(['andrey18051@gmail.com',$arrEmail['email']])
			->setSubject('Изменения в счете на '.$client)
			->setHtmlBody($content)
		  ->send();

                $message = "Оплачен счет за $date №: $id Клиент: $client Сумма: $model->cost грн";
                self::messageToBot($message, 120352595);
        }
		}

//			
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Invoice model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
		$model = $this->findModel($id);
        $this->findModel($id)->delete();
		Yii::$app->session->setFlash ('success', 'Счет успешно удален');
		
		 // Отправка сообщения об удаленном счете
		
			$declArr= Declaration::find()->where(['=','id',$model->decl_id])->all();
			foreach ($declArr as $value) 
			(
			$decl= $value->decl_number
			
			);
			
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
			
			$content   = '<b>Удален счет </b>'.$id.' от '.$date.'</b></br>'.
						 'Клиент: '.$client.'</br>'.
						 'Сумма: '.$model->cost.'грн</br>'.
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
        $message = "$user_name удалил(а) счет за $date №: $model->id Клиент: $client Сумма: $model->cost грн";
        self::messageToBot($message, 120352595);
        self::messageToBot($message, 474748019);

        return $this->redirect(['index']);
    }

    /**
     * Finds the Invoice model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Invoice the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Invoice::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
	
	
	public function actionExport($file)// Скачивание cчета декларации из базы
    {
 	 
	
		$file = 'files/'.$file.'.xls';
	 
		if (file_exists($file)) {
			/* return \Yii::$app->response->sendFile($file)->on(\yii\web\Response::EVENT_AFTER_SEND, function($event) {
			unlink($event->data);
			}, $file); */
			return \Yii::$app->response->sendFile($file);
		} 
		throw new \Exception('File not found'); 

	
	}

	public function actionFile($id)
    {
        invoice_file($id);

		$file = 'files/invoice.xls';

		if (file_exists($file)) {
			 return \Yii::$app->response->sendFile($file)->on(\yii\web\Response::EVENT_AFTER_SEND, function($event) {
			unlink($event->data);
			}, $file); 
		} 
		throw new \Exception('Нужно найти заново'); 

	
	}
	
	
}
