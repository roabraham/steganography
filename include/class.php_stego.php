<?php
    /**
     * @file
     * Simple Image Steganography
     * This class provides a simple module for effective image steganography designed to hide larger amount of data in an image file.
     *
     * Modified MIT License
     *
     * Copyright (c) 2024 Robert Abraham
     *
     * Permission is hereby granted, free of charge, to any person obtaining a copy
     * of this software and associated documentation files (the "Software"), to deal
     * in the Software without restriction, including without limitation the rights
     * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
     * copies of the Software, and to permit persons to whom the Software is
     * furnished to do so, subject to the following conditions:
     *
     * The above copyright notice and this permission notice shall be included in all
     * copies or substantial portions of the Software.
     *
     * The Software and its derivatives may not be used for any harmful purposes,
     * including but not limited to activities that compromise privacy,
     * engage in illegal activities, or cause harm to individuals, groups, or entities.
     * The user of the Software agrees to use it responsibly and in compliance with
     * applicable laws and ethical standards.
     *
     * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
     * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
     * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
     * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
     * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
     * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
     * SOFTWARE.
     */

    /** Simple Image Steganography Class */
    class PHP_STEGO {

        /** The algorythm for the encryption */
        const ENCRYPTION_ALGORYTHM = 'aes-256-cbc';

        /** Cipher mode for encryption (compatibility mode) */
        const ENCRYPTION_CIPHER = 'CBC';

        /** Maximal encryption key length */
        const MAX_KEY_LENGTH = 32;

        /** The prefix of the temporary image file names */
        const IMAGE_TEMPNAME = 'php_stego_image';

        /** @var binary: the input data you want to hide */
        protected $input_data = null;

        /** @var binary: the image data you want to use as a carrier (or decoy) for the hidden data */
        protected $carrier_data = null;

        /** @var boolean: the direction of the steganography process */
        protected $encoding_direction = true;

        /** @var array: the associative array of the available carrier dimensions */
        protected $carrier_dimensions;

        /** @var string: the selected carrier image dimensions */
        protected $selected_carrier_dimension = 'AUTO';

        /** @var string: the target color component to modify on the carrier */
        protected $target_rgb_component = 'RED';

        /** @var string: the encryption key for the data to hide */
        protected $encryption_key = null;

        /** @var integer: the level of compression */
        protected $compression_level = -1;

        /** @var string: the original filename of the input to encode in the image */
        protected $original_filename = null;

        /** @var string: output filename (helper) */
        protected $new_filename = 'output.png';

        /** @var boolean: enable or disable checksum validation */
        protected $validate_checksum = true;

        /** @var boolean: use pure PHP code for encryption and decryption to ensure compatibility across different PHP versions */
        protected $compatibility_mode = false;

        /** @return binary: the raw input data you want to hide */
        public function get_input_data() { return $this->input_data; }

        /** @return binary: the image data you want to use as a carrier (or decoy) for the hidden data */
        public function get_carrier_data() { return $this->carrier_data; }

        /** @return boolean: the direction of the steganography process */
        public function get_encoding_direction() { return $this->encoding_direction; }

        /** @return string: the selected carrier image dimensions */
        public function get_carrier_dimensions() { return $this->selected_carrier_dimension; }

        /** @return string: the target color component to modify on the carrier */
        public function get_target_rgb_component() { return $this->target_rgb_component; }

        /** @return string: get the encryption key of the hidden data */
        public function get_encryption_key() { return $this->encryption_key; }

        /** @return integer: get the level of compression */
        public function get_compression_level() { return $this->compression_level; }

        /** @return string: the original filename of the input encoded in the image */
        public function get_original_filename() { return $this->original_filename; }

        /** @return string: output filename */
        public function get_new_filename() { return $this->new_filename; }

        /** @return boolean: get whether the checksum is validated */
        public function is_checksum_validated() { return $this->validate_checksum; }

        /** @return boolean: get whether compatibility mode is used for encryption and decryption */
        public function compatibility_mode_used() { return $this->compatibility_mode; }

        /**
         * Sets the input data you want to hide
         * @param binary $new_input_data: the input to hide
         * @return boolean: returns TRUE on success, FALSE otherwise
         */
        public function set_input_data($new_input_data) {
            try {
                if (!$new_input_data) { return false; }
                $this->input_data = $new_input_data;
                return true;
            } catch (Exception $x) {
                echo 'Exception: ' . trim($x->getMessage());
                return false;
            }
        }

        /**
         * Sets the image data you want to use as a carrier (or decoy) for the hidden data
         * @param binary $new_carrier_data: the carrier image data (if empty, no carrier will be used)
         * @return boolean: returns TRUE on success, FALSE otherwise
         */
        public function set_carrier_data($new_carrier_data) {
            try {
                $this->carrier_data = $new_carrier_data;
                return true;
            } catch (Exception $x) {
                echo 'Exception: ' . trim($x->getMessage());
                return false;
            }
        }

        /**
         * Sets the direction of the steganography process
         * @param boolean $new_encoding_direction: if set TRUE, the program will hide the input data in the carrier data
         * @return boolean: returns TRUE on success, FALSE otherwise
         */
        public function set_encoding_direction($new_encoding_direction) {
            try {
                if ($new_encoding_direction) {
                    $this->encoding_direction = true;
                } else {
                    $this->encoding_direction = false;
                }
                return true;
            } catch (Exception $x) {
                echo 'Exception: ' . trim($x->getMessage());
                return false;
            }
        }

        /**
         * Sets the preferred preferred carrier image dimensions (width-height ratio)
         * @param string $new_carrier_dimensions: the preferred carrier image dimensions (SQUARE|SMALLSCREEN|WIDESCREEN|AUTO)
         * @return boolean: returns TRUE on success, FALSE otherwise
         */
        public function set_carrier_dimensions($new_carrier_dimensions) {
            try {
                $new_carrier_dimensions_fixed = strtoupper(trim($new_carrier_dimensions));
                if (!$new_carrier_dimensions_fixed) { return false; }
                if ($new_carrier_dimensions_fixed == 'AUTO') {
                    $this->selected_carrier_dimension = $new_carrier_dimensions_fixed;
                    return true;
                }
                if (!isset($this->carrier_dimensions[$new_carrier_dimensions_fixed])) { return false; }
                $this->selected_carrier_dimension = $new_carrier_dimensions_fixed;
                return true;
            } catch (Exception $x) {
                echo 'Exception: ' . trim($x->getMessage());
                return false;
            }
        }

        /**
         * Set the target color component to modify on the carrier
         * @param string $new_target_rgb_component: the color component to modify (RED|GREEN|BLUE|ALPHA)
         * @return boolean: returns TRUE on success, FALSE otherwise
         */
        public function set_target_rgb_component($new_target_rgb_component) {
            try {
                $new_target_rgb_component_fixed = strtoupper(trim($new_target_rgb_component));
                if (!$new_target_rgb_component_fixed) { return false; }
                if (!preg_match('/^(RED|GREEN|BLUE|ALPHA)$/', $new_target_rgb_component_fixed)) { return false; }
                $this->target_rgb_component = $new_target_rgb_component_fixed;
                return true;
            } catch (Exception $x) {
                echo 'Exception: ' . trim($x->getMessage());
                return false;
            }
        }

        /**
         * Set the encryption key for the data to hide
         * @param string $new_encryption_key: the new encryption key
         * @return boolean: returns TRUE on success, FALSE otherwise
         */
        public function set_encryption_key($new_encryption_key) {
            try {
                $new_encryption_key_fixed = trim($new_encryption_key);
                if (!(strlen($new_encryption_key_fixed) >= 0)) { return false; }
                $this->encryption_key = $new_encryption_key_fixed;
                return true;
            } catch (Exception $x) {
                echo 'Exception: ' . trim($x->getMessage());
                return false;
            }
        }

        /**
         * Set new compression level
         * @param integer $new_compression_level: the new level of compression
         * @return boolean: returns TRUE on success, FALSE otherwise
         */
        public function set_compression_level($new_compression_level) {
            try {
                if (!isset($new_compression_level)) { return false; }
                $this->compression_level = min(max(intval($new_compression_level), -1), 9);
                if ($this->compression_level == 0) { $this->compression_level = 1; }
                return true;
            } catch (Exception $x) {
                echo 'Exception: ' . trim($x->getMessage());
                return false;
            }
        }

        /**
         * Set original filename to encode in the output image (for proper download filename when decoding)
         * @param string $new_original_filename: the filename to encode
         * @return boolean: returns TRUE on success, FALSE otherwise
         */
        public function set_original_filename($new_original_filename) {
            try {
                $new_original_filename_fixed = trim($new_original_filename);
                if (strlen($new_original_filename_fixed) >= 1) {
                    $this->original_filename = $new_original_filename_fixed;
                } else {
                    $this->original_filename = null;
                }
                return true;
            } catch (Exception $x) {
                echo 'Exception: ' . trim($x->getMessage());
                return false;
            }
        }

        /**
         * Enable or disable checksum validation in the input file you want to decode. Although checksum validation is strongly recommended to prevent decoding manipulated or corrupted input files, in some rare cases you may want to allow errors at the decoding process (for instance: if the encoded file is an image) so you can switch it off if you really need it.
         * @param boolean $new_checksum_validation: if set TRUE, the checksum will be validated (recommended), validation will be disabled on FALSE
         * @return boolean: returns TRUE on success, FALSE otherwise
         */
        public function set_checksum_validation($new_checksum_validation) {
            try {
                if ($new_checksum_validation) {
                    $this->validate_checksum = true;
                } else {
                    $this->validate_checksum = false;
                }
                return true;
            } catch (Exception $x) {
                echo 'Exception: ' . trim($x->getMessage());
                return false;
            }
        }

        /**
         * Enable or disable compatibility mode for encryption and decryption to ensure compatibility across different PHP versions (slow)
         * @param boolean $use_compatibility_mode: if set TRUE, pure PHP code will be used for encryption and decryption (not depending on OpenSSL extension)
         * @return boolean: returns TRUE on success, FALSE otherwise
         */
        public function set_compatibility_mode($use_compatibility_mode){
            try {
                if ($use_compatibility_mode) {
                    $this->compatibility_mode = true;
                } else {
                    $this->compatibility_mode = false;
                }
                return true;
            } catch (Exception $x) {
                echo 'Exception: ' . trim($x->getMessage());
                return false;
            }
        }

        /**
         * Encrypts the input data with the specified key (helper)
         * @param string $input_data: the input data to encrypt
         * @param string $encryption_key: encrypt input data with this key
         * @param boolean $compatibility_mode: if set TRUE, compatibility mode will be used for encryption (optional, slow)
         * @return string: the encrypted data on success, NULL otherwise
         */
        public static function encrypt_data($input_data, $encryption_key, $compatibility_mode = false) {
            try {
                //Check basic encryption parameters
                if (!$input_data) { return null; }
                $encryption_key_fixed = trim($encryption_key);
                if (!(strlen($encryption_key_fixed) >= 1)) { return null; }
                if (strlen($encryption_key_fixed) > self::MAX_KEY_LENGTH) { return null; }
                $input_data_modified = "ENCRYPTED#{$input_data}";
                if ($compatibility_mode) {
                    //Use phpAES for encryption (compatibility mode)
                    $iv_characters = '0123456789abcdef';
                    $iv_characters_length = strlen($iv_characters);
                    $initialization_vector = '';
                    for ($i = 0; $i < 16; ++$i) {
                        $iv_character_index = round(rand(0, $iv_characters_length - 1));
                        if (($iv_character_index >= 0) && ($iv_character_index <= $iv_characters_length - 1)) {
                            $initialization_vector .= trim(substr($iv_characters, $iv_character_index, 1));
                        }
                    }
                    if (strlen($initialization_vector) != 16) { return null; }
                    $encryption_key_fixed = str_pad($encryption_key_fixed, self::MAX_KEY_LENGTH, '0', STR_PAD_RIGHT);
                    require_once(str_replace('\\', '/', __DIR__) . '/class.aes.php');
                    $cipher = new AES($encryption_key_fixed, self::ENCRYPTION_CIPHER, $initialization_vector);
                    $output_data = $cipher->encrypt($input_data_modified);
                    if (strlen($output_data) >= 1) { return "{$initialization_vector}{$output_data}"; }
                    return null;
                }
                $initialization_vector = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::ENCRYPTION_ALGORYTHM));
                if (!$initialization_vector) { return null; }
                $output_data = openssl_encrypt($input_data_modified, self::ENCRYPTION_ALGORYTHM, $encryption_key_fixed, OPENSSL_RAW_DATA, $initialization_vector);
                if ($output_data === false) { return null; }
                return "{$initialization_vector}{$output_data}";
            } catch (Exception $x) {
                echo 'Exception: ' . trim($x->getMessage());
                return null;
            }
        }

        /**
         * Decrypts the input data with the specified key (helper)
         * @param string $input_data: the input data to decrypt
         * @param string $encryption_key: decrypt input data with this key
         * @param boolean $compatibility_mode: if set TRUE, compatibility mode will be used for decryption (optional, slow)
         * @return string: the decrypted data on success, NULL otherwise
         */
        public static function decrypt_data($input_data, $encryption_key, $compatibility_mode = false) {
            try {
                if (!$input_data) { return null; }
                $encryption_key_fixed = trim($encryption_key);
                if (!(strlen($encryption_key_fixed) >= 1)) { return null; }
                if (strlen($encryption_key_fixed) > self::MAX_KEY_LENGTH) { return null; }
                if ($compatibility_mode) {
                    $encryption_key_fixed = str_pad($encryption_key_fixed, self::MAX_KEY_LENGTH, '0', STR_PAD_RIGHT);
                    $initialization_vector = trim(substr($input_data, 0, 16));
                    if (strlen($initialization_vector) != 16) { return null; }
                    if (preg_match('/[^0123456789abcdef]/', $initialization_vector)) { return null; }
                    $input_data_original = substr($input_data, 16);
                    if (!$input_data_original) { return null; }
                    require_once(str_replace('\\', '/', __DIR__) . '/class.aes.php');
                    $cipher = new AES($encryption_key_fixed, self::ENCRYPTION_CIPHER, $initialization_vector);
                    $output_data = $cipher->decrypt($input_data_original);
                    if (!(strlen($output_data) >= 1)) { return null; }
                    if (!preg_match('/^ENCRYPTED#/', $output_data)) { return null; }
                    $output_data = preg_replace('/^ENCRYPTED#/', '', $output_data);
                    if (!$output_data) { return null; }
                    return $output_data;
                }
                $initialization_vector_length = strlen(openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::ENCRYPTION_ALGORYTHM)));
                if (!$initialization_vector_length) { return null; }
                $initialization_vector = substr($input_data, 0, $initialization_vector_length);
                if (!$initialization_vector) { return null; }
                $input_data_original = substr($input_data, $initialization_vector_length);
                if (!$input_data_original) { return null; }
                $output_data = openssl_decrypt($input_data_original, self::ENCRYPTION_ALGORYTHM, $encryption_key_fixed, OPENSSL_RAW_DATA, $initialization_vector);
                if ($output_data === false) { return null; }
                if (!preg_match('/^ENCRYPTED#/', $output_data)) { return null; }
                $output_data = preg_replace('/^ENCRYPTED#/', '', $output_data);
                if (!$output_data) { return null; }
                return $output_data;
            } catch (Exception $x) {
                echo 'Exception: ' . trim($x->getMessage());
                return null;
            }
        }

        /**
         * Encoding or decoding the raw input data depending on the direction of the steganography process
         * @return binary: the encoded/decoded data on success, NULL otherwise
         */
        public function convert() {
            try {
                if (!$this->input_data) { return null; }
                if (!(strlen($this->encryption_key) >= 1)) { return null; }
                //Create image from input data
                if ($this->encoding_direction) {
                    $input_data_final = gzcompress($this->input_data, $this->compression_level);
                    if (!$input_data_final) { return null; }
                    $input_data_final = 'CHECKSUM_MD5:' . md5($input_data_final) . "#{$input_data_final}";
                    if (strlen($this->original_filename) >= 1) {
                        $base_filename = trim(pathinfo($this->original_filename, PATHINFO_FILENAME));
                        if (strlen($base_filename) >= 1) { $this->new_filename = "{$base_filename}.png"; }
                        $input_data_final = 'ORIGINAL_FILENAME:' . base64_encode($this->original_filename) . "#{$input_data_final}";
                    }
                    $input_data_final = self::encrypt_data($input_data_final, $this->encryption_key, $this->compatibility_mode);
                    if (!$input_data_final) { return null; }
                    $data_base64 = base64_encode($input_data_final) . '#';
                    $data_length = strlen($data_base64);
                    if (!$data_length) { return null; }
                    $image_data = false;
                    $image_width = null;
                    $image_height = null;
                    $using_carrier = false;
                    if ($this->carrier_data) {
                        $image_data = imagecreatefromstring($this->carrier_data);
                        if ($image_data === false) { return null; }
                        $carrier_width = imagesx($image_data);
                        if (!$carrier_width) { return null; }
                        $carrier_height = imagesy($image_data);
                        if (!$carrier_height) { return null; }
                        if (!imageistruecolor($image_data)) {
                            $new_image_data = imagecreatetruecolor($carrier_width, $carrier_height);
                            if ($new_image_data === false) { return null; }
                            if (!imagecopy($new_image_data, $image_data, 0, 0, 0, 0, $carrier_width, $carrier_height)) { return null; }
                            if ($new_image_data === false) { return null; }
                            imagedestroy($image_data);
                            $image_data = $new_image_data;
                        }
                        if ($this->selected_carrier_dimension == 'AUTO') {
                            $image_width_ratio = $carrier_width / $carrier_height;
                            if (!$image_width_ratio) { return null; }
                        } else {
                            $image_width_ratio = $this->carrier_dimensions[$this->selected_carrier_dimension]['width'] / $this->carrier_dimensions[$this->selected_carrier_dimension]['height'];
                            if (!$image_width_ratio) { return null; }
                        }
                        $image_width = ceil(sqrt($data_length * $image_width_ratio));
                        if (!$image_width) { return null; }
                        $image_height = ceil($image_width / $image_width_ratio);
                        if (!$image_height) { return null; }
                        $new_image_data = imagecreatetruecolor($image_width, $image_height);
                        if ($new_image_data === false) { return null; }
                        if (!imagecopyresampled($new_image_data, $image_data, 0, 0, 0, 0, $image_width, $image_height, $carrier_width, $carrier_height)) { return null; }
                        if ($new_image_data === false) { return null; }
                        imagedestroy($image_data);
                        $image_data = $new_image_data;
                        if ($image_data === false) { return null; }
                        if ($this->target_rgb_component == 'ALPHA') {
                            imagealphablending($image_data, false);
                            imagesavealpha($image_data, true);
                        }
                        $using_carrier = true;
                    } else {
                        if ($this->selected_carrier_dimension == 'AUTO') {
                            if (!$this->set_carrier_dimensions('SQUARE')) { return null; }
                        }
                        $image_width_ratio = $this->carrier_dimensions[$this->selected_carrier_dimension]['width'] / $this->carrier_dimensions[$this->selected_carrier_dimension]['height'];
                        if (!$image_width_ratio) { return null; }
                        $image_width = ceil(sqrt($data_length * $image_width_ratio));
                        if (!$image_width) { return null; }
                        $image_height = ceil($image_width / $image_width_ratio);
                        if (!$image_height) { return null; }
                        $image_data = imagecreatetruecolor($image_width, $image_height);
                        if ($image_data === false) { return null; }
                    }
                    for ($i = 0; $i < $data_length; ++$i) {
                        $new_color_component = ord(substr($data_base64, $i, 1));
                        $x = $i % $image_width;
                        $y = floor($i / $image_width);
                        $new_color = null;
                        if ($using_carrier) {
                            $old_color = imagecolorat($image_data, $x, $y);
                            $red = ($old_color >> 16) & 0xFF;
                            $green = ($old_color >> 8) & 0xFF;
                            $blue = $old_color & 0xFF;
                            switch ($this->target_rgb_component) {
                                case 'RED': $new_color = imagecolorallocate($image_data, $new_color_component, $green, $blue); break;
                                case 'GREEN': $new_color = imagecolorallocate($image_data, $red, $new_color_component, $blue); break;
                                case 'BLUE': $new_color = imagecolorallocate($image_data, $red, $green, $new_color_component); break;
                                case 'ALPHA': $new_color = imagecolorallocatealpha($image_data, $red, $green, $blue, $new_color_component); break;
                                default: return null;
                            }
                        } else {
                            $new_color = imagecolorallocate($image_data, $new_color_component, $new_color_component, $new_color_component);
                        }
                        imagesetpixel($image_data, $x, $y, $new_color);
                    }
                    $output_file = tempnam(sys_get_temp_dir(), self::IMAGE_TEMPNAME);
                    if ($output_file === false) { return null; }
                    imagepng($image_data, $output_file);
                    $output_image = file_get_contents($output_file);
                    unlink($output_file);
                    imagedestroy($image_data);
                    if ($output_image === false) { return null; }
                    return $output_image;
                }
                //Create binary data from input
                $image_data = imagecreatefromstring($this->input_data);
                if ($image_data === false) { return null; }
                $image_width = imagesx($image_data);
                if (!$image_width) { return null; }
                $image_height = imagesy($image_data);
                if (!$image_height) { return null; }
                if (!imageistruecolor($image_data)) {
                    $new_image_data = imagecreatetruecolor($image_width, $image_height);
                    if ($new_image_data === false) { return null; }
                    if (!imagecopy($new_image_data, $image_data, 0, 0, 0, 0, $image_width, $image_height)) { return null; }
                    if ($new_image_data === false) { return null; }
                    imagedestroy($image_data);
                    $image_data = $new_image_data;
                }
                $data_base64 = '';
                for ($y = 0; $y < $image_height; ++$y) {
                    for ($x = 0; $x < $image_width; ++$x) {
                        $color = imagecolorat($image_data, $x, $y);
                        $red = ($color >> 16) & 0xFF;
                        $green = ($color >> 8) & 0xFF;
                        $blue = $color & 0xFF;
                        $current_byte = null;
                        switch ($this->target_rgb_component) {
                            case 'RED': $current_byte = chr($red); break;
                            case 'GREEN': $current_byte = chr($green); break;
                            case 'BLUE': $current_byte = chr($blue); break;
                            case 'ALPHA':
                            {
                                $colors = imagecolorsforindex($image_data, $color);
                                if (!isset($colors['alpha'])) { return null; }
                                $current_byte = chr($colors['alpha']);
                                break;
                            }
                            default: return null;
                        }
                        if ($current_byte == '#') { break 2; }
                        $data_base64 .= $current_byte;
                    }
                }
                imagedestroy($image_data);
                $data_length = strlen($data_base64);
                if (!$data_length) { return null; }
                $binary_data = base64_decode($data_base64);
                if ($binary_data === false) { return null; }
                $binary_data = self::decrypt_data($binary_data, $this->encryption_key, $this->compatibility_mode);
                if (!$binary_data) { return null; }
                $this->new_filename = 'output.dat';
                $pattern = '/^ORIGINAL_FILENAME:([^#]+)#/';
                $matches = array();
                if (preg_match($pattern, $binary_data, $matches)) {
                    $this->new_filename = trim(base64_decode($matches[1]));
                    $binary_data = preg_replace($pattern, '', $binary_data);
                    if (!$binary_data) { return null; }
                }
                $pattern = '/^CHECKSUM_MD5:([^#]+)#/';
                $matches = array();
                if (preg_match($pattern, $binary_data, $matches)) {
                    if ($this->validate_checksum) {
                        $checksum_stored = trim($matches[1]);
                        if (!strlen($checksum_stored)) { return null; }
                        $binary_data = preg_replace($pattern, '', $binary_data);
                        if (!$binary_data) { return null; }
                        if ($checksum_stored != md5($binary_data)) { return null; }
                    } else {
                        $binary_data = preg_replace($pattern, '', $binary_data);
                        if (!$binary_data) { return null; }
                    }
                }
                $binary_data = gzuncompress($binary_data);
                if ($binary_data === false) { return null; }
                return $binary_data;
            } catch (Exception $x) {
                echo 'Exception: ' . trim($x->getMessage());
                return null;
            }
        }

        /** Constructor */
        public function __construct() {
            try {
                $this->carrier_dimensions = array(
                    'SQUARE' => array('width' => 1, 'height' => 1),
                    'SMALLSCREEN' => array('width' => 4, 'height' => 3),
                    'WIDESCREEN' => array('width' => 16, 'height' => 9));
            } catch (Exception $x) {
                echo 'Exception: ' . trim($x->getMessage());
            }
        }
    }
