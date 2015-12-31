<?php

namespace DevGroup\ExtensionsManager\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "{{%extensions}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $installed_version
 * @property string $description
 * @property integer $active
 * @property string $type
 */
class Extension extends \yii\db\ActiveRecord
{
    const TYPE_DOTPLANT = 'dotplant-extension';
    const TYPE_YII = 'yii2-extension';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%extensions}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'installed_version', 'type'], 'required'],
            [['active'], 'integer'],
            [['name'], 'string', 'max' => 180],
            [['installed_version', 'type'], 'string', 'max' => 20],
            [['description'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'installed_version' => 'Installed Version',
            'description' => 'Description',
            'active' => 'Active',
            'type' => 'Type',
        ];
    }

    public function search($params)
    {
        /** @var $query \yii\db\ActiveQuery */
        $query = self::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        if (!($this->load($params))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['active' => $this->active]);
        $query->andFilterWhere(['type' => $this->type]);
        return $dataProvider;
    }

    public static function getTypes()
    {
        return [
            self::TYPE_DOTPLANT => Yii::t('extensions-manager', 'Dotplant extension'),
            self::TYPE_YII => Yii::t('extensions-manager', 'Yii2 extension'),
        ];
    }
}
