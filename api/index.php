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
include_once '../admin/classes/domain/NoteType.php';
include_once '../admin/classes/domain/ResourceNote.php';
include_once '../admin/classes/domain/ResourcePayment.php';
include_once '../admin/classes/domain/AdministeringSite.php';
include_once '../admin/classes/domain/ResourceAdministeringSiteLink.php';

if (!isAllowed()) {
    header('HTTP/1.0 403 Forbidden');
    echo "Unauthorized IP: " . $_SERVER['REMOTE_ADDR'];
    die();
}

Flight::route('/proposeResource/', function(){

    $resource = new Resource();
    $resource->createDate = date( 'Y-m-d' );
    $resource->createLoginID = 'coral';
    $resource->statusID = 1;
    $resource->updateDate                   = '';
    $resource->updateLoginID                = '';
    $resource->orderNumber                  = '';
    $resource->systemNumber                 = '';
    $resource->userLimitID                  = '';
    $resource->authenticationUserName       = '';
    $resource->authenticationPassword       = '';
    $resource->storageLocationID            = '';
    $resource->registeredIPAddresses        = '';
    $resource->coverageText                 = '';
    $resource->archiveDate                  = '';
    $resource->archiveLoginID               = '';
    $resource->workflowRestartDate          = '';
    $resource->workflowRestartLoginID       = '';
    $resource->currentStartDate             = '';
    $resource->currentEndDate               = '';
    $resource->subscriptionAlertEnabledInd  = '';
    $resource->authenticationTypeID         = '';
    $resource->accessMethodID               = '';
    $resource->recordSetIdentifier          = '';
    $resource->hasOclcHoldings              = '';
    $resource->numberRecordsAvailable       = '';
    $resource->numberRecordsLoaded          = '';
    $resource->bibSourceURL                 = '';
    $resource->catalogingTypeID             = '';
    $resource->catalogingStatusID           = '';
    $resource->mandatoryResource            = '';
    $resource->resourceID                   = null;

    $fieldNames = array("titleText", "descriptionText", "providerText", "resourceURL", "resourceAltURL", "noteText", "resourceTypeID", "resourceFormatID", "acquisitionTypeID");
    foreach ($fieldNames as $fieldName) {
        $resource->$fieldName = Flight::request()->data->$fieldName;
    }
    try {
        $resource->save();
        $resourceID = $resource->primaryKey;
        //add note
        if ((Flight::request()->data['noteText']) || ((Flight::request()->data['providerText']) && (!Flight::request()->data['organizationID']))){
            //first, remove existing notes in case this was saved before
            $resource->removeResourceNotes();

            //this is just to figure out what the creator entered note type ID is
            $noteType = new NoteType();

            $resourceNote = new ResourceNote();
            $resourceNote->resourceNoteID   = '';
            $resourceNote->updateLoginID    = 'coral';
            $resourceNote->updateDate       = date( 'Y-m-d' );
            $resourceNote->noteTypeID       = $noteType->getInitialNoteTypeID();
            $resourceNote->tabName          = 'Product';
            $resourceNote->resourceID       = $resourceID;

            //only insert provider as note if it's been submitted
            if ((Flight::request()->data['providerText']) && (!Flight::request()->data['organizationID'])){
                $resourceNote->noteText     = "Provider:  " . Flight::request()->data['providerText'] . "\n\n" . Flight::request()->data['noteText'];
            }else{
                $resourceNote->noteText     = Flight::request()->data['noteText'];
            }

            $resourceNote->save();
        }

        //add administering site
        if (Flight::request()->data['administeringSiteID']) {
            foreach (Flight::request()->data['administeringSiteID'] as $administeringSiteID) {
                $resourceAdministeringSiteLink = new ResourceAdministeringSiteLink();
                $resourceAdministeringSiteLink->resourceAdministeringSiteLinkID = '';
                $resourceAdministeringSiteLink->resourceID = $resourceID;
                $resourceAdministeringSiteLink->administeringSiteID = $administeringSiteID;
                try {
                    $resourceAdministeringSiteLink->save();
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
        }

        // add home location 
        foreach (array("homeLocationNote" => "Home Location") as $key => $value) {
            if (Flight::request()->data[$key]) {
                $noteType = new NoteType();
                $noteTypeID = $value ? createNoteType($value) : $noteType->getInitialNoteTypeID();
                $resourceNote = new ResourceNote();
                $resourceNote->resourceNoteID   = '';
                $resourceNote->updateLoginID    = 'coral';
                $resourceNote->updateDate       = date( 'Y-m-d' );
                $resourceNote->noteTypeID       = $noteTypeID;
                $resourceNote->tabName          = 'Product';
                $resourceNote->resourceID       = $resourceID;
                $resourceNote->noteText         = Flight::request()->data[$key];
                $resourceNote->save();
            }
        }

        // add publication year and/or edition
        foreach (array("publicationYear" => "Publication Year or order start date", "edition" => "Edition", "holdLocation" => "Hold location", "patronHold" => "Patron hold") as $key => $value) {
            if (Flight::request()->data[$key]) {
                $noteType = new NoteType();
                $noteTypeID = $noteType->getInitialNoteTypeID();
                $resourceNote = new ResourceNote();
                $resourceNote->resourceNoteID   = '';
                $resourceNote->updateLoginID    = 'coral';
                $resourceNote->updateDate       = date( 'Y-m-d' );
                $resourceNote->noteTypeID       = $noteTypeID;
                $resourceNote->tabName          = 'Product';
                $resourceNote->resourceID       = $resourceID;
                $resourceNote->noteText         = $value . ": " . Flight::request()->data[$key];
                $resourceNote->save();
            }
        }

        // add existing license and/or license required
        foreach (array("licenseRequired" => "License required?", "existingLicense" => "Existing License?") as $key => $value) {
            $noteTypeID = createNoteType("License Type");
            $resourceNote = new ResourceNote();
            $resourceNote->resourceNoteID   = '';
            $resourceNote->updateLoginID    = 'coral';
            $resourceNote->updateDate       = date( 'Y-m-d' );
            $resourceNote->noteTypeID       = $noteTypeID;
            $resourceNote->tabName          = 'Access';
            $resourceNote->resourceID       = $resourceID;
            $resourceNote->noteText         = $value . " " . Flight::request()->data[$key];
            $resourceNote->save();
        }

        // add CM
        foreach (array("CMRanking" => "CM Ranking", "subjectCoverage" => "Subject coverage", "audience" => "Audience", "frequency" => "Frequency and language", "access" => "Access via indexes", "contributingFactors" => "Contributing factors", "ripCode" => "RIP code") as $key => $value) {
            if (Flight::request()->data[$key]) {
                $noteTypeID = createNoteType("CM Ranking");
                $resourceNote = new ResourceNote();
                $resourceNote->resourceNoteID   = '';
                $resourceNote->updateLoginID    = 'coral';
                $resourceNote->updateDate       = date( 'Y-m-d' );
                $resourceNote->noteTypeID       = $noteTypeID;
                $resourceNote->tabName          = 'Product';
                $resourceNote->resourceID       = $resourceID;
                $resourceNote->noteText         = $value . ": " . Flight::request()->data[$key];
                $resourceNote->save();
            }
        }

        // add fund and cost
        if (Flight::request()->data['cost'] && Flight::request()->data['fund']) {
            $rp = new ResourcePayment();
            $rp->resourcePaymentID = ''; 
            $rp->selectorLoginID = 'coral'; 
            $rp->year = ''; 
            $rp->subscriptionStartDate = ''; 
            $rp->subscriptionEndDate = ''; 
            $rp->costDetailsID = ''; 
            $rp->costNote = ''; 
            $rp->invoiceNum = ''; 
            $rp->resourceID = $resourceID;
            $rp->fundName = Flight::request()->data['fund'];
            $rp->paymentAmount = cost_to_integer(Flight::request()->data['cost']);
            $rp->currencyCode = 'USD';
            $rp->orderTypeID = 2;
            $rp->save();
        }


    } catch (Exception $e) {
        Flight::json(array('error' => $e->getMessage()));
    }
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

Flight::route('/getAdministeringSites/', function() {
   $as = new AdministeringSite();
   $asArray = $as->allAsArray();
    Flight::json($asArray);
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

// Create a note type if it doesn't exist
// Return noteTypeID
function createNoteType($name) {
    $noteType = new NoteType();
    $noteTypeID = $noteType->getNoteTypeIDByName($name);
    if ($noteTypeID) return $noteTypeID;

    $noteType->shortName = $name;
    $noteType->noteTypeID = '';
    $noteType->save();
    return $noteType->noteTypeID;
}
?>
