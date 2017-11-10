<?php

namespace common\models\search;

use common\models\Answer;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Attachment;

/**
 * AttachmentSearch represents the model behind the search form about `common\models\Attachment`.
 */
class AttachmentSearch extends Attachment
{
    public $size = 1000000;
    public $sort = [
        'id' => SORT_DESC,
    ];
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'object_id', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['table', 'extension', 'url'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Attachment::find()
            ->where([
                'type' => 1,
                'admin_id' => User::adminId()
            ]);

//            ->innerJoin('answer', 'answer.user_audit_id = attachment.object_id')
//        ->where(['answer.process_type' => 3]);

//        $query = Answer::find()->leftJoin('attachment', 'attachment.object_id = answer.user_audit_id')
//            ->where(['answer.process_type' => 3]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
            'sort' => [
                'defaultOrder' => $this->sort
            ],
        ]);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'attachment.id' => $this->id,
            'attachment.object_id' => $this->object_id,
            'attachment.status' => $this->status,
            'attachment.created_at' => $this->created_at,
            'attachment.updated_at' => $this->updated_at,
            'attachment.updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'attachment.table', $this->table])
            ->andFilterWhere(['like', 'attachment.url', $this->url]);

        if($this->extension !== 'pdf'){
//            $query->leftJoin('answer', 'answer.id = attachment.object_id')
//                ->where(['answer.process_type' => 3]);
            $query->andFilterWhere(['not like', 'attachment.extension', 'pdf']);
        }else{
            $query->andFilterWhere(['like', 'attachment.extension', 'pdf']);

        }

        return $dataProvider;
    }
}
