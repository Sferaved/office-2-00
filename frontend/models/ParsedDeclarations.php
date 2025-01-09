<?php


namespace frontend\models;

/**
 * This is the model class for table "parsed_declarations".
 *
 */
class ParsedDeclarations extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'parsed_declarations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'custom' => 'Custom',
            'contragent_id' => 'Contragent ID',
            'ex_im' => 'Ex Im',
            'cod_EGRPOU' => 'Cod Egrpou',
            'costCurrency' => 'Cost Currency',
            'costValue' => 'Cost Value',
            'costCurs' => 'Cost Curs',
            'dop_list' => 'Dop List',
            'decl' => 'Decl',
            'decl_date' => 'Decl Date',
            'client_id' => 'Client ID',
        ];
    }
}
