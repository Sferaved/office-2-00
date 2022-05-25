<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Declaration;

/**
 * DeclarationSearch represents the model behind the search form of `app\models\Declaration`.
 */
class DeclarationSearch extends Declaration
{
    /**
     * {@inheritdoc}
     */
	public $date_from;
	public $date_to; 
	 
	 
    public function rules()
    {
        return [
            [['id', 'client_id', 'user_id'], 'integer'],
            [['date', 'decl_number', 'decl_iso','date_from','date_to'], 'safe'],
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
        $query = Declaration::find();

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
            'client_id' => $this->client_id,
            'user_id' => $this->user_id,
          ]);

        $query->andFilterWhere(['like', 'decl_number', $this->decl_number])
            ->andFilterWhere(['like', 'decl_iso', $this->decl_iso]);

        return $dataProvider;
    }
}
