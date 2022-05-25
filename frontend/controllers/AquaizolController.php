<?php

namespace frontend\controllers;

use Yii;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

use frontend\models\Aquaizol;
use frontend\models\AquaizolSearch;
use frontend\models\Declaration;
use common\models\Contragent;

/**
 * AquaizolController implements the CRUD actions for Aquaizol model.
 */
class AquaizolController extends Controller
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
                        'actions' => ['index','view'],
                        'allow' => true,
                        'roles' => ['runo'],
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
     * Lists all Aquaizol models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AquaizolSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $models = $dataProvider->getModels(); // Таблица отбранных записей
		
//Decl		
		 foreach ($models  as $value) (            //Получили отобранные id деклараций
           $arrIdDecl[$value->id] = $value->decl_number_id
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
//Contragent		
		
		$arrCntr = Contragent::find()->all(); 
		foreach ($arrCntr as $value) (// пара ключ-значение для отобранных записей
           $arrContr[$value->id] = $value->contragent
        );
		
        
//Итоги по broker	
	    $sumBroker=0;
		foreach ($models  as $value) (           
           $sumBroker += $value->broker
		);
		
//Итоги по dosmotr	
	    $sumDosmotr=0;
		foreach ($models  as $value) (           
           $sumDosmotr += $value->dosmotr
		);

//Итоги по custom	
	    $sumCustom=0;
		foreach ($models  as $value) (           
           $sumCustom += $value->custom
		);

//Итоги по Досмотр	
	    $sumFito=0;
		foreach ($models  as $value) (           
           $sumFito += $value->fito
		);
		
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
			'arrDecl' =>$arrDecl,
			'arrContr' =>$arrContr,
			'sumBroker' =>$sumBroker,
			'sumDosmotr' =>$sumDosmotr,
			'sumCustom' =>$sumCustom,
			'sumFito' =>$sumFito,
        ]);
    }


		

    /**
     * Displays a single Aquaizol model.
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
     * Creates a new Aquaizol model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Aquaizol();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Aquaizol model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Aquaizol model.
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
     * Finds the Aquaizol model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Aquaizol the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Aquaizol::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
	
	public function actionExport($aq_fl_id,$date_from,$date_to)// Скачивание отчета
    {
 	 	
        aq_fl_report($aq_fl_id,$date_from,$date_to); 
		$file = 'files/report.xls';
		
		if (file_exists($file)) {
			 return \Yii::$app->response->sendFile($file)->on(\yii\web\Response::EVENT_AFTER_SEND, function($event) {
			unlink($event->data);
			}, $file); 
		} 
		throw new \Exception('File not found'); 
	
	}
	
	
	public function actionDocuments($aq_fl_id,$date_from,$date_to)// Формирование документов
    {
 	 	 
		aq_fl_documents($aq_fl_id,$date_from,$date_to);
		
		$file='files/documents.zip';
		
		if (file_exists($file)) {
			 return \Yii::$app->response->sendFile($file)->on(\yii\web\Response::EVENT_AFTER_SEND, function($event) {
			unlink($event->data);
			}, $file); 
		} 
		throw new \Exception('File not found'); 
	
        
	}
	
	public function actionFile($id) // Скачивание изображений декларации из базы
    {
 
		$model = Declaration::find()-> where (['=','id', $id]) ->one();
		
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
	
}
