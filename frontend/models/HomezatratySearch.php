<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Homezatraty;

/**
 * HomezatratySearch represents the model behind the search form of `app\models\Homezatraty`.
 */
class HomezatratySearch extends Homezatraty
{
    /**
     * {@inheritdoc}
     */
	public $date_from;
	public $date_to; 
	
    public function rules()
    {
        return [
            [['id', 'statya'], 'integer'],
            [['date','date_from','date_to', 'comment'], 'safe'],
            [['cost'], 'number'],
        ];
    }
	public function attributeLabels()
    {
        return [
           
            'date_from' => 'Период отбора записей',
		     ];
    }      
      
    /**
     * {@inheritdoc}
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
    public function search($params)
    {
        $query = Homezatraty::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
			'sort'=>[
			'defaultOrder'=>[
				 'id'=>SORT_DESC
			],
        ],
        ]);
        $dataProvider->pagination->pageSize=10; // Количество строк в таблице
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
		$query->andFilterWhere(['between', 'date', $this->date_from, $this->date_to]);
        $query->andFilterWhere([
            'id' => $this->id,
            'date' => $this->date,
            'statya' => $this->statya,
            'cost' => $this->cost,
        ]);

        $query->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
