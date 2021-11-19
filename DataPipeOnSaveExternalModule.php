<?php

namespace Vanderbilt\DataPipeOnSaveExternalModule;

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;
use mysql_xdevapi\Exception;
use REDCap;

class DataPipeOnSaveExternalModule extends AbstractExternalModule
{
    function redcap_data_entry_form($project_id, $record, $instrument, $event_id, $group_id = NULL, $repeat_instance = 1) {
        //The below code was necessary for resetting module settings for a project so it could be reset with all new records.

        /*if (in_array($project_id,array(103538,102495,106458,102710,111557,111562,116774,116805,116831))) {
            $remove = $this->removeLogs("DELETE WHERE message LIKE 'Auto record for%'");
            echo "<pre>";
            print_r($remove);
            echo "</pre>";
        }*/

        //This code is for switching up where a record goes to fix old, invalid log mapping
        /*$recordChange = array(
            185=>1058,366=>1149,5=>"Laycox, Gloria June",312=>1119,493=>1203,358=>1145,549=>1228,43=>"Wade, Chester A",413=>1172,69=>1002,387=>1163,276=>1100,294=>1109,226=>1072,434=>1179,383=>1161,372=>1152,228=>1074,327=>1133,6=>"Alexander, Ronetta Renee",418=>1174,253=>1088,155=>1050,227=>1073,159=>1051,78=>1020,362=>1153,310=>1117,281=>1102,238=>1081,129=>1036,546=>1222,367=>1150,231=>1079,247=>1085,79=>1007,519=>1216,483=>1199,306=>1115,318=>1130,71=>1004,282=>1108,430=>1180,136=>1040,289=>1105,365=>1148,474=>1202,244=>1083,102=>1026,340=>1140,85=>1011,112=>1027,348=>1142,391=>1165,7=>"Cook, Allen R",233=>1077,328=>1134,73=>1005,167=>1054,475=>1195,339=>1138,432=>1182,191=>1061,293=>1107,117=>1031,41=>"Ruiz, Susie",162=>1052,239=>1082,131=>1037,33=>"Engidaye, Worknesh G",303=>1113,308=>1132,304=>1114,438=>1184,292=>1124,127=>1034,52=>1000,209=>1067,272=>1098,9=>"Eastridge, Joanne T",466=>1193,385=>1162,251=>1087,184=>1057,507=>1207,230=>1075,186=>1059,378=>1159,471=>1201,350=>1158,60=>"Devault, Michael J",63=>"Stephens, Susan",165=>1053,314=>1120,360=>1146,207=>1070,437=>1183,89=>1015,133=>1038,174=>1055,555=>1227,334=>1136,201=>1069,421=>1187,12=>"Tuan, Alh",515=>1213,116=>1030,405=>1171,255=>1090,125=>1033,321=>1127,221=>1086,151=>1049,347=>1141,422=>1175,375=>1156,264=>1096,34=>"Ruiz, Emeterio Medina",389=>1164,263=>1094,178=>1056,417=>1173,331=>1135,218=>1071,14=>"Goodrum, Stephen Carline",70=>1003,527=>1219,363=>1154,149=>1047,351=>1143,486=>1200,92=>1018,371=>1151,467=>1190,35=>"Mina, Mina L",511=>1211,83=>1010,476=>1196,97=>1023,88=>1014,37=>"Younan, Nagwa",119=>1032,113=>1028,145=>1044,220=>1078,192=>1062,462=>1189,544=>1221,287=>1104,86=>1012,439=>1185,379=>1168,16=>"Abdelmalak, Maria",260=>1093,433=>1178,397=>1169,521=>1212,498=>1204,455=>1188,104=>1022,138=>1041,478=>1197,533=>1215,232=>1076,529=>1214,94=>1019,99=>1024,423=>1176,275=>1122,380=>1160,503=>1224,442=>1186,68=>1001,541=>1220,77=>1008,543=>1223,31=>"Granstaff, Mary Eleanor",505=>1206,19=>"Cathcart, Cyril L",325=>1131,20=>"Nelson, John",285=>1106,273=>1099,38=>"Hernandez Perez=> Bertha",74=>1006,27=>"Cook, Jack H",237=>1080,54=>"Akin, James Leon",256=>1091,512=>1209,22=>"Garcia, Eba",29=>"Lian, Do Khan",30=>"Alvarado Cadena, Alfredo",28=>"Bonilla, Manuel De Jesus",75=>1009,39=>"Haji, Bardo",48=>"Franklin, Donnie",59=>"Vargas, Ricardo",134=>1039,90=>1017,87=>1013,91=>1016,114=>1029,100=>1021,106=>1025,111=>1035,206=>1065,141=>1042,190=>1060,144=>1043,146=>1045,197=>1064,200=>1066,150=>1048,210=>1068,195=>1063,148=>1046,254=>1089,357=>1147,317=>1123,284=>1103,297=>1112,280=>1101,245=>1084,311=>1118,309=>1116,266=>1095,271=>1097,295=>1110,299=>1125,257=>1092,404=>1170,377=>1157,343=>1139,355=>1144,424=>1177,315=>1121,320=>1126,374=>1155,396=>1167,322=>1128,316=>1129,338=>1137,392=>1166,469=>1191,479=>1198,470=>1192,444=>1194,563=>1231,427=>1181,499=>1205,508=>1208,560=>1230,520=>1218,552=>1226,536=>1217,514=>1210,556=>1229,548=>1225,
        );
        if (in_array($record,array_keys($recordChange)) && $project_id == "110730") {
            $this->removeLogs("DELETE WHERE message = 'Auto record for $record' AND project_id=$project_id");
            $logID = $this->log("Auto record for " . $record, ["destination_record_id" => $recordChange[$record]]);
        }*/
    }
    
