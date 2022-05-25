<?php

namespace backend\models;
use common\models\Client;

use Yii;

/**
 * This is the model class for table "client_info".
 *
 * @property int $id
 * @property int $client_id
 * @property string $bank
 * @property string $adress
 * @property string $director
 */
class ClientInfo extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'client_info';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['client_id', 'telephon', 'adress', 'director'], 'required'],
            [['client_id'], 'integer'],
            [['adress'], 'string'],
            [['telephon'], 'string', 'max' => 100],
            [['director'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'client_id' => 'Клиент',
            'telephon' => 'Телефон',
            'adress' => 'Адрес',
            'director' => 'Директор',
        ];
    }
	
	public function getClient0()
    {
        return $this->hasOne(Client::className(), ['id' => 'client_id']);
    }
	
}
