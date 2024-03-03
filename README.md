# Simple Image Steganography

This web application provides a simple way for effective image _steganography_ designed to hide larger amount of data in an image file by replacing an entire _color component_ of the _carrier image_ instead of a few bits so you can hide much more data than a simple message. On the con, this method is much weaker than the traditional image steganorgaphy so you should choose a carrier image with a strong _dominant color palette_ and hide the data by replacing the _weakest color component_ to make this steganography less noticable. _If it is essential to hide your data properly, you should choose a different steganography tool_ as this module was designed to hide large amount of data instead of hiding data deep in the carrier image to make finding the data nearly impossible.

## Requirements

To install this web application, you need a _web server_ supporting **PHP 5.4** or later with PHP's **GD** and **OpenSSL extensions**.

## Installation

Place this entire repository somewhere in your web server's document _root directory_. A subdirectory should also work.

## Usage

Once the web application is installed on your web server, navigate to `index.php` and follow the instructions in your web browser.

## Embedding PHP steganography class in a different web application

To _embed PHP steganography class_ in a separate web application, place `include/class.php_stego.php` file in your web application and include the class in the corresponding script as you can see bellow:

```php
require_once 'class.php_stego.php';
...
$php_stego = new PHP_STEGO();
//if $bin_to_image = 1, binary data fill be converted to an image
//binary data will be extracted from the image otherwise
$php_stego->set_encoding_direction($bin_to_image);
$php_stego->set_carrier_data($carrier_image_data);
$php_stego->set_input_data($binary_data_to_encode);
$php_stego->set_encryption_key('Your Password');
$output_image_data = $php_stego->convert();
```

To extract the encoded binary data, do the following:

```php
...
$php_stego->set_input_data($output_image_data);
$php_stego->set_encoding_direction(false);
$output_file = $php_stego->convert();
```

For further information on how to use the class, check `include/convert.php` script.

## Multiple Licensing Information

This project consists of **multiple components**, each with its **own license**. When using this project, carefully choose based on the license of the component you intend to use:

- The **core PHP application** (located in `include` directory) is licensed under a **modified MIT license** (©2024 Robert Abraham).

- The **Graphical User Interface (GUI)** of the *web application* (located in this directory excluding the `include` subdirectory) was built using standard [jQuery](https://jquery.com/) (located in `javascript` subdirectory) and is licensed under **MIT**.

Both licenses allow commercial use under the corresponding conditions. For more details, please refer to the `license.md` file.
