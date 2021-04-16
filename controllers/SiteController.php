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
        $json = new BaseJson();
        $cache = Yii::$app->redis;
        $key = 'index_cats';
        if ($cache->exists($key) == false) {
            $client = new Client();
            $response = $client->request('GET', 'https://api.thecatapi.com/v1/breeds', [
                // 'query' => ['limit' => 5]
            ]);

            $result = $json->decode($response->getBody());

            // shuffle array
            shuffle($result);

            // get 5 first elements
            $result = array_slice($result, 0, 5);

            // get new cat images
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
            $cache->set($key, $json->encode($arr));
            $data = $arr;
        } else {
            $data = $json->decode($cache->get($key));
        }
        
        return $this->render('index.twig', ['cats' => $data]);
    }

    public function actionAlphabetic($letter)
    {
        $json = new BaseJson();
        $cache = Yii::$app->redis;
        $key = 'alphabetic_'.$letter;
        if ($cache->exists($key) == false) {
            $client = new Client();
            $response = $client->request('GET', 'https://api.thecatapi.com/v1/breeds', [
                // 'query' => ['limit' => 5]
            ]);

            $result = $json->decode($response->getBody());

            // get cat names only starting with letter
            $data = array();
            for ($i = 0; $i < count($result); $i++) {
                if ($result[$i]['name'][0] == $letter) {
                    array_push($data, $result[$i]);
                }
            }

            // get new cat images
            $arr = array();
            $model = new Cat();
            for ($i = 0; $i < count($data); $i++) {
                $response = $client->request('GET', 'https://api.thecatapi.com/v1/images/search', [
                    'query' => [
                        'breed_id' => $data[$i]['id'],
                        'size' => 'small'
                    ]
                ]);
                $cat_result = $json->decode($response->getBody());
                array_push($arr, $cat_result[0]);
            }
            $cache->set($key, $json->encode($arr));
            $data = $arr;
        } else {
            $data = $json->decode($cache->get($key));
        }
        
        return $this->render('index.twig', ['cats' => $data]);
    }

    public function actionSearch($breed)
    {
        $json = new BaseJson();
        $cache = Yii::$app->redis;
        $key = 'search_breed_'.$breed;
        if ($cache->exists($key) == false) {
            $client = new Client();
            $response = $client->request('GET', 'https://api.thecatapi.com/v1/breeds/search', [
                'query' => ['q' => $breed]
            ]);
    
            $json = new BaseJson();
            $result = $json->decode($response->getBody());
            $cache->set($key, $json->encode($result));
        } else {
            $result = $json->decode($cache->get($key));
        }

        // get image for each breed
        $arr = array();
        for ($i = 0; $i < count($result); $i++) {
            $breed = $result[$i]['id'];
            $key = 'search_'.$breed;
            if ($cache->exists($key) == false) {
                $client = new Client();
                $response = $client->request('GET', 'https://api.thecatapi.com/v1/images/search', [
                    'query' => [
                        'breed_id' => $breed,
                        'size' => 'small'
                    ]
                ]);
                $cat_result = $json->decode($response->getBody());
                $cache->set($key, $json->encode($cat_result));
                $data = $cat_result;
            } else {
                $data = $json->decode($cache->get($key));
            }

            // check if there is image
            if (count($data) > 0) {
                array_push($arr, $data[0]);
            }
        }
        
        return $this->render('index.twig', ['cats' => $arr]);
    }

    public function actionDetail($breed_id)
    {
        $json = new BaseJson();
        $cache = Yii::$app->redis;
        $key = 'detail_'.$breed_id;
        if ($cache->exists($key) == false) {
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
                $cache->set($key, $json->encode($result[0]));
                $data = $result[0];
            } else {
                $data = null;
            }
        } else {
            $data = $json->decode($cache->get($key));
        }

        return $this->render('detail.twig', ['cat' => $data]);
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
