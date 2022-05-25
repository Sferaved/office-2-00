<?php
namespace frontend\controllers;
//namespace common\models;

use Yii;
use yii\filters\AccessControl;
use app\models\Homezatraty;
use app\models\HomezatratySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\HomestatyaModel;


/**
 * HomezatratyController implements the CRUD actions for Homezatraty model.
 */
class HomezatratyController extends Controller
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
     * Lists all Homezatraty models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new HomezatratySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $models = $dataProvider->getModels(); // Таблица отбранных записей


		 $arrHS = HomestatyaModel::find()->all();	

		foreach ($arrHS as $value) (
           $arrHomestatya[$value->id] = $value->statya
        );

    // баланс
    $zatraty_home_tabl = Homezatraty::find() ->asArray()-> where (['<=','date', date('Y-m-d')])->  all ();
    $cost_all_after_day_minus =0;
    $cost_all_after_day_plus=0;
    
    foreach ($zatraty_home_tabl as $tabl) {
                   
        $statya = HomestatyaModel::findOne($tabl["statya"]); 
        $st = $statya ['statya'];
        $minus_plus= $statya ['income']; 
    
        if ($minus_plus == 'Нет') {
            $cost_all_after_day_minus += $tabl["cost"];
        }
        else {
            $cost_all_after_day_plus += $tabl["cost"];
        }
        }
    
 //Итоги по cost	
	    $sumCost['0']=0;
		$sumCost['1']=0;
		
		 foreach ($models as $value) {// пара ключ-значение для отобранных записей
          if ($value['statya'] != 106) {
			   $sumCost['0'] += $value->cost;
		  }
		  else {
			  $sumCost['1'] += $value->cost;
		  }
		 
		 };
		
	// 	debug ($sumCost);
		$sumCost = $sumCost['1']-$sumCost['0'];

		
    $cost_all_after_day =$cost_all_after_day_plus - $cost_all_after_day_minus; 

    return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            /* 'arrIdHome' =>$arrIdHome, */
            'arrHomestatya' =>$arrHomestatya,
            'cost_all_after_day' =>$cost_all_after_day,
			'sumCost' =>$sumCost,
        ]);
    }

    /**
     * Displays a single Homezatraty model.
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
     * Creates a new Homezatraty model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Homezatraty();

        $model->date   = date('Y-m-d');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash ('success', 'Данные приняты');
            return $this->redirect(['view', 
            'id' => $model->id,]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Homezatraty model.
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
     * Deletes an existing Homezatraty model.
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
     * Finds the Homezatraty model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Homezatraty the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Homezatraty::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
	
	public function actionExport($date_from,$date_to)// Скачивание отчета брокера
    {
 	 	
        home_report ($date_from,$date_to); 
		$file = 'files/home.xls';
		
		if (file_exists($file)) {
			 return \Yii::$app->response->sendFile($file)->on(\yii\web\Response::EVENT_AFTER_SEND, function($event) {
			unlink($event->data);
			}, $file); 
		} 
		throw new \Exception('File not found'); 
	
	}
}
