<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Convert File</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style/main.css" type="text/css" />
        <script type="text/javascript" src="javascript/jquery.min.js"></script>
        <script type="text/javascript">
            function set_encoding_direction(new_encoding_direction) {
                if (new_encoding_direction) {
                    $('#input_for_encoding').show();
                    $('#input_for_decoding').hide();
                    $('#input_file').removeAttr('accept');
                    $('#encryption_password_title').show();
                    $('#decryption_password_title').hide();
                    $('#encryption_password_description').show();
                    $('#decryption_password_description').hide();
                    $('#encryption_password_confirmation_container').show();
                    $('#encryption_password_confirmation').attr('required', 'required');
                    $('#carrier_file_container').show();
                    $('#aspect_ratio_container').show();
                    $("[name='aspect_ratio']").attr('required', 'required');
                    $('#replace_color_component_description').show();
                    $('#replaced_color_component_description').hide();
                    $('#compression_level_container').show();
                    $("[name='compression_level']").attr('required', 'required');
                    return;
                }
                $('#input_for_encoding').hide();
                $('#input_for_decoding').show();
                $('#input_file').attr('accept', 'image/*');
                $('#encryption_password_title').hide();
                $('#decryption_password_title').show();
                $('#encryption_password_description').hide();
                $('#decryption_password_description').show();
                $('#encryption_password_confirmation').removeAttr('required');
                $('#encryption_password_confirmation_container').hide();
                $('#carrier_file_container').hide();
                $("[name='aspect_ratio']").removeAttr('required');
                $('#aspect_ratio_container').hide();
                $('#replace_color_component_description').hide();
                $('#replaced_color_component_description').show();
                $("[name='compression_level']").removeAttr('required');
                $('#compression_level_container').hide();
            }
            function validate_password() {
                var encryption_password = $('#encryption_password').val().trim();
                if (!encryption_password.length) {
                    alert('Password not provided!');
                    return false;
                }
                if (!parseInt($('input[name="bin_to_image"]:checked').val())) { return true; }
                var encryption_password_confirmation = $('#encryption_password_confirmation').val().trim();
                if (!encryption_password_confirmation.length) {
                    alert('Password not confirmed!');
                    return false;
                }
                if (encryption_password != encryption_password_confirmation) {
                    alert('Password confirmation failed!');
                    return false;
                }
                return true;
            }
            function validate_form() {
                if (!$('#input_file').val()) {
                    alert('Input file not set!');
                    return false;
                }
                if ($('#carrier_file_container').is(':visible')) {
                    if (!$('#carrier_file').val()) {
                        alert('Carrier image not set!');
                        return false;
                    }
                }
                return validate_password();
            }
            function change_filename(source_object, target_id){
                if (!source_object) { return false; }
                if (!target_id) { return false; }
                var filename = source_object.value;
                if (!filename) { return false; }
                $('#'+target_id).html(filename);
                return true;
            }
            window.onload = function () {
                set_encoding_direction(true);
            };
        </script>
    </head>
    <body>
        <form action="include/convert.php" method="post" enctype="multipart/form-data" onsubmit="return validate_form()">
            <div class="title">
                <img src="image/logo.png" alt="Logo" />
                <h1>Simple Image Steganography</h1>
                <p>This application provides a simple module for effective image steganography designed to hide larger amount of data in an image file.</p>
            </div>
            <div class="title">
                <h2>Convert Input File*</h2>
                <div class="radio_button">
                    <input type="radio" name="bin_to_image" value="1" required="required" checked onclick="set_encoding_direction(true);" /><span>to image</span>
                    <div class="description">Create image file from any binary data.</div>
                </div>
                <div class="radio_button">
                    <input type="radio" name="bin_to_image" value="0" required="required" onclick="set_encoding_direction(false);" /><span>to data</span>
                    <div class="description">Extract the encoded binary data from the input image file.</div>
                </div>
            </div>
            <div class="title">
                <h2>Input file*</h2>
                <p class="description" id="input_for_encoding">The binary data you want to hide in a carrier image file</p>
                <p class="description" id="input_for_decoding">The image file you want to extract the hidden data from</p>
                <div class="file_input">
                    <label for="input_file">Open</label>
                    <input type="file" name="input_file" id="input_file" onchange="change_filename(this, 'input_filename');"/>
                    <div id="input_filename">No file selected (max. <?php echo trim(ini_get('upload_max_filesize')); ?>)</div>
                </div>
            </div>
            <div class="title">
                <h2 id="encryption_password_title">Encryption password*</h2>
                <h2 id="decryption_password_title">Password for decryption*</h2>
                <p class="description" id="encryption_password_description">Enter password to encrypt the data you want to hide!</p>
                <p class="description" id="decryption_password_description" style="display:none;">Enter password to decrypt the data you want to retrieve!</p>
                <input class="large" type="password" name="encryption_password" maxlength="64" id="encryption_password" value="" required="required"/>
            </div>
            <div class="title" id="encryption_password_confirmation_container">
                <h2>Confirm password*</h2>
                <input class="large" type="password" name="encryption_password_confirmation" maxlength="64" id="encryption_password_confirmation" value="" required="required"/>
            </div>
            <div class="title" id="carrier_file_container">
                <h2>Carrier image*</h2>
                <p class="description">Hide the input file in this (carrier) image.</p>
                <div class="file_input">
                    <label for="carrier_file">Open</label>
                    <input type="file" name="carrier_file" id="carrier_file" accept="image/*" onchange="change_filename(this, 'carrier_file_filename');"/>
                    <div id="carrier_file_filename">No file selected (max. <?php echo trim(ini_get('upload_max_filesize')); ?>)</div>
                </div>
            </div>
            <div class="title" id="aspect_ratio_container">
                <h2>Output aspect ratio*</h2>
                <p class="description">Output image resolution will be adjusted automatically to fit data size.</p>
                <div class="radio_button"><input type="radio" name="aspect_ratio" value="AUTO" required="required" checked /><span>Auto</span></div>
                <div class="radio_button"><input type="radio" name="aspect_ratio" value="SQUARE" required="required" /><span>Square (1:1)</span></div>
                <div class="radio_button"><input type="radio" name="aspect_ratio" value="SMALLSCREEN" required="required" /><span>Smallscreen (4:3)</span></div>
                <div class="radio_button"><input type="radio" name="aspect_ratio" value="WIDESCREEN" required="required" /><span>Widescreen (16:9)</span></div>
            </div>
            <div class="title">
                <div id="replace_color_component_description">
                    <h2>Replace color component*</h2>
                    <p class="description">This color component will be replaced by the data you want to hide in the RGB palette (this may make the output image grainy if your carrier image is not monochromatic or there is no significantly dominant color in the carrier image making the steganography more noticable).</p>
                </div>
                <h2 id="replaced_color_component_description">Replaced color component*</h2>
                <div class="radio_button"><input type="radio" name="color_component" value="RED" required="required" checked /><span>red</span></div>
                <div class="radio_button"><input type="radio" name="color_component" value="GREEN" required="required" /><span>green</span></div>
                <div class="radio_button"><input type="radio" name="color_component" value="BLUE" required="required" /><span>blue</span></div>
            </div>
            <div class="title" id="compression_level_container">
                <h2>Compression level*</h2>
                <p class="description">The compression level of the output</p>
                <div class="radio_button"><input type="radio" name="compression_level" value="1" required="required" /><span>1 (minimum compression)</span></div>
                <div class="radio_button"><input type="radio" name="compression_level" value="2" required="required" /><span>2</span></div>
                <div class="radio_button"><input type="radio" name="compression_level" value="3" required="required" /><span>3</span></div>
                <div class="radio_button"><input type="radio" name="compression_level" value="4" required="required" /><span>4</span></div>
                <div class="radio_button"><input type="radio" name="compression_level" value="5" required="required" /><span>5</span></div>
                <div class="radio_button"><input type="radio" name="compression_level" value="6" required="required" checked /><span>6 (default)</span></div>
                <div class="radio_button"><input type="radio" name="compression_level" value="7" required="required" /><span>7</span></div>
                <div class="radio_button"><input type="radio" name="compression_level" value="8" required="required" /><span>8</span></div>
                <div class="radio_button"><input type="radio" name="compression_level" value="9" required="required" /><span>9 (maximum compression)</span></div>
            </div>
            <div class="title">
                <input type="submit" value="Submit" />
            </div>
            <div class="title">
                <p class="required_field_description">*required</p>
                <p><a href="doc/index.html" target="_blank">API documentation</a></p>
            </div>
        </form>
    </body>
</html>
