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
use yii\web\IdentityInterface;

/**
 * This is the model class for table "app_user".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $password
 * @property string $username
 * @property string $phone
 * @property integer $account_type
 * @property integer $password_reset_token
 * @property string $auth_key
 * @property string $email
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property User $user
 * @property UserAudit[] $userAudits
 */
class AppUser extends ExtendedActiveRecord implements IdentityInterface
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
        return 'app_user';
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
            [['user_id', 'account_type', 'username'], 'required'],
            [['user_id', 'account_type', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['password', 'username', 'email'], 'string', 'max' => 255],
            [['phone'], 'string'],
            [['username', 'user_id'], 'unique', 'targetAttribute' => ['username', 'user_id']],
            [['auth_key'], 'string', 'max' => 32],
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
            'user_id' => 'User ID',
            'password' => 'Password',
            'username' => 'Username',
            'phone' => 'Phone',
            'account_type' => 'Account Type',
            'auth_key' => 'Auth Key',
            'email' => 'Email',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public function oneFields()
    {
        return self::getFields($this, [
            'id',
            'username',
            'password',
            'account_type',
            'email',
            'auth_key',
            'created_at',
            'created_by'
        ]);
    }

    public static function allFields($result)
    {
        return self::responseAll($result, [
            'id',
            'username',
            'password',
            'account_type',
            'email',
            'auth_key',
            'created_at',
            'created_by'
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserAudits()
    {
        return $this->hasMany(UserAudit::className(), ['app_user_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @param mixed $token
     * @param null $type
     * @return AppUser
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['auth_key' => $token]);
//        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

}
