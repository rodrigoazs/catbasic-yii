<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use GuzzleHttp\Client;
use yii\helpers\BaseJson;
use app\models\Cat;

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
        $client = new Client();
        $response = $client->request('GET', 'https://api.thecatapi.com/v1/breeds', [
            // 'query' => ['limit' => 5]
        ]);

        // echo $response->getStatusCode(); // 200
        // echo $response->getHeaderLine('content-type'); // 'application/json; charset=utf8'
        // echo $response->getBody(); // '{"id": 1420053, "name": "guzzle", ...}'

        $json = new BaseJson();
        $result = $json->decode($response->getBody());

        // shuffle array
        shuffle($result);

        // get 5 first elements
        $result = array_slice($result, 0, 5);

        // convert to Cat model
        $arr = array();
        $model = new Cat();
        for ($i = 0; $i < count($result); $i++) {
            $response = $client->request('GET', 'https://api.thecatapi.com/v1/images/search', [
                'query' => [
                    'breed_id' => $result[$i]['id'],
                    'size' => 'small'
                ]
            ]);
            $cat_result = $json->decode($response->getBody());
            array_push($arr, $cat_result[0]);
        }
        
        return $this->render('index.twig', ['cats' => $arr]);
    }

    public function actionSearch($breed)
    {
        $client = new Client();
        $response = $client->request('GET', 'https://api.thecatapi.com/v1/breeds/search', [
            'query' => ['q' => $breed]
        ]);

        $json = new BaseJson();
        $result = $json->decode($response->getBody());

        // convert to Cat model
        $arr = array();
        $model = new Cat();
        for ($i = 0; $i < count($result); $i++) {
            $response = $client->request('GET', 'https://api.thecatapi.com/v1/images/search', [
                'query' => [
                    'breed_id' => $result[$i]['id'],
                    'size' => 'small'
                ]
            ]);
            // check if there is image
            $cat_result = $json->decode($response->getBody());
            if (count($cat_result) > 0) {
                array_push($arr, $cat_result[0]);
            }
        }
        
        return $this->render('search.twig', ['cats' => $arr]);
    }

    public function actionDetail($breed_id)
    {
        $client = new Client();
        $response = $client->request('GET', 'https://api.thecatapi.com/v1/images/search', [
            'query' => [
                'breed_id' => $breed_id,
                'size' => 'small'
            ]
        ]);

        $json = new BaseJson();
        $result = $json->decode($response->getBody());

        if (count($result) > 0) {
            $result = $result[0];
        } else {
            $result = null;
        }
        
        return $this->render('detail.twig', ['cat' => $result]);
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
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about.twig');
    }
}
