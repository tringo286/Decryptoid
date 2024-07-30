<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Encrypt-Decrypt Form</title>
<link rel="stylesheet" href="CSS/style.css">
<script>
    function validateFormEncrypt() {
        var inputText = document.getElementById('inputTextEncrypt').value;
        var inputFile = document.getElementById('inputFileEncrypt').value;
        var fileExtension = inputFile.split('.').pop().toLowerCase();
        if (inputText.trim() === '' && inputFile.trim() === '') {
            alert('Please enter text or upload a file for encryption.');
            return false;
        }
        if (inputText !== '' && inputFile !== '') {
            alert('Please choose either input text or upload a file, not both, for encryption.');
            return false;
        }
        if (inputFile !== '') {
            if (fileExtension !== 'txt') {
                alert('Please upload a .txt file for encryption.');
                return false;
            }
        }
        return true;
    }

    function validateFormDecrypt() {
        var inputText = document.getElementById('inputTextDecrypt').value;
        var inputFile = document.getElementById('inputFileDecrypt').value;
        var fileExtension = inputFile.split('.').pop().toLowerCase();
        if (inputText.trim() === '' && inputFile.trim() === '') {
            alert('Please enter text or upload a file for decryption.');
            return false;
        }
        if (inputText !== '' && inputFile !== '') {
            alert('Please choose either input text or upload a file, not both, for decryption.');
            return false;
        }
        if (inputFile !== '') {
            if (fileExtension !== 'txt') {
                alert('Please upload a .txt file for decryption.');
                return false;
            }
        }
        return true;
    }
</script>
</head>
<body>
<div class="container">
    <div class="logout-btn">
            <a href="logout.php">Logout</a>
    </div>
    <div class="form-container">
        <h2>Encryption</h2>        
        <form id="encryptForm" action="encrypt.php" method="post" enctype="multipart/form-data" onsubmit="return validateFormEncrypt()">
                <div class="form-group">
                    <label for="inputTextEncrypt">Input Text:</label>
                    <textarea id="inputTextEncrypt" name="inputTextEncrypt" rows="5" placeholder="Enter plain text to be Encrypted"></textarea>
                </div>
                <div class="form-group">
                    <label for="inputFileEncrypt">Upload File (.txt only):</label>
                    <input type="file" id="inputFileEncrypt" name="inputFileEncrypt" accept=".txt">
                </div>
                <div class="form-group">
                    <label for="cipherSelectEncrypt">Select Cipher:</label>
                    <select id="cipherSelectEncrypt" name="cipherSelectEncrypt">
                        <option value="simpleSubstitution">Simple Substitution</option>
                        <option value="doubleTransposition">Double Transposition</option>
                        <option value="rc4">RC4</option>
                    </select>
                </div>
                <button type="submit" name="encryptBtn">Encrypt</button>
            </form>
    </div>
    <div class="form-container">
        <h2>Decryption</h2>
        <form action="decrypt.php" method="post" enctype="multipart/form-data" onsubmit="return validateFormDecrypt()">
            <div class="form-group">
                <label for="inputTextDecrypt">Input Text:</label>
                <textarea id="inputTextDecrypt" name="inputTextDecrypt" rows="5" placeholder="Enter plain text to be Descrypted" ></textarea>
            </div>
            <div class="form-group">
                <label for="inputFileDecrypt">Upload File (.txt only):</label>
                <input type="file" id="inputFileDecrypt" name="inputFileDecrypt" accept=".txt">
            </div>
            <div class="form-group">
                <label for="cipherSelectDecrypt">Select Cipher:</label>
                <select id="cipherSelectDecrypt" name="cipherSelectDecrypt">
                    <option value="simpleSubstitution">Simple Substitution</option>
                    <option value="doubleTransposition">Double Transposition</option>
                    <option value="rc4">RC4</option>
                </select>
            </div>
            <button type="submit" name="decryptBtn">Decrypt</button>
        </form>
    </div>
</div>
</body>
</html>
