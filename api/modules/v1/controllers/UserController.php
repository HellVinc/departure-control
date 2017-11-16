<?php

namespace api\modules\v1\controllers;

use api\modules\v1\modelForms\SignupForm;
use common\components\traits\errors;
use common\components\UploadModel;
use common\models\Answer;
use common\models\Attachment;
use common\models\Audit;
use common\models\LoginForm;
use common\models\UserAudit;
use Faker\Provider\DateTime;
use kartik\mpdf\Pdf;
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
        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::className(),
            'tokenParam' => 'auth_key',
            'only' => [
//                'test',
                'all',
                'one',
                'create',
                'update',
                'delete',
            ],
        ];

        $behaviors['verbFilter'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'all' => ['get'],
                'one' => ['get'],
                'create' => ['post'],
                'register' => ['post'],
                'signup' => ['post'],
                'login' => ['post'],
                'app-login' => ['post'],
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
     * @return array
     */
    public function actionAll()
    {
        $model = new UserSearch();
        $dataProvider = $model->searchAll(Yii::$app->request->get());
        return [
            'models' => User::allFields($dataProvider->getModels()),
//            'page_count' => $dataProvider->pagination->pageCount,
//            'page' => $dataProvider->pagination->page + 1,
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

//            $model->account_type = 1;
            return $model->signup();
        }
        return $model->errors;
    }

    /**
     * @return array|bool
     */
    public function actionRegister()
    {
        $model = new User();
        $model->scenario = 'register';
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $code = random_int(10000, 999999);
            $model->sub_end = time() + (86400 * 30);
            $model->activation_code = $code;
            $message = 'Activation code:' . $code . "\r\n" .
                'http://dc-app.de/files/android-debug.apk';
            Yii::$app->mailer->compose()
                ->setFrom('from@dc-app.de')
                ->setTo($model->email)
                ->setSubject('Registration code')
                ->setTextBody('Activation code:' . $code . "\r\n" .
                    'http://dc-app.de/files/android-debug.apk')
//                ->setHtmlBody('<b>текст сообщения в формате HTML</b>')
                ->send();
//            imap_mail($model->email, 'test reg', $message);
            $model->account_type = 1;
            return $model->register();
        }
        return $model->errors;
    }

    /**
     * @return array|mixed
     */
    public function actionSignup()
    {
        $code = Yii::$app->request->post('activation_code');

        if($model = User::findOne(['activation_code' => $code]) !== null){
            $model = User::findOne(['activation_code' => $code]);
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $model->activation_code = null;
                if(!$model->signup()){
                    return $model->errors;
                }
            }
            return $model->errors;
        }
        return ['error' => 'Not found'];
    }

    /**
     * @return array
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post(), "")) {
            if ($model->login()) {
                $result = Yii::$app->user->identity->oneFields();
                if ((time() - $result['0']['created_at']) > $result['0']['sub_end']) {

                    return ['error' => 'die Testzeit ist abgelaufen'];  //the test time has expired
                }
                return $result;
            }
            return ['error' => 'Ungültiger Anmeldename oder Passwort'];  //Invalid login or password
        }
        return ['error' => 'Error. Ungültige Anforderung.'];
    }

    public function actionAppLogin()
    {
        $data = Yii::$app->request->post();

        $model = $this->findModel(['username' => Yii::$app->request->post('username')]);
        if ($model->pass == $data['password'] && $model->account_type != 0) {
            return $model->oneFields();
        }
        return ['error' => 'Ungültiger Anmeldename oder Passwort'];  //Invalid login or password
//        return ['error' => 'Bad request'];
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
