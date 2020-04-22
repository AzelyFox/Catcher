<?php

require_once "./inc.php";

# initialize service key
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

# initialize log offset
if (isset($_REQUEST["log_offset"]))
{
    $log_offset = $_REQUEST["log_offset"];
    if (!is_numeric($log_offset)) {
        $output = array();
        $output["result"] = -1;
        $output["error"] = "log_offset IS NOT NUMERIC";
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
} else {
    $log_offset = 0;
}

# initialize log limit
if (isset($_REQUEST["log_limit"]))
{
    $log_limit = $_REQUEST["log_limit"];
    if (!is_numeric($log_limit)) {
        $output = array();
        $output["result"] = -1;
        $output["error"] = "log_limit IS NOT NUMERIC";
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
} else {
    $log_limit = 100;
}

# initialize log order
if (isset($_REQUEST["log_order"]))
{
    $log_order = $_REQUEST["log_order"];
    if (!is_numeric($log_order)) {
        $output = array();
        $output["result"] = -1;
        $output["error"] = "log_order IS NOT NUMERIC";
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
} else {
    $log_order = 0;
}


$logResult = array();
# execute service logs query
try {
    $TEMP_TABLE_NAME = "catcher_".$validation["service_index"];
    /** @noinspection SqlResolve */
    $DB_SQL = "SELECT `log_index`, `log_tag`, `log_user`, `log_level`, `log_title`, `log_content`, `log_created` FROM $TEMP_TABLE_NAME";
    if ($log_order == 0) $DB_SQL .= " ORDER BY `log_index` ASC";
    if ($log_order == 1) $DB_SQL .= " ORDER BY `log_index` DESC";
    if ($log_order == 2) $DB_SQL .= " ORDER BY `log_level` ASC";
    if ($log_order == 3) $DB_SQL .= " ORDER BY `log_level` DESC";
    $DB_SQL .= " LIMIT ".$log_offset.", ".$log_limit;
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
    $DB_STMT->execute();
    if ($DB_STMT->errno != 0) {
        # service logs query error
        $output = array();
        $output["result"] = -4;
        $output["error"] = $DB_STMT->error;
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
    $DB_STMT->bind_result($TEMP_REVIEW_INDEX, $TEMP_REVIEW_TAG, $TEMP_REVIEW_USER, $TEMP_REVIEW_RATING, $TEMP_REVIEW_TITLE, $TEMP_REVIEW_CONTENT, $TEMP_REVIEW_CREATED);
    while($DB_STMT->fetch()) {
        $logObject = array();
        $logObject["log_index"] = $TEMP_REVIEW_INDEX;
        $logObject["log_tag"] = $TEMP_REVIEW_TAG;
        $logObject["log_user"] = $TEMP_REVIEW_USER;
        $logObject["log_level"] = $TEMP_REVIEW_RATING;
        $logObject["log_title"] = $TEMP_REVIEW_TITLE;
        $logObject["log_content"] = $TEMP_REVIEW_CONTENT;
        $logObject["log_created"] = $TEMP_REVIEW_CREATED;
        array_push($logResult, $logObject);
    }
    $DB_STMT->close();
} catch(Exception $e) {
    # service logs query error
    $output = array();
    $output["result"] = -2;
    $output["error"] = $e->getMessage();
    $output["error_debug"] = basename(__FILE__).".".__LINE__;
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

# log list success
$output = array();
$output["result"] = 0;
$output["error"] = "";
$output["logs"] = $logResult;
$outputJson = json_encode($output);
echo urldecode($outputJson);

?>