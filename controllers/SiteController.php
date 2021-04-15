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
        $cache = Yii::$app->getCache();
        $data = $cache->getOrSet('index_cats', function () {
            $client = new Client();
            $response = $client->request('GET', 'https://api.thecatapi.com/v1/breeds', [
                // 'query' => ['limit' => 5]
            ]);

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
            return $arr;
        });
        
        return $this->render('index.twig', ['cats' => $data]);
    }

    public function actionSearch($breed)
    {
        $cache = Yii::$app->getCache();
        $result = $cache->getOrSet('search_breed_'.$breed, function () use ($breed) {
            $client = new Client();
            $response = $client->request('GET', 'https://api.thecatapi.com/v1/breeds/search', [
                'query' => ['q' => $breed]
            ]);
    
            $json = new BaseJson();
            $result = $json->decode($response->getBody());

            return $result;
        });

        // get image for each breed
        $arr = array();
        for ($i = 0; $i < count($result); $i++) {
            $breed = $result[$i]['id'];
            $data = $cache->getOrSet('search_'.$breed, function () use ($breed) {
                $client = new Client();
                $response = $client->request('GET', 'https://api.thecatapi.com/v1/images/search', [
                    'query' => [
                        'breed_id' => $breed,
                        'size' => 'small'
                    ]
                ]);
                $json = new BaseJson();
                $cat_result = $json->decode($response->getBody());

                return $cat_result;
            });

            // check if there is image
            if (count($data) > 0) {
                array_push($arr, $data[0]);
            }
        }
        
        return $this->render('index.twig', ['cats' => $arr]);
    }

    public function actionDetail($breed_id)
    {
        $cache = Yii::$app->getCache();
        $data = $cache->getOrSet('detail_'.$breed_id, function () use ($breed_id) {
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
            
            return $result;
        });

        return $this->render('detail.twig', ['cat' => $data]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        $teste = array(
            parse_url($_ENV['REDISCLOUD_URL'], PHP_URL_HOST),
            parse_url($_ENV['REDISCLOUD_URL'], PHP_URL_PORT),
            parse_url($_ENV['REDISCLOUD_URL'], PHP_URL_PASS)
        );
        return $this->render('about.twig', ['teste' => $teste]);
    }
}
