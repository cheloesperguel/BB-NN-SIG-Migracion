<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * INSTALL PEAR and package request2  http://pear.php.net/package/HTTP_Request2/
 */
require_once dirname(__FILE__) . '/../extensions/FirePHPCore/fb.php';
require_once 'HTTP/Request2.php';
/**
 * cswClient allows to request a OGC CSW 2.0.2 - ISO API service
 * @package csw
 * @author lagarde pierre
 * @copyright BRGM
 * @name cswClient
 * @version 1.0.0
 */
class cswClient {
	private $_cswAddress;
	private $_authentAddress;
	private $_cswLogin;
	private $_cswPassword;
	private $_bAuthent;
	private $_sessionID;

	private $_response;

	/**
	 *
	 * @param String $cswAddress address of the CSW URL
	 * @param String $cswLogin login of the user to CSW-T
	 * @param String $cswPassword  password of the user to CSW-T
	 * @param String $authentAddress address of the login/logout address
	 */

	function  __construct($cswAddress,$cswLogin=null,$cswPassword=null,$authentAddress=null) {
		$this->_cswAddress=$cswAddress;
		$this->_bAuthent=false;
		if (isset($cswLogin)) {
			$this->_cswLogin=$cswLogin;
			$this->_cswPassword=$cswPassword;
			$this->_authentAddress=$authentAddress;
			$this->_bAuthent=true;
		}
	}

	/**
	 *
	 * @return bool Request success / error
	 */
	private function _callHTTPCSW($request) {

		try {
			$resp= $request->send();
		
			if (200 == $resp->getStatus()) {
				$this->_response = $resp->getBody();
				$cookies = $resp->getCookies();
				foreach ($cookies as $cook) {
					if ($cook['name']=='JSESSIONID'){
						$this->_sessionID = $cook['value'];
					}
				}
				return true;
			} else {
				Fb::log("exception: ".$resp->getStatus() . ' ' .$resp->getReasonPhrase());
				$this->_response = $resp->getStatus() . ' ' .$resp->getReasonPhrase();
				return false;
			}
		} catch (HTTP_Request2_Exception $e) {
			$this->_response = 'Error: ' . $e->getMessage();
			return false;
		}

	}

	public function authentication($request){
		return $this->_authentication($request);
	}
	
	/**
	 *
	 * @return bool authentication success or error
	 */
	private function _authentication($request) {
		//only available for Geosource and Geonetwork
		//start by logout
		if ($this->_bAuthent) {
			$req = new HTTP_Request2($this->_authentAddress.'/xml.user.logout', HTTP_Request2::METHOD_POST);

			if ($this->_callHTTPCSW($req)) {
				//success so next step
				//start to login
				$req = new HTTP_Request2( $this->_authentAddress.'/xml.user.login');
				$req->setMethod(HTTP_Request2::METHOD_POST)
				->setHeader("'Content-type': 'application/x-www-form-urlencoded', 'Accept': 'text/plain'")
				->addPostParameter('username', $this->_cswLogin)
				->addPostParameter('password', $this->_cswPassword);
				if ($this->_callHTTPCSW($req)) {
					$request->addCookie('JSESSIONID', $this->_sessionID);
					return true;
				}
			}
			return false;
		}
		return true;
	}

	/**
	 * Login with admin geonetwork (always)
	 *
	 * @return bool authentication success or error
	 */
	private function _authenticationAdmin($request) {

		$properties = require dirname(__FILE__).'/../../protected/config/properties.php';
		$adminUser=$properties['userGeonetwork'];
		$adminPass=$properties['pwGeonetwork'];

		//only available for Geosource and Geonetwork
		//start by logout
		if ($this->_bAuthent) {
			$req = new HTTP_Request2($this->_authentAddress.'/xml.user.logout', HTTP_Request2::METHOD_POST);

			if ($this->_callHTTPCSW($req)) {
				//success so next step
				//start to login
				$req = new HTTP_Request2( $this->_authentAddress.'/xml.user.login');
				$req->setMethod(HTTP_Request2::METHOD_POST)
				->setHeader("'Content-type': 'application/x-www-form-urlencoded', 'Accept': 'text/plain'")
				->addPostParameter('username', $adminUser)
				->addPostParameter('password', $adminPass);
				if ($this->_callHTTPCSW($req)) {
					$request->addCookie('JSESSIONID', $this->_sessionID);
					return true;
				}
			}
			return false;
		}
		return true;
	}

