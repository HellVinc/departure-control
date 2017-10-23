<?php

namespace common\components;

use common\components\traits\soft;
use common\models\Attachment;
use common\models\UserAudit;
use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class UploadModel extends Model
{
    use soft;
    /**
     * @var UploadedFile[]
     */
    public $files;
    /** @var  UploadedFile $imageFile */
    public $imageFile;

    const ONE_FILE = 'oneFile';

    public function rules()
    {
        return [
            [['files'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg', 'maxFiles' => 3, 'on' => 'default'],
            [['imageFile'], 'file', 'extensions' => 'png, jpg', 'on' => 'oneFile'],
        ];
    }

    public function upload($id, $path)
    {
        $dir = dirname(Yii::getAlias('@app')) . '/' . $path . '/' . $id;
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir);
        }
        if ($this->validate()) {
            $name = hash_file('crc32', $this->imageFile->tempName);
            $this->imageFile->saveAs($dir . '/' . $name . '.' . $this->imageFile->extension);
            return $name . '.' . $this->imageFile->extension;
        }
        return false;
    }

    public function uploads($id, $table)
    {
        if ($this->validate()) {
            $dir = dirname(Yii::getAlias('@app')) . '/files/' . $table . '/' . $id;
            if (!is_dir($dir)) {
                FileHelper::createDirectory($dir);
            }
            foreach ($this->files as $file) {
                $name = hash_file('crc32', $file->tempName);
                $file->saveAs($dir . '/' . $name . '.' . $file->extension);
                $model = new Attachment();
                $model->object_id = $id;
                $model->table = $table;
                $model->extension = $file->extension;
                $model->url = $name . '.' . $file->extension;
                $model->save();
            }
            return true;
        }
        return false;
    }

    public static function uploadBase($base64, $extension, $name)
    {
        $photoCount = (int)UserAudit::find()
            ->where(['between', 'created_at', strtotime('today'), time()])
            ->count();

        if($extension === 'jpg')
        {
            $format = $extension;
            $extension = 'jpeg';
        }else{
            $format = $extension;
        }
        $path = '/files/photo/';


        $data = str_replace('data:image/' . $extension . ';base64,', '', $base64);
        $data = str_replace(' ', '+', $data);
        $data = base64_decode($data); // Decode image using base64_decode
        $file = 'DCF-' . date('Ymd',time()) . '-' . self::beginWithZero($photoCount) . '-' . $name . '.' . $format;
//        $file = mt_rand(10000, 900000) . '.' . $extension;

        $dir = dirname(Yii::getAlias('@app')) . $path;
        if (!is_dir($dir)) {
            FileHelper::createDirectory($dir);
        }

        $dir = dirname(Yii::getAlias('@app')) . $path . $file;
        if (!file_put_contents($dir, $data)) {
            return false;
        }
        return $file;
    }
}