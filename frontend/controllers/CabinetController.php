<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Cabinet;
use frontend\models\Cabinetstatya;
use frontend\models\CabinetSearch;
use frontend\models\User;
use frontend\models\Declaration;
use common\models\Client;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use frontend\models\AuthAssignment;


/**
 * CabinetController implements the CRUD actions for Cabinet model.
 */
class CabinetController extends Controller
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
     * Lists all Cabinet models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CabinetSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $models = $dataProvider->getModels(); // Таблица отбранных записей
//User		

		
		$arr_W = ['user'];
		$arrUsers = AuthAssignment::find()->where(['item_name'=>$arr_W])->all();
		
		foreach ($arrUsers as $value) (            //Получили отобранные id=User
				   $arrIdUser[] = $value->user_id
				);
		
		$arrUs = User::find()->where(['id' => $arrIdUser]) ->all(); 

        foreach ($arrUs as $value) (// пара ключ-значение для отобранных записей
           $arrUser[$value->id] = $value->username
        );
		
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
//Client

		$arrCl = Client::find()->all(); 	
		
        foreach ($arrCl as $value) (// пара ключ-значение для отобранных записей
           $arrClient[$value->id] = $value->client
        );
		
//Cabinetstatya


		$arrCabSt = Cabinetstatya::find()->all(); 	
	
        foreach ($arrCabSt as $value) (// пара ключ-значение для отобранных записей
           $arrCab[$value->id] = $value->statya
        );
		
		
//	debug ($models);

//Итоги по cost	
	    $sumCost['0']=0;
		$sumCost['1']=0;
		
		 foreach ($models as $value) {// пара ключ-значение для отобранных записей
          if ($value['coment_id'] != 103) {
			   $sumCost['0'] += $value->cost;
		  }
		  else {
			  $sumCost['1'] += $value->cost;
		  }
		 
		 };
		
	// 	debug ($sumCost);
		$sumCost = $sumCost['1']-$sumCost['0'];

		
// баланс
    
    $arrCabinet_bal =cabinet_bal();		

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
			'arrUser' =>$arrUser,
            'arrCabinet_bal' =>$arrCabinet_bal,
			'arrDecl' =>$arrDecl,
			'arrClient' =>$arrClient,
			'arrCab' =>$arrCab,
			'sumCost' =>$sumCost,
        ]);
		

    }

    /**
     * Displays a single Cabinet model.
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
     * Creates a new Cabinet model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Cabinet();
	 
		$model->date = date ('Y-m-d');
	
        $model->user_id = Yii::$app->user->id;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
			Yii::$app->session->setFlash ('success', 'Данные приняты');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Cabinet model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
			Yii::$app->session->setFlash ('success', 'Данные приняты');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Cabinet model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Cabinet model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Cabinet the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Cabinet::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
	 public function actionFile($id) // Скачивание изображений декларации из базы
    {
 
		$model = Declaration::find()->where(['=','id',$id])->one();
				
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
	public function actionExport($date_from,$date_to,$id)// Скачивание отчета брокера
    {
 	 	
        broker_report ($date_from,$date_to,$id); 
		$file = 'files/broker.xls';
		
		if (file_exists($file)) {
			 return \Yii::$app->response->sendFile($file)->on(\yii\web\Response::EVENT_AFTER_SEND, function($event) {
			unlink($event->data);
			}, $file); 
		} 
		throw new \Exception('File not found'); 
	
	}
}
