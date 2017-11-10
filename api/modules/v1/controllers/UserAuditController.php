<?php

namespace api\modules\v1\controllers;

use common\components\UploadModel;
use common\models\Answer;
use common\models\Attachment;
use common\models\NoAnswer;
use common\models\User;
use kartik\mpdf\Pdf;
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
                'new-create',
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
                'new-create' => ['post'],
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

    public function actionCreate()
    {
        foreach (Yii::$app->request->post() as $audit) {
            $signature = [];
            $model = new UserAudit();
            $user = User::findOne(Yii::$app->user->id);
            $model->admin_id = $user->created_by;
            if ($model->load($audit) && $model->saveModel()) {
                foreach ($audit['kriterien'] as $one) {
                    $answer =  Answer::answerHandler($one, $model->id);
                    if($answer == 3){
                        $model->light_type = Answer::answerHandler($one, $model->id);
                        $model->save();
                    }
                    if(isset($one['signature'])){
                        $signature[] = Attachment::saveFile($one, $model->id);
                    }
                }
            } else {
                return $model->errors;
            }
            $username = User::findOne(Yii::$app->user->id);
            $reportTemplate = '@api/modules/v1/views/default/index-test';
            $content = Yii::$app->controller->renderPartial($reportTemplate, [
                'answers' => $audit,
                'username' => $username->username,
                'audit' => 'DCP-' . date('Ymd', time()) . '-' . UserAudit::beginWithZero($model->count_per_date),
                'signature' => $signature,
            ]);
//
            $pdf = new Pdf();
            $mpdf = $pdf->api; // fetches mpdf api
            $mpdf->showImageErrors = true;
            $path = Yii::getAlias('@files') . '/pdf/' . $model->getName();

            $mpdf->WriteHtml($content); // call mpdf write html
            $mpdf->Output($path . '.pdf', 'F');
            $file = new Attachment();
            $file->object_id = $model->id;
            $file->table = 'user_audit';
            $file->extension = 'pdf';
            $file->admin_id = $user->created_by;
            $file->url = $model->getName() . '.' . 'pdf';
            if (!$file->save())
                return $file->errors;
        }
        return true;
    }


    /**
     * Updates an existing UserAudit model.
     * If update is successful, the browser will be redirected to the 'view' page.
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
