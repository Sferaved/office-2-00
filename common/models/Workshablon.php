<?php

namespace common\models;

use Yii;

use common\models\Workstatya;
use common\models\Client;

/**
 * This is the model class for table "workshablon".
 *
 * @property int $id
 * @property int $client_id
 * @property float $cost
 * @property int $statya_id
 * @property string $ex_im
 */
class Workshablon extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'workshablon';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['client_id', 'cost', 'statya_id', 'ex_im'], 'required'],
            [['client_id', 'statya_id'], 'integer'],
            [['cost'], 'number'],
            [['ex_im'], 'string', 'max' => 7],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'client_id' => 'Клиент',
            'cost' => 'Сумма',
            'statya_id' => 'Статья',
            'ex_im' => 'Экспорт/импорт',
        ];
    }
	
	public function getStatya0()
    {
        return $this->hasOne(Workstatya::className(), ['id' => 'statya_id']);
    }
	
	public function getClient0()
    {
        return $this->hasOne(Client::className(), ['id' => 'client_id']);
    }
	
}
