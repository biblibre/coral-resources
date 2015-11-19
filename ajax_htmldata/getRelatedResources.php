<?php
//session_start();
//include_once '../directory.php';

			$resourceID = $_GET['resourceID'];
			$resource = new Resource(new NamedArguments(array('primaryKey' => $resourceID)));
            $offset = $_GET['offset'] ? $_GET['offset'] : 0;
            $limit = $_GET['limit'] ? $_GET['limit'] : 100;
            $type = ($_GET['type'] == 'parents') ? 'resourceID' : 'relatedResourceID';
            $rtype = ($_GET['type'] == 'parents') ? 'relatedResourceID' : 'resourceID';

			//get related resources
			$relatedResourceArray = array();
			foreach ($resource->getRelatedResources($type, $offset, $limit) as $instance) {
                if ($instance->resourceID) {

				foreach (array_keys($instance->attributeNames) as $attributeName) {
					$sanitizedInstance[$attributeName] = $instance->$attributeName;
				}

				$sanitizedInstance[$instance->primaryKeyName] = $instance->primaryKey;

				array_push($relatedResourceArray, $sanitizedInstance);
                }
			}

				if (count($relatedResourceArray) > 0) { 
					foreach ($relatedResourceArray as $relatedResource){
						$relatedResourceObj = new Resource(new NamedArguments(array('primaryKey' => $relatedResource[$rtype])));
            echo $relatedResourceObj->titleText . "<a href='resource.php?resourceID=" . $relatedResourceObj->resourceID . "' target='_BLANK'><img src='images/arrow-up-right.gif' alt='view resource' title='View " . $relatedResourceObj->titleText . "' style='vertical-align:top;'></a><br />";
					}
                }
?>
