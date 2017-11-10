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
 * @property integer $name
 * @property string $question
 * @property integer $description
 * @property integer $employee
 * @property integer $process_type
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property Audit[] $audits
 * @property AuditHasKriterien[] $auditHasKriteriens
 */
class Kriterien extends ExtendedActiveRecord
{
    use soft;
    use findRecords;
    use errors;
    use modelWithFiles;

    const TYPE_DATE = 1;
    const TYPE_QUESTION = 2;
    const TYPE_PHOTO = 3;
    const TYPE_SIGNATURE= 4;
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
            [['name', 'process_type', 'employee', 'question'], 'required'],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['question', 'description'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'question' => 'Question',
            'position' => 'Position',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public function oneFields()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'question' => $this->question,
            'description' => $this->description,
            'employee' => (int)$this->employee,
            'process_type' => (int)$this->process_type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ];
    }

//    public function getCreated_by()
//    {
//        return User::find()->where(['id' => $this->created_by])->one()->username;
//    }


    public function getAudits()
    {
        return $this->hasMany(Audit::className(), ['id' => 'audit_id'])
            ->viaTable('audit_has_kriterien', ['kriterien_id' => 'id']);
    }

    public function getAuditHasKriteriens()
    {
        return $this->hasMany(AuditHasKriterien::className(), ['kriterien_id' => 'id']);
    }
}
