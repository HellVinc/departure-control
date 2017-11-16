<?php

namespace common\models;

use common\components\helpers\ExtendedActiveRecord;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use common\components\traits\errors;
use common\components\traits\modelWithFiles;
use common\components\traits\soft;
use common\components\traits\findRecords;

/**
 * This is the model class for table "user_audit".
 *
 * @property integer $id
 * @property integer $name
 * @property integer $user_id
 * @property integer $audit_id
 * @property integer $admin_id
 * @property integer $start_date
 * @property integer $end_date
 * @property integer $count_per_date
 * @property integer $success
 * @property integer $light_type
 * @property integer $description
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property Answer[] $answers
 * @property Audit $audit
 * @property User $user
 */
class UserAudit extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;
    use modelWithFiles;

    const GREEN_LIGHT = 1;
    const YELLOW_LIGHT = 2;
    const RED_LIGHT = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_audit';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at'
                ]
            ],
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['audit_id', 'name'], 'required'],
            [['user_id', 'audit_id', 'light_type', 'count_per_date', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['name', 'description', 'start_date', 'end_date'], 'string', 'max' => 255],
            [['audit_id'], 'exist', 'skipOnError' => true, 'targetClass' => Audit::className(), 'targetAttribute' => ['audit_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'user_id' => 'User ID',
            'audit_id' => 'Audit ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public static function allFields($result)
    {
        return self::responseAll($result, [
            'id',
            'name' => 'Name',
            'user_id',
            'audit_id',
            'admin_id',
            'start_date',
            'end_date',
            'count_per_date',
            'status',
            'description',
            'light_type'
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAnswers()
    {
        return $this->hasMany(Answer::className(), ['user_audit_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAudit()
    {
        return $this->hasOne(Audit::className(), ['id' => 'audit_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'DCP-' . date('Ymd', $this->created_at) . '-' . self::beginWithZero($this->count_per_date) . '-' .  $this->name;
    }

    /**
     * @return int
     */
    public function checkCountPerDate()
    {
        return (int)self::find()
            ->where(['between', 'created_at', strtotime('today'), time()])
            ->count();
    }


    public function saveModel()
    {
        $count = $this->checkCountPerDate();
        $count++;
        $this->count_per_date = $count;
        $this->user_id = Yii::$app->user->id;
        return $this->save();
    }
}
