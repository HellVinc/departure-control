<?php

namespace api\modules\v1\controllers;

use common\models\AuditHasKriterien;
use Yii;
use common\models\Kriterien;
use common\models\search\KriterienSearch;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * KriterienController implements the CRUD actions for Kriterien model.
 */
class KriterienController extends Controller
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

    /**
     * Lists all Kriterien models.
     * @return mixed
     */
    public function actionAll()
    {
        $model = new KriterienSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get());
        return [
            'models' => Kriterien::allFields($dataProvider->getModels()),
//            'page_count' => $dataProvider->pagination->pageCount,
//            'page' => $dataProvider->pagination->page + 1,
            'count_model' => $dataProvider->getTotalCount()
        ];
    }

    /**
     * Displays a single Kriterien model.
     * @return mixed
     */
    public function actionOne()
    {
        return $this->findModel(Yii::$app->request->get('id'));
    }

    /**
     * Creates a new Kriterien model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Kriterien();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model->oneFields();
        }
        return ['errors' => 'Message'];
    }

    /**
     * Updates an existing Kriterien model.
     * If update is successful, the browser will be redirected to the 'view' page.
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
     * Deletes an existing Kriterien model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public function actionDelete()
    {
        $id = Yii::$app->request->post('id');
        $count = AuditHasKriterien::find()->where(['kriterien_id' => $id])->count();

        if($count > 0){
            throw new HttpException(403, 'false');
        }
        return $this->findModel($id)->delete(false);
    }

    /**
     * Finds the Kriterien model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Kriterien the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Kriterien::findOne($id)) !== null) {
            if ($model->status !== 0) {
                return $model;
            }
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
