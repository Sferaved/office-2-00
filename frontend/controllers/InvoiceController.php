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
        $models = $dataProvider->getModels(); // Таблица отобранных записей

        // Decl
        $arrIdDecl = [];
        foreach ($models as $value) { // Получение отобранных id деклараций
            $arrIdDecl[$value->id] = $value->decl_id;
        }
        $arrDecl = [];
        if (!empty($arrIdDecl)) {
            $arrIdDecl = array_unique($arrIdDecl); // Убираем повторяющиеся id деклараций
            $arrDCl = Declaration::find()->where(['id' => $arrIdDecl])->all();

            foreach ($arrDCl as $value) { // Пара ключ-значение для отобранных записей
                $arrDecl[$value->id] = $value->decl_number;
            }
        } else {
            $arrDecl[0] = '';
        }

        // User
        $arr_W = ['user', 'admin'];
        $arrUsers = AuthAssignment::find()->where(['item_name' => $arr_W])->all();
        $arrIdUser = [];

        foreach ($arrUsers as $value) { // Получаем отобранные id пользователей
            $arrIdUser[] = $value->user_id;
        }

        $arrUser = [];
        if (!empty($arrIdUser)) {
            $arrUs = User::find()->where(['id' => $arrIdUser])->all();

            foreach ($arrUs as $value) { // Пара ключ-значение для отобранных пользователей
                $arrUser[$value->id] = $value->username;
            }
        }

        // Client
        $arrCl = Client::find()->all();
        $arrClient = [];

        foreach ($arrCl as $value) { // Пара ключ-значение для отобранных клиентов
            $arrClient[$value->id] = $value->client;
        }

        // Итоги по Invoice
        $sumInvoice = 0;
        foreach ($models as $value) {
            $sumInvoice += $value->cost;
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'arrClient' => $arrClient,
            'arrUser' => $arrUser,
            'arrDecl' => $arrDecl,
            'sumInvoice' => $sumInvoice
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
//        $cacheKey = 'view_invoice_' . $id;
//        $model = Yii::$app->cache->get($cacheKey);
//
//        if ($model === false) {
//            $model = $this->findModel($id);
//
//            if ($model === null) {
//                throw new NotFoundHttpException("Запрашиваемая страница не найдена.");
//            }
//
//            // Сохраняем модель в кэш на 1 час
//            Yii::$app->cache->set($cacheKey, $model, 3600);
//        }
        $model = $this->findModel($id);

        if ($model === null) {
            throw new NotFoundHttpException("Запрашиваемая страница не найдена.");
        }
        return $this->render('view', [
            'model' => $model,
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

    /**
     * Updates an existing Invoice model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */

//    public function actionUpdate($id)
//    {
//        $cacheKey = 'invoice_' . $id;
//        Yii::info("Начало обновления статуса оплаты счета с ID: $id", __METHOD__);
//
//        // Обновление поля oplata на 'Да' без загрузки полной модели
//        $rowsUpdated = Invoice::updateAll(['oplata' => 'Да'], ['id' => $id]);
//
//        if ($rowsUpdated > 0) {
//            Yii::$app->cache->delete($cacheKey); // Очистка кеша после обновления
//            Yii::$app->session->setFlash('success', 'Статус оплаты счета обновлен.');
//            Yii::info("Статус оплаты счета с ID: $id обновлен на 'Да'.", __METHOD__);
//
//            $date = date('d.m.Y');
//            $user_name = Yii::$app->user->identity->username;
//            $message = "$user_name подтвердил(а) оплату счета №: $id за $date";
//            self::messageToBot($message, 120352595);
//            self::messageToBot($message, 474748019);
//
//            return $this->redirect(['view', 'id' => $id]);
//        } else {
//            Yii::$app->session->setFlash('error', 'Ошибка при обновлении статуса оплаты счета.');
//            Yii::error("Ошибка при обновлении статуса оплаты счета с ID: $id.", __METHOD__);
//            throw new NotFoundHttpException("Счет не найден или статус не был обновлен.");
//        }
//    }

    public function actionUpdate($id)
    {
        $cacheKey = 'invoice_' . $id;
        Yii::info("Начало обновления счета с ID: $id", __METHOD__);
        ini_set('memory_limit', '2G');
        // Используем кеширование модели
        $model = Yii::$app->cache->get($cacheKey);

        if ($model === false) {
            Yii::info("Модель счета не найдена в кэше, загружаем из базы данных.", __METHOD__);
            $model = Invoice::find()
                ->where(['id' => $id])
                ->select(['id', 'client_id', 'decl_id', 'cost', 'date', 'oplata', 'user_id', 'forma_oplat']) // Выберите только необходимые поля
//                ->asArray()
                ->one();
            if ($model !== null) {
                Yii::$app->cache->set($cacheKey, $model, 3600);
                Yii::info("Модель счета загружена и сохранена в кэш.", __METHOD__);
            } else {
                Yii::warning("Счет с ID: $id не найден в базе данных.", __METHOD__);
            }
        } else {
            Yii::info("Модель счета загружена из кэша.", __METHOD__);
        }

        if ($model === null) {
            throw new NotFoundHttpException("Счет не найден.");
        }

        if ($model->load(Yii::$app->request->post())) {
            // Пробуем сохранить модель
            Yii::info("Попытка сохранить модель счета.", __METHOD__);
            if ($model->save(false)) { // Убираем валидацию, если она не нужна
                Yii::$app->session->setFlash('success', 'Счет успешно исправлен');
                Yii::info("Счет с ID: $id успешно сохранен.", __METHOD__);

                // Извлечение данных декларации
                $declNumber = Declaration::find()
                    ->select('decl_number')
                    ->where(['id' => $model->decl_id])
                    ->scalar();

                // Извлечение данных клиента
                $clientDetails = Client::find()
                    ->select(['client', 'dogovor', 'date_begin', 'date_finish'])
                    ->where(['id' => $model->client_id])
                    ->asArray()
                    ->one();

                // Проверка наличия данных клиента
                if ($clientDetails) {
                    $client = $clientDetails['client'];
                    $dogovor = $clientDetails['dogovor'];
                    $date_begin = date('d.m.Y', strtotime($clientDetails['date_begin']));
                    $date_finish = date('d.m.Y', strtotime($clientDetails['date_finish']));
                    Yii::info("Данные клиента успешно извлечены: $client", __METHOD__);
                } else {
                    // Установка значений по умолчанию
                    $client = $dogovor = $date_begin = $date_finish = null;
                    Yii::warning("Данные клиента не найдены для счета с ID: $id.", __METHOD__);
                }

                $date = date('d.m.Y', strtotime($model->date));
                $user_name = Yii::$app->user->identity->username;
                $content = $this->generateEmailContent($id, $date, $client, $model->cost, $declNumber, $dogovor, $date_begin, $date_finish, $user_name);

                // Отправка уведомлений
//                $this->sendEmail('Изменения в счете на ' . $client, $content, ['andrey18051@gmail.com', 'any26113@gmail.com']);
                Yii::info("Уведомления успешно отправлены.", __METHOD__);

                $message = "$user_name исправил(а) счет за $date №: $model->id Клиент: $client Сумма: $model->cost грн";
                self::messageToBot($message, 120352595);
                self::messageToBot($message, 474748019);

                // Проверка оплаты счета
                if ($model->oplata == 'Да' && Yii::$app->user->can('buh')) {
                    $arrEmail = User::find()
                        ->select('email')
                        ->where(['id' => $model->user_id])
                        ->scalar();

                    if ($arrEmail) {
//                        $this->sendEmail('Оплата счета на ' . $client, $content, ['andrey18051@gmail.com', $arrEmail]);
                        Yii::info("Уведомление об оплате счета отправлено пользователю с ID: {$model->user_id}.", __METHOD__);

                        $message = "Оплачен счет за $date №: $id Клиент: $client Сумма: $model->cost грн";
                        self::messageToBot($message, 120352595);
                    }
                }

                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка при сохранении счета.');
                Yii::error("Ошибка при сохранении счета с ID: $id.", __METHOD__);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }
    protected function generateEmailContent($id, $date, $client, $cost, $declNumber, $dogovor, $date_begin, $date_finish, $user_name)
    {
        // Шаблон письма
        $content = "<h1>Изменения в счете</h1>";
        $content .= "<p>Уважаемый(ая) {$client},</p>";
        $content .= "<p>Сообщаем вам, что счет с ID: <strong>{$id}</strong> был обновлен.</p>";
        $content .= "<p><strong>Дата:</strong> {$date}</p>";
        $content .= "<p><strong>Сумма:</strong> {$cost} грн</p>";
        $content .= "<p><strong>Номер декларации:</strong> {$declNumber}</p>";
        $content .= "<p><strong>Договор:</strong> {$dogovor}</p>";
        $content .= "<p><strong>Дата начала:</strong> {$date_begin}</p>";
        $content .= "<p><strong>Дата окончания:</strong> {$date_finish}</p>";
        $content .= "<p><strong>Исполнитель:</strong> {$user_name}</p>";
        $content .= "<p>Спасибо за ваше сотрудничество!</p>";

        return $content;
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
        Yii::$app->session->setFlash('success', 'Счет успешно удален');

        $clientArr=Client::find()->where(['=','id',$model->client_id])->all();
        foreach ($clientArr as $value) {
            $client= $value->client;
        };

        $date = date('d.m.Y', strtotime($model->date));
        $user_name = Yii::$app->user->identity->username;

        $message = "$user_name удалил(а) счет за $date №: $model->id Клиент: $client Сумма: $model->cost грн";
        self::messageToBot($message, 120352595);
        self::messageToBot($message, 474748019);

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        $cacheKey = 'invoice_' . $id;
//        $model = Yii::$app->cache->get($cacheKey);
//
//        if ($model === false) {
//            $model = Invoice::findOne($id);
//            if ($model !== null) {
//                Yii::$app->cache->set($cacheKey, $model, 3600); // Кэш на 1 час
//            }
//        }
        $model = Invoice::findOne($id);
        if ($model !== null) {
            Yii::$app->cache->set($cacheKey, $model, 3600); // Кэш на 1 час
        }
        if ($model !== null) {
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
