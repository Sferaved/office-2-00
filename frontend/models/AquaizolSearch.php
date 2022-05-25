<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Aquaizol;

/**
 * AquaizolSearch represents the model behind the search form of `frontend\models\Aquaizol`.
 */
class AquaizolSearch extends Aquaizol
{
    /**
     * {@inheritdoc}
     */
	
	public $date_from;
	public $date_to; 
	 
    public function rules()
    {
        return [
            [['id', 'decl_number_id', 'contragent_id'], 'integer'],
            [['date', 'ex_im','date_from','date_to'], 'safe'],
            [['broker', 'dosmotr', 'custom', 'fito'], 'number'],
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
        $query = Aquaizol::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
			'sort'=>[
			'defaultOrder'=>[
				 'date'=>SORT_DESC
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
            'decl_number_id' => $this->decl_number_id,
            'contragent_id' => $this->contragent_id,
            'broker' => $this->broker,
            'dosmotr' => $this->dosmotr,
            'custom' => $this->custom,
            'fito' => $this->fito,
        ]);

        $query->andFilterWhere(['like', 'ex_im', $this->ex_im]);

        return $dataProvider;
    }
}
