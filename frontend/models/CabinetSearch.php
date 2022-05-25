<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Cabinet;

/**
 * CabinetSearch represents the model behind the search form of `frontend\models\Cabinet`.
 */
class CabinetSearch extends Cabinet
{
    /**
     * {@inheritdoc}
     */
	public $date_from;
	public $date_to;
	 
    public function rules()
    {
        return [
            [['id', 'decl_id', 'coment_id', 'user_id','client_id'], 'integer'],
            [['date','date_from','date_to'], 'safe'],
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
        $query = Cabinet::find();

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

        if (Yii::$app->user->id !=1 && Yii::$app->user->id !=2){
			 $query->where('user_id=:id', array(':id'=>Yii::$app->user->id));
		}
		
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
            'decl_id' => $this->decl_id,
            'cost' => $this->cost,
            'coment_id' => $this->coment_id,
            'user_id' => $this->user_id,
			'client_id' => $this->client_id,
        ]);

        return $dataProvider;
    }
}
