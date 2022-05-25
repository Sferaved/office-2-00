<?php

namespace frontend\models;

use Yii;
use frontend\models\User;
use frontend\models\Cabinetstatya;
use common\models\Client;

/**
 * This is the model class for table "cabinet".
 *
 * @property int $id
 * @property string $date
 * @property int $decl_id
 * @property float $cost
 * @property int $coment_id
 * @property int $user_id
 *
 * @property Declaration $decl
 * @property User $user
 * @property Cabinetstatya $coment
 */
class Cabinet extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cabinet';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'cost', 'coment_id', 'user_id','client_id'], 'required'],
            [['date'], 'safe'],
            [['decl_id', 'coment_id', 'user_id','client_id'], 'integer'],
            [['cost'], 'number'],
			[['user_id'], 'default', 'value' => 'Yii::$app->user->id'],
            [['decl_id'], 'exist', 'skipOnError' => true, 'targetClass' => Declaration::className(), 'targetAttribute' => ['decl_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['coment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cabinetstatya::className(), 'targetAttribute' => ['coment_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Номер записи',
            'date' => 'Дата',
            'decl_id' => 'Номер декларации',
            'cost' => 'Сумма',
            'coment_id' => 'Коментарий',
            'user_id' => 'Брокер',
			'client_id'=>'Клиент'
        ];
    }

    /**
     * Gets query for [[Decl]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDecl0()
    {
        return $this->hasOne(Declaration::className(), ['id' => 'decl_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser0()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Gets query for [[Coment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComent0()
    {
        return $this->hasOne(Cabinetstatya::className(), ['id' => 'coment_id']);
    }
	
	public function getClient0()
    {
        return $this->hasOne(Client::className(), ['id' => 'client_id']);
    }
	
}
