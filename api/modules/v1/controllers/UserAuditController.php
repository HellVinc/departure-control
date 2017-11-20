<?php

namespace api\modules\v1\controllers;

use common\components\UploadModel;
use common\models\Answer;
use common\models\AppUser;
use common\models\Attachment;
use common\models\NoAnswer;
use common\models\User;
use kartik\mpdf\Pdf;
use Yii;
use common\models\UserAudit;
use common\models\search\UserAuditSearch;
use yii\filters\auth\QueryParamAuth;
use yii\rest\Controller;
use yii\web\HttpException;
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
//                'create',
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
        if(AppUser::findIdentityByAccessToken(Yii::$app->request->get('auth_key'))){

            $appUser = AppUser::findIdentityByAccessToken(Yii::$app->request->get('auth_key'));

            foreach (Yii::$app->request->post() as $audit) {
                $signature = [];
                $model = new UserAudit();
                $model->admin_id = $appUser->user_id;
                $model->app_user_id = $appUser->id;
//            $model->start_date = strtotime( Yii::$app->request->get('start_date'));
//            $model->end_date = strtotime( Yii::$app->request->get('end_date'));
                if ($model->load($audit) && $model->saveModel()) {

                    foreach ($audit['kriterien'] as $one) {
                        if (isset($one['signature'])) {
                            $signature[] = Attachment::saveFile($one, $model->id, $appUser->user_id);
                        } else {
                            $answer = Answer::answerHandler($one, $model->id);
                            if ($answer['status'] == 3) {
                                $model->light_type = $answer['status'];
                                $model->save();
                            }
                            if(array_key_exists('photo', $one)){
//                            throw new HttpException('401', $answer['model']);
                                Attachment::uploadFiles($one, $model->id, $appUser->user_id);
                            }
                        }
                    }
                } else {
                    return $model->errors;
                }
                $reportTemplate = '@api/modules/v1/views/default/index-test';
                $content = Yii::$app->controller->renderPartial($reportTemplate, [
                    'answers' => $audit,
                    'username' => $appUser->username,
                    'audit' => 'DCP-' . date('Ymd', time()) . '-' . UserAudit::beginWithZero($model->count_per_date),
                    'signature' => $signature,
                ]);
//
                $pdf = new Pdf();


                $mpdf = $pdf->api; // fetches mpdf api
                $mpdf->showImageErrors = true;
                $path = Yii::getAlias('@files') . '/pdf/' . $model->getName();
                $mpdf->AddPage('', // L - landscape, P - portrait
                    '', '', '', '',
                    10, // margin_left
                    10, // margin right
                    30, // margin top
                    30, // margin bottom
                    0, // margin header
                    10); // margin footer

                $date = explode(' ', $audit['start_date']);
                $result =  explode('-', $date['0']);
                $superName = 'DCP-' . date('Ymd', time()) . '-' . UserAudit::beginWithZero($model->count_per_date) . '-' . $audit['name'];
                $protokolle = 'Protokoll vom' . "<br>" . $result['2'] . '.' . $result['1'] . '.' . $result['0'];
                $mpdf->SetHTMLFooter(
                    '<div  style="width: 100%; display: inline-block; font-size: 12px">' .
                    '<div style="font-size: 12px; float: left; width: 33%">' .
                    '<div style="width: 100%">' .
                    '<b>' . htmlspecialchars_decode($superName) . '</b>
                            <br><b>' . htmlspecialchars_decode($appUser->username) . '</b>' .
                    '</div>' .
                    '</div>' .
                    '<div style="width: 33%; float: left;">' .
                    '<div style="text-align: center;width: 100%">' .
                    htmlspecialchars_decode($protokolle) .
                    '</div>' .
                    '</div>' .
                    '<div style="width: 33%">' .
                    ' <br><div style="font-size: 12px; text-align: right; width: 100%">' .
                    "{PAGENO} of {nb}" .
                    '</div>' .
                    '</div> ' .
                    '</div>'
                );
                $mpdf->WriteHtml($content); // call mpdf write html
                $mpdf->Output($path . '.pdf', 'F');
                $file = new Attachment();
                $file->object_id = $model->id;
                $file->table = 'user_audit';
                $file->extension = 'pdf';
                $file->admin_id = $appUser->user_id;
                $file->url = $model->getName() . '.' . 'pdf';
                if (!$file->save())
                    return $file->errors;
            }
            return true;
        }
        return ['error' => 'false'];
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
        return  $model->errors;
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
