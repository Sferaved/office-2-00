<?php

namespace backend\models;

use Yii;
use common\models\Contragent;

/**
 * This is the model class for table "aq_fl_cost".
 *
 * @property int $id
 * @property int $contragent_id
 * @property float $cost
 *
 * @property Contragent $contragent
 */
class AqFlCost extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'aq_fl_cost';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['contragent_id', 'cost'], 'required'],
            [['contragent_id'], 'integer'],
            [['cost'], 'number'],
            [['contragent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Contragent::className(), 'targetAttribute' => ['contragent_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'contragent_id' => 'Контрагент',
            'cost' => 'Сумма',
        ];
    }

    /**
     * Gets query for [[Contragent]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContragent()
    {
        return $this->hasOne(Contragent::className(), ['id' => 'contragent_id']);
    }
}
