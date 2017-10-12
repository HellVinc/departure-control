<?php

namespace common\components\traits;

use Yii;

trait errors
{
    # error

    public function getErrors($attribute = null)
    {
        $error = $this->errorRecursive(parent::getErrors());
        if($error != null){
            return ['error' => $error];
//                Yii::t('app',$error)];
        }
        return false;
    }

    protected function errorRecursive($error)
    {
        if (is_array($error)) {
            return $this->errorRecursive(array_shift($error));
        }
        return $error;
    }
}