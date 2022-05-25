<?php

namespace frontend\models;

use Yii;
use frontend\models\User;
use common\models\Client;

/**
 * This is the model class for table "declaration".
 *
 * @property int $id
 * @property string $date
 * @property string|null $decl_number
 * @property int $client_id
 * @property int $user_id
 * @property resource $decl_iso
 */
class Declaration extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'declaration';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'client_id', 'user_id'], 'required'],
            [['date'], 'safe'],
            [['client_id', 'user_id', ], 'integer'],
			[['user_id'], 'default', 'value' => 'Yii::$app->user->id'],
		    [['decl_iso'], 'safe'],
			[['decl_iso'], 'default', 'value' => 'Yii::$app->user->id'],
            [['decl_number'], 'string', 'max' => 20],
			
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
           'id' => 'Номер записи',
            'date' =>  'Дата',
            'decl_number' => 'Номер декларации',
            'client_id' => 'Клиент',
            'user_id' => 'Брокер',
        ];
    }
	
	public function getUser0()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
	
	public function getClient0()
    {
        return $this->hasOne(Client::className(), ['id' => 'client_id']);
    }
	
	
 
}
