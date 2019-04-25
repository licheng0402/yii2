<?php

namespace app\controllers;

use Codeception\Extension\Logger;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

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
                'only' => ['logout'],
                'rules' => [
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
     * @return string
     */
    public function actionIndex()
    {
        Yii::info('test_message','cc');die;
        echo '<pre>';
        // 配置 kafka
        $conf = new \RdKafka\Conf();
        $conf->setDrMsgCb(function ($kafka, $message) {

            var_dump($kafka,$message,rd_kafka_err2str($message->err));
        });
        $conf->setErrorCb(function ($kafka, $err, $reason) {
            $error = sprintf("Kafka error: %s (reason: %s)", rd_kafka_err2str($err), $reason);
        });
        $conf->set('socket.timeout.ms', 3000);

        if (function_exists('pcntl_sigprocmask')) {
            // 此设置允许 librdkafka 线程在 librdkafka 完成后立即终止。有效地使 PHP 进程/请求快速终止
            pcntl_sigprocmask(SIG_BLOCK, array(SIGIO));
            $conf->set('internal.termination.signal', SIGIO);
        } else {
            // librdkafka 在发送一批消息之前将等待的最长和默认时间。将此设置减少到例如1ms 可确保尽快发送消息，而不是批处理。可以减少rdkafka 实例和 PHP 进程/请求的关闭时间。
            $conf->set('queue.buffering.max.ms', 1);
        }


        try{
            // kafka设置超时时间3s
            $topicConf = new \RdKafka\TopicConf();
            $topicConf->set("message.timeout.ms", 60000);
            $topicConf->set('request.required.acks', 0);
            // Producer 实例
            $rk = new \RdKafka\Producer($conf);
            $rk->addBrokers('127.0.0');
            $topic = $rk->newTopic('tv_server_logstash_log',$topicConf);
            $topic->produce(RD_KAFKA_PARTITION_UA, 0, "Message payload");
        }catch (\Exception $e){
            //var_dump($e);
        }
        while ($len = $rk->getOutQLen() > 0) {
            $rk->poll(0);
        }

        die;


    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
