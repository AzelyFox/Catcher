<?php

require_once "./inc.php";

# validate service key
if (isset($_REQUEST["service_key"]))
{
    $service_key = $_REQUEST["service_key"];
    $validation = validateKey($DB, $service_key);
} else {
    $output = array();
    $output["result"] = -1;
    $output["error"] = "service_key IS EMPTY";
    $output["error_debug"] = basename(__FILE__).".".__LINE__;
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

# initialize log index
if (isset($_REQUEST["log_index"]))
{
    $log_index = $_REQUEST["log_index"];
} else {
    $output = array();
    $output["result"] = -1;
    $output["error"] = "log_index IS EMPTY";
    $output["error_debug"] = basename(__FILE__).".".__LINE__;
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

# execute log deletion query
try {
    $TEMP_TABLE_NAME = "catcher_".$validation["service_index"];
    /** @noinspection SqlResolve */
    $DB_SQL = "DELETE FROM $TEMP_TABLE_NAME WHERE `log_index` = ?";
    $DB_STMT = $DB->prepare($DB_SQL);
    # database query not ready
    if (!$DB_STMT) {
        $output = array();
        $output["result"] = -2;
        $output["error"] = "DB PREPARE FAILURE";
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
    $DB_STMT->bind_param("i", $log_index);
    $DB_STMT->execute();
    if ($DB_STMT->errno != 0) {
        # log deletion query error
        $output = array();
        $output["result"] = -4;
        $output["error"] = $DB_STMT->error;
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
    $DB_STMT->close();
} catch(Exception $e) {
    # log deletion query error
    $output = array();
    $output["result"] = -2;
    $output["error"] = $e->getMessage();
    $output["error_debug"] = basename(__FILE__).".".__LINE__;
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

# log deletion success
$output = array();
$output["result"] = 0;
$output["error"] = "";
$outputJson = json_encode($output);
echo urldecode($outputJson);

?>