<?php

namespace common\models;

use common\components\helpers\ExtendedActiveRecord;
use Yii;
use common\components\traits\errors;
use common\components\traits\modelWithFiles;
use common\components\traits\soft;
use common\components\traits\findRecords;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "audit_has_kriterien".
 *
 * @property integer $id
 * @property integer $kriterien_id
 * @property integer $audit_id
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property Audit $audit
 * @property Kriterien $kriterien
 */
class AuditHasKriterien extends ExtendedActiveRecord
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
        return 'audit_has_kriterien';
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
            [['kriterien_id', 'audit_id'], 'required'],
            [['kriterien_id', 'audit_id', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['audit_id'], 'exist', 'skipOnError' => true, 'targetClass' => Audit::className(), 'targetAttribute' => ['audit_id' => 'id']],
            [['kriterien_id'], 'exist', 'skipOnError' => true, 'targetClass' => Kriterien::className(), 'targetAttribute' => ['kriterien_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'kriterien_id' => 'Kriterien ID',
            'audit_id' => 'Audit ID',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKriterien()
    {
        return $this->hasOne(Kriterien::className(), ['id' => 'kriterien_id']);
    }
}