	/**
	 * retrieve a specific metadata with UUID in GeoNetwork / Geosource
	 * @param String $id of the metadata
	 * @return XML content
	 */
	public function getRecordById($id) {
		$getRecodByIDRequest = new HTTP_Request2($this->_cswAddress);
		$getRecodByIDRequest->setMethod(HTTP_Request2::METHOD_POST)
		->setHeader('Content-type: text/xml; charset=utf-8')
		->setBody("<?xml version='1.0'?>".
	                           "<csw:GetRecordById xmlns:csw='http://www.opengis.net/cat/csw/2.0.2' service='CSW' version='2.0.2' outputSchema='http://www.isotc211.org/2005/gmd' elementSetName='full'>".
	                           "<csw:Id>".$id."</csw:Id>".
	                           "</csw:GetRecordById>");
		//authentication if needed
		if (!$this->_authentication($getRecodByIDRequest)) throw new Exception($this->_response, "001");
		if ($this->_callHTTPCSW($getRecodByIDRequest)) {
			$getRecodByIDRequest=null;
			return $this->_response;
		}
		else {
			$getRecodByIDRequest=null;
			throw new Exception($this->_response, "002");
		}

	}
	
/**
	 * retrieve a specific metadata with UUID in GeoNetwork / Geosource
	 * @param String $id of the metadata
	 * @return XML content
	 */
	public function getXmlById($id) {
		
		$getRecodByIDRequest = new HTTP_Request2($this->_authentAddress.'/iso19139.xml?uuid='.$id);
		$getRecodByIDRequest->setMethod(HTTP_Request2::METHOD_GET)
		->setHeader('Content-type: text/xml; charset=utf-8');
		//authentication if needed
		if (!$this->_authentication($getRecodByIDRequest)) throw new Exception($this->_response, "001");
		if ($this->_callHTTPCSW($getRecodByIDRequest)) {
			$getRecodByIDRequest=null;
			return $this->_response;
		}
		else {
			$getRecodByIDRequest=null;
			throw new Exception($this->_response, "002");
		}

	}
	
