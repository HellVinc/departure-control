<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Kriterien;

/**
 * KriterienSearch represents the model behind the search form about `common\models\Kriterien`.
 */
class KriterienSearch extends Kriterien
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
            [['id', 'audit_id', 'position', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['question'], 'safe'],
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
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Kriterien::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this->size,
            ],
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
            'audit_id' => $this->audit_id,
            'position' => $this->position,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'question', $this->question]);

        return $dataProvider;
    }
}
