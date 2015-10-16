<?php
namespace backend\components;

ini_set("display_errors", "On");
error_reporting(E_ALL);

class GeoserverWrapper {
	var $serverUrl = '';
	var $username = '';
	var $password = '';

	// Internal stuff
	public function __construct($serverUrl, $username = '', $password = '') {
		if (substr($serverUrl, -1) !== '/') $serverUrl .= '/';
		$this->serverUrl = $serverUrl;
		$this->username = $username;
		$this->password = $password;
	}

	private function authGet($apiPath) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->serverUrl.$apiPath);
		curl_setopt($ch, CURLOPT_USERPWD, $this->username.":".$this->password);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$rslt = curl_exec($ch);
		$info = curl_getinfo($ch);

		if ($info['http_code'] == 401) {
			return 'Access denied. Check login credentials.';
		} else {
			return $rslt;
		}
	}

	private function runApi($apiPath, $method = 'GET', $data = '', $contentType = 'text/xml') {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->serverUrl.'rest/'.$apiPath);
		curl_setopt($ch, CURLOPT_USERPWD, $this->username.":".$this->password);
		if ($method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		} else if ($method == 'DELETE' || $method == 'PUT') {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
			#Borra recursivamente el objeto workspace>datastore>capa
			curl_setopt($ch, CURLOPT_URL, curl_getinfo($ch, CURLINFO_EFFECTIVE_URL).'?recurse=true');
		}

		if ($data != '') {
			curl_setopt($ch, CURLOPT_HTTPHEADER,
			array("Content-Type: $contentType",
				'Content-Length: '.strlen($data))
			);
		}

		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$rslt = curl_exec($ch);
		$info = curl_getinfo($ch);

		if ($info['http_code'] == 401) {
			return 'Access denied. Check login credentials.';
		} else {
			return $rslt;
		}
	}

	// Workspace APIs
	public function listWorkspaces() {
		return json_decode($this->runApi('workspaces.json'));
	}

	public function createWorkspace($workspaceName) {
		return $this->runApi('workspaces', 'POST', '<workspace><name>'.htmlentities($workspaceName, ENT_COMPAT).'</name></workspace>');
	}

	public function deleteWorkspace($workspaceName) {
		return $this->runApi('workspaces/'.urlencode($workspaceName), 'DELETE');
	}

	// Datastore APIs
	public function listDatastores($workspaceName) {
		return json_decode($this->runApi('workspaces/'.urlencode($workspaceName).'/datastores.json'));
	}

	public function createPostGISDataStore($datastoreName, $workspaceName, $databaseName, $databaseUser, $databasePass, $databaseHost = 'localhost', $databasePort = '5432') {
		return $this->runApi('workspaces/'.urlencode($workspaceName).'/datastores', 'POST', '<dataStore>
			<name>'.htmlentities($datastoreName, ENT_COMPAT).'</name>
			<type>PostGIS</type>
			<enabled>true</enabled>
			<connectionParameters>
				<entry key="port">'.htmlentities($databasePort, ENT_COMPAT).'</entry>
				<entry key="Connection timeout">20</entry>
				<entry key="passwd">'.htmlentities($databasePass, ENT_COMPAT).'</entry>
				<entry key="dbtype">postgis</entry>
				<entry key="host">'.htmlentities($databaseHost, ENT_COMPAT).'</entry>
				<entry key="validate connections">true</entry>
				<entry key="encode functions">false</entry>
				<entry key="max connections">10</entry>
				<entry key="database">'.htmlentities($databaseName, ENT_COMPAT).'</entry>
				<entry key="namespace">'.htmlentities($workspaceName, ENT_COMPAT).'</entry>
				<entry key="schema">public</entry>
				<entry key="Loose bbox">true</entry>
				<entry key="Expose primary keys">false</entry>
				<entry key="fetch size">1000</entry>
				<entry key="Max open prepared statements">50</entry>
				<entry key="preparedStatements">false</entry>
				<entry key="Estimated extends">true</entry>
				<entry key="user">'.htmlentities($databaseUser, ENT_COMPAT).'</entry>
				<entry key="min connections">1</entry>
			</connectionParameters>
			</dataStore>');
	}

	public function createShpDirDataStore($datastoreName, $workspaceName, $location) {
		return $this->runApi('workspaces/'.urlencode($workspaceName).'/datastores', 'POST', '<dataStore>
			<name>'.htmlentities($datastoreName, ENT_COMPAT).'</name>
			<type>Directory of spatial files (shapefiles)</type>
			<enabled>true</enabled>
			<connectionParameters>
				<entry key="memory mapped buffer">false</entry>
				<entry key="timezone">America/Boise</entry>
				<entry key="create spatial index">true</entry>
				<entry key="charset">ISO-8859-1</entry>
				<entry key="filetype">shapefile</entry>
				<entry key="cache and reuse memory maps">true</entry>
				<entry key="url">file:'.htmlentities($location, ENT_COMPAT).'</entry>
				<entry key="namespace">'.htmlentities($workspaceName, ENT_COMPAT).'</entry>
			</connectionParameters>
			</dataStore>');
	}

	public function deleteDataStore($datastoreName, $workspaceName) {
		return $this->runApi('workspaces/'.urlencode($workspaceName).'/datastores/'.urlencode($datastoreName), 'DELETE');
	}

	// Layer APIs
	public function listLayers($workspaceName, $datastoreName) {
		return json_decode($this->runApi('workspaces/'.urlencode($workspaceName).'/datastores/'.urlencode($datastoreName).'/featuretypes.json'));
	}

	public function createLayer($layerName, $workspaceName, $datastoreName, $description = '') {
		// Add the store's feature type:
		// If layerName is a shapefile, the shapefile should exist in store already; uploaded via external means
		// If layerName is a postgis database table, that table should already exist

		// Just in case it's a .shp and the .shp was included
		$layerName = str_replace('.shp', '', str_replace('.SHP', '', $layerName));
		return $this->runApi('workspaces/'.urlencode($workspaceName).'/datastores/'.urlencode($datastoreName).'/featuretypes.xml', 'POST', '<featureType>
			<name>'.$layerName.'</name>
			<nativeName>'.$layerName.'</nativeName>
			<description>'.htmlentities($description, ENT_COMPAT).'</description>
			<store class="dataStore"><name>'.htmlentities($datastoreName, ENT_COMPAT).'</name></store>
			</featureType>');
	}

	public function deleteLayer($layerName, $workspaceName, $datastoreName) {
		$this->runApi('layers/'.urlencode($layerName), 'DELETE');
		return $this->runApi('workspaces/'.urlencode($workspaceName).'/datastores/'.urlencode($datastoreName).'/featuretypes/'.urlencode($layerName), 'DELETE');
	}

	public function viewLayer($layerName, $workspaceName, $format = 'GML', $maxGMLFeatures = 1000000, $overrideServerURL = '') {
		// overrideServerURL = useful if using reverseproxy-like configurations
		if ($format == 'GML') {
			return $this->authGet(urlencode($workspaceName).'/ows?service=WFS&version=1.0.0&request=GetFeature&typeName='.urlencode($workspaceName).':'.urlencode($layerName).'&maxFeatures='.$maxGMLFeatures);
		} else if ($format == 'KML') {
			return $this->authGet(urlencode($workspaceName).'/wms/kml?layers='.urlencode($workspaceName).':'.urlencode($layerName));
		}
	}

	public function viewLayerLegend($layerName, $workspaceName, $width = 20, $height = 20) {
		return $this->authGet("wms?REQUEST=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&WIDTH=$width&HEIGHT=$height&LAYER=".urlencode($workspaceName).':'.urlencode($layerName));
	}

	public function wfsPost($apiPath, $post) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->serverUrl.'wfs'.$apiPath);
		curl_setopt($ch, CURLOPT_USERPWD, $this->username.":".$this->password);
		if ($post != '') {
			curl_setopt($ch, CURLOPT_HTTPHEADER,
			array("Content-Type: text/xml",
				'Content-Length: '.strlen($post))
			);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$rslt = curl_exec($ch);
		$info = curl_getinfo($ch);

		if ($info['http_code'] == 401) {
			return 'Access denied. Check login credentials.';
		} else {
			return $rslt;
		}
	}

	public function executeWFSTransaction($WFSTRequest) {
		// WFS-T is just WFS really...
		return $this->wfsPost('', $WFSTRequest);
	}

	// Style APIs
	public function listStyles() {
		return json_decode($this->runApi('styles.json'));
	}

	public function createStyle($styleName, $SLD) {
		// Crea el estilo sin cuerpo
		$rv = $this->runApi('styles.xml', 'POST', '<style>
			<name>'.htmlentities($styleName, ENT_COMPAT).'</name>
			<filename>'.htmlentities($styleName, ENT_COMPAT).'.sld</filename>
			</style>');
		// Establece el cuerpo (No funciona)
		$this->runApi('styles/'.urlencode($styleName), 'PUT', stripslashes($SLD), 'application/vnd.ogc.sld+xml');
		return $rv;
	}

	public function updateStyle($styleName, $SLD) {
		// Crea el estilo sin cuerpo
		$rv = $this->runApi('styles.xml', 'PUT', '<style>
			<name>'.htmlentities($styleName, ENT_COMPAT).'</name>
			<filename>'.htmlentities($styleName, ENT_COMPAT).'.sld</filename>
			</style>');
		$rv2 = $this->runApi('reload', 'PUT');
		return $rv.' - '.$rv2;
	}

	/**
	 * Create style from file given by styleName
	 *
	 * @param $stylePathName sld file path
	 */
	public function createStyleFromSLDFile($stylePathName) {
		// Crea el estilo sin cuerpo
		$rv = $this->runApi('styles.xtml'.$stylePathName, 'POST', '<style>
			<name>'.htmlentities($stylePathName, ENT_COMPAT).'</name>
			<filename>'.htmlentities($stylePathName, ENT_COMPAT).'.sld</filename>
			</style>');
		$this->runApi('reload', 'PUT');
		return $rv;
	}

	public function addStyleToLayer($layerName, $workspaceName, $styleName) {
		// Just adds style to the list of supported styles - then WMS requests can pass the desired style
		return $this->runApi('layers/'.urlencode($layerName).'/styles', 'POST', '<style><name>'.htmlentities($styleName, ENT_COMPAT).'</name></style>');
	}

	/**
	 * Changes layer default style
	 * @param $layerName
	 * @param $workspace
	 * @param $styleName
	 */
	public function asignStyleToLayer($layerName, $workspace, $styleName, $service, $user, $pass) {
		// Initiate cURL session
		$request = "/rest/layers/".$workspace.":".$layerName;
		$url = $service . $request;
		$ch = curl_init($url);
		$passwordStr = $user.":".$pass;

		// Open log file
		$logfh = fopen("GeoserverPHP.log", 'w') or die("can't open log file");

		// Optional settings for debugging
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //option to return string
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_STDERR, $logfh); // logs curl messages


		//Required POST request settings
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_USERPWD, $passwordStr);

		//POST data
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type:text/xml"));
		$xmlStr = "<layer><defaultStyle><name>".$styleName."</name></defaultStyle></layer>";
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlStr);

		//POST return code
		$successCode = 201;

		$buffer = curl_exec($ch); // Execute the curl request

		// Check for errors and process results
		$info = curl_getinfo($ch);
		if ($info['http_code'] != $successCode) {
			$msgStr = "# Unsuccessful cURL request to ";
			$msgStr .= $url." [". $info['http_code']. "]\n";
			fwrite($logfh, $msgStr);
		} else {
			$msgStr = "# Successful cURL request to ".$url."\n";
			fwrite($logfh, $msgStr);
		}
		fwrite($logfh, $buffer."\n");

		curl_close($ch);
		fclose($logfh);

		return $info['http_code'];
	}

	public function deleteStyle($styleName) {
		return $this->runApi('styles/'.urlencode($styleName), 'DELETE');
	}

	// ########################################################################################
	// ######## Funciones especificas para controlar la seguridad de acceso a los servicios
	// ########################################################################################
	public function createUser($user, $pass) {

		$properties = require dirname(__FILE__).'/../config/properties.php';
		$path=$properties['geoserverHomeDataPath'];
		$filePath=$path."/security/usergroup/default/users.xml";

		// lectura datos actuales
		$current=file_get_contents($filePath);

		// agregacion del usuario
		$tag='<user enabled="true" name="'.$user.'" password="plain:'.$pass.'"/>'.'</users>';
		$current=str_replace('</users>', $tag, $current);

		// guardado
		file_put_contents($filePath, $current, LOCK_EX);

	}

	public function deleteUser($user, $pass) {

//		$properties = require dirname(__FILE__).'/../../protected/config/properties.php';
		$properties = require dirname(__FILE__).'/../config/properties.php';
		
		$path=$properties['geoserverHomeDataPath'];
		$filePath=$path."/security/usergroup/default/users.xml";

		// lectura datos actuales
		$current=file_get_contents($filePath);

		// eliminacion del usuario
		$tag='<user enabled="true" name="'.$user.'" password="plain:'.$pass.'"/>';

		$current=str_replace($tag, '', $current);

		// guardado
		file_put_contents($filePath, $current, LOCK_EX);

	}

	public function updateUser($user, $pass) {
		$this->deleteUser($user, $pass);
		$this->createUser($user, $pass);
	}

	/**
	 * Crea los roles necesarios por grupo en el fichero roles.xml.
	 * Utilizado para la creación de un nuevo grupo.
	 * @param unknown_type $groupId
	 */
	public function createRoles($groupId) {

		//$properties = require dirname(__FILE__).'/../../protected/config/properties.php';
	    $properties = require dirname(__FILE__).'/../config/properties.php';
		
		$path=$properties['geoserverHomeDataPath'];
		$filePath=$path."/security/role/default/roles.xml";

		// lectura datos actuales
		$current=file_get_contents($filePath);

		// agregacion de rol de EDITOR (para edicion wfs)
		$tag='<role id="'.$groupId.'_EDITOR"/>'.'</roleList>';
		$current=str_replace('</roleList>', $tag, $current);

		// guardado
		file_put_contents($filePath, $current, LOCK_EX);

	}

	/**
	 * Elimina los rol y relaciones con usuarios del perfil editor en el fichero roles.xml.
	 * Al borrar un grupo.
	 */
	public function deleteRolEditor($groupId) {

		//$properties = require dirname(__FILE__).'/../../protected/config/properties.php';
		
		$properties = require dirname(__FILE__).'/../config/properties.php';
		
		$path=$properties['geoserverHomeDataPath'];
		$filePath=$path."/security/role/default/roles.xml";

		// lectura datos actuales
		$current=file_get_contents($filePath);

		// elimniacion de rol de EDITOR (para edicion wfs)
		$tag='<role id="'.$groupId.'_EDITOR"/>';
		$current=str_replace($tag, '', $current);

		// guardado
		file_put_contents($filePath, $current, LOCK_EX);

	}

	/**
	 * Crea los roles necesarios por usuario en grupo en el fichero roles.xml (para lectura de wms).
	 * Cuando se da de alta un nuevo usuario en un grupo.
	 * @param unknown_type $groupId
	 * @param unknown_type $userId
	 * * @param unknown_type $ISeDITOR
	 */
	public function addRelationUserRole($groupId, $userId, $userName, $isEditor){
		
		
		//$properties = require dirname(__FILE__).'/../../protected/config/properties.php';
		
		$properties = require dirname(__FILE__).'/../config/properties.php';
		$path=$properties['geoserverHomeDataPath'];
		$filePath=$path."/security/role/default/roles.xml";

		// lectura datos actuales
		$current=file_get_contents($filePath);

		// Crear rol de lectura de usuario en grupo
		// 		Solo si el rol no existe
		$tag='<role id="'.$groupId.'_USU'.$userId.'_INVITADO"/>';
		if(strpos($current, $tag)!==false){
			// ya existe
			//	nothing to do
		}else{
			// no existe
			$tag=$tag.'</roleList>';
			$current=str_replace('</roleList>', $tag, $current);
		}

		// Asociar usuario con el rol lector (invitado)
		//		Solo si no se ha asociado ya (rol no existe)

		$tag='<userRoles username="'.$userName.'">';
		if(strpos($current, $tag)!==false){
			// ya existe
			//	nothing to do

			$tag='<roleRef roleID="'.$groupId.'_USU'.$userId.'_INVITADO"/>';
			if(strpos($current, $tag)!==false){
				// ya existe la etiqueta invitado
				//	nothing to do
			}else{
				$tagUserRoles='<userRoles username="'.$userName.'">';
				$current=str_replace($tagUserRoles, $tagUserRoles.$tag, $current);
			}

		}else{
			// no existe
			$tag=$tag.'<roleRef roleID="'.$groupId.'_USU'.$userId.'_INVITADO"/>'.'</userRoles>';
			$current=str_replace('<userList>', '<userList>'.$tag, $current);
		}

		// Si es editor, asociar usuario con el rol editor
		$tag='<userRoles username="'.$userName.'">';
		if($isEditor==true){
			$tag=$tag.'<roleRef roleID="'.$groupId.'_EDITOR"/>';
			$current=str_replace('<userRoles username="'.$userName.'">', $tag, $current);
		}

		// guardado
		file_put_contents($filePath, $current, LOCK_EX);
	}

	/**
	 * Elimina el rol INVITADO de un usuario y la relacion de este usuario con cualquier rol.
	 *
	 * @param unknown_type $groupId
	 * @param unknown_type $userId
	 * @param unknown_type $userName
	 * @param unknown_type $isEditor
	 */
	public function deleteRelationUserRole($groupId, $userId, $userName, $isEditor, $delete) {

		//$properties = require dirname(__FILE__).'/../../protected/config/properties.php';
		$properties = require dirname(__FILE__).'/../config/properties.php';
		
		$path=$properties['geoserverHomeDataPath'];
		$filePath=$path."/security/role/default/roles.xml";

		// borrar todo
		// lectura datos actuales
		$current=file_get_contents($filePath);

		$rolTag='<role id="'.$groupId.'_USU'.$userId.'_INVITADO"/>';
		// eliminar relaciones con el rol
		if(strpos($current, $rolTag)!==false){

			// hay que buscar la cadena que indica que el usuario tiene un rol del grupo...
			$tag='<userRoles username="'.$userName.'">';
			$endTag='</userRoles>';
			$posIni=strpos($current, $tag);
			$posFin=strpos($current, $endTag, $posIni + strLen($endTag)) + strLen($endTag);

			$tagInvitado='<roleRef roleID="'.$groupId.'_USU'.$userId.'_INVITADO"/>';
			$tagEditor='<roleRef roleID="'.$groupId.'_EDITOR"/>';

			// cadena dentro de la asigancion de roles del usuario
			// solamente se borra la informacion del grupo actual
			$cadIntermedia=substr($current, $posIni, $posFin - $posIni);
			$cadIntermedia=str_replace($tagInvitado, "", $cadIntermedia);
			$cadIntermedia=str_replace($tagEditor, "", $cadIntermedia);

			$cad1=substr($current, 0, $posIni);
			$cad2=substr($current, $posFin, strlen($current) - $posFin);
			$current=$cad1.$cadIntermedia.$cad2;

			// eliminar rol
			$tag='<role id="'.$groupId.'_USU'.$userId.'_INVITADO"/>';
			$current=str_replace($tag, '', $current);

			file_put_contents($filePath, $current, LOCK_EX);
		}
		// fin borrar todo
			
		if($delete==false){
			// si "delete", significa que el usuario debe borrarse completamente
			// (no se esta borrando un perfil, se esta borrando el usuario dentro del grupo)
			$this->addRelationUserRole($groupId, $userId, $userName, !$isEditor);
		}

	}

	/**
	 *
	 * Al crear un servicio, dar permisos de editor en el grupo.
	 */
	public function addLayerEditRole($namespace, $groupId) {

		//$properties = require dirname(__FILE__).'/../../protected/config/properties.php';
		
		$properties = require dirname(__FILE__).'/../config/properties.php';
		$path=$properties['geoserverHomeDataPath'];
		$filePath=$path."/security/layers.properties";

		// agregar rol editor al namespace
		$tag="\n".$namespace.'.*.w='.$groupId.'_EDITOR';

		// guardado
		file_put_contents($filePath, $tag, FILE_APPEND | LOCK_EX);

	}

	/**
	 *
	 * Al eliminar un servicio, borrar permisos de editor en el grupo.
	 */
	public function deleteLayerEditRole($namespace, $groupId) {

		//$properties = require dirname(__FILE__).'/../../protected/config/properties.php';
		$properties = require dirname(__FILE__).'/../config/properties.php';
		
		$path=$properties['geoserverHomeDataPath'];
		$filePath=$path."/security/layers.properties";

		// lectura datos actuales
		$current=file_get_contents($filePath);

		// eliminacion rol editor al namespace
		$tag="\n".$namespace.'.*.w='.$groupId.'_EDITOR';
		$current=str_replace($tag, '', $current);

		// guardado
		file_put_contents($filePath, $current, LOCK_EX);

	}

	/**
	 * Regenera el fichero layer.properties para actualizar los permisos a capas
	 */
	public function updateLayersViewRol(){

		$connection = Yii::app()->db;

		// query services and groups
		$command = $connection->createCommand('select x_group_user, x_seso, group_users_x_group_users, t_server from s2_group_users join s2_services_sources on s2_group_users.x_group_user=s2_services_sources.group_users_x_group_users order by x_group_user');
		$rowsServices = $command->queryAll();

		// query services and groups
		$command2 = $connection->createCommand('select x_privileges, group_profile_x_group_profile, service_x_service, group_x_group, user_x_user from s2_privileges join s2_group_users_profiles on s2_privileges.group_profile_x_group_profile=s2_group_users_profiles.x_group_user_profile order by group_x_group');
		$rowsPrivileges = $command2->queryAll();

		// recorrido de servicios
		$servicesByGroup=array();
		for($i=0; $i<count($rowsServices); $i++){
			$urlNodes =  preg_split("/\//", $rowsServices[$i]['t_server']);
			$workspaceName =  $urlNodes[sizeof($urlNodes)-2];
			$service=new ServiceClass();
			$service->id=$rowsServices[$i]['x_seso'];
			$service->service=$workspaceName;

			if(!isset($servicesByGroup[$rowsServices[$i]['x_group_user']])){
				$servicesByGroup[$rowsServices[$i]['x_group_user']]=array();
			}
			$arrayServices=$servicesByGroup[$rowsServices[$i]['x_group_user']];
			array_push($arrayServices, $service);
			$servicesByGroup[$rowsServices[$i]['x_group_user']]=$arrayServices;

		}
		Fb::log(var_export($servicesByGroup, true));

		// recorrido privilegios por servicio
		$privilegesByService=array();
		for($i=0; $i<count($rowsPrivileges); $i++){
			$privilege=new PrivilegeServiceClass();
			$privilege->id=$rowsPrivileges[$i]['x_privileges'];
			$privilege->service=$rowsPrivileges[$i]['service_x_service'];
			$privilege->group=$rowsPrivileges[$i]['group_x_group'];
			$privilege->user=$rowsPrivileges[$i]['user_x_user'];

			if(!isset($privilegesByService[$rowsPrivileges[$i]['service_x_service']])){
				$privilegesByService[$rowsPrivileges[$i]['service_x_service']]=array();
			}
			$arrayPrivileges=$privilegesByService[$rowsPrivileges[$i]['service_x_service']];
			array_push($arrayPrivileges, $privilege);
			$privilegesByService[$rowsPrivileges[$i]['service_x_service']]=$arrayPrivileges;
		}
		Fb::log(var_export($privilegesByService, true));

		// construccion del fichero
		$current = "*.*.r\n";
		$current = $current."*.*.w\n";

		foreach ($servicesByGroup as $key=>$services){
			$idGroup=$key;

			for ($i=0; $i<count($services); $i++){
				$serv=$services[$i];

				// perfil editor
				$current=$current.$serv->service.".*.w=".$idGroup."_EDITOR\n";

				// perfiles view
				if(isset($privilegesByService[$serv->id])){
					$privileges=$privilegesByService[$serv->id];
					$existencias=array();
					$separador="";
					if(count($privileges)>0){
						$current=$current.$serv->service.".*.r=";
					}else{
						$current=$current.$serv->service.".*.r=*";
					}
					for ($j=0; $j<count($privileges); $j++){
						$priv=$privileges[$j];
						if (!isset($existencias[$priv->user]) || $existencias[$priv->user]==false){
							$current=$current.$separador.$idGroup."_USU".$priv->user."_INVITADO";
							$separador=",";
							$existencias[$priv->user]=true;
						}
					}
					$current=$current."\n";
				}else{
					$current=$current.$serv->service.".*.r=*\n";
				}

			}
		}

		$current=$current.'mode=HIDE';
		Fb::log($current);
		// fin construccion fichero

		// escritura fichero
		//$properties = require dirname(__FILE__).'/../../protected/config/properties.php';
		$properties = require dirname(__FILE__).'/../config/properties.php';
		$path=$properties['geoserverHomeDataPath'];
		$filePath=$path."/security/layers.properties";

		file_put_contents($filePath, $current, LOCK_EX);
		// fin escritura fichero

	}

	/**
	 *
	 * Al crear un grupo, agregar el rol de editor para permisos de escritura WFS.
	 * @param unknown_type $groupId
	 */
	public function addServiceEditorTransaction($groupId) {

		//$properties = require dirname(__FILE__).'/../../protected/config/properties.php';
		$properties = require dirname(__FILE__).'/../config/properties.php';

		$path=$properties['geoserverHomeDataPath'];
		$filePath=$path."/security/services.properties";

		// agregacion de permisos para transaction al rol editor
		$tag="\n".'wfs.Transaction='.$groupId.'_EDITOR';

		file_put_contents($filePath, $tag, FILE_APPEND | LOCK_EX);

	}

	/**
	 * Al eliminar un grupo, eliminar permisos de escritura WFS para el rol editor
	 * @param unknown_type $groupId
	 */
	public function deleteServiceEditorTransaction($groupId) {

		//$properties = require dirname(__FILE__).'/../../protected/config/properties.php';
		$properties = require dirname(__FILE__).'/../config/properties.php';
		
		$path=$properties['geoserverHomeDataPath'];
		$filePath=$path."/security/services.properties";

		// lectura datos actuales
		$current=file_get_contents($filePath);

		// eliminacion de permisos para transaction al rol editor
		$tag="\n".'wfs.Transaction='.$groupId.'_EDITOR';
		$current=str_replace($tag, '', $current);

		file_put_contents($filePath, $current, LOCK_EX);

	}

}

// clases auxiliares para actualizacion de datos
class ServiceClass {
	public $id;
	public $service;
}

class PrivilegeServiceClass {
	public $id;
	public $service;
	public $user;
	public $group;
}