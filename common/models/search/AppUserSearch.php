<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\AppUser;

/**
 * AppUserSearch represents the model behind the search form about `common\models\AppUser`.
 */
class AppUserSearch extends AppUser
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
            [['id', 'user_id', 'account_type', 'status', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['password', 'username', 'phone', 'auth_key', 'email'], 'safe'],
        ];
    }


    /**
     * Creates data provider instance with search query applied
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = AppUser::find()->where(['created_by' => Yii::$app->user->id]);

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
            'user_id' => $this->user_id,
            'account_type' => $this->account_type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'email', $this->email]);

        return $dataProvider;
    }
}
