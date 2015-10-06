<?php

namespace backend\controllers;

use Yii;
use backend\models\Users;
use backend\models\UsersSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UsersController implements the CRUD actions for Users model.
 */
class UsersController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Users models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UsersSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Users model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Users model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */

    //actionCreate : Lista , Testing
    public function actionCreate()
    {
        $model = new Users();
        /* yii2.0 , borrar si todo funciona ok 
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->x_user]);
        } else {
        */
         ////////////////// 7   
        if(isset($_POST['Users']))
            {
                $model->attributes=$_POST['Users'];
                $model->isNewRecord = true;
                $internalPass = $this->generateRandomString();
                $model->setTInternalPassword($internalPass);
    
            if($model->save()){
                $dataProvider=new ActiveDataProvider('Users');

                // conexion con geoserver
                $properties = require dirname(__FILE__).'/../../protected/config/properties.php';

                $geoserver = new GeoserverWrapper($properties['urlGeoserver'], $properties['userGeoserver'], $properties['pwGeoserver']);
                $geoserver->createUser($model->t_user, $model->t_internal_password);
                // fin conexion con geoserver

                return $this->redirect(['view','id'=>$model->x_user]);
            }
        }
        /////////7
        
        return $this->render('create', [
            'model' => $model,
        ]);
    
    }

    /**
     * Updates an existing Users model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */

    //actionUpdate COMPLETA (no probada). testing
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if(isset($_POST['Users']))
        {
            $model->attributes=$_POST['Users'];
            if($model->save()){
                // conexion con geoserver
                $properties = require dirname(__FILE__).'/../../protected/config/properties.php';

                $geoserver = new GeoserverWrapper($properties['urlGeoserver'], $properties['userGeoserver'], $properties['pwGeoserver']);
                $geoserver->updateUser($model->t_user, $model->t_internal_password);
                // fin conexion con geoserver

                return $this->redirect(['view','id'=>$model->x_user]);
            }
        }

        $model->t_password_repeat=$model->t_password;
        return $this->render('update',[
            'model'=>$model,
        ]);
    }

    /**
     * Deletes an existing Users model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $properties = require dirname(__FILE__).'/../../protected/config/properties.php';
        try {
            $criteria = new /*C*/DbCriteria;
            $criteria->addCondition('user_x_user = '.$id);
            $result=GroupUsersProfiles::model()->find($criteria);
            if(count($result)>0)
            {
                header("HTTP/1.0 500 Relation Restriction");
                return $properties['deleteErrorMessageUser'];
            }else {

                $this->findModel($id)->delete();

                // conexion con geoserver
                $geoserver = new GeoserverWrapper($properties['urlGeoserver'], $properties['userGeoserver'], $properties['pwGeoserver']);
                $geoserver->deleteUser($model->t_user, $model->t_internal_password);
                // fin conexion con geoserver
                    
                $model->delete();
                // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
                if(!isset($_GET['ajax'])){
                    return $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : ['view']);
                }
            }
        }catch (Exception $e){
        //          header("HTTP/1.0 500 Relation Restriction");
            return $properties['deleteErrorMessage'];
        }
    }

    /**
     * Finds the Users model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Users the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Users::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }



    ///////funciones agregadas desde 1.2


    /////Lista, testing
    public function accessRules()
    {
        return [
        ['allow',  // allow all users to perform 'index' and 'view' actions
                'actions'=>['index','view','authenticate','create','update','delete'],
                'users'=>['*']
        ],
        ['allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>['create','update'],
                'users'=>['@']
        ],
        ['allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions'=>['admin','delete'],
                'users'=>['admin']
        ],
        ['deny',  // deny all users
                'users'=>['*']
        ],
        ];
    }

    ////// Incompleta, Testing
    public function actionAuthenticate()
    {
        $s2Users=new S2Users;
        $viewpath = '//site/';
        $model2 = new LoginForm;

        if ($_POST['LoginForm']['usuario']!=""){
                
            $s2Users->setTUser($_POST['LoginForm']['usuario']);
            $s2Users->setTPassword($_POST['LoginForm']['password']);
                
            $model = $s2Users->search();
            $criteria = new CDbCriteria;
            $criteria->addCondition("t_user = '".$_POST['LoginForm']['usuario']."'");
            $criteria->addCondition("t_password = '".$_POST['LoginForm']['password']."'");
            $results = $model->model->findAll($criteria);
            $this->layout='//layouts/column1_Gen';

            if(count($results) == 0){

                Yii::app()->params['error'] = true;
                Yii::app()->session['userName'] = '';
                Yii::app()->session['userTUser'] = '';
                Yii::app()->session['userXUser'] = '';

                $this->render($viewpath.'login',[
                    'model'=>$model2,
                ]);

            } else{

                Yii::app()->params['error'] = '0';
                Yii::app()->session['userName'] = $results[0]->getTName();
                Yii::app()->session['userTUser'] = $results[0]->getTUser();
                Yii::app()->session['userXUser'] = $results[0]->getXUser();

                $this->render($viewpath.'login',[
                    'model'=>$model2,
                ]);

            }
        } else {
                
            Yii::app()->params['error'] = true;
            Yii::app()->session['userName'] = '';
            Yii::app()->session['userTUser'] = '';
            Yii::app()->session['userXUser'] = '';

            $this->layout='//layouts/column1_Gen';
            $this->render($viewpath.'login',[
                    'model'=>$model2,
            ]);
        }
    }

    /// Completa TESTING
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }


}
