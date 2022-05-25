<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Client;

/**
 * ClientSearch represents the model behind the search form of `common\models\Client`.
 */
class ClientSearch extends Client
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['cod_EGRPOU', 'client', 'dogovor', 'date_begin', 'date_finish'], 'safe'],
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
        $query = Client::find();

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
        $query->andFilterWhere([
            'id' => $this->id,
            'date_begin' => $this->date_begin,
            'date_finish' => $this->date_finish,
        ]);

        $query->andFilterWhere(['like', 'cod_EGRPOU', $this->cod_EGRPOU])
            ->andFilterWhere(['like', 'client', $this->client])
            ->andFilterWhere(['like', 'dogovor', $this->dogovor]);

        return $dataProvider;
    }
}
