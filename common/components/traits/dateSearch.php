<?php
namespace common\components\traits;

use yii\db\ActiveQuery;

trait dateSearch
{
    public $date_from;
    public $date_to;

    public $created_from;
    public $created_to;

    public $updated_from;
    public $updated_to;

    /**
     * @param $query
     */
    public function initDateSearch(ActiveQuery $query)
    {
        $this->created_from = $this->strToTs($this->created_from);
        $this->created_to = $this->strToTs($this->created_to);

        $this->updated_from = $this->strToTs($this->updated_from);
        $this->updated_to = $this->strToTs($this->updated_to);

        $this->date_from = $this->strToTs($this->date_from);
        $this->date_to = $this->strToTs($this->date_to);


        if (!empty($this->date_from)) {
            $this->created_from = $this->updated_from = $this->date_from;
        }
        if (!empty($this->date_to)) {
            $this->created_to = $this->updated_to = $this->date_to;
        }


        $query->andFilterWhere(['or',
            ['>=', self::tableName() . '.created_at', $this->created_from],
            ['>=', self::tableName() . '.updated_at', $this->updated_from],
        ]);

        $query->andFilterWhere(['or',
            ['<=', self::tableName() . '.created_at', $this->created_to],
            ['<=', self::tableName() . '.updated_at', $this->updated_to],
        ]);
    }
}