<?php
// This is a Web application configuration file. Any enviroment
// variable must be declared here.
return array(

	'version_number'=>'4.0.0.1',
	'version_date'=>'26/06/2014',
    'loginUserAdmin'=>array(
        'admin'
    ),
	

    'urlViewer'=>'http://localhost/geonodo30',
    'urlGeonodoadm'=>'http://localhost/geonodoadm30',
    'urlGeoserver' => 'http://localhost/geoserverGeonodo',
    'geoserverPath' => '/usr/local/apache-tomcat-7.0.47/webapps/geoserverGeonodo/data/styles',
    'geoserverHomeDataPath' => '/usr/local/apache-tomcat-7.0.47/webapps/geoserverGeonodo/data',
    'userGeoserver' => 'admin',
    'pwGeoserver' => 'geoserver',
    'apacheHome' => '/var/www/html',
    'deleteErrorMessage' => 'No es posible eliminar este elemento. Elimine previamente los elementos que dependen de él.',
    'deleteErrorMessageUser' => 'No es posible eliminar un usuario que perteneza a un grupo.',
  	'deleteErrorMessageAdm' => 'No es posible eliminar al Administrador de grupo',
    'allowedSRS'=>'900913,4326,3857', // No se está usando
    'urlGeonetwork' => 'http://localhost/geonetworkGeonodo/srv/eng',
    'urlCsw' => 'http://localhost/geonetworkGeonodo/srv/eng/csw',
    'userGeonetwork' => 'admin',
    'pwGeonetwork' => 'admin',
     //Alias config
    'aliasUrl' => 'http://localhost/geonodoadm30/external/alias/',
    'aliasFolder' => '/var/www/html/geonodoadm30/external/alias/',
    'proxyUrl' => 'http://localhost/geonodoadm30/assets/proxy/proxy.php?url=',
	
    //Menu config
    'menuGestionFuentesDatos'=>array(3),
    'menuGestionServiciosPropios'=>array(3),
    'menuGestionServiciosWms'=>array(3),
    'menuGestionCapas'=>array(3),
    'menuGestionCategorias'=>array(3),
    'menuGestionMapas'=>array(3,5),
    'menuGestionVisores'=>array(3),
    'menuGestionUsuariosGrupos'=>array(2),
    'menuGestionUsuarios'=>array(0),
    'menuGestionGrupos'=>array(0),
   	'menuGestionUsuariosInvitadosGrupos'=>array(0),
    'menuGestionBanners'=>array(3),
    'menuEditInfoContacto'=>array(2),
    'menuGestionPrivilegios'=>array(3),
    'menuCatalogoMetadatos'=>array(4,3),
    'menuGestionUsuariosPorGrupo'=>array(2),
    
	// default contact info
	't_name' => 'snit',
    't_contact' => 'Secretario Ejecutivo SNIT',
    't_mail' => 'snit@mbienes.cl',
    't_phone' => '(2) 937 5800 ',
    't_postalcode' => '8330132',
    't_region' => 'Santiago',
    't_country' => 'Chile',
    't_fax' => '9375804',
    't_web' => 'http://www.snit.cl',
    't_address' => 'serrano 62 departamento 511',
    't_city' => 'Santiago',
    
    //messages
    'fuentes' => 'Fuentes de datos',
    'servicios' => 'Servicios propios',
    'serviciosWMS' => 'Servicios WMS',
    'mapas' => 'Mapas',
    'visores' => 'Visores',
    'banners' => 'Banners',
   
    'timeZone' => 'Europe/Berlin',
    'idProfileAdminGroup' => '2'
    
    
    
);