	/**
	 *
	 * @return Number of metadata in the csw server
	 */
	public function getCountRecords() {
		$getCountRecordsRequest = new HTTP_Request2($this->_cswAddress);
		$getCountRecordsRequest->setMethod(HTTP_Request2::METHOD_POST)
		->setHeader('Content-type: text/xml; charset=utf-8')
		->setBody("<?xml version='1.0'?>".
	                                "<csw:GetRecords xmlns:csw='http://www.opengis.net/cat/csw/2.0.2' service='CSW' version='2.0.2' resultType='hits'>".
	                                "<csw:Query typeNames='csw:Record'>".
	                                "<csw:Constraint version='1.1.0'>".
	                                "    <Filter xmlns='http://www.opengis.net/ogc' xmlns:gml='http://www.opengis.net/gml'/>".
	                                "</csw:Constraint>".
	                                "</csw:Query>".
	                                "</csw:GetRecords>");
		//authentication if needed
		if (!$this->_authentication($getCountRecordsRequest)) throw new Exception($this->_response, "001");
		if ($this->_callHTTPCSW($getCountRecordsRequest)) {
			$docXml= new DOMDocument();
			if ($docXml->loadXML($this->_response)) {
				$xp = new DOMXPath($docXml);
				$xpathString="//@numberOfRecordsMatched";
				$nodes = $xp->query($xpathString);
				if ($nodes->length==1)
				return $nodes->item(0)->textContent;
				else
				return 0;
			}
			else {
				throw new Exception($this->_response, "004");
			}

		}
		else
		throw new Exception($this->_response, "003");

	}
	/**
	 * Insert a new metadata in the csw server
	 * @param DOMDocument $xmlISO19139 content to add
	 * @return number of insered metadata
	 */
	public function insertMetadata($xmlISO19139) {
	
		$insertMetadataRequest = new HTTP_Request2($this->_cswAddress);
		$insertMetadataRequest->setMethod(HTTP_Request2::METHOD_POST)
		->setHeader('Content-type: text/xml; charset=utf-8')
		->setBody($xmlISO19139);
		//authentication is needed !!
		if (!$this->_authentication($insertMetadataRequest)) throw new Exception("authentication mandatory", "001");
		if ($this->_callHTTPCSW($insertMetadataRequest)) {
			$docXml= new DOMDocument();
			if ($docXml->loadXML($this->_response)) {
				$xp = new DOMXPath($docXml);
				$xpathString="//csw:totalInserted";
				$nodes = $xp->query($xpathString);
				if ($nodes->length==1)
				return $nodes->item(0)->textContent;
				else
				return 0;
			}
			else {
				throw new Exception($this->_response, "004");
			}
		}
		else
		throw new Exception($this->_response, "002");


	}

/**
     * update a  metadata in the csw server
     * @param DOMDocument $xmlISO19139 content to add
     * @return number of updated metadata
     */
    public function updateMetadata($xmlISO19139) {

        //first, find the uuid of the metadata !

        $nFI=$xmlISO19139->getElementsByTagName('fileIdentifier');
        if ($nFI->length==1) {
            $uuid = $nFI->item(0)->childNodes->item(1)->nodeValue;
        }
        else
        throw new Exception("No fileIdentifier found","UM.001");

       $updateMetadataRequest = new HTTP_Request2($this->_cswAddress);
        $updateMetadataRequest->setMethod(HTTP_Request2::METHOD_POST)
        ->setHeader('Content-type: text/xml; charset=utf-8')
		->setBody("<?xml version='1.0'?>".
                               '<csw:Transaction xmlns:ogc="http://www.opengis.net/ogc" xmlns:csw="http://www.opengis.net/cat/csw/2.0.2" version="2.0.2" service="CSW">'.
                               '<csw:Update>'.str_replace('<?xml version="1.0"?>','',$xmlISO19139->saveXML()).
                               '<csw:Constraint version="1.1.0">'.
      							'<ogc:Filter xmlns:gml="http://www.opengis.net/gml">'.
        						'<ogc:PropertyIsEqualTo>'.
          						'<ogc:PropertyName>apiso:identifier</ogc:PropertyName>'.
          						'<ogc:Literal>'.$uuid.'</ogc:Literal>'.
        						'</ogc:PropertyIsEqualTo>'.
      							'</ogc:Filter>'.
    							'</csw:Constraint>'.
  								'</csw:Update>'.
								'</csw:Transaction>');

        //authentication is needed !!

        if (!$this->_authentication($updateMetadataRequest)) throw new Exception("authentication mandatory", "001");

        if ($this->_callHTTPCSW($updateMetadataRequest)) {
            $docXml= new DOMDocument();

            if ($docXml->loadXML($this->_response)) {

                $xp = new DOMXPath($docXml);
                $xpathString="//csw:totalUpdated";
                $nodes = $xp->query($xpathString);
                if ($nodes->length==1)
                return $nodes->item(0)->textContent;
                else
                return 0;
            }
            else {
                throw new Exception($this->_response, "004");
            }
        }
        else
        throw new Exception($this->_response, "002");


    }

	/**
	 * deleted a  metadata in the csw server
	 * @param DOMDocument $xmlISO19139 content to add
	 * @return number of deleted metadata
	 */
	public function deleteMetadata($xmlISO19139) {
		//first, find the uuid of the metadata !

		$nFI=$xmlISO19139->getElementsByTagName('fileIdentifier');
		if ($nFI->length==1) {
			$uuid = $nFI->item(0)->childNodes->item(1)->nodeValue;
			return $this->deleteMetadataFromUuid($uuid);
		}
		else
		throw new Exception("No fileIdentifier found","UM.001");

	}

