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

    //public $t_password_repeat;

    function __construct() {
    }

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
            [['t_password' /*, 't_password_repeat'*/], 'required'],
            //[['t_password_repeat'], 'compare', 'compareAttribute' => 't_password'],
            [['t_password', /*'repeat_password'*/], 'safe'],
            [['t_password', /*'t_password_repeat'*/], 'string', 'min'=>6, 'max'=>20],
            [['x_user'], 'string', 'max'=>4],
            [['t_user'], 'string', 'max' => 15],
            [['t_name', 't_dni', 'created', 'modified'], 'string', 'max' => 20],
            [['t_lastname'], 'string', 'max' => 40],
            [['t_mail'], 'string', 'max'=> 50],
            [['t_mail'], 'match','pattern'=>'/^[_A-Za-z0-9-\\+]+(\\.[_A-Za-z0-9-]+)*@[A-Za-z0-9-]+(\\.[A-Za-z0-9]+)*(\\.[A-Za-z]{2,})$/'],
            [['x_user, t_user, t_name, t_lastname, t_dni, t_mail, group_origin, created, creation_date, modified, modification_date'], 'safe', 'on'=>'search']
        //////
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            /// Listo Funcional
            'x_user' => 'Id. Usuario',
            't_user' => 'Nombre de usuario',
            't_name' => 'Nombre',
            //'t_password_repeat' => 'Confirma Contraseña',
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

    public function getTPassword() {
        return $this->t_password;
    }

    /**
     * Sets the t password.
     *
     * @param password the new t password
     */
    public function setTPassword($password) {
        $this->t_password = $password;
    }

    public function getTInternalPassword() {
        return $this->t_internal_password;
    }

    /**
     * 
     *
     * Sets the t password.
     *
     * @param password the new t password
     */
    public function setTInternalPassword($password) {
        $this->t_internal_password = $password;
    }

    public function getTLastname() {
        return $this->t_lastname;
    }

    /**
     * Sets the t last name.
     *
     * @param TLastname the new t last name
     */
    public function setTLastname($TLastname) {
        $this->t_lastname = $TLastname;
    }

    /**
     * Gets the t dni.
     *
     * @return the t dni
     */
    public function getTDni() {
        return $this->t_dni;
    }

    /**
     * Sets the t dni.
     *
     * @param TDni the new t dni
     */
    public function setTDni($TDni) {
        $this->t_dni = $TDni;
    }

    /**
     * Gets the t mail.
     *
     * @return the t mail
     */
    public function getTMail() {
        return $this->t_mail;
    }

    /**
     * Sets the t mail.
     *
     * @param TMail the new t mail
     */
    public function setTMail($TMail) {
        $this->t_mail = $TMail;
    }
    
    /**
     * Gets the group origin.
     *
     * @return the group origin
     */

    /**
     * Sets the group origin.
     *
     * @param GroupOrigin the new group origin
     */
    public function setGroupOrigin($GroupOrigin) {
        $this->group_origin = $GroupOrigin;
    }
    
    /**
     * Sets the modification date.
     *
     * @param modificationDate the new modification date
     */
    public function setModificationDate($modificationDate) {
        $this->modification_date = $modificationDate;
    }

    /**
     * Gets the s2 geoprofiles x users.
     *
     * @return the s2 geoprofiles x users
     */
    public function getS2GeoprofilesXUserses() {
        return $this->s2GeoprofilesXUsers;
    }

    /**
     * Sets the s2 geoprofiles x users.
     *
     * @param geoprofilesXUserses the new s2 geoprofiles x users
     */
    public function setS2GeoprofilesXUserses($geoprofilesXUserses) {
        $this->s2GeoprofilesXUsers = $geoprofilesXUserses;
    }

    /**
     * Gets the s2 groups users.
     *
     * @return the s2 groups users
     */
    
    /**
     * Sets the s2 groups users.
     *
     * @param groupUsers the new s2 group users
     */
    public function setS2GroupUsers($groupUsers) {
        $this->s2GroupUsers = $groupUsers;
    }
    
    /**
     * Gets the s2 group users profiles.
     *
     * @return the s2 group users profiles
     */
    
    /**
     * Sets the s2 geoprofiles x users.
     *
     * @param geoprofilesXUserses the new s2 geoprofiles x users
     */
    public function setS2GroupUsersProfiles($groupUsersProfiles) {
        $this->s2GroupUsersProfiles = $groupUsersProfiles;
    }
    
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return S2Users the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

        public function userByXUser($s2Users)
    {
        $criteria = new CDbCriteria;
        $criteria->condition = 'x_user='.$s2Users;

        return $usuarios = S2Users::model()->findAll($criteria);
    }
    

    /**
     * Mount groups for users group creation and edition
     * @return string with users
     */
    public function montarPeticionUsuariosGrupo(){
        return "{ \"xuser\": \"".$this->getXUser()."\", \"tuser\" : \"".$this->getTUser() ."\", \"tname\" : \"".$this->getTName(). "\", \"tlastname\": \"".$this->getTLastname() ."\", \"gettdni\": \"".$this->getTDni() ."\"},";
    }

    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new /*C*/DbCriteria;

        $criteria->compare('x_user',$this->x_user,true);
        $criteria->compare('t_user',$this->t_user,true);
        $criteria->compare('t_name',$this->t_name,true);
        $criteria->compare('t_password',$this->t_password,true);
        $criteria->compare('t_lastname',$this->t_lastname,true);
        $criteria->compare('t_dni',$this->t_dni,true);
        $criteria->compare('t_mail',$this->t_mail,true);
        $criteria->compare('created',$this->created,true);
        $criteria->compare('creation_date',$this->creation_date,true);
        $criteria->compare('modified',$this->modified,true);
        $criteria->compare('modification_date',$this->modification_date,true);

        return new /*C*/ActiveDataProvider($this, [
            'criteria'=>$criteria,
        ]);
    }

    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return [
            's2GeoprofilesXUsers' => [self::HAS_MANY, 'S2GeoprofilesXUsers', 'user_x_user'],
            's2GroupUsers' => [self::HAS_MANY, 's2GroupUsers', 'user_x_user'],
            's2GroupUsersProfiles' => [self::HAS_MANY, 's2GroupUsersProfiles', 'user_x_user'],
            'groupOriginXGroupOrigin' => [self::BELONGS_TO, 'S2GroupUsers', 'group_origin'],
        ];
    }
}

