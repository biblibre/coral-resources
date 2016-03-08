<?php
require 'Flight/flight/Flight.php';

include_once '../directory.php';
include_once '../admin/classes/common/NamedArguments.php';
include_once '../admin/classes/common/Object.php';
include_once '../admin/classes/common/DynamicObject.php';
include_once '../admin/classes/common/Utility.php';
include_once '../admin/classes/common/Configuration.php';
include_once '../admin/classes/common/DBService.php';
include_once '../admin/classes/common/DatabaseObject.php';
include_once '../admin/classes/domain/Resource.php';
include_once '../admin/classes/domain/ResourceType.php';
include_once '../admin/classes/domain/AcquisitionType.php';
include_once '../admin/classes/domain/ResourceFormat.php';

if (!isAllowed()) {
    header('HTTP/1.0 403 Forbidden');
    echo "Unauthorized IP: " . $_SERVER['REMOTE_ADDR'];
    die();
}

Flight::route('/proposeResource/', function(){
    $resource = new Resource();
    $resource->createDate = date( 'Y-m-d' );
    $fieldNames = array("titleText", "descriptionText", "providerText", "resourceURL", "resourceAltURL", "noteText", "resourceTypeID", "resourceFormatID", "acquisitionTypeID");
    foreach ($fieldNames as $fieldName) {
        $resource->$fieldName = Flight::request()->data->$fieldName;
    }
    try {
        $resource->save();
    } catch (Exception $e) {
        Flight::json(array('error' => $e->getMessage()));
    }
    $resourceID = $resource->primaryKey;
    Flight::json(array('resourceID' => $resourceID));

});

Flight::route('/version/', function() {
    Flight::json(array('API' => 'v1'));
});

Flight::route('/getResourceTypes/', function() {
    $rt = new ResourceType();
    $resourceTypeArray = $rt->allAsArray();
    Flight::json($resourceTypeArray);
});

Flight::route('/getAcquisitionTypes/', function() {
    $acquisitionTypeObj = new AcquisitionType();
    $acquisitionTypeArray = $acquisitionTypeObj->sortedArray();
    Flight::json($acquisitionTypeArray);
});

Flight::route('/getResourceFormats/', function() {
   $resourceFormatObj = new ResourceFormat();
   $resourceFormatArray = $resourceFormatObj->sortedArray();
    Flight::json($resourceFormatArray);
});

Flight::start();

function isAllowed() {
    $config = new Configuration();

    // If apiAuthorizedIP is not set, don't allow
    if (!$config->settings->apiAuthorizedIP) { return 0; }

    // If apiAuthorizedIP could not be parsed, don't allow
    $authorizedIP = explode(',', $config->settings->apiAuthorizedIP);
    if (!$authorizedIP) { return 0; }

    // If a matching IP has been found, allow
    if (array_filter($authorizedIP, "IpFilter")) { return 1; } 

    return 0;
}

// A matching IP is either a complete IP or the start of one (allowing IP range)
function IpFilter($var) {
    $pos = strpos($_SERVER['REMOTE_ADDR'], $var);
    return $pos === false ? false : true;
}
?>
