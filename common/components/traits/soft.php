<?php

namespace common\components\traits;

use common\models\Advertisement;
use common\models\Message;
use common\models\User;
use Yii;

trait soft
{
    # class name

    public static function lastNameClass($class)
    {
        $array = explode('\\', $class);
        return array_pop($array);
    }

    # load

    public function load($data, $formName = null)
    {
        $className = $this::lastNameClass(static::className());

        if (array_key_exists($className, $data)) {
            return parent::load($data, $formName);
        }

        return parent::load([$className => $data], $formName);
    }

    public function remove()
    {
        $className = $this::lastNameClass(static::className());
        $data = [
            'status' => 10
        ];
        parent::load([$className => $data]);
        return $this->save();
    }

    public function saveModel()
    {
        if ($this->isNewRecord) {
            $this->created_by = Yii::$app->user->id;
            $this->created_at = time();
        } else {
            $this->updated_by = Yii::$app->user->id;
            $this->updated_at = time();
        }
        return $this->save();
    }

    public function saveWithCheck()
    {
        //проверяем. существует ли такая запись
        if ($this->findModel()) {
            return $this->addError('error', Yii::t('msg/error', 'Record was added before'));
        }
        $this->created_at = time();
        // сохраняем новую запись
        return $this->save();
    }

    public function saveWithCheckAndRestore()
    {
        //проверяем. существует ли такая запись
        $model = $this->findModel();
        if ($model) {
            if ($model->status == 10) {
                $model->status = 10;
            }
            return $model->save();
//            $this->addError(['number' => Yii::t('msg/error', 'Record was added before')]);
        } else {
            // сохраняем новую запись
            $this->created_at = time();
            return $this->save();
        }
    }

    public function getUserInfo()
    {
        $user = User::findOne($this->created_by);
        if ($user) {
            return [
                'id' => $user->id,
                'name' => $user->first_name,
                'surname' => $user->last_name,
                'photo' => $user->photoPath,
                'phone' => $user->getPhone()
            ];
        }
        return [
            'name' => null,
            'surname' => null,
            'photo' => 'http://agro.grassbusinesslabs.tk' . '/photo/user/empty.jpg'
        ];
    }

    public static function unreadMessages()
    {
        return [
            'count_unread' => Advertisement::unreadCount() + Message::unreadCount(),
            'chat_count' => (int) Message::unreadCount(),
            'finance_count' => (int) Message::unreadFinanceCount(),
            'buy_count' => (int) Advertisement::unreadBuyCount(),
            'sell_count' => (int) Advertisement::unreadSellCount()
        ];
    }

//    public function disable()
//    {
//        $className = $this::lastNameClass(static::className());
//        $data = [
//            'disable' => $this->disable
//        ];
//        parent::load([$className => $data]);
//        return $this->save();
//    }
}