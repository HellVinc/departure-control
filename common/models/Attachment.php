<?php

namespace common\models;

use common\components\helpers\ExtendedActiveRecord;
use common\components\UploadModel;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\HttpException;
use yii\web\UploadedFile;
use common\components\traits\errors;
use common\components\traits\modelWithFiles;
use common\components\traits\soft;
use common\components\traits\findRecords;

/**
 * This is the model class for table "attachment".
 *
 * @property integer $id
 * @property integer $object_id
 * @property string $table
 * @property string $extension
 * @property string $url
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 */
class Attachment extends ExtendedActiveRecord
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
        return 'attachment';
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
            [['object_id', 'table', 'extension'], 'required'],
            [['object_id', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['table', 'extension', 'url'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'object_id' => 'Object ID',
            'table' => 'Table',
            'extension' => 'Extension',
            'url' => 'Url',
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
            'object_id' => $this->object_id,
            'table' => $this->table,
            'extension' => $this->extension,
            'name' => $this->getName(),
            'url' => $this->getUrl(),
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
        ];
    }

    /**
     * @param $result
     * @return array
     */
    public static function allFields($result)
    {
        return self::responseAll($result, [
            'id',
            'object_id',
            'name',
            'table',
            'extension',
            'url' => 'Url',
            'created_at',
            'created_by',
        ]);
    }

    public function getName()
    {
        $name = pathinfo($this->getUrl());
        return $name['filename'];
    }

    public function getUrl()
    {
        if ($this->extension === 'pdf') {
            return Yii::$app->request->hostInfo . '/files/pdf/' . $this->url;
        }
        return Yii::$app->request->hostInfo . '/files/photo/' . $this->url;
    }

    public static function saveFile($data, $id)
    {
        if(isset($data['photo']) || isset($data['signature'])) {
            $name = UserAudit::findOne($id)->name;
            $photoCount = (int)Answer::find()->leftJoin('attachment', 'attachment.object_id = answer.id')->where([
                'answer.user_audit_id' => $id,
            ])->count();
            $photoCount++;
            $model = new self;
            $model->table = 'user_audit';
            $model->object_id = $id;
            $model->extension = $data['extension'];
            if ($data['signature']) {
                $model->url = UploadModel::uploadBase($data['signature'], $data['extension'], mt_rand(10000, 900000), $photoCount);
            } else {
                $model->url = UploadModel::uploadBase($data['photo'], $data['extension'], $name, $photoCount);
            }
            if (!$model->save()) {
                throw new HttpException(400, $model->errors);
            }
            return $model->getUrl();
        }
        return true;
    }

    public static function uploadFiles($id, $table)
    {
        $model = new UploadModel();
        $model->files = UploadedFile::getInstancesByName('photo');
        if ($model->uploads($id, $table)) {
            return $model;
        }
    }

}
