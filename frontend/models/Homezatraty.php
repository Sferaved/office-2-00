<?php

namespace app\models;


use Yii;
use common\models\HomestatyaModel;

/**
 * This is the model class for table "homezatraty".
 *
 * @property int $id
 * @property string $date
 * @property int $statya
 * @property float $cost
 * @property string $comment
 */
class Homezatraty extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'homezatraty';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'statya', 'cost'], 'required'],
            [['date'], 'safe'],
            [['statya'], 'integer'],
            [['cost'], 'number'],
            [['comment'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => '№',
            'date' => 'Дата',
            'statya' => 'Статья',
            'cost' => 'Сумма',
            'comment' => 'Коментарий',
        ];
    }

    public function getStatya0()
    {
        return $this->hasOne(HomestatyaModel::className(), ['id' => 'statya']);
    }

}
