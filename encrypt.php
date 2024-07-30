<?php
// Form submission handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if input text or file upload is selected
    if (!empty($_POST['inputTextEncrypt'])) {
        // Retrieve input text
        $inputTextEncrypt = $_POST['inputTextEncrypt'];
        // Perform encryption based on selected cipher
        if ($_POST['cipherSelectEncrypt'] === 'simpleSubstitution') {
            // Call the simple substitution encryption function
            $encryptedText = simpleSubstitutionEncrypt($inputTextEncrypt);
        } elseif ($_POST['cipherSelectEncrypt'] === 'doubleTransposition') {
            // Call the double transposition encryption function
            $encryptedText = doubleTranspositionEncrypt($inputTextEncrypt);
        } elseif ($_POST['cipherSelectEncrypt'] === 'rc4') {
            // Call the RC4 encryption function with a key
            $key = 'secretkey'; 
            $encryptedText = rc4Encrypt($inputTextEncrypt, $key);
        } else {
            // Handle other ciphers if needed
            echo '<p>Error: Unsupported cipher selected.</p>';
        }
        // Store the encryption record in the database with operation_type 'encryption'
        storeEncryptionRecord($inputTextEncrypt, $_POST['cipherSelectEncrypt'], 'encryption');
        // Display the encryption result table
        displayEncryptionResult($inputTextEncrypt, $encryptedText);
    } elseif (!empty($_FILES['inputFileEncrypt']['tmp_name'])) {
        // Handle file upload encryption
        $uploadedFile = $_FILES['inputFileEncrypt']['tmp_name'];
        $fileContent = file_get_contents($uploadedFile);
        // Perform encryption based on selected cipher
        if ($_POST['cipherSelectEncrypt'] === 'simpleSubstitution') {
            // Call the simple substitution encryption function
            $encryptedText = simpleSubstitutionEncrypt($fileContent);
        } elseif ($_POST['cipherSelectEncrypt'] === 'doubleTransposition') {
            // Call the double transposition encryption function
            $encryptedText = doubleTranspositionEncrypt($fileContent);
        } elseif ($_POST['cipherSelectEncrypt'] === 'rc4') {
            // Call the RC4 encryption function with a key 
            $key = 'secretkey'; 
            $encryptedText = rc4Encrypt($fileContent, $key);
        } else {
            // Handle other ciphers if needed
            echo '<p>Error: Unsupported cipher selected.</p>';
        }
        // Store the encryption record in the database with operation_type 'encryption'
        storeEncryptionRecord($fileContent, $_POST['cipherSelectEncrypt'], 'encryption');
        // Display the encryption result table with the uploaded file content
        displayEncryptionResult($fileContent, $encryptedText); // Use the actual content of the uploaded file
    } else {
        // Handle empty input or file upload
        echo '<p>Error: No input text or file uploaded.</p>';
    }
}


// Function to perform simple substitution encryption
function simpleSubstitutionEncrypt($inputText) {
    // Define the substitution key
    $key = array(
        'a' => 'z', 'b' => 'y', 'c' => 'x', 'd' => 'w', 'e' => 'v',
        'f' => 'u', 'g' => 't', 'h' => 's', 'i' => 'r', 'j' => 'q',
        'k' => 'p', 'l' => 'o', 'm' => 'n', 'n' => 'm', 'o' => 'l',
        'p' => 'k', 'q' => 'j', 'r' => 'i', 's' => 'h', 't' => 'g',
        'u' => 'f', 'v' => 'e', 'w' => 'd', 'x' => 'c', 'y' => 'b',
        'z' => 'a',
    );

    // Convert input text to lowercase for consistent encryption
    $inputText = strtolower($inputText);

    // Encrypt the input text using the substitution key
    $encryptedText = '';
    for ($i = 0; $i < strlen($inputText); $i++) {
        $char = $inputText[$i];
        // Check if the character is a lowercase letter
        if (array_key_exists($char, $key)) {
            // Substitute the character using the key
            $encryptedText .= $key[$char];
        } else {
            // Keep non-alphabetic characters unchanged
            $encryptedText .= $char;
        }
    }

    return $encryptedText;
}

// Function to perform Double Transposition encryption
function doubleTranspositionEncrypt($inputText) {
    // Define the number of rows and columns for transposition
    $rows = 4;
    $columns = ceil(strlen($inputText) / $rows);

    // Pad the input text to fit the matrix size
    $paddedText = str_pad($inputText, $rows * $columns, ' ');

    // Create a matrix for transposition
    $matrix = [];
    for ($i = 0; $i < $rows; $i++) {
        $matrix[] = str_split(substr($paddedText, $i * $columns, $columns));
    }

    // Transpose rows
    $transposedRows = array_map(null, ...$matrix);

    // Transpose columns (performing a second transposition)
    $transposedColumns = [];
    foreach ($transposedRows as $row) {
        $transposedColumns[] = str_split(implode('', $row));
    }

    // Flatten the transposed columns to get the encrypted text
    $encryptedText = '';
    foreach ($transposedColumns as $column) {
        $encryptedText .= implode('', $column);
    }

    return $encryptedText;
}

// Function to perform RC4 encryption and return lowercase hexadecimal representation
function rc4Encrypt($inputText, $key) {
    $s = range(0, 255);
    $j = 0;
    $key = array_values(unpack('C*', $key));
    $inputText = array_values(unpack('C*', $inputText));
    $len = count($inputText);
    $output = [];

    for ($i = 0; $i < 256; $i++) {
        $j = ($j + $s[$i] + $key[$i % count($key)]) % 256;
        [$s[$i], $s[$j]] = [$s[$j], $s[$i]];
    }

    $i = $j = 0;
    for ($k = 0; $k < $len; $k++) {
        $i = ($i + 1) % 256;
        $j = ($j + $s[$i]) % 256;
        [$s[$i], $s[$j]] = [$s[$j], $s[$i]];
        $output[] = $inputText[$k] ^ $s[($s[$i] + $s[$j]) % 256];
    }

    // Convert the output to lowercase hexadecimal representation
    $hexOutput = '';
    foreach ($output as $byte) {
        $hexOutput .= sprintf("%02x", $byte); // Use %02x for lowercase letters
    }

    return $hexOutput;
}


function storeEncryptionRecord($inputText, $cipherUsed, $operationType) {
    session_start();
    require_once 'db_connection.php'; // Include the database connection file
    
    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $timestamp = date('Y-m-d H:i:s');
    $username = $_SESSION['username'];
    
    $sql = "INSERT INTO user_inputs (username, input_text, cipher_used, operation_type, created_at) 
            VALUES ('$username', '$inputText', '$cipherUsed', '$operationType', '$timestamp')";
    
    if ($conn->query($sql) === TRUE) {
        // Record inserted successfully
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}


// Function to display the encryption result table
function displayEncryptionResult($plainText, $encryptedText) {
    echo '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Encryption Results</title>
<link rel="stylesheet" href="CSS/style.css">
</head>
<body>
<table class="encryption-table">
    <tr>
        <th>Input</th>
        <th>Output</th>
    </tr>
    <tr>
        <td>' . htmlspecialchars($plainText) . '</td>
        <td>' . htmlspecialchars($encryptedText) . '</td>
    </tr>
</table>
<div class="home-btn">
    <a href="home.php">Home</a>
</div>
</body>
</html>';
}
?>