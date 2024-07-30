<?php
//Form submission handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if input text or file upload is selected
    if (!empty($_POST['inputTextDecrypt'])) {
        // Retrieve input text
        $inputTextDecrypt = $_POST['inputTextDecrypt'];
        // Perform decryption based on selected cipher
        if ($_POST['cipherSelectDecrypt'] === 'simpleSubstitution') {
            // Call the simple substitution decryption function
            $decryptedText = simpleSubstitutionDecrypt($inputTextDecrypt);
        } elseif ($_POST['cipherSelectDecrypt'] === 'doubleTransposition') {
            // Call the double transposition decryption function
            $decryptedText = doubleTranspositionDecrypt($inputTextDecrypt);
        } elseif ($_POST['cipherSelectDecrypt'] === 'rc4') {
            // Call the RC4 decryption function with a key
            $key = 'secretkey'; 
            $decryptedText = rc4Decrypt($inputTextDecrypt, $key);
        } else {
            // Handle other ciphers if needed
            echo '<p>Error: Unsupported cipher selected.</p>';
        }
        // Store the decryption record in the database with operation_type 'decryption'
        storeDecryptionRecord($inputTextDecrypt, $_POST['cipherSelectDecrypt'], 'decryption');
        // Display the decryption result table
        displayDecryptionResult($inputTextDecrypt, $decryptedText);
    } elseif (!empty($_FILES['inputFileDecrypt']['tmp_name'])) {
        // Handle file upload decryption
        $uploadedFileDecrypt = $_FILES['inputFileDecrypt']['tmp_name'];
        $fileContentDecrypt = file_get_contents($uploadedFileDecrypt);
        // Perform decryption based on selected cipher
        if ($_POST['cipherSelectDecrypt'] === 'simpleSubstitution') {
            // Call the simple substitution decryption function
            $decryptedText = simpleSubstitutionDecrypt($fileContentDecrypt);
        } elseif ($_POST['cipherSelectDecrypt'] === 'doubleTransposition') {
            // Call the double transposition decryption function
            $decryptedText = doubleTranspositionDecrypt($fileContentDecrypt);
        } elseif ($_POST['cipherSelectDecrypt'] === 'rc4') {
            // Call the RC4 decryption function with a key
            $key = 'secretkey'; 
            $decryptedText = rc4Decrypt($fileContentDecrypt, $key);
        } else {
            // Handle other ciphers if needed
            echo '<p>Error: Unsupported cipher selected.</p>';
        }
        // Store the decryption record in the database with operation_type 'decryption'
        storeDecryptionRecord($fileContentDecrypt, $_POST['cipherSelectDecrypt'], 'decryption');
        // Display the decryption result table with the uploaded file content
        displayDecryptionResult($fileContentDecrypt, $decryptedText); // Use the actual content of the uploaded file
    } else {
        // Handle empty input or file upload
        echo '<p>Error: No input text or file uploaded.</p>';
    }
}   



// Function to perform simple substitution decryption
function simpleSubstitutionDecrypt($inputText) {
    // Define the substitution key
    $key = array(
        'z' => 'a', 'y' => 'b', 'x' => 'c', 'w' => 'd', 'v' => 'e',
        'u' => 'f', 't' => 'g', 's' => 'h', 'r' => 'i', 'q' => 'j',
        'p' => 'k', 'o' => 'l', 'n' => 'm', 'm' => 'n', 'l' => 'o',
        'k' => 'p', 'j' => 'q', 'i' => 'r', 'h' => 's', 'g' => 't',
        'f' => 'u', 'e' => 'v', 'd' => 'w', 'c' => 'x', 'b' => 'y',
        'a' => 'z',
    );

    // Convert input text to lowercase for consistent decryption
    $inputText = strtolower($inputText);

    // Decrypt the input text using the substitution key
    $decryptedText = '';
    for ($i = 0; $i < strlen($inputText); $i++) {
        $char = $inputText[$i];
        // Check if the character is a lowercase letter
        if (array_key_exists($char, $key)) {
            // Substitute the character using the key
            $decryptedText .= $key[$char];
        } else {
            // Keep non-alphabetic characters unchanged
            $decryptedText .= $char;
        }
    }

    return $decryptedText;
}

// Function to perform Double Transposition decryption
function doubleTranspositionDecrypt($encryptedText) {
    // Define the number of rows and columns for transposition
    $rows = 4;
    $columns = ceil(strlen($encryptedText) / $rows);

    // Create a matrix for transposition
    $matrix = [];
    for ($i = 0; $i < $columns; $i++) {
        $matrix[] = str_split(substr($encryptedText, $i * $rows, $rows));
    }

    // Transpose rows (performing the first transposition)
    $transposedRows = array_map(null, ...$matrix);

    // Transpose columns (performing the second transposition)
    $transposedColumns = [];
    foreach ($transposedRows as $row) {
        $transposedColumns[] = str_split(implode('', $row));
    }

    // Flatten the transposed columns to get the decrypted text
    $decryptedText = '';
    foreach ($transposedColumns as $column) {
        $decryptedText .= implode('', $column);
    }

    // Trim any extra padding characters from the decrypted text
    return trim($decryptedText);
}



// Function to perform RC4 decryption from hexadecimal input
function rc4Decrypt($hexInput, $key) {
    // Convert hexadecimal input to binary
    $inputText = pack("H*", $hexInput);

    // Initialize RC4 variables
    $s = range(0, 255);
    $j = 0;
    $key = array_values(unpack('C*', $key));
    $inputText = array_values(unpack('C*', $inputText));
    $len = count($inputText);
    $output = [];

    // Key-scheduling algorithm (KSA) initialization
    for ($i = 0; $i < 256; $i++) {
        $j = ($j + $s[$i] + $key[$i % count($key)]) % 256;
        [$s[$i], $s[$j]] = [$s[$j], $s[$i]];
    }

    // Pseudo-random generation algorithm (PRGA) for decryption
    $i = $j = 0;
    for ($k = 0; $k < $len; $k++) {
        $i = ($i + 1) % 256;
        $j = ($j + $s[$i]) % 256;
        [$s[$i], $s[$j]] = [$s[$j], $s[$i]];
        $output[] = $inputText[$k] ^ $s[($s[$i] + $s[$j]) % 256];
    }

    // Convert the output to plaintext
    $plaintext = implode('', array_map('chr', $output));

    return $plaintext;
}

function storeDecryptionRecord($inputText, $cipherUsed, $operationType) {
    session_start();
    require_once 'db_connection.php'; 
    
    $conn = new mysqli($hn, $un, $pw, $db);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $timestamp = date('Y-m-d H:i:s');
   
    $username = $_SESSION['username']; 
    
    $sql = "INSERT INTO user_inputs (username, input_text, cipher_used, operation_type, created_at) 
            VALUES ('$username', '$inputText', '$cipherUsed', '$operationType', '$timestamp')";
    
    if ($conn->query($sql) === TRUE) {        
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }    
    $conn->close();
}


// Function to display the decryption result table
function displayDecryptionResult($cipherText, $decryptedText) {
    echo '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Decryption Results</title>
<link rel="stylesheet" href="CSS/style.css">
</head>
<body>
<table class="encryption-table">
    <tr>
        <th>Encrypted Text</th>
        <th>Decrypted Text</th>
    </tr>
    <tr>
        <td>' . htmlspecialchars($cipherText) . '</td>
        <td>' . htmlspecialchars($decryptedText) . '</td>
    </tr>
</table>
<div class="home-btn">
    <a href="home.php">Home</a>
</div>
</body>
</html>';
}
