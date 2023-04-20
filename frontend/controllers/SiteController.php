<?php
namespace frontend\controllers;

use frontend\models\Cabinet;
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

        $decl = 0;
		$invoice = 0;
		
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
			->setFrom(['sferaved@ukr.net' => 'Офис on-line'])
			->setTo(['andrey18051@gmail.com','any26113@gmail.com',$user_info['email']])
			->setSubject($user_name.' НЕ ЗАКОНЧИЛА РАБОТУ.')
			->setHtmlBody($content)
		  ->send();	  
			}

            $message ="Не выставлены счета на выполненную работу: $inv_abs_plus";
            self::messageToBot($message, 120352595);

            return $this->render('index');
		}
		else {  
		
			
			if (Yii::$app->user->can('user')) { // Отправка сообщения об окончании работы
			
			$decl= Declaration::find()->where(['=','user_id',Yii::$app->user->id])
									  ->andWhere(['=','date',date('Y-m-d')])
									  ->andWhere(['!=','decl_number','Операции за день'])->count();
			
			$invoice=Invoice::find()->where(['=','user_id',Yii::$app->user->id])
									  ->andWhere(['=','date',date('Y-m-d')])->count();

			$payment_decl = Declaration::find()->asArray()->where(['=','user_id',Yii::$app->user->id])
                ->andWhere(['=','date',date('Y-m-d')])
                ->andWhere(['=','decl_number','Операции за день'])->all();

			$payment = Cabinet::find()->asArray()->where(['=', 'decl_id',  $payment_decl[0]['id']])->all();
			$date = date('d.m.Y');
			
			
			$content   = '<b>Итоги работы за '.$date.'</b></br></br>'.
						 'Оформлено деклараций: '.$decl.'</br>'.
						 'Выставлено счетов: '.$invoice.'</br>'.
						 '--------------------------------</b></br>'.
						 '<b>Офис on-line. </b>';		


		
			Yii::$app->mailer->compose()
			->setFrom(['sferaved@ukr.net' => 'Офис on-line'])
			->setTo(['andrey18051@gmail.com','any26113@gmail.com',$user_info['email']])
			->setSubject($user_name.' закончила работу.')
			->setHtmlBody($content)
		  ->send();
			}

            $message = "$user_name закончил(а) работу. Оформлено деклараций: $decl. Выставлено счетов: $invoice.";

            self::messageToBot($message, 474748019);
            $privat24 = $payment[0]['cost'];
            $message = $message . " Зарплата за день:  $privat24 грн";
            $buttons = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => 'Офис 🏢',
                            'url' => 'https://sferaved-office-online.ru'
                        ],
                        [
                            'text' => 'Приват24 🏦',
                            'url' => 'https://next.privat24.ua/'
                        ],
                    ],
                ]
            ];

            self::buttonsToBot($message, 120352595, json_encode($buttons));

            Yii::$app->user->logout();
         return $this->goHome();
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

    public function buttonsToBot($message, $chat_id, $buttons)
    {
        $bot = '6235702872:AAFW6QzdfvAILGe0oA9_X7lgx-I9O2w_Vg4';

        $array = array(
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'html',
            'reply_markup' => $buttons
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