    function redcap_save_record($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance = "") {
        //echo "Started: ".time()."<br/>";
        $this->pipeDataToDestinationProjects($record, $event_id, $instrument, $repeat_instance);
        //echo "Ended: ".time()."<br/>";
        //$this->exitAfterHook();
    }

    function pipeDataToDestinationProjects($record, $event_id, $instrument, $repeat_instance="") {
        $debug = $this->getProjectSetting("enable_debug_logging");
        $emailErrors = $this->getProjectSetting("error_email");

        $destinationProjectIDs = $this->getProjectSetting("destination_project");
        $triggerFields = $this->getProjectSetting("field_flag");
        $triggerValues = $this->getProjectSetting("value_flag");
        $recordNames = $this->getProjectSetting("new_record");
        $overwrites = $this->getProjectSetting("overwrite-record");
        $sourceFields = $this->getProjectSetting("source-field");
        $destinationFields = $this->getProjectSetting("destination-field");

        $project_id = $this->getProjectId();
        $currentProject = new \Project($project_id);
        $fieldsOnForm = array_keys($currentProject->forms[$instrument]['fields']);
        
        $eventName = $currentProject->uniqueEventNames[$event_id];

        $currentData = REDCap::getData($project_id, 'array', $record, array());

        foreach ($destinationProjectIDs as $topIndex => $destinationProjectID) {
            $triggerField = $triggerFields[$topIndex];
            $triggerValue = $triggerValues[$topIndex];
            $recordName = $recordNames[$topIndex];
            $overwrite = $overwrites[$topIndex];

            $destinationProject = new \Project($destinationProjectID);
            $currentSourceFields = ($sourceFields[$topIndex][0] != "" ? $sourceFields[$topIndex] : $fieldsOnForm);
            $currentDestinationFields = ($destinationFields[$topIndex][0] != "" ? $destinationFields[$topIndex] : $fieldsOnForm);

            if (!in_array($triggerField,$fieldsOnForm) && $triggerField != "") continue;
            $results = json_decode(REDCap::getData($project_id, 'json', $record, $triggerField, $event_id),true);
            $triggerFieldValue = "";
            foreach ($results as $indexData) {
                if ((!isset($indexData['redcap_event_name']) || $indexData['redcap_event_name'] == $eventName) && $indexData[$triggerField] != "") {
                    $triggerFieldValue = $indexData[$triggerField];
                }
            }

            $triggerFieldSet = false;
            if ($triggerFieldValue == $triggerValue || ($triggerValue == "" && $triggerFieldValue != "") || $triggerField == "") {
                $triggerFieldSet = true;
            }

            if ($triggerFieldSet && $recordName != "") {
                $newRecordName = $this->getNewRecordName($project_id,$record,$currentData,$recordName,$instrument,$event_id,$repeat_instance);
                //echo "New record: $newRecordName<br/>";
                if ($newRecordName != "") {
                    $destRecordExists = false;
                    $targetRecordSql = "SELECT record FROM redcap_data WHERE project_id='$destinationProjectID' && record='$newRecordName' LIMIT 1";
                    $result = db_query($targetRecordSql);

                    while ($row = db_fetch_assoc($result)) {
                        if ($row['record'] == $newRecordName) {
                            $destRecordExists = true;
                        }
                    }
                    
                    if ($debug == "1") {
                        $this->log("Checking values for pid $destinationProjectID", [
                            '$targetProjectID' => $destinationProjectID,
                            '$destinationRecordID' => $newRecordName,
                            '$destRecordExists' => $destRecordExists
                        ]);
                    }

                    if (($destRecordExists && $overwrite == "overwrite") || !$destRecordExists) {
                        //echo "Before transfer: ".time()."<br/>";
                        $saveData = $this->transferRecordData($currentData,$currentProject,$destinationProject,$currentSourceFields,$currentDestinationFields,$newRecordName,$event_id,$repeat_instance);
                        /*echo "<pre>";
                        print_r($saveData);
                        echo "</pre>";*/
                        //echo "After transfer: ".time()."<br/>";
                        $results = $this->saveDestinationData($destinationProject->project_id,$saveData);
                        $errors = $results['errors'];
                        /*echo "Result:<br/>";
                        echo "<pre>";
                        print_r($results);
                        echo "</pre>";*/
                        if(!empty($errors)){
                            $errorString = stripslashes(json_encode($errors, JSON_PRETTY_PRINT));
                            $errorString = str_replace('""', '"', $errorString);

                            $message = "The " . $this->getModuleName() . " module could not copy values for record " . $newRecordName . " from project $project_id to project ".$destinationProjectID." because of the following error(s):\n\n$errorString";
                            error_log($message);

                            $errorEmail = $this->getProjectSetting('error_email');
                            //if ($errorEmail == "") $errorEmail = "james.r.moore@vumc.org";
                            if(!empty($errorEmail)){
                                ## Add check for universal from email address
                                global $from_email;
                                if($from_email != '') {
                                    $headers = "From: ".$from_email."\r\n";
                                }
                                else {
                                    $headers = null;
                                }
                                mail($errorEmail, $this->getModuleName() . " Module Error", $message, $headers);
                            }
                        }
                        /*echo "<pre>";
                        print_r($results);
                        echo "</pre>";*/
                    }
                }
            }
        }
    }

