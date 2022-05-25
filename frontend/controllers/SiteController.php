<?php
namespace frontend\controllers;

use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use frontend\models\Declaration;
use frontend\models\Invoice;
use frontend\models\User;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
				
        return $this->render('index');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        

		
		
		$decl_inv = invoice_absent();
		$user_name = Yii::$app->user->identity->username;  
		$user_info= User::find()->where(['=','id',Yii::$app->user->id])->asArray()->one();
		
	//	debug ($user_info);
		
		if ($decl_inv[0] !=0){
	 	
	 		if (Yii::$app->user->can('user')) { // Отправка сообщения об окончании работы
			
				
			$decl= Declaration::find()->where(['=','user_id',Yii::$app->user->id])
									  ->andWhere(['=','date',date('Y-m-d')])
									  ->andWhere(['!=','decl_number','Операции за день'])->count();
			
			$invoice=Invoice::find()->where(['=','user_id',Yii::$app->user->id])
									  ->andWhere(['=','date',date('Y-m-d')])->count();
			
			$date = date('d.m.Y');

			 
			$inv_abs_plus='';
			
			foreach ($decl_inv as $tabl) {
							$inv_abs_plus =$inv_abs_plus.$tabl['decl_number'].'</br>';
							  }
			$inv_abs = '<b>Не выставлены счета на выполненную работу: </b></br>'.$inv_abs_plus;
			
	
			$content   = '<b>Итоги работы за '.$date.'</b></br></br>'.
						 'Оформлено деклараций: '.$decl.'</br>'.
						 'Выставлено счетов: '.$invoice.'</b></br></br>'.
					     $inv_abs. 
						 '--------------------------------</br>'.
						 '<b>Офис on-line. </b>';		


		
			Yii::$app->mailer->compose()
			->setFrom(['sferaved@gmail.com' => 'Офис on-line'])
			->setReplyTo('sferaved@gmail.com')
			->setTo(['andrey18051@gmail.com','any26113@gmail.com',$user_info['email']])
			->setSubject($user_name.' НЕ ЗАКОНЧИЛА РАБОТУ.')
			->setHtmlBody($content)
		  ->send();	  
			}
		
		
		
		
			  return $this->render('index');  
		}
		else {  
		
			
			if (Yii::$app->user->can('user')) { // Отправка сообщения об окончании работы
			
			$decl= Declaration::find()->where(['=','user_id',Yii::$app->user->id])
									  ->andWhere(['=','date',date('Y-m-d')])
									  ->andWhere(['!=','decl_number','Операции за день'])->count();
			
			$invoice=Invoice::find()->where(['=','user_id',Yii::$app->user->id])
									  ->andWhere(['=','date',date('Y-m-d')])->count();
			
			$date = date('d.m.Y');
			
			
			$content   = '<b>Итоги работы за '.$date.'</b></br></br>'.
						 'Оформлено деклараций: '.$decl.'</br>'.
						 'Выставлено счетов: '.$invoice.'</br>'.
						 '--------------------------------</b></br>'.
						 '<b>Офис on-line. </b>';		


		
			Yii::$app->mailer->compose()
			->setFrom(['sferaved@gmail.com' => 'Офис on-line'])
			->setReplyTo('sferaved@gmail.com')
			->setTo(['andrey18051@gmail.com','any26113@gmail.com',$user_info['email']])
			->setSubject($user_name.' закончила работу.')
			->setHtmlBody($content)
		  ->send();	
			}
		 Yii::$app->user->logout();
         return $this->goHome();
		}  
	  

    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($user = $model->verifyEmail()) {
            if (Yii::$app->user->login($user)) {
                Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
                return $this->goHome();
            }
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
    }

}
