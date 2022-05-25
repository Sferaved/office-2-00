<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "workstatya".
 *
 * @property int $id
 * @property string $statya
 * @property string $report
 */
class Workstatya extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'workstatya';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['statya', 'report'], 'required'],
            [['statya'], 'string', 'max' => 20],
            [['report'], 'string', 'max' => 3],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'statya' => Yii::t('app', 'Статья'),
            'report' => Yii::t('app', 'Отчет'),
        ];
    }
}
