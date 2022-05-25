<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Invoice;

/**
 * InvoiceSearch represents the model behind the search form of `frontend\models\Invoice`.
 */
class InvoiceSearch extends Invoice
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'decl_id', 'client_id', 'user_id'], 'integer'],
            [['date', 'oplata', 'forma_oplat'], 'safe'],
            [['cost'], 'number'],
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
        $query = Invoice::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
			'sort'=>[
			'defaultOrder'=>[
				'oplata'=>SORT_DESC,
				'date'=>SORT_DESC,
				'id'=>SORT_DESC,
				 
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
        $query->andFilterWhere([
            'id' => $this->id,
            'date' => $this->date,
            'decl_id' => $this->decl_id,
            'client_id' => $this->client_id,
            'cost' => $this->cost,
            'user_id' => $this->user_id,
        ]);

        $query->andFilterWhere(['like', 'oplata', $this->oplata])
            ->andFilterWhere(['like', 'forma_oplat', $this->forma_oplat]);

        return $dataProvider;
    }
}
