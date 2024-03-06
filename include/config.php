<?php
    /**
     * @file
     * Simple Image Steganography - Main Configuration File
     * This is the main configuration file of the frontend and the conversion script
     */

     /** Enable or disable server settings on frontend. On production evnironment, keep them disabled! */
    define('ALLOW_OVERRIDE_PHP_SETTINGS', 0);

    /** Disable or enable checksum validation in input file. Checksum validation is essential to detect manipulated and corrupted files so you should keep this option switched off except if you really need to allow invalid files too. */
    define('DISABLE_CHECKSUM_VALIDATION', 0);
