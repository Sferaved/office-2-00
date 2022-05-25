<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Workzatraty;

/**
 * WorkzatratySearch represents the model behind the search form of `backend\models\Workzatraty`.
 */
class WorkzatratySearch extends Workzatraty
{
    /**
     * {@inheritdoc}

     */

    public $date_from;
	public $date_to;
	
    public function rules()
    {
        return [
            [['id', 'decl_id', 'client_id', 'workstatya_id'], 'integer'],
            [['date','date_from','date_to'], 'safe'],
            [['cost'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
	public function attributeLabels()
    {
        return [
           
            'date_from' => 'Период отбора записей',
		          
        ];
    } 
	 
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
        $query = Workzatraty::find();

        // add conditions that should always apply here
      
	  $dataProvider = new ActiveDataProvider([
            'query' => $query,
			'sort'=>[
			'defaultOrder'=>[
			//	 'client_id'=>SORT_ASC,
				 'date'=>SORT_DESC,
			],
		],	
        ]);
        $dataProvider->pagination->pageSize=10; // Количество строк в таблице 

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $this->load($params);
		
        // grid filtering conditions
		
  		
		$query->andFilterWhere(['between', 'date', $this->date_from, $this->date_to]);
		
		$query->andFilterWhere([
            'id' => $this->id,
            'date' => $this->date,
            'decl_id' => $this->decl_id,
            'client_id' => $this->client_id,
            'cost' => $this->cost,
            'workstatya_id' => $this->workstatya_id,
        ]);

        return $dataProvider;
    }
}
