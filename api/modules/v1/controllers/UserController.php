<?php

namespace api\modules\v1\controllers;

use api\modules\v1\modelForms\SignupForm;
use common\models\LoginForm;
use Yii;
use common\models\User;
use common\models\search\UserSearch;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
//        $behaviors['authenticator'] = [
//            'class' => QueryParamAuth::className(),
//            'tokenParam' => 'auth_key',
//            'only' => [
//                'all',
//                'one',
//                'create',
//                'update',
//                'delete',
//            ],
//        ];
//        $behaviors['access'] = [
//            'class' => AccessControl::className(),
//            'only' => [
//                'create',
//                'update',
//                'delete',
//            ],
//            'rules' => [
//                [
//                    'actions' => [
//                        'create',
//                        'update',
//                        'delete',
//                    ],
//                    'allow' => true,
//                    'roles' => ['admin'],
//
//                ],
//            ],
//        ];

        $behaviors['verbFilter'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'all' => ['get'],
                'one' => ['get'],
                'create' => ['post'],
                'login' => ['post'],
                'update' => ['post'],
                'delete' => ['post'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @return string
     */
    public function actionAll()
    {
        $model = new UserSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get());
        return [
            'models' => User::allFields($dataProvider->getModels()),
            'page_count' => $dataProvider->pagination->pageCount,
            'page' => $dataProvider->pagination->page + 1,
            'count_model' => $dataProvider->getTotalCount()
        ];
    }

    /**
     * Displays a single User model.
     * @return mixed
     */
    public function actionOne()
    {
        return $this->findModel(Yii::$app->request->get('id'));
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();
        $model->scenario = 'signUp';
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            return $model->signup();
        }
        return ['errors' => $model->errors];
    }

    public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post(), "")) {
            if ($model->login()) {
                $result = Yii::$app->user->identity->oneFields();
                return $result;
            }
            return ['error' => 'Invalid login or password'];
        }
        return ['error' => 'Error. Bad request.'];
    }


    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate()
    {
        $model = $this->findModel(Yii::$app->request->post('id'));

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model;
        }
        return ['errors' => $model->errors];
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionDelete()
    {
        return $this->findModel(Yii::$app->request->post('id'))->delete();
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            if ($model->status !== 0) {
                return $model;
            }
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
