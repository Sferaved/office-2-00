<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "client".
 *
 * @property int $id
 * @property string $cod_EGRPOU
 * @property string $client
 * @property string $dogovor
 * @property string $date_begin
 * @property string $date_finish
 */
class Client extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'client';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cod_EGRPOU', 'client', 'dogovor', 'date_begin', 'date_finish'], 'required'],
            [['client'], 'string'],
            [['date_begin', 'date_finish'], 'safe'],
            [['cod_EGRPOU'], 'string', 'max' => 10],
            [['dogovor'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'cod_EGRPOU' => 'ЕГРПОУ',
            'client' => 'Клиент',
            'dogovor' => 'Номер договора',
            'date_begin' => 'Дата начала',
            'date_finish' => 'Дата окончания',
        ];
    }
}
