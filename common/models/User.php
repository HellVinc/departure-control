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
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property integer $first_name
 * @property integer $last_name
 * @property integer $firma
 * @property string $username
 * @property string $pass
 * @property string $phone
 * @property string $activation_code
 * @property string $sub_end
 * @property integer $account_type
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 *
 * @property UserAudit[] $userAudits
 */
class User extends ExtendedActiveRecord implements IdentityInterface
{

    use soft;
    use findRecords;
    use errors;
    use modelWithFiles;

    const TYPE_ADMIN = 0;
    const TYPE_VERLANDER = 1;
    const TYPE_FRACHTFUHRER = 2;
    const TYPE_EMPFANGER = 3;

    public $password;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
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
            [['account_type', 'username', 'password'], 'required', 'on' => 'create'],
            [['first_name', 'last_name', 'email', 'phone', 'firma'], 'required', 'on' => 'register'],
            [['account_type', 'activation_code', 'sub_end', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['username', 'first_name', 'last_name', 'firma', 'password_reset_token', 'email'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            ['password', 'required', 'on' => 'signUp'],
            ['password', 'string', 'min' => 6],
            [['phone'], 'string', 'max' => 13],
            [['username'], 'unique'],
//            [['email'], 'unique'],
            [['password_reset_token'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'account_type' => 'Account Type',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    public function signup()
    {
//        if (!$this->validate()) {
//            return null;
//        }
        $this->pass = $this->password;
//        $this->setPassword($this->password);
        $this->generateAuthKey();
        return $this->save() ? $this->oneFields() : $this->errors;
    }

    public function register()
    {
//        if (!$this->validate()) {
//            return null;
//        }
//        $this->generateAuthKey();

        return $this->save() ? $this->oneFields() : $this->errors;
    }

    public static function allFields($result)
    {
        return self::responseAll($result, [
            'id',
            'username',
            'password' => 'pass',
            'account_type',
            'sub_end',
            'email',
            'auth_key',
            'created_at',
            'created_by'
        ]);
    }

    public function oneFields()
    {
        return self::getFields($this, [
            'id',
            'username',
            'password' => 'pass',
            'account_type',
            'sub_end',
            'email',
            'auth_key',
            'created_at',
            'created_by'
        ]);
    }

    public static function sendMail($mail, $pass)
    {
        return Yii::$app->mailer->compose()
            ->setFrom('admin@DC.com')
            ->setTo($mail)
            ->setSubject('TestReg')
            ->setTextBody($pass)
            ->send();
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
     * @return User
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

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }


    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
        $this->generateAuthKey();
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserAudits()
    {
        return $this->hasMany(UserAudit::className(), ['user_id' => 'id']);
    }
}