	/**
	 * delete a  metadata in the csw server
	 * @param String $uuid id of the metadata
	 * @return number of deleted metadata
	 */
	public function deleteMetadataFromUuid($uuid) {


		$deleteMetadataRequest = new HTTP_Request2($this->_cswAddress);
		$deleteMetadataRequest->setMethod(HTTP_Request2::METHOD_POST)
		->setHeader('Content-type: text/xml; charset=utf-8')
		->setBody("<?xml version='1.0'?>".
	                           "<csw:Transaction service='CSW' version='2.0.2' xmlns:csw='http://www.opengis.net/cat/csw/2.0.2' xmlns:ogc='http://www.opengis.net/ogc' xmlns:apiso='http://www.opengis.net/cat/csw/apiso/1.0'>".
	                           "<csw:Delete>".
	                           "<csw:Constraint version='1.0.0'>".
	                           "<Filter xmlns='http://www.opengis.net/ogc' xmlns:gml='http://www.opengis.net/gml'>".
	                           "<PropertyIsLike wildCard='%' singleChar='_' escapeChar='\'>".
	                           "    <PropertyName>apiso:identifier</PropertyName>".
	                           "    <Literal>".$uuid."</Literal>".
	                           "</PropertyIsLike>".
	                           "</Filter>".
	                           "</csw:Constraint>".
	                           "</csw:Delete>".
	                           "</csw:Transaction>");
		//authentication is needed !!

		if (!$this->_authentication($deleteMetadataRequest)) throw new Exception("authentication mandatory", "001");

		if ($this->_callHTTPCSW($deleteMetadataRequest)) {
			$docXml= new DOMDocument();
			if ($docXml->loadXML($this->_response)) {
				$xp = new DOMXPath($docXml);
				$xpathString="//csw:totalDeleted";
				$nodes = $xp->query($xpathString);
				if ($nodes->length==1)
				return $nodes->item(0)->textContent;
				else
				return 0;
			}
			else {
				throw new Exception($this->_response, "004");
			}
		}
		else
		throw new Exception($this->_response, "002");
	}

	// ########################################################################################
	// ######## Funciones especificas para controlar la seguridad de acceso a los metadatos
	// ########################################################################################

	/**
	 * A partir del id grupo del metamodelo de geonodo, recupera el id grupo almacenado en geonetwork.
	 *
	 * @param unknown_type $groupId
	 */
	public function getIdGroup($groupId, $tag){

		// no es necesaria autenticacion
		// peticion get users
		$getGroupsRequest = new HTTP_Request2($this->_cswAddress.'/xml.group.list');
		$getGroupsRequest->setMethod(HTTP_Request2::METHOD_GET)
		->setHeader('Content-type: text/xml; charset=utf-8');

		// llamada y tratamiento
		if ($this->_callHTTPCSW($getGroupsRequest)) {
			$idGroup=-1;
			$docXml= new DOMDocument();
			
			if ($docXml->loadXML($this->_response)) {
				$xp = new DOMXPath($docXml);

				$xpathStringId="/response/record/id";
				$xpathStringName="/response/record/name";
				$nameNodes = $xp->query($xpathStringName);
				$idNodes = $xp->query($xpathStringId);
				
				if ($nameNodes->length>0){
					for($i=0; $i<$nameNodes->length; $i++){
						$groupName=$nameNodes->item($i)->textContent;
						if($groupName=='GROUP'.$tag.$groupId){
							$idGroup=$idNodes->item($i)->textContent;
							break;
						}
					}
				}

				return $idGroup;
			}
			else {
				throw new Exception($this->_response, "004");
			}

		}
		else{
			throw new Exception($this->_response, "002");
		}

	}

	/**
	 * Crea un grupo en geonetwork a partir del id grupo de geonodo. El nombre del grupo será GROUP + ID_GRUPO_GEONODO.
	 * Importante: El id del grupo en geonetwork no tiene por qué corresponder con el id del grupo en geonodo.
	 *
	 * @param unknown_type $groupId
	 * @throws Exception
	 */
	public function createGroup($groupId, $tag) {

		// peticion crear grupo
		$getGroupsRequest = new HTTP_Request2($this->_authentAddress.'/group.update');
		$getGroupsRequest->setMethod(HTTP_Request2::METHOD_POST)
		->setHeader('Content-type: text/xml; charset=utf-8')
		->setBody("<?xml version='1.0'?>".
			"<request>".
				"<name>GROUP".$tag.$groupId."</name>".
				"<description>GROUP".$tag.$groupId."</description>".
			"</request>");

		//authentication is needed !!
		if (!$this->_authenticationAdmin($getGroupsRequest)) throw new Exception("authentication mandatory", "001");

		// llamada y tratamiento
		if ($this->_callHTTPCSW($getGroupsRequest)) {
			return $this->_response;
		}else{
			throw new Exception($this->_response, "002");
		}

	}

