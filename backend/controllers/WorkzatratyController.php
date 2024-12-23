<?php

namespace backend\controllers;

use Yii;
use backend\models\Workzatraty;
use backend\models\WorkzatratySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

use common\models\Client;
use frontend\models\Declaration;
use common\models\Workstatya;

/**
 * WorkzatratyController implements the CRUD actions for Workzatraty model.
 */
class WorkzatratyController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
		    'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['admin'],
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
     * Lists all Workzatraty models.
     * @return mixed
     */
    public function actionIndex()
    {
        ini_set('memory_limit', '2G');
        $searchModel = new WorkzatratySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
		$models = $dataProvider->getModels(); // Таблица отбранных записей
	

//Client

		$arrCl = Client::find()->all(); 	
		
        foreach ($arrCl as $value) (// пара ключ-значение для отобранных записей
           $arrClient[$value->id] = $value->client
        );
		
//Statya		
 
		$arrSt = Workstatya::find()->where(['=','report','Да'])->all();  

        foreach ($arrSt as $value) (
           $arrStatya[$value->id] = $value->statya
        );	

	
//Decl		
	
		$arrDCl = Declaration::find()->where(['!=','decl_number','Операции за день'])->all(); // пара ключ-значение для отобранных записей

        foreach ($arrDCl as $value) (
           $arrDecl[$value->id] = $value->decl_number
        );	
	 
	//	$arrDecl=array_reverse($arrDecl);
//Итоги по cost	
	    $sumCost=0;
		foreach ($models  as $value) (           
           $sumCost += $value->cost
		);		
	
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
			'arrClient' =>$arrClient,
			'arrDecl' =>$arrDecl,
			'arrStatya' =>$arrStatya,
			'sumCost' =>$sumCost,
        ]);
		 
    }



    /**
     * Displays a single Workzatraty model.
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
     * Creates a new Workzatraty model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Workzatraty();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
			Yii::$app->session->setFlash ('success', 'Данные приняты');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Workzatraty model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        ini_set('memory_limit', '2G');
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Workzatraty model.
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
     * Finds the Workzatraty model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Workzatraty the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Workzatraty::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
	
	public function actionExport($date_from,$date_to)// Скачивание отчета
    {
 	 
	    workzatraty_report($date_from,$date_to);
		$file = 'files/report.xls';
	 
		if (file_exists($file)) {
			 return \Yii::$app->response->sendFile($file)->on(\yii\web\Response::EVENT_AFTER_SEND, function($event) {
			unlink($event->data);
			}, $file); 
		} 
		throw new \Exception('Нужно найти заново'); 

	
	}
}
