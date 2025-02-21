<?php


session_start();

$prer = '';

if (ini_get("allow_url_fopen")) {
    if (PHP_VERSION_ID >= 70200) {
        if (is_writable("data")) {
            if (extension_loaded("openssl")) {
                if (extension_loaded("simplexml")) {
                    $xmlinfo = simplexml_load_file("data/settings.xml");

                    if (isset($_REQUEST["legacy"])) {
                        if ($_REQUEST["legacy"] == "Yes") {
                            $xmlinfo->legacy = "Yes";
                            $xmlinfo->asXML("data/settings.xml");
                        }
                    } else {
                        if ($xmlinfo->legacy != "Yes") {
                            $prer = $prer . "OpenSSL is not installed or disabled. How to <a href=\"https://dashboardbuilder.net/how-to-install-openssl\" style=\"color:#fff;\">enable?</a> <br/>\r\n\t\tTo continue without OpenSSL - the software will run in Legacy mode without SSL <a href=\"./?legacy=Yes\" style=\"color:#fff;\">Continue?</a> ";
                        }
                    }
                } else {
                    $prer = $prer . "SimpleXML is not installed. How to <a href=\"https://dashboardbuilder.net/how-to-install-simplexml\" style=\"color:#fff;\">install?</a> ";
                }
            } else {
                $prer = $prer . "The Dashboard Builder dependencies require a PHP version >= 7.2.0. You are running " . PHP_VERSION . ".";
            }
        } else {
            $prer = $prer . "Error! Folder /data/ is not writable. Read-Write permission to folders and sub-folders of dashboardbuilder i.e chmod -R 774 dashboardbuilder ";
        }
    } else {
        $prer = $prer . "The Dashboard Builder dependencies require a PHP version >= 7.2.0. You are running " . PHP_VERSION . ".";
    }
} else {
    $prer = "allow_url_fopen is disabled. How to <a href=\"https://dashboardbuilder.net/enable-allow-url-fopen\" style=\"color:#fff;\">enable?</a> ";
}
$prer="";
if (empty($prer)) {
    if (isset($_REQUEST["param"])) {
        header("Location: lib/dashboard.php?param=" . $_REQUEST["param"]);
        exit;
    } else {
        header("Location: lib");
        exit;
    }
} else {
    echo "\t<style>\r\n\t.alert {\r\n\t  padding: 20px;\r\n\t  background-color: #f44336; /* Red */\r\n\t  color: white;\r\n\t  margin-bottom: 15px;\r\n\t  text-align:center;\r\n\t}\r\n\r\n\t/* The close button */\r\n\t.closebtn {\r\n\t  margin-left: 15px;\r\n\t  color: white;\r\n\t  font-weight: bold;\r\n\t  float: right;\r\n\t  font-size: 22px;\r\n\t  line-height: 20px;\r\n\t  cursor: pointer;\r\n\t  transition: 0.3s;\r\n\t}\r\n\r\n\t/* When moving the mouse over the close button */\r\n\t.closebtn:hover {\r\n\t  color: black;\r\n\t}\r\n\t</style>\r\n\t<div class=\"alert\">\r\n\t  <span class=\"closebtn\" onclick=\"this.parentElement.style.display='none';\">&times;</span>\r\n\t  " . $prer . "\r\n\t</div> \r\n\t";
    die;
}


?>