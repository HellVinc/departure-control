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
 * This is the model class for table "audit".
 *
 * @property integer $id
 * @property string $type
 * @property string $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property Kriterien[] $kriteriens
 * @property AuditHasKriterien[] $auditHasKriteriens
 * @property UserAudit[] $userAudits
 */
class Audit extends ExtendedActiveRecord
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
        return 'audit';
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
            [['type'], 'required'],
            [['type'], 'unique'],
            [['created_at', 'updated_at', 'created_by', 'updated_by', 'status'], 'integer'],
            [['type'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public function oneFields()
    {
        $result = [
            'id' => $this->id,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'created_at' => date('d.m.Y', $this->created_at),
            'kriteriens' => $this->kriteriens
        ];
        return $result;
    }

    /**
     * @param $result
     * @return array
     */
    public static function allFields($result)
    {
        return self::responseAll($result, [
            'id',
            'type',
            'status',
            'kriteriens',
        ]);
    }

    public function getAuditHasKriteriens()
    {
        return $this->hasMany(AuditHasKriterien::className(), ['audit_id' => 'id']);
    }

    public function getKriteriens()
    {
        return Kriterien::find()
            ->leftJoin('audit_has_kriterien', 'audit_has_kriterien.kriterien_id = kriterien.id')
            ->where(['audit_has_kriterien.audit_id' => $this->id])
            ->orderBy('audit_has_kriterien.id')
            ->all();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserAudits()
    {
        return $this->hasMany(UserAudit::className(), ['audit_id' => 'id'])
            ->leftJoin('kriterien', 'kriterien.id = audit_has_kriterien.kriterien_id');
    }
}
