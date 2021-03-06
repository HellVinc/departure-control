<?php

namespace common\models\search;

use common\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Answer;

/**
 * AnswerSearch represents the model behind the search form about `common\models\Answer`.
 */
class AnswerSearch extends Answer
{
    public $size = 10;
    public $sort = [
        'id' => SORT_ASC,
    ];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_audit_id', 'answer', 'process_type', 'start_date', 'end_date', 'no_type', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['answer', 'question'], 'safe']
            ];
    }

    /**
     * Creates data provider instance with search query applied
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Answer::find()
            ->where(['created_by' => User::adminId()]);

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
            'id' => $this->id,
            'user_audit_id' => $this->user_audit_id,
            'answer' => $this->answer,
            'no_type' => $this->no_type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'answer', $this->answer])
            ->andFilterWhere(['like', 'question', $this->question]);

        return $dataProvider;
    }
}
