<?php

namespace common\models;

use common\components\helpers\ExtendedActiveRecord;
use common\components\UploadModel;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use common\components\traits\errors;
use common\components\traits\modelWithFiles;
use common\components\traits\soft;
use common\components\traits\findRecords;
use yii\web\HttpException;

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
            [['user_audit_id', 'question', 'answer', 'start_date', 'process_type'], 'required'],
            [['question', 'name', 'start_date', 'end_date'], 'string', 'max' => 255],
            [['answer'], 'string', 'max' => 50],
            [['data'], 'string'],
            [['user_audit_id', 'process_type',  'no_type', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
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

    public static function answerHandler($data, $id)
    {
        $model = new self();
        $model->user_audit_id = $id;
        if($model->load($data) && $model->save()){
//            if(array_key_exists('photo', $data)){
//                throw new HttpException('401', '1');
//                Attachment::saveFile($data, $id);
//            }
            if($model->no_type == 1){
                $noAnswer = new NoAnswer();
                $noAnswer->answer_id = $model->id;
                $noAnswer->description = $data['description'];
                if(!$noAnswer->save())
                return $noAnswer->errors;
            }
            if($model->no_type == 2){
                return  [
                    'status' => 3,
                    'model' => $model->id
                ];
            }
            return [
                'status' => 1,
                'model' => $model->id
            ];
        }
        return $model->errors;
    }


    public function checkProccess_type()
    {
        $file = new Attachment();
        $file->object_id = $this->id;
        $file->table = 'user_audit';
        $photoCount = $this->checkPhotoCount();
        switch ($this->process_type){
            case 3:
                $photoCount++;
//                $file->url = UploadModel::uploadBase($one['photo'], $one['extension'], $this->userAudit->name, $photoCount);
                return $file->save();

            case 4:
                $photoCount++;

        }
    }

    /**
     * @return int
     */
    public function checkPhotoCount()
    {
        return (int)self::find()->leftJoin('attachment', 'attachment.object_id = answer.id')->where([
            'answer.user_audit_id' => $this->id,
        ])->count();
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

    public function saveModel()
    {
        $this->save();
    }
}
