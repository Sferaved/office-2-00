<?php

namespace backend\models;

use Yii;
use common\models\Client;
use common\models\Workstatya;
use frontend\models\Declaration;

/**
 * This is the model class for table "workzatraty".
 *
 * @property int $id
 * @property string $date
 * @property int|null $decl_id
 * @property int $client_id
 * @property float $cost
 * @property int $workstatya_id
 *
 * @property Client $client
 * @property Declaration $decl
 * @property Workstatya $workstatya
 */
class Workzatraty extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'workzatraty';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'client_id', 'cost', 'workstatya_id'], 'required'],
            [['date'], 'safe'],
            [['decl_id', 'client_id', 'workstatya_id'], 'integer'],
            [['cost'], 'number'],
         /*    [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['client_id' => 'id']],
            [['decl_id'], 'exist', 'skipOnError' => true, 'targetClass' => Declaration::className(), 'targetAttribute' => ['decl_id' => 'id']],
            [['workstatya_id'], 'exist', 'skipOnError' => true, 'targetClass' => Workstatya::className(), 'targetAttribute' => ['workstatya_id' => 'id']],
        */ ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Номер записи',
            'date' => 'Дата',
            'decl_id' => 'Декларация',
            'client_id' => 'Субъект',
            'cost' =>  'Сумма',
            'workstatya_id' => 'Статья',
        ];
    }

    /**
     * Gets query for [[Client]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Client::className(), ['id' => 'client_id']);
    }

    /**
     * Gets query for [[Decl]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDecl()
    {
        return $this->hasOne(Declaration::className(), ['id' => 'decl_id']);
    }

    /**
     * Gets query for [[Workstatya]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWorkstatya()
    {
        return $this->hasOne(Workstatya::className(), ['id' => 'workstatya_id']);
    }
}
