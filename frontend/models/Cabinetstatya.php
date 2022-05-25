<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "cabinetstatya".
 *
 * @property int $id
 * @property string $statya
 * @property string $income
 *
 * @property Cabinet[] $cabinets
 */
class Cabinetstatya extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cabinetstatya';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['statya', 'income'], 'required'],
            [['statya'], 'string', 'max' => 20],
            [['income'], 'string', 'max' => 3],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'statya' => Yii::t('app', 'Statya'),
            'income' => Yii::t('app', 'Income'),
        ];
    }

    /**
     * Gets query for [[Cabinets]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCabinets()
    {
        return $this->hasMany(Cabinet::className(), ['coment_id' => 'id']);
    }
}
