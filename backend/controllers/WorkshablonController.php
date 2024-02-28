<?php

namespace backend\controllers;

use Yii;
use common\models\Workshablon;
use common\models\Client;
use common\models\Workstatya;

use backend\models\WorkshablonSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * WorkshablonController implements the CRUD actions for Workshablon model.
 */
class WorkshablonController extends Controller
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
     * Lists all Workshablon models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new WorkshablonSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $models = $dataProvider->getModels(); // Таблица отбранных записей

//Client

		$arrCl = Client::find()->all();

        foreach ($arrCl as $value) (// пара ключ-значение для отобранных записей
           $arrClient[$value->id] = $value->client
        );
		
//Statya
        $arrIdStatya = []; // Определение переменной как пустого массива
		foreach ($models  as $value) (            //Получили отобранные id статей
           $arrIdStatya[$value->id] = $value->statya_id
		);
		if($arrIdStatya !=null) {
			$arrIdStatya = array_unique($arrIdStatya);   //Убрали повторяющиеся статьи отобранных записей
		};
		$arrSt = Workstatya::find()->where(['id' => $arrIdStatya])->all(); // пара ключ-значение для отобранных записей
        $arrStatya =  [];
        foreach ($arrSt as $value) (
           $arrStatya[$value->id] = $value->statya
        );
				
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
			'arrClient' =>$arrClient,
			'arrStatya' =>$arrStatya,
        ]);
    }

    /**
     * Displays a single Workshablon model.
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
     * Creates a new Workshablon model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Workshablon();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
			Yii::$app->session->setFlash ('success', 'Данные приняты');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Workshablon model.
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
     * Deletes an existing Workshablon model.
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
     * Finds the Workshablon model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Workshablon the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Workshablon::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
