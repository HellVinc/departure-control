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
 * This is the model class for table "kriterien".
 *
 * @property integer $id
 * @property integer $audit_id
 * @property string $question
 * @property integer $position
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property Audit $audit
 */
class Kriterien extends ExtendedActiveRecord
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
        return 'kriterien';
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
            [['audit_id', 'question', 'position'], 'required'],
            [['audit_id', 'position', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['question'], 'string'],
            [['audit_id'], 'exist', 'skipOnError' => true, 'targetClass' => Audit::className(), 'targetAttribute' => ['audit_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'audit_id' => 'Audit ID',
            'question' => 'Question',
            'position' => 'Position',
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
    public function getAudit()
    {
        return $this->hasOne(Audit::className(), ['id' => 'audit_id']);
    }
}
