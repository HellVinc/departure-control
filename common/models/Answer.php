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
 * This is the model class for table "answer".
 *
 * @property integer $id
 * @property integer $user_audit_id
 * @property integer $process_type
 * @property integer $question
 * @property integer $answer
 * @property integer $start_date
 * @property integer $end_date
 * @property integer $no_type
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property UserAudit $userAudit
 * @property NoAnswer[] $noAnswers
 */
class Answer extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;
    use modelWithFiles;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'answer';
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
            [['user_audit_id', 'question', 'answer', 'start_date'], 'required'],
            [['question', 'name'], 'string', 'max' => 255],
            [['answer'], 'string', 'max' => 50],
            [['data'], 'string'],
            [['user_audit_id', 'process_type', 'start_date', 'end_date', 'no_type', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['user_audit_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserAudit::className(), 'targetAttribute' => ['user_audit_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_audit_id' => 'User Audit ID',
            'process_type' => 'Answer Type',
            'answer' => 'Answer',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'no_type' => 'No Type',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserAudit()
    {
        return $this->hasOne(UserAudit::className(), ['id' => 'user_audit_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNoAnswers()
    {
        return $this->hasMany(NoAnswer::className(), ['answer_id' => 'id']);
    }
}
