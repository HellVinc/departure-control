<?php

namespace api\modules\v1\controllers;

use Yii;
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
                'year',
                'month',
                'days',
                'hours'
            ],
        ];

        $behaviors['verbFilter'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'year' => ['get'],
                'month' => ['get'],
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
    public function actionYear()
    {
        $start_date = strtotime(Yii::$app->request->get('start_date'));
        $end_date = strtotime(Yii::$app->request->get('end_date'));
        $ldate = date('Y', $end_date);
        if (date("L", mktime(0, 0, 0, 7, 7, $ldate))) {
            $daysLastYear = 366;
        } else {
            $daysLastYear = 365;
        }
        $end_date += $daysLastYear * 86400;
        $good = [];
        $bad = [];

        $i = $start_date;

        while ($end_date > $i) {

            $sdate = date('Y', $i);
            if (date("L", mktime(0, 0, 0, 7, 7, $sdate))) {
                $daysInYear = 366;
            } else {
                $daysInYear = 365;
            }

            $rYear = $i;

            $good[date('Y', $rYear)] = UserAudit::find()
                ->where(['between', 'created_at', $i, $i + ($daysInYear * 86400)])
                ->andWhere(['light_type' => 1])
//                ->andWhere(['admin_id' => UserAudit::adminId()])
                ->count();
            $bad[date('Y', $rYear)] = UserAudit::find()
                ->where(['between', 'created_at', $i, $i + ($daysInYear * 86400)])
                ->andWhere(['in', 'light_type', [2, 3]])
//                ->andWhere(['admin_id' => UserAudit::adminId()])
                ->count();

            $i += $daysInYear * 86400;
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
    public function actionMonth()
    {
        $start_date = strtotime(Yii::$app->request->get('start_date'));
        $end_date = strtotime(Yii::$app->request->get('end_date'));
        $good = [];
        $bad = [];

        $i = $start_date;

        while ($end_date >= $i) {

            $sdate = date('Y-m', $i);
            $date = explode('-', $sdate);



            $mons = array(
                '01' => 'Januar',
                '02' => 'Februar',
                '03' => 'März',
                '04' => 'April',
                '05' => 'Mai',
                '06' => 'Juni',
                '07' => 'Juli',
                '08' => 'August',
                '09' => 'Septembe',
                '10' => 'Oktober',
                '11' => 'November',
                '12' => 'Dezember',
            );

            $monthName = $mons[$date['1']];

            $dayCount = cal_days_in_month(CAL_JULIAN, $date['1'], $date['0']);

            $good[$monthName] = UserAudit::find()
                ->where(['between', 'created_at', $i, $i + (86400 * $dayCount)])
                ->andWhere(['light_type' => 1])
                ->andWhere(['admin_id' => UserAudit::adminId()])
                ->count();
            $bad[$monthName] = UserAudit::find()
                ->where(['between', 'created_at', $i, $i + (86400 * $dayCount)])
                ->andWhere(['in', 'light_type', [2, 3]])
                ->andWhere(['admin_id' => UserAudit::adminId()])
                ->count();

            $i += 86400 * $dayCount;
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
    public function actionDays()
    {
        $start_date = strtotime(Yii::$app->request->get('start_date'));
        $end_date = strtotime(Yii::$app->request->get('end_date'));
        $good = [];
        $bad = [];

        $days = ($end_date - $start_date) / 86400 + 1;

        for ($i = 1; $i <= $days; $i++) {
            $good[date('Y-m-d', $start_date)] = UserAudit::find()
                ->where(['between', 'created_at', $start_date, $start_date + 86400])
                ->andWhere([
                    'light_type' => 1,
                    'admin_id' => UserAudit::adminId()
                ])
                ->count();
            $bad[date('Y-m-d', $start_date)] = UserAudit::find()
                ->where(['between', 'created_at', $start_date, $start_date + 86400])
                ->andWhere(['in', 'light_type', [2, 3]])
                ->andWhere(['admin_id' => UserAudit::adminId()])
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
    public function actionHours()
    {
        $start_date = strtotime(Yii::$app->request->get('start_date'));
        $good = [];
        $bad = [];

        for ($i = 1; $i <= 24; $i++) {
            $good[date('H:i', $start_date)] = UserAudit::find()
                ->where(['between', 'created_at', $start_date, $start_date + 3600])
                ->andWhere(['success' => 1])
                ->andWhere(['admin_id' => UserAudit::adminId()])
                ->count();
            $bad[date('H:i', $start_date)] = UserAudit::find()
                ->where(['between', 'created_at', $start_date, $start_date + 3600])
                ->andWhere(['in', 'light_type', [2, 3]])
                ->andWhere(['admin_id' => UserAudit::adminId()])
                ->count();
            $start_date += 3600;
        }
        return [
            'good' => $good,
            'bad' => $bad
        ];
    }


}