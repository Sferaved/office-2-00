<?php

namespace frontend\models;

use Yii;
use frontend\models\User;
use common\models\Client;
use frontend\models\Declaration;

/**
 * This is the model class for table "invoice".
 *
 * @property int $id
 * @property string $date
 * @property int|null $decl_id
 * @property int $client_id
 * @property float $cost
 * @property int $user_id
 * @property string $oplata
 * @property string $forma_oplat
 */
class Invoice extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invoice';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'client_id', 'cost', 'user_id', 'oplata', 'forma_oplat'], 'required'],
            [['date'], 'safe'],
            [['decl_id', 'client_id', 'user_id'], 'integer'],
            [['cost'], 'number'],
            [['oplata', 'forma_oplat'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Номер счета',
            'date' => 'Дата',
            'decl_id' => 'Декларация',
            'client_id' => 'Клиент',
            'cost' => 'Сумма',
            'user_id' => 'Брокер',
            'oplata' => 'Оплата',
            'forma_oplat' => 'Форма оплаты',
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
	
	public function getDeclaration0()
    {
        return $this->hasOne(Declaration::className(), ['id' => 'decl_id']);
    }
}