    private function getFieldType($fieldName) {
        if(empty($fieldName)){
            return null;
        }

        $fieldName = db_real_escape_string($fieldName);
        $result = $this->query("select element_type from redcap_metadata where project_id = " . $this->getProjectId() . " and field_name = '$fieldName'");
        $row = $result->fetch_assoc();

        return $row['element_type'];
    }

    function getNewRecordName($project_id, $record, $recordData,$recordSetting,$instrument,$event_id,$repeat_instance = "") {
        $newRecordID = "";

        if ($recordSetting != "") {
            //$newRecordID = $this->parseRecordSetting($project,$instrument,$event_id,$recordSetting,$recordData,$repeat_instance);
            $newRecordID = \Piping::replaceVariablesInLabel($recordSetting,$record,$event_id,$repeat_instance,$recordData,true,$project_id,false);
            $newRecordID = $this->parseSpecialTags($project_id,$newRecordID);
        }

        return $newRecordID;
    }

    function parseRecordSetting(\Project $currentProject,$repeat_instrument,$event_id,$recordsetting,$recordData,$repeat_instance = "") {
        $returnString = $recordsetting;

        $events = $currentProject->getUniqueEventNames();
        $eventNameToId = array_flip($events);

        $parser = new \LogicParser();

        $formatCalc = \Calculate::formatCalcToPHP($recordsetting,$currentProject);
        //echo "First calc: $formatCalc<br/>";
        if ($currentProject->longitudinal) {
            $formatCalc = \LogicTester::logicPrependEventName($formatCalc, $currentProject->getUniqueEventNames($event_id), $currentProject);
        }
        //echo "Format calc: $formatCalc<br/>";
        list ($funcName,$argMap) = $parser->parse($formatCalc,$eventNameToId,true,true,false,false,true);
        $logicFuncToArgs[$funcName] = $argMap;
        $thisInstanceArgMap = $logicFuncToArgs[$funcName];

        if ($repeat_instance != "") {
            foreach ($thisInstanceArgMap as &$theseArgs) {
                // If there is no instance number for this arm map field, then proceed
                if ($theseArgs[3] == "") {
                    $thisInstanceArgEventId = ($theseArgs[0] == "") ? $event_id : $theseArgs[0];
                    $thisInstanceArgEventId = is_numeric($thisInstanceArgEventId) ? $thisInstanceArgEventId : $currentProject->getEventIdUsingUniqueEventName($thisInstanceArgEventId);
                    $thisInstanceArgField = $theseArgs[1];
                    $thisInstanceArgFieldForm = $currentProject->metadata[$thisInstanceArgField]['form_name'];

                    // If this event or form/event is repeating event/instrument, the add the current instance number to arg map
                    if ( // Is a valid repeating instrument?
                        ($repeat_instrument != '' && $currentProject->isRepeatingForm($thisInstanceArgEventId, $thisInstanceArgFieldForm))
                        // Is a valid repeating event?
                        || ($repeat_instrument == '' && $currentProject->isRepeatingEvent($thisInstanceArgEventId)))
                        // NOTE: The commented line below was causing calcs not to be calculated if referencing a field on a repeating event whose form was not designated for the event
                        // || ($repeat_instrument == '' && $currentProject->isRepeatingEvent($thisInstanceArgEventId) && in_array($thisInstanceArgFieldForm, $currentProject->eventsForms[$thisInstanceArgEventId])))
                    {
                        $theseArgs[3] = $repeat_instance;
                    }
                }
            }
            unset($theseArgs);
        }

        foreach ($recordData as $record => $thisRecordData) {
            $returnString = \LogicTester::evaluateCondition(null,$thisRecordData,$funcName,$thisInstanceArgMap,$currentProject);
        }
        /*preg_match_all("/\[(.*?)\]/",$recordsetting,$matchRegEx);
        $stringsToReplace = $matchRegEx[0];
        $fieldNamesReplace = $matchRegEx[1];
        foreach ($fieldNamesReplace as $index => $fieldName) {
            $returnString = db_real_escape_string(str_replace($stringsToReplace[$index],$recorddata[$fieldName],$returnString));
        }*/
        return $returnString;
    }

