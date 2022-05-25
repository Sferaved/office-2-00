<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Workshablon;

/**
 * WorkshablonSearch represents the model behind the search form of `common\models\Workshablon`.
 */
class WorkshablonSearch extends Workshablon
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'client_id', 'statya_id'], 'integer'],
            [['cost'], 'number'],
            [['ex_im'], 'safe'],
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
        $query = Workshablon::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
			'sort'=>[
			'defaultOrder'=>[
				 'client_id'=>SORT_ASC
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
        $query->andFilterWhere([
            'id' => $this->id,
            'client_id' => $this->client_id,
            'cost' => $this->cost,
            'statya_id' => $this->statya_id,
        ]);

        $query->andFilterWhere(['like', 'ex_im', $this->ex_im]);

        return $dataProvider;
    }
}
