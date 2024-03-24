<?php
    /**
     * @file
     * Simple Image Steganography - Call script for Ajax
     * This script will convert any binary data to image vica-versa
     */

    //ini_set('display_errors', 1);

    /** The absolute filepath of the uploaded input file */
    $input_file = isset($_FILES['input_file']['tmp_name']) ? $_FILES['input_file']['tmp_name'] : false;

    /** The direction of the encoding process: if set TRUE, the input file will be converted to an image file, the encoded binary data will be extracted from the uploaded image file otherwise */
    $bin_to_image = isset($_REQUEST['bin_to_image']) ? $_REQUEST['bin_to_image'] : true;

    /** The absolute filepath of the carrier image file you want to encode the input data into */
    $carrier_file = isset($_FILES['carrier_file']['tmp_name']) ? $_FILES['carrier_file']['tmp_name'] : false;

    /** The aspect ratio of the output image file that carries the encoded input data (SQUARE|SMALLSCREEN|WIDESCREEN|AUTO) */
    $aspect_ratio = isset($_REQUEST['aspect_ratio']) ? trim($_REQUEST['aspect_ratio']) : false;

    /** The color component to replace with the input data you want to encode into the carrier image file; the replaced color component if you are decoding the input image file (RED|GREEN|BLUE|ALPHA) */
    $color_component = isset($_REQUEST['color_component']) ? $_REQUEST['color_component'] : false;

    /** Encrypt the uploaded input file with this password if it is not encrypted already */
    $encryption_password = isset($_REQUEST['encryption_password']) ? $_REQUEST['encryption_password'] : false;

    /** Compress input data with this level of compression */
    $compression_level = isset($_REQUEST['compression_level']) ? $_REQUEST['compression_level'] : null;

    /** Use pure PHP code for encryption and decryption to ensure compatibility across different PHP versions (slow) */
    $compatibility_mode = isset($_REQUEST['compatibility_mode']) ? $_REQUEST['compatibility_mode'] : false;

    /** Time limit for the conversion process in secods (overrides `php.ini`) */
    $process_timeout = isset($_REQUEST['process_timeout']) ? max(intval(trim($_REQUEST['process_timeout'])), 0) : null;

    /** Memory limit for the conversion process (overrides `php.ini`) */
    $process_memory_limit = isset($_REQUEST['process_memory_limit']) ? trim($_REQUEST['process_memory_limit']) : null;

    /** @cond */

    //Load configuration file
    require_once(str_replace('\\', '/', __DIR__) . '/config.php');
    if (ALLOW_OVERRIDE_PHP_SETTINGS) {
        $process_memory_limit = preg_replace('/[^0-9a-z\-]/i', '', $process_memory_limit);
        if ($process_memory_limit != '-1') {
            if (!preg_match('/^[0-9]+(K|M|G|)$/i', $process_memory_limit)) { $process_memory_limit = null; }
        }
        if (isset($process_timeout)) { ini_set('max_execution_time', $process_timeout); }
        if (isset($process_memory_limit)) { ini_set('memory_limit', $process_memory_limit); }
    }

    /** @endcond */

    /**
     * Generates an error page with the supplied error message
     * @param string $error_message: the error message you want to show
     * @return string: the HTML code of the error page
     */
    function handle_input_errors($error_message) {
        try {
            $error_message_fixed = trim($error_message);
            if (!$error_message_fixed) { $error_message_fixed = 'Unknown error!'; }
            $result_value = "<!DOCTYPE html>\n";
            $result_value .= "<html>\n";
            $result_value .= "<head>\n";
            $result_value .= "<meta charset=\"utf-8\"/>\n";
            $result_value .= "<title>Error</title>\n";
            $result_value .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
            $result_value .= "<link rel=\"stylesheet\" href=\"../style/main.css\" type=\"text/css\"/>\n";
            $result_value .= "</head>\n";
            $result_value .= "<body>\n";
            $result_value .= "<div class=\"error_page\">\n";
            $result_value .= "<div class=\"title\">\n";
            $result_value .= "<h1>ERROR: {$error_message_fixed}</h1>\n";
            $result_value .= "<div class=\"url\">\n";
            $server_parameters = ALLOW_OVERRIDE_PHP_SETTINGS ? '&amp;process_timeout=PROCESS_TIMEOUT&amp;process_memory_limit=PROCESS_MEMORY_LIMIT' : '';
            $result_value .= '<p><strong>Direct usage</strong>:&nbsp;' . basename(__FILE__) . "?input_file=INPUT_FILEPATH&amp;bin_to_image=BIN_TO_IMAGE&amp;carrier_file=CARRIER_FILEPATH&amp;aspect_ratio=ASPECT_RATIO&amp;color_component=COLOR_COMPONENT&amp;encryption_password=ENCRYPTION_PASSWORD&amp;compression_level=COMPRESSION_LEVEL&amp;compatibility_mode=COMPATIBILITY_MODE{$server_parameters}</p>\n";
            $result_value .= "</div>\n";
            $result_value .= "<p>where</p>\n";
            $result_value .= "<ul>\n";
            $result_value .= "<li><strong>INPUT_FILEPATH</strong> is the file you want to convert into an image</li>\n";
            $result_value .= "<li><strong>BIN_TO_IMAGE</strong>: is set to 1 then binary data will be converted to an image file, image file will be converted to binary otherwise (default: 1)</li>\n";
            $result_value .= "<li><strong>CARRIER_FILEPATH</strong> is the base image you want to be the carrier of the encoded binary data</li>\n";
            $result_value .= "<li><strong>ASPECT_RATIO</strong> is the preferred preferred carrier image dimensions (SQUARE|SMALLSCREEN|WIDESCREEN|AUTO, default: AUTO)</li>\n";
            $result_value .= "<li><strong>COLOR_COMPONENT</strong>: the color component you want to replace with the binary data (RED|GREEN|BLUE|ALPHA, default: RED)</li>\n";
            $result_value .= "<li><strong>ENCRYPTION_PASSWORD</strong>: the encryption key for the data to hide</li>\n";
            $result_value .= "<li><strong>COMPRESSION_LEVEL</strong>: compress input data with this level of compression (default: 6)</li>\n";
            $result_value .= "<li><strong>COMPATIBILITY_MODE</strong>: Use pure PHP code for encryption and decryption to ensure compatibility across different PHP versions (default: 0, slow)</li>\n";
            if (ALLOW_OVERRIDE_PHP_SETTINGS) {
                $result_value .= "<li><strong>PROCESS_TIMEOUT</strong>: Time limit for the conversion process in secods (overrides php.ini, optional)</li>\n";
                $result_value .= "<li><strong>PROCESS_MEMORY_LIMIT</strong>: Memory limit for the conversion process (overrides php.ini, optional)</li>\n";
            }
            $result_value .= "</ul>\n";
            $result_value .= "</div>\n";
            $result_value .= "<div class=\"button\">\n";
            $result_value .= "<a href=\"../index.php\">Back</a>\n";
            $result_value .= "</div>\n";
            $result_value .= "</div>\n";
            $result_value .= "</body>\n";
            $result_value .= "</html>";
            return $result_value;
        } catch (Exception $x) {
            return trim($x->getMessage());
        }
    }

    /** @cond */

    //Check input
    $original_filename = null;
    if ($input_file) {
        if (!isset($_FILES['input_file']['name'])) { die(handle_input_errors('Invalid input file!')); }
        $original_filename = trim(basename($_FILES['input_file']['name']));
    } else {
        if (!isset($_REQUEST['input_file'])) { die(handle_input_errors('Input file not provided!')); }
        $input_file = $_REQUEST['input_file'];
        if (!$input_file) { die(handle_input_errors('Invalid input file!')); }
        $original_filename = trim(basename($input_file));
    }
    if (!file_exists($input_file)) { die(handle_input_errors('Input file does not exist!')); }
    if (!$original_filename) { die(handle_input_errors('Invalid input filename!')); }
    if ($encryption_password === false) { die(handle_input_errors('Encryption key not set!')); }
    //Load Steganography class
    require_once(str_replace('\\', '/', __DIR__) . '/class.php_stego.php');
    $php_stego = new PHP_STEGO();
    $php_stego->set_encoding_direction($bin_to_image);
    $php_stego->set_compatibility_mode($compatibility_mode);
    if (!$php_stego->set_encryption_key($encryption_password)) {
        die(handle_input_errors('Could not set encryption key!'));
    }
    if ($aspect_ratio) {
        if (!$php_stego->set_carrier_dimensions($aspect_ratio)) {
            die(handle_input_errors('Could not set aspect ratio!'));
        }
    }
    if ($color_component) {
        if (!$php_stego->set_target_rgb_component($color_component)) {
            die(handle_input_errors('Could not set target color component!'));
        }
    }
    //Create image from input file
    if ($bin_to_image) {
        if (!$carrier_file) {
            if (!isset($_REQUEST['carrier_file'])) { die(handle_input_errors('Carrier file not provided!')); }
            $carrier_file = $_REQUEST['carrier_file'];
            if (!$carrier_file) { die(handle_input_errors('Invalid carrier file!')); }
        }
        if (!file_exists($carrier_file)) { die(handle_input_errors('Carrier file does not exist!')); }
        if (!preg_match('/\.(jpg|jpeg|png|gif|bmp|wbmp|gd2|webp)$/i', trim(basename($_FILES['carrier_file']['name'])))) { die(handle_input_errors('Carrier file must be a JPEG, PNG, GIF, BMP, WBMP, GD2 or WEBP image!')); }
        if (!$php_stego->set_carrier_data(file_get_contents($carrier_file))) { die(handle_input_errors('Failed to load carrier file!')); }
        if (!$php_stego->set_input_data(file_get_contents($input_file))) { die(handle_input_errors('Failed to load input file!')); }
        $php_stego->set_original_filename($original_filename);
        $php_stego->set_compression_level($compression_level);
        $output_file = $php_stego->convert();
        if (!$output_file) { die(handle_input_errors('Failed to create image from binary data!')); }
        header('Content-Type: image/png');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="' . $php_stego->get_new_filename() . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($output_file));
        echo $output_file;
        exit;
    }
    //Create binary data from input file
    if (!preg_match('/\.(jpg|jpeg|png|gif|bmp|wbmp|gd2|webp)$/i', $original_filename)) { die(handle_input_errors('Input file must be a JPEG, PNG, GIF, BMP, WBMP, GD2 or WEBP image!')); }
    if (!$php_stego->set_input_data(file_get_contents($input_file))) { die(handle_input_errors('Failed to load input file!')); }
    if (DISABLE_CHECKSUM_VALIDATION) { $php_stego->set_checksum_validation(false); }
    $output_file = $php_stego->convert();
    if (!$output_file) { die(handle_input_errors('Failed to create output! Incorrect password or corrupted file?')); }
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $php_stego->get_new_filename() . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($output_file));
    echo $output_file;
