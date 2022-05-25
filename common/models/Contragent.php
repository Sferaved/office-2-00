<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "contragent".
 *
 * @property int $id
 * @property string $contragent
 */
class Contragent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'contragent';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['contragent'], 'required'],
            [['contragent'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'contragent' => 'Контрагент',
        ];
    }
}