    function transferRecordData($sourceData, \Project $sourceProject, \Project $destProject, $fieldsToUse, $destinationFields, $recordToUse, $eventToUse = "", $instanceToUse = "") {
        $eventMapping = array();
        $sourceEvents = $sourceProject->eventInfo;
        $destEvents = $destProject->eventInfo;
        $destEventIDLeft = $destEvents;
        $eventOffset = 0;
        $sourceMeta = $sourceProject->metadata;
        $destMeta = $destProject->metadata;
        $destRecordField = $destProject->table_pk;

        $destData = array();

        foreach ($sourceEvents as $eventID => $eventInfo) {
            if (count($destEvents) > 1) {
                foreach ($destEvents as $destID => $destEventInfo) {
                    if ($eventInfo['name'] == $destEventInfo['name']) {
                        $eventMapping[$eventID] =  $destID;
                        unset($destEventIDLeft[$destID]);
                    }
                }
            }
            elseif (($eventToUse != "" && $eventID == $eventToUse) || $eventToUse == "") {
                $destEventID = array_keys($destEvents)[0];

                if ($destEventID != "") {
                    $eventMapping[$eventID] = $destEventID;
                    unset($destEventIDLeft[$destEventID]);
                    break;
                }
            }
            $eventOffset++;
        }

        $eventOffset = 0;
        foreach ($sourceEvents as $eventID => $eventInfo) {
            if (!isset($eventMapping[$eventID]) && count($destEventIDLeft) > 0) {
                $eventMapping[$eventID] = array_keys(array_slice($destEventIDLeft,$eventOffset,1,true))[0];
                $eventOffset++;
            }
        }
        //echo "Before data looping: ".time()."<br/>";
        if (!empty($sourceData)) {
            foreach ($sourceData as $recordID => $recordData) {
                foreach ($recordData as $eventID => $eventData) {
                    if ($eventID == "repeat_instances") {
                        foreach ($eventData as $subEventID => $subEventData) {
                            if ($eventToUse != "" && $subEventID != $eventToUse) continue;
                            if (isset($eventMapping[$subEventID])) {
                                $destEventID = $eventMapping[$subEventID];
                                foreach ($subEventData as $instrument => $instrumentData) {
                                    foreach ($instrumentData as $instance => $instanceData) {
                                        if (($instanceToUse != "" && $instance == $instanceToUse) || $instanceToUse == "") {
                                            foreach ($instanceData as $fieldName => $fieldValue) {
                                                if ($fieldValue == "") continue;
                                                if ((in_array($fieldName,$fieldsToUse) || empty($fieldsToUse))) {
                                                    if ($fieldName == $destRecordField && $fieldValue != "") $fieldValue = $recordToUse;
                                                    $fieldInstrument = $sourceMeta[$fieldName]['form_name'];
                                                    $instrumentRepeats = $sourceProject->isRepeatingForm($subEventID, $fieldInstrument);
                                                    if (($instrument == $fieldInstrument && !$instrumentRepeats) || ($instrument != "" && $instrument != $fieldInstrument)) continue;
                                                    $destFieldName = $destinationFields[array_search($fieldName,$fieldsToUse)];
                                                    if ($destFieldName != "" && $fieldValue != "") {
                                                        //echo "Before save $destFieldName, $fieldValue: ".time()."<br/>";
                                                        $this->updateDestinationData($destData,$sourceProject, $destProject, $destFieldName, $fieldValue, $recordToUse, $destEventID, $instance);
                                                        //echo "After save: ".time()."<br/>";
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    elseif (isset($eventMapping[$eventID])) {
                        if ($eventToUse != "" && $eventID != $eventToUse) continue;
                        //TODO Need to check if a field is on a repeating/non-repeating basis when looking here for a valid field value, it will be empty ALWAYS otherwise
                        $destEventID = $eventMapping[$eventID];

                        foreach ($eventData as $fieldName => $fieldValue) {
                            if ((in_array($fieldName,$fieldsToUse) || empty($fieldsToUse))) {
                                echo "Field $fieldName, Value: $fieldValue<br/>";
                                if ($fieldValue == "") continue;
                                if ($fieldName == $destRecordField && $fieldValue != "") $fieldValue = $recordToUse;
                                $fieldInstrument = $sourceMeta[$fieldName]['form_name'];
                                $instrumentRepeats = $sourceProject->isRepeatingForm($eventID, $fieldInstrument);
                                if ($instrumentRepeats) continue;
                                $destFieldName = $destinationFields[array_search($fieldName,$fieldsToUse)];
                                /*echo "Dest field: $destFieldName, Source: $fieldName, ".array_search($fieldName,$fieldsToUse)."<br/>";
                                echo "<pre>";
                                print_r($destinationFields);
                                echo "</pre>";*/
                                if ($destFieldName != "" && $fieldValue != "") {
                                    //echo "Before save single $destFieldName, $fieldValue: ".time()."<br/>";
                                    $this->updateDestinationData($destData,$sourceProject, $destProject, $destFieldName, $fieldValue, $recordToUse, $destEventID);
                                    //echo "After save single: ".time()."<br/>";
                                }
                            }
                        }
                    }
                }
            }
        }
        //echo "After data looping: ".time()."<br/>";
        return $destData;
    }

    function updateDestinationData(&$destData,\Project $sourceProject, \Project $destProject, $destFieldName, $srcFieldValue, $destRecord, $destEvent,$destRepeat = 1) {
        $destMeta = $destProject->metadata;
        $destEventForms = $destProject->eventsForms[$destEvent];

        $destInstrument = $destMeta[$destFieldName]['form_name'];
        $destRecordField = $destProject->table_pk;
        $destInstrumentRepeats = $destProject->isRepeatingForm($destEvent, $destInstrument);
        $destEventRepeats = $destProject->isRepeatingEvent($destEvent);

        if (in_array($destInstrument,$destEventForms)) {
            if ($destInstrumentRepeats) {
                $destData[$destRecord][$destEvent][$destRecordField] = $destRecord;
                //$destData[$destRecord][$destEvent]['redcap_repeat_instrument'] = "";
                //$destData[$destRecord][$destEvent]['redcap_repeat_instance'] = $destRepeat;
                $destData[$destRecord]['repeat_instances'][$destEvent][$destInstrument][$destRepeat][$destFieldName] = $srcFieldValue;
            } elseif ($destEventRepeats) {
                $destData[$destRecord][$destEvent][$destRecordField] = $destRecord;
                //$destData[$destRecord][$destEvent]['redcap_repeat_instrument'] = "";
                //$destData[$destRecord][$destEvent]['redcap_repeat_instance'] = $destRepeat;
                $destData[$destRecord]['repeat_instances'][$destEvent][''][$destRepeat][$destFieldName] = $srcFieldValue;
            } else {
                $destData[$destRecord][$destEvent][$destFieldName] = $srcFieldValue;
            }
        }
        /*echo "<pre>";
        print_r($destData);
        echo "</pre>";*/
        //$results = \Records::saveData($destProject->project_id, 'array', $destData);
        /*echo "<pre>";
        print_r($results);
        echo "</pre>";*/
        return $destData;
    }

    function saveDestinationData($project_id, $destData) {
        $results = "";
        $results = \Records::saveData($project_id, 'array', $destData);
        return $results;
    }
    
    function validFieldValue($fieldMeta,$fieldValue) {
        //TODO THIS DOES NOT PROPERLY CHECK
        $validValue = false;
        $enumArray = $this->processFieldEnum($fieldMeta['element_enum']);
        switch ($fieldMeta['element_type']) {
            case "text":
                if ($fieldValue != "") $validValue = true;
            case "file":
            case "slider":
                if (is_numeric($fieldValue)) $validValue = true;
            case "select":
            case "radio":
            case "yesno":
            case "truefalse":
            case "checkbox":
                if (in_array($fieldValue,array_keys($enumArray))) $validValue = true;
        }
        return $validValue;
    }

    function processFieldEnum($enum) {
        $enumArray = array();
        $splitEnum = explode("\\n",$enum);
        foreach ($splitEnum as $valuePair) {
            $splitPair = explode(",",$valuePair);
            $enumArray[trim($splitPair[0])] = trim($splitPair[1]);
        }
        return $enumArray;
    }

    function parseSpecialTags($project_id,$setting) {
        preg_match_all('#(?<=:).+?(?=:)#',$setting,$matches);

        foreach ($matches[0] as $index => $match) {
            if ($index % 2 != 0) continue;
            switch ($match) {
                case "next_id":
                    $setting = $this->nextID($project_id,$setting,$match);
                    break;
            }
        }
        return $setting;
    }

    function nextID($project_id,$setting,$match) {
        $return = $setting;

        $replaceString = ":".$match.":";
        $searchString = str_replace($replaceString,"%",$setting);
        $baseRecordName = str_replace($replaceString,'',$setting);
        $nextNumber = 1;

        $sql = "SELECT record
						FROM redcap_record_list
						WHERE project_id = ?
                        AND record LIKE ?
						ORDER BY CAST(SUBSTR(record,".(strlen($searchString)+1).") AS UNSIGNED) DESC
						LIMIT 1";
        $result = $this->query($sql, [$project_id,$searchString]);
        while ($row = $result->fetch_assoc()) {
            if ($row['record'] != "") {
                $numberID = str_replace($baseRecordName,'',$row['record']);
                if (is_numeric($numberID)) {
                    $nextNumber = $numberID+1;
                    break;
                }
            }
        }
        $return = htmlspecialchars($baseRecordName.$nextNumber,ENT_QUOTES);

        $maxLoops = 0;

        while ($this->checkRecordExists($project_id,$return) && $maxLoops < 20) {
            $nextNumber++;
            $maxLoops++;
            $return = htmlspecialchars($baseRecordName.$nextNumber,ENT_QUOTES);
        }

        if ($maxLoops == 20) {
            return false;
        }
        return $return;
    }

    function checkRecordExists($project_id,$record) {
        $sql = "SELECT record
						FROM redcap_record_list
						WHERE project_id = ?
                        AND record = ?";
        $result = $this->query($sql,[$project_id,$record]);
        return ($result->num_rows > 0);
    }
}