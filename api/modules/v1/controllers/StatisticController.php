<?php

namespace api\modules\v1\controllers;

use common\models\UserAudit;
use yii\filters\AccessControl;
use yii\filters\auth\QueryParamAuth;
use yii\filters\VerbFilter;
use yii\rest\Controller;

class StatisticController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::className(),
            'tokenParam' => 'auth_key',
            'only' => [
                'days',
                'hours'
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
                'days' => ['get'],
                'hours' => ['get'],
            ],
        ];

        return $behaviors;
    }

    public function actionDays($start_date, $end_date)
    {
        $good = [];
        $bad = [];

        $days = ($end_date - $start_date) / 86400;

        for ($i = 1; $i <= $days; $i++) {
            $good[date('Y-m-d', $start_date)] = UserAudit::find()
                ->where(['between', 'created_at', $start_date, $start_date + 86400])
                ->andWhere(['success' => 1])
                ->count();
            $bad[date('Y-m-d', $start_date)] = UserAudit::find()
                ->where(['between', 'created_at', $start_date, $start_date + 86400])
                ->andWhere(['success' => 0])
                ->count();
            $start_date += 86400;
        }
        return [
            'good' => $good,
            'bad' => $bad
        ];
    }

    public function actionHours($start_date)
    {
        $good = [];
        $bad = [];

        for ($i = 1; $i <= 24; $i++) {
            $good[date('H:i', $start_date)] = UserAudit::find()
                ->where(['between', 'created_at', $start_date, $start_date + 3600])
                ->andWhere(['success' => 1])
                ->count();
            $bad[date('H:i', $start_date)] = UserAudit::find()
                ->where(['between', 'created_at', $start_date, $start_date + 3600])
                ->andWhere(['success' => 0])
                ->count();
            $start_date += 3600;
        }
        return [
            'good' => $good,
            'bad' => $bad
        ];
    }
}