	/**
	 * Elimina un grupo de geonetwork.
	 * El parametro idGroup se corresponde con el identificador del grupo en geonetwork, no en geonodo.
	 *
	 * @param unknown_type $idGroup
	 */
	public function deleteGroup($idGroup) {

		// peticion eliminar grupo
		$deleteRequest = new HTTP_Request2($this->_authentAddress.'/group.remove');
		$deleteRequest->setMethod(HTTP_Request2::METHOD_POST)
		->setHeader('Content-type: text/xml; charset=utf-8')
		->setBody("<?xml version='1.0'?>".
			"<request>".
				"<id>".$idGroup."</id>".
			"</request>");

		//authentication is needed !!
		if (!$this->_authenticationAdmin($deleteRequest)) throw new Exception("authentication mandatory", "001");

		// llamada y tratamiento
		if ($this->_callHTTPCSW($deleteRequest)) {
			return $this->_response;
		}else{
			throw new Exception($this->_response, "002");
		}

	}

	/**
	 * A partir del id grupo del metamodelo de geonodo recupera el id del usuario de geonodo.
	 * Un grupo en geonodo implica un usuario 'grupo' en geonetwork.
	 *
	 * @param unknown_type $userId
	 * @throws Exception
	 */
	public function getIdUser($groupId, $tag) {

		// peticion get users
		$getRequest = new HTTP_Request2($this->_cswAddress.'/xml.info?type=users');
		$getRequest->setMethod(HTTP_Request2::METHOD_GET)
		->setHeader('Content-type: text/xml; charset=utf-8');

		//authentication is needed !!
		if (!$this->_authenticationAdmin($getRequest)) throw new Exception("authentication mandatory", "001");

		// llamada y tratamiento
		if ($this->_callHTTPCSW($getRequest)) {
			$idUser=-1;
			$docXml= new DOMDocument();
			if ($docXml->loadXML($this->_response)) {
				$xp = new DOMXPath($docXml);

				$xpathStringId="/info/users/user/id";
				$xpathStringName="/info/users/user/name";
				$nameNodes = $xp->query($xpathStringName);
				$idNodes = $xp->query($xpathStringId);
				if ($nameNodes->length>0){
					for($i=0; $i<$nameNodes->length; $i++){
						$groupName=$nameNodes->item($i)->textContent;
						if($groupName=='USER_GROUP'.$tag.$groupId){
							$idUser=$idNodes->item($i)->textContent;
							break;
						}
					}
				}

				return $idUser;
			}
			else {
				throw new Exception($this->_response, "004");
			}

		}
		else{
			throw new Exception($this->_response, "002");
		}

	}

	/**
	 * Crea un usuario de grupo a partir del id grupo de geonodo, id grupo de geonetwork y contraseña.
	 *
	 * @param unknown_type $groupId
	 * @param unknown_type $groupIdGeonetwork
	 * @param unknown_type $password
	 * @throws Exception
	 */
	public function createUser($groupId, $groupIdGeonetwork, $password, $tag) {

		// peticion crear grupo
		$createUser = new HTTP_Request2($this->_authentAddress.'/user.update');
		$createUser->setMethod(HTTP_Request2::METHOD_POST)
		->setHeader('Content-type: text/xml; charset=utf-8')
		->setBody("<?xml version='1.0'?>".
			"<request>".
				"<operation>newuser</operation>".
				"<username>USER_GROUP".$tag.$groupId."</username>".
				"<password>".$password."</password>".
				"<profile>Editor</profile>".
				"<name>USER_GROUP".$tag.$groupId."</name>".
				"<groups>".$groupIdGeonetwork."</groups>".
			"</request>");

		//authentication is needed !!
		if (!$this->_authenticationAdmin($createUser)) throw new Exception("authentication mandatory", "001");

		// llamada y tratamiento
		if ($this->_callHTTPCSW($createUser)) {
			return $this->_response;
		}else{
			throw new Exception($this->_response, "002");
		}

	}

	/**
	 * Elimina un usuario de geonetwork.
	 * El paramatro idUser indica el identificador del usuario en geonetwork
	 *
	 * @param unknown_type $idUser
	 * @throws Exception
	 */
	public function deleteUser($idUser) {

		// peticion eliminar usuario
		$deleteRequest = new HTTP_Request2($this->_authentAddress.'/user.remove');
		$deleteRequest->setMethod(HTTP_Request2::METHOD_POST)
		->setHeader('Content-type: text/xml; charset=utf-8')
		->setBody("<?xml version='1.0'?>".
			"<request>".
				"<id>".$idUser."</id>".
			"</request>");

		//authentication is needed !!
		if (!$this->_authenticationAdmin($deleteRequest)) throw new Exception("authentication mandatory", "001");

		// llamada y tratamiento
		if ($this->_callHTTPCSW($deleteRequest)) {
			return $this->_response;
		}else{
			throw new Exception($this->_response, "002");
		}

	}

