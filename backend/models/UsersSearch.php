<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Users;

/**
 * UsersSearch represents the model behind the search form about `backend\models\Users`.
 */
class UsersSearch extends Users
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['x_user', 'group_origin'], 'number'],
            [['t_user', 't_name', 't_password', 't_lastname', 't_dni', 't_mail', 'created', 'creation_date', 'modified', 'modification_date', 't_internal_password'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Users::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'x_user' => $this->x_user,
            'creation_date' => $this->creation_date,
            'modification_date' => $this->modification_date,
            'group_origin' => $this->group_origin,
        ]);

        $query->andFilterWhere(['like', 't_user', $this->t_user])
            ->andFilterWhere(['like', 't_name', $this->t_name])
            ->andFilterWhere(['like', 't_password', $this->t_password])
            ->andFilterWhere(['like', 't_lastname', $this->t_lastname])
            ->andFilterWhere(['like', 't_dni', $this->t_dni])
            ->andFilterWhere(['like', 't_mail', $this->t_mail])
            ->andFilterWhere(['like', 'created', $this->created])
            ->andFilterWhere(['like', 'modified', $this->modified])
            ->andFilterWhere(['like', 't_internal_password', $this->t_internal_password]);

        return $dataProvider;
    }
}
