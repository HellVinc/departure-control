<?php

namespace api\modules\v1\controllers;

use Yii;
use common\models\AppUser;
use common\models\search\AppUserSearch;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * AppUserController implements the CRUD actions for AppUser model.
 */
class AppUserController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::className(),
            'tokenParam' => 'auth_key',
            'only' => [
                'all',
                'one',
                'create',
                'update',
                'delete',
            ],
        ];
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
                'update' => ['post'],
                'delete' => ['post'],
            ],
        ];

        return $behaviors;
    }

    public function actionTest()
    {
        return 1;
    }

    /**
     * Lists all AppUser models.
     * @return mixed
     */
    public function actionAll()
    {
//        return AppUser::find()->all();
        $model = new AppUserSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get());
        return [
            'models' => AppUser::allFields($dataProvider->getModels()),
//            'page_count' => $dataProvider->pagination->pageCount,
//            'page' => $dataProvider->pagination->page + 1,
            'count_model' => $dataProvider->getTotalCount()
        ];
    }


    /**
     * Displays a single AppUser model.
     * @param integer $id
     * @return mixed
     */
    public function actionOne()
    {
        return $this->findModel(Yii::$app->request->get('id'));
    }

    /**
     * Creates a new AppUser model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new AppUser();
        $model->user_id = Yii::$app->user->id;
        $model->auth_key = Yii::$app->security->generateRandomString();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model;
        }
        return $model->errors;
    }

    /**
     * Updates an existing AppUser model.
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
        return $model->errors;
    }

    /**
     * Deletes an existing AppUser model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete()
    {
        return $this->findModel(Yii::$app->request->post('id'))->delete(false);
    }

    /**
     * Finds the AppUser model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return AppUser the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AppUser::findOne($id)) !== null) {
            if ($model->status !== 0) {
                return $model;
            }
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