	/**
	 * Actualiza los privilegios del metadato, dando permisos de lectura a todo el mundo y para el grupo viewer.
	 * Cada asignación reemplaza la asignación anterior, por lo que es necesario aplicar todos los privilegios en cada transacción.
	 * La asignación de privilegios es _GRUPO_TIPOPRIVILEGIO (_G_O).
	 * El grupo 1 es "todos".
	 *
	 * @param unknown_type $idMetadata
	 * @param unknown_type $idGroup
	 */
	public function addViewPrivileges($idMetadata, $idGroup, $idGroupViewer)
	 {
		// peticion eliminar usuario
		$addPrivilegesRequest = new HTTP_Request2($this->_authentAddress.'/metadata.admin');
		$addPrivilegesRequest->setMethod(HTTP_Request2::METHOD_POST)
		->setHeader('Content-type: text/xml; charset=utf-8')
		->setBody("<?xml version='1.0'?>".
			"<request>".
				"<id>".$idMetadata."</id>".
				"<_1_0 />".
				"<_".$idGroupViewer."_0 />".
				"<_".$idGroup."_0 />".
				"<_".$idGroup."_1 />".
				"<_".$idGroup."_2 />".
			"</request>");
	
		//authentication is needed !!
		if (!$this->_authenticationAdmin($addPrivilegesRequest))
			 throw new Exception("authentication mandatory", "001");
			
		// llamada y tratamiento
		if ($this->_callHTTPCSW($addPrivilegesRequest)) {
			return $this->_response;
		}else{
			throw new Exception($this->_response, "002");
		}

	}

	/**
	 * Actualiza los privilegios del metadato, eliminando permisos de lectura a todo el mundo y para el grupo lector.
	 * Cada asignación reemplaza la asignación anterior, por lo que es necesario aplicar todos los privilegios en cada transacción.
	 * La asignación de privilegios es _GRUPO_TIPOPRIVILEGIO (_G_O).
	 * El grupo 1 es "todos".
	 *
	 * @param unknown_type $idMetadata
	 * @param unknown_type $idGroup
	 */
	public function removeViewPrivileges($idMetadata, $idGroup, $idGroupViewer) {

		// peticion eliminar usuario
		$removePrivilegesRequest = new HTTP_Request2($this->_authentAddress.'/metadata.admin');
		$removePrivilegesRequest->setMethod(HTTP_Request2::METHOD_POST)
		->setHeader('Content-type: text/xml; charset=utf-8')
		->setBody("<?xml version='1.0'?>".
			"<request>".
				"<id>".$idMetadata."</id>".
				"<_".$idGroup."_0 />".
				"<_".$idGroup."_1 />".
				"<_".$idGroup."_2 />".
			"</request>");
			fb::log("<?xml version='1.0'?>".
			"<request>".
				"<id>".$idMetadata."</i	d>".
				"<_".$idGroup."_0 />".
				"<_".$idGroup."_1 />".
				"<_".$idGroup."_2 />".
			"</request>");
		//authentication is needed !!
		if (!$this->_authenticationAdmin($removePrivilegesRequest)) throw new Exception("authentication mandatory", "001");

		// llamada y tratamiento
		if ($this->_callHTTPCSW($removePrivilegesRequest)) {
			return $this->_response;
		}else{
			throw new Exception($this->_response, "002");
		}

	}

	function getJSessionId(){
		return $this->_sessionID;
	}
	
	/**
	 * retrieve a specific metadata with UUID in GeoNetwork / Geosource
	 * @param String $id of the metadata
	 * @return XML content
	 */
	public function xmlSearchById($uuid) {
		
		$getRecodByIDRequest = new HTTP_Request2($this->_authentAddress.'/xml.search?uuid='.$uuid);
		$getRecodByIDRequest->setMethod(HTTP_Request2::METHOD_GET)
		->setHeader('Content-type: text/xml; charset=utf-8');
		//authentication if needed
		if (!$this->_authentication($getRecodByIDRequest)) throw new Exception($this->_response, "001");
		if ($this->_callHTTPCSW($getRecodByIDRequest)) {
			$getRecodByIDRequest=null;
			return $this->_response;
		}
		else {
			$getRecodByIDRequest=null;
			throw new Exception($this->_response, "002");
		}

	}
}


?>