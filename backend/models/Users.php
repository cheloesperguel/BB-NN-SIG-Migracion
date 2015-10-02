<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "s2_users".
 *
 * @property string $x_user
 * @property string $t_user
 * @property string $t_name
 * @property string $t_password
 * @property string $t_lastname
 * @property string $t_dni
 * @property string $t_mail
 * @property string $created
 * @property string $creation_date
 * @property string $modified
 * @property string $modification_date
 * @property string $t_internal_password
 * @property string $group_origin
 *
 * @property S2GeoprofilesXUsers[] $s2GeoprofilesXUsers
 * @property S2GroupUsers[] $s2GroupUsers
 * @property S2GroupUsersProfiles[] $s2GroupUsersProfiles
 * @property S2GroupUsers $groupOrigin
 */
class Users extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 's2_users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
        /// Modificado : Listo , funcional
            [['t_user', 't_name', 't_lastname', 't_mail'], 'required'],
            [['t_password', 't_password_repeat'], 'required'],
            [['t_password_repeat'], 'compare', 'compareAttribute' => 't_password'],
            [['t_password', 'repeat_password'], 'safe'],
            [['t_password', 't_password_repeat'], 'string', 'min'=>6, 'max'=>20],
            [['x_user'], 'string', 'max'=>4],
            [['t_user'], 'string', 'max' => 15],
            [['t_name', 't_dni', 'created', 'modified'], 'string', 'max' => 20],
            [['t_lastname'], 'string', 'max' => 40],
            [['t_mail'], 'string',
            [['t_mail'], 'match','pattern'=>'/^[_A-Za-z0-9-\\+]+(\\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\\.[A-Za-z0-9]+)*(\\.[A-Za-z]{2,})$/']
        //////
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            ///
            'x_user' => 'Id. Usuario',
            't_user' => 'Nombre de usuario',
            't_name' => 'Nombre',
            't_password_repeat' => 'Confirma Contraseña',
            't_password' => 'Contraseña',
            't_lastname' => 'Apellidos',
            't_dni' => 'RUT',
            't_mail' => 'E-Mail',
            'created' => 'Created',
            'creation_date' => 'Creation Date',
            'modified' => 'Modified',
            'modification_date' => 'Modification Date',
            ///
            //X//'t_internal_password' => 'T Internal Password',
            //X//'group_origin' => 'Group Origin',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS2GeoprofilesXUsers()
    {
        return $this->hasMany(S2GeoprofilesXUsers::className(), ['user_x_user' => 'x_user']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS2GroupUsers()
    {
        return $this->hasMany(S2GroupUsers::className(), ['user_x_user' => 'x_user']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getS2GroupUsersProfiles()
    {
        return $this->hasMany(S2GroupUsersProfiles::className(), ['user_x_user' => 'x_user']);
    }

    /**giActiveQuery
     */
    public function getGroupOrigin()
    {
        return $this->hasOne(S2GroupUsers::className(), ['x_group_user' => 'group_origin']);
    }
}
