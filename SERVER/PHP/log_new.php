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

# initialize log user
if (isset($_REQUEST["log_user"]))
{
    $log_user = $_REQUEST["log_user"];
} else {
    $log_user = "";
}

# initialize log level
if (isset($_REQUEST["log_level"]))
{
    $log_level = $_REQUEST["log_level"];
    if (!is_numeric($log_level)) {
        $output = array();
        $output["result"] = -1;
        $output["error"] = "log_level IS NOT NUMERIC";
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
    $log_level = intval($log_level);
} else {
    $log_level = 0;
}

# initialize log tag
if (isset($_REQUEST["log_tag"]))
{
    $log_tag = $_REQUEST["log_tag"];
} else {
    $log_tag = "";
}

# initialize log title
if (isset($_REQUEST["log_title"]))
{
    $log_title = $_REQUEST["log_title"];
} else {
    $log_title = "";
}

# initialize log_content
if (isset($_REQUEST["log_content"]))
{
    $log_content = $_REQUEST["log_content"];
} else {
    $log_content = "";
}

# execute log insertion query
try {
    $TEMP_TABLE_NAME = "catcher_".$validation["service_index"];
    /** @noinspection SqlResolve */
    $DB_SQL = "INSERT INTO $TEMP_TABLE_NAME (`log_tag`, `log_user`, `log_level`, `log_title`, `log_content`, `log_created`) VALUES (?, ?, ?, ?, ?, NOW())";
    $DB_STMT = $DB->prepare($DB_SQL);
    # database query not ready
    if (!$DB_STMT) {
        $output = array();
        $output["result"] = -2;
        $output["error"] = $DB->error;
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
    $DB_STMT->bind_param("ssdss", $log_tag, $log_user, $log_level, $log_title, $log_content);
    $DB_STMT->execute();
    if ($DB_STMT->errno != 0) {
        # log insertion query error
        $output = array();
        $output["result"] = -4;
        $output["error"] = $DB_STMT->error;
        $output["error_debug"] = basename(__FILE__).".".__LINE__;
        $outputJson = json_encode($output);
        echo urldecode($outputJson);
        exit();
    }
    $TEMP_INSERTED_ROW = $DB_STMT->insert_id;
    $DB_STMT->close();
} catch(Exception $e) {
    # log insertion query error
    $output = array();
    $output["result"] = -2;
    $output["error"] = $e->getMessage();
    $output["error_debug"] = basename(__FILE__).".".__LINE__;
    $outputJson = json_encode($output);
    echo urldecode($outputJson);
    exit();
}

increaseCount($DB, $validation);

# log insertion success
$output = array();
$output["result"] = 0;
$output["error"] = "";
$output["log_index"] = $TEMP_INSERTED_ROW;
$outputJson = json_encode($output);
echo urldecode($outputJson);

?>