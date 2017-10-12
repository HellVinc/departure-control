<?php

namespace common\components\traits;

use Yii;
use yii\debug\models\timeline\DataProvider;

trait findRecords
{
    # search records

    /**
     * @param null $request
     * @return DataProvider
     */
    public function searchAll($request = null)
    {
        $this->status = 10;
        if ($request && (!$this->load([soft::lastNameClass(static::className()) => $request]) || !$this->validate())) {
            return null;
        }
        return $this->search();
//        return [
//            'models' => $models,
////            'count_page' => $dataProvider->pagination->pageCount,
//            'count_model' => $dataProvider->getTotalCount()
//        ];

    }
}