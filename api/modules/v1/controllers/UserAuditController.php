<?php

namespace api\modules\v1\controllers;

use common\components\UploadModel;
use common\models\Answer;
use common\models\Attachment;
use common\models\NoAnswer;
use Yii;
use common\models\UserAudit;
use common\models\search\UserAuditSearch;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UserAuditController implements the CRUD actions for UserAudit model.
 */
class UserAuditController extends Controller
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
     * Lists all UserAudit models.
     * @return mixed
     */
    public function actionAll()
    {
        $model = new UserAuditSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get());
        return [
            'models' => UserAudit::allFields($dataProvider->getModels()),
            'page_count' => $dataProvider->pagination->pageCount,
            'page' => $dataProvider->pagination->page + 1,
            'count_model' => $dataProvider->getTotalCount()
        ];
    }

    /**
     * Displays a single UserAudit model.
     * @return mixed
     */
    public function actionOne()
    {
        return $this->findModel(Yii::$app->request->get('id'));
    }

    /**
     * Creates a new UserAudit model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new UserAudit();
        $model->user_id = Yii::$app->user->id;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $data = Yii::$app->request->post('kriterien');
            foreach ($data as $one) {
                $answer = new Answer();
                $answer->user_audit_id = $model->id;
                if ($answer->load($one) && $answer->save()) {
                    if($answer->process_type == 4){
                        $file = new Attachment();
                        $file->object_id = $answer->id;
                        $file->table = 'user_audit';
                        $file->extension = $one['extention'];
                        $file->url = UploadModel::uploadBase($one['signature'], $one['extention'], mt_rand(10000, 900000));
                        $file->save();
                    }
                    if($answer->process_type == 3){
                        $file = new Attachment();
                        $file->object_id = $answer->id;
                        $file->table = 'answer';
                        $file->extension = $one['extention'];
                        $file->url = UploadModel::uploadBase($one['signature'], $one['extention'], mt_rand(10000, 900000));
                        $file->save();
                    }
                    if ($one['answer'] == 'false') {
                        $noAnswer = new NoAnswer();
                        $noAnswer->answer_id = $answer->id;
                        $noAnswer->description = $one['description'];
                        if(isset($one['photo'])){
                            UploadModel::uploadBase($one['photo'], $one['extention'], $one['name']);
                        }
                        if(!$noAnswer->save()){
                            return $noAnswer->errors;
                        }
                        if($answer->no_type === 2){
                            return 1;
                        }
                    }else
                    return 2;
                }else
                return $model->errors;
            }
            return true;
        }
        return ['errors' => $model->errors];
    }


    /**
     * Updates an existing UserAudit model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public
    function actionUpdate()
    {
        $model = $this->findModel(Yii::$app->request->post('id'));

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $model;
        }
        return ['errors' => $model->errors];
    }

    /**
     * Deletes an existing UserAudit model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @return mixed
     */
    public
    function actionDelete()
    {
        return $this->findModel(Yii::$app->request->post('id'))->delete();
    }

    /**
     * Finds the UserAudit model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return UserAudit the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected
    function findModel($id)
    {
        if (($model = UserAudit::findOne($id)) !== null) {
            if ($model->status !== 0) {
                return $model;
            }
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
