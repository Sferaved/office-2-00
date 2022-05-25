<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "homestatya".
 *
 * @property int $id
 * @property string $statya
 * @property string $income
 */
class HomestatyaModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'homestatya';
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
            'statya' => 'Статья',
            'income' => 'Доход',
        ];
    }

    public function getStatya0()
    {
        return $this->hasOne(StatyaHome::className(), ['id' => 'statya']);
    }
  	  
}
