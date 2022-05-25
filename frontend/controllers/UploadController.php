<?php

namespace frontend\controllers;


use Yii;
use yii\web\Controller;
use frontend\models\UploadForm;
use yii\web\UploadedFile;



class UploadController extends Controller
{
    public $layout = 'main';
    
   public function actionIndex()
   {
    
    $model = new UploadForm();
    
  if(Yii::$app->request->post())
  {
  $model->file = UploadedFile::getInstance($model, 'file');
    if ($model->validate()) {
     $model->file->saveAs( 'files/' . $model->file->baseName . '.' . $model->file->extension);
    }
    }
	
	
	

    return $this->render('index', ['model'=>$model]);
   } 
}
