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

        $behaviors['verbFilter'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'days' => ['get'],
                'hours' => ['get'],
            ],
        ];

        return $behaviors;
    }

    /**
     * @param $start_date
     * @param $end_date
     * @return array
     */
    public function actionYear($start_date, $end_date)
    {
        $good = [];
        $bad = [];

        $days = ($end_date - $start_date) / 86400;

        for ($i = 1; $i <= $days; $i++) {
            $good[date('Y-m-d', $start_date)] = UserAudit::find()
                ->where(['between', 'created_at', $start_date, $start_date + 86400])
                ->andWhere(['light_type' => 1])
                ->count();
            $bad[date('Y-m-d', $start_date)] = UserAudit::find()
                ->where(['between', 'created_at', $start_date, $start_date + 86400])
                ->andWhere(['in', 'light_type', [2, 3]])
                ->count();
            $start_date += 86400;
        }
        return [
            'good' => $good,
            'bad' => $bad
        ];
    }

    /**
     * @param $start_date
     * @param $end_date
     * @return array
     */
    public function actionMonth($start_date, $end_date)
    {
        $good = [];
        $bad = [];

        $days = ($end_date - $start_date) / (86400 * 30);

        for ($i = 1; $i <= $days; $i++) {
            $good[date('Y-m-d', $start_date)] = UserAudit::find()
                ->where(['between', 'created_at', $start_date, $start_date + 86400])
                ->andWhere(['light_type' => 1])
                ->count();
            $bad[date('Y-m-d', $start_date)] = UserAudit::find()
                ->where(['between', 'created_at', $start_date, $start_date + 86400])
                ->andWhere(['in', 'light_type', [2, 3]])
                ->count();
            $start_date += 86400;
        }
        return [
            'good' => $good,
            'bad' => $bad
        ];
    }

    /**
     * @param $start_date
     * @param $end_date
     * @return array
     */
    public function actionDays($start_date, $end_date)
    {
        $good = [];
        $bad = [];

        $days = ($end_date - $start_date) / 86400;

        for ($i = 1; $i <= $days; $i++) {
            $good[date('Y-m-d', $start_date)] = UserAudit::find()
                ->where(['between', 'created_at', $start_date, $start_date + 86400])
                ->andWhere(['light_type' => 1])
                ->count();
            $bad[date('Y-m-d', $start_date)] = UserAudit::find()
                ->where(['between', 'created_at', $start_date, $start_date + 86400])
                ->andWhere(['in', 'light_type', [2, 3]])
                ->count();
            $start_date += 86400;
        }
        return [
            'good' => $good,
            'bad' => $bad
        ];
    }

    /**
     * @param $start_date
     * @return array
     */
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
                ->andWhere(['in', 'light_type', [2, 3]])
                ->count();
            $start_date += 3600;
        }
        return [
            'good' => $good,
            'bad' => $bad
        ];
    }


}