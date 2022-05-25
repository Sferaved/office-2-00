<?php

namespace frontend\models;

use Yii;
use common\models\Contragent;
use frontend\models\Declaration;

/**
 * This is the model class for table "aquaizol".
 *
 * @property int $id
 * @property string $date
 * @property string $ex_im
 * @property int|null $decl_number_id
 * @property int $contragent_id
 * @property float|null $broker
 * @property float|null $dosmotr
 * @property float|null $custom
 * @property float|null $fito
 *
 * @property Contragent $contragent
 * @property Declaration $declNumber
 */
class Aquaizol extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'aquaizol';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'ex_im', 'contragent_id','decl_number_id'], 'required'],
            [['date'], 'safe'],
            [['decl_number_id', 'contragent_id'], 'integer'],
            [['broker', 'dosmotr', 'custom', 'fito'], 'number'],
			[['dosmotr', 'custom'],'default', 'value' => '550'],
			[['broker', 'fito'],'default', 'value' => '0'],
            [['ex_im'], 'string', 'max' => 15],
          /*   [['contragent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Contragent::className(), 'targetAttribute' => ['contragent_id' => 'id']],
            [['decl_number_id'], 'exist', 'skipOnError' => true, 'targetClass' => Declaration::className(), 'targetAttribute' => ['decl_number_id' => 'id']], */
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'date' => 'Дата',
            'ex_im' => 'Тип',
            'decl_number_id' => 'Декларация',
            'contragent_id' => 'Контрагент',
            'broker' => 'Доп.брокер',
            'dosmotr' => 'Досмотр',
            'custom' => 'Таможня',
            'fito' => 'Фито',
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

    /**
     * Gets query for [[DeclNumber]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDeclNumber()
    {
        return $this->hasOne(Declaration::className(), ['id' => 'decl_number_id']);
    }
}
