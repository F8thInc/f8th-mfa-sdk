# F8th MFA PHP SDK Documentation
[![Copyright](https://img.shields.io/badge/Copyright-F8th_Inc.-yellow.svg?style=plastic)](https://f8th.ai/doc/licenses/copyright/LICENSE)
![Version](https://img.shields.io/badge/Version-2.0.01-blue.svg?style=plastic)  

## <a id="how-to-install"></a>How to Install?
1. Download the SDK Package 
2. Move the SDK package (the folder `f8th` and its content) into the source of your project  
3. Fill out the `config.php` file with the information provided. See [How to Setup the Configuration File](#how-to-setup-the-configuration-file) for more details  
4. Include the SDK package in the in the beginning of your code then create the `F8th` object. See [How to Add the SDK](#how-to-add-the-sdk) for more details
5. Include the F8th JavaScript library into your code including the session ID requested when the `F8th` object has been created. See [How to Add the JavaScript Library](#how-to-add-the-javascript-library) for more details

## <a id="how-to-setup-the-configuration-file"></a>How to Setup the Configuration File
* **$f8thMfaIsEnabled**: Set to `true` by default. Set the value to `false` in order to disable F8th MFA (see the [Effects of isEnabled Set to False](#effects-of-isenabled-set-to-false))
* **$f8thMfaApiKey**: Insert the API key  
*Example: `xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`*
* **$f8thMfaApiUrl**: Insert the API endpoint provided  
*Example: `https://demo-api.cluster-01.f8th.cloud/`*
* **$f8thMfaCdnUrl**: Insert the CDN endpoint provided  
*Example: `https://demo-cdn.cluster-01.f8th.cloud/`*
* **$f8thMfaBranchId**: Optionally, can be provided in order to keep the behavioral biometrics separate from other branches

## <a id="how-to-add-the-sdk"></a>How to Add the SDK
1. Include F8th SDK at the beginning of your code by pointing out the file `f8th.php` into the folder `f8th` as below
   ```php
   include_once('./f8th/f8th.php');
   ```
2. Create the `F8th` object by passing the user ID if the user is already authenticated as below
   ```php
   // example without an user logged in
   $f8th = new F8th();

   // example with an user logged in
   $f8th = new F8th($_SESSION['your_user_id_variable']);
   ```

**Important:**  
Including F8th SDK will run the `session_start()` function, so if you use this function already, use one of these options:

1. verify if the `session_status()` is active before to use `session_start()` as below
    ```php
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    ```
2. include F8th SDK after you use `session_start()` as below
    ```php
    session_start();
    $f8th = new F8th();
    ```

## <a id="how-to-add-the-javascript-library"></a>How to Add the JavaScript Library
Add an HTML element `script` to your HTML element `head` including the `$f8th->cdnUrl()` in the `src` value and `$f8th->sessionId()` as value of the `sid` URL parameter such as below.
```html
<script src="<?= $f8th->cdnUrl() ?>f8th.js?sid=<?= $f8th->sessionId() ?>"></script>
```
### Key Mapping Url Parameter
If the URL parameter `km` is not set every HTML elements `input[type=password]` will be key mapped. Which means, no pressed key code will be recorded from these HTML elements.  

If you would like to customize the URL parameter `km` to protect sensitive information such as credit card or SIN number, you will have to set the value with an URL encoded string that represents a list of HTML elements that match the specified group of selectors (this string must be a valid CSS selector string).

**Important:**  
If you set a value and want to keep key mapping the HTML elements `input[type=password]`, add `%2Cinput%5Btype%3Dpassword%5D` at the end of your string. Which is the value of `input[type=password]` URL encoded.

**Example:**  
Below is an example of a key mapping implemented into the HTML elements with the class name `f8th` and the HTML elements `input[type=password]`.  
```html
<script src="<?= $f8th->cdnUrl() ?>f8th.js?sid=<?= $f8th->sessionId() ?>&km=.f8th%2Cinput%5Btype%3Dpassword%5D"></script>
```

## Web Page Example
Follow the step 1 to 3 of [How to Install](#how-to-install), then create a `.php` file and copy and past the code below.

See this [link](example/web-page/example-01) for the source files.
```php
<?php
    // including F8th MFA SDK
    include_once('f8th/f8th.php');
    // creating the F8th object
    $f8th = new F8th();
?>
<!doctype html>
<html lang="en">

<head>
    <script src="<?= $f8th->cdnUrl() ?>f8th.js?sid=<?= $f8th->sessionId() ?>"></script>
    <title>F8th MFA PHP SDK Example 01</title>
</head>

<body>
    Session ID: <?= $f8th->sessionId(); ?>
</body>

</html>
```

## F8th MFA Methods

### **`cdnUrl()`**
Return the Content Delivery Network URL.

**Output:** (`string`): Content Delivery Network URL

### **`sessionId()`**
Return the Session ID requested when creating the `F8th` object.

**Output:** (`int`): Session ID requested

### **`setUserId(userId = null)`**
Sets the `userId`

**Input:**  

* **userId** (`string`): Unique identification of the user

### **`authCheck(policy, userId = null)`**
Detects multiple risks of fraud and account take over. A policy must be provided as an associative array (see schema [AuthCheckScore](#auth-check-score) for the policy risk types options). The policy risk scores sent represent the maximum of acceptable risk. Which means, if the response's risk scores are equal of lower than all the policy risk scores provided, the value of `is_auth` of the response will be `1`.

**Input:**

* **policy**<strong style="color:red">*</strong> (`AuthCheckScore`): Checks the authenticity of the user  
* **userId** (`string`): Unique identification of the user

**Usage Example:**
```php
$authCheck = $f8th->authCheck(
    [
        'authenticity' => 70,
        'web_bot' => 90,
        'insider_threat' => 95,
        'blacklist' => 80
    ],
    $_POST['username']
);

if($authCheck->is_auth) {
    // user is authenticated
} else {
    // user is did not pass the policy requirements
}
```

**Important:**  
The file `config_policy.php` into the folder `f8th` has pre-made policy that can be used as below. Feel free to create your own policies into this file.
```php
$f8th->authCheck($f8thMfaPolicyLogin, $_POST['username'])
```

**Output:**
 
* **is_auth<span style="color:red">*</span>** (`bool`): Checks if the response is valid  
* **score** (`AuthCheckScore`): Response Score   
* **request** (`GetAuthCheck`): Request received by the server   

**Response Example:**
```php
object(stdClass) (3) {
    ["is_auth"] => int(1)
    ["score"] => object(stdClass) (5) {
        ["risk"] => int(12)
        ["authenticity"] => int(39)
        ["web_bot"] => int(14)
        ["insider_threat"] => int(36)
        ["blacklist"] => int(2)
    }
    ["request"] => object(stdClass) (3) {
        ["user_id"] => string(8) "jonathan"
        ["session_id"] => int(4088)
        ["policy"] => object(stdClass) (5) {
            ["risk"] => NULL
            ["authenticity"] => int(70)
            ["web_bot"] => int(90)
            ["insider_threat"] => int(95)
            ["blacklist"] => int(80)
        }
    }
}
``` 

### **`authConfirm(sessionId = null, userId = null)`**
Confirms the authentication of an user

**Input:**

* **sessionId** (`int`): Session ID of the confirmation
* **userId** (`string`): Unique identification of the user

**Usage Example:**
```php
$f8th->authConfirm();
```

**Output:**

* **affected_rows<span style="color:red">*</span>** (`int`): The number of row affected   
* **request** (`GetAuthCheck`): Request received by the server   

**Response Example**
```php
object(stdClass) (2) {
    ["affected_rows"] => int(22)
    ["request"] => object(stdClass) (1) {
        "session_id": 294
        "user_id": "jonathan"
    }
}
```  

### **`authCancel(sessionId = null, userId = null)`**
Cancels the authentication of an user

**Input:**

* **sessionId** (`int`): Session ID of the cancellation
* **userId** (`string`): Unique identification of the user

**Usage Example:**
```php
$f8th->authCancel();
```

**Output:**

* **affected_rows<span style="color:red">*</span>** (`int`): The number of row affected   
* **request** (`GetAuthCheck`): Request received by the server   

**Response Example**
```php
object(stdClass) (2) {
    ["affected_rows"] => int(13)
    ["request"] => object(stdClass) (1) {
        "session_id": 27
        "user_id": "jonathan"
    }
}
```  

### **`authReset(userId = null)`**
Resets the behavioral biometric of the user    

**Input:**

* **userId** (`string`): Unique identification of the user

**Usage Example:**
```php
$f8th->authReset();
```

**Output:**

* **affected_rows<span style="color:red">*</span>** (`int`): The number of row affected   
* **request** (`GetAuthCheck`): Request received by the server   

**Response Example**
```php
object(stdClass) (2) {
    ["affected_rows"] => int(356)
    ["request"] => object(stdClass) (1) {
        "user_id": "jonathan"
    }
}
```  

## Schema

### <a id="auth-check-score"></a>AuthCheckScore

* **risk** (`integer`): Risk global based on every others scores
* **authenticity** (`integer`): Risk of account take over
* **web_bot** (`integer`): Risk of web bot actor
* **insider_threat** (`integer`): Risk of an insider threat
* **blacklist** (`integer`): Risk of blacklisted profile 

### GetAuthCheck

* **user_id** (`string`): Unique identification of the user
* **session_id** (`integer`): Session ID requested
* **policy** (`AuthCheckScore`): Policy risk scores associative array

## <a id="effects-of-isenabled-set-to-false"></a>Effects of isEnabled Set to False

This property is often used when developers work in a development environment. When `isEnabled` is set to `false` the F8th MFA is disabled, and:

* the method `sessionId` return `0`, which disable the JavaScript library
* the method `setUserId` is disabled
* the method `authCheck` return `1` as below
  ```php
  object(stdClass) (1) {
    ["is_auth"] => int(1)
  }
  ```
* the method `authReset` return as below
  ```php
  object(stdClass) (1) {
    ["affected_rows"] => int(0)
  }
  ```

## To Keep in Mind 
* You can send API requests only if your IP has been added to the list of allowed IPs to use this API key. It's also possible that there's no IPs assigned to your API key, in which case any IP is allowed to make requests. If you're running into 401 "Unauthorized" error messages, there's a chance your IP isn't on the allow list.
* If you run F8th MFA on your localhost, your IP seen by your local webserver will be different then the one seen by F8th's clouds, which means, the user won't be allowed to send behavioral data to the cloud, and will receive an HTTP error code `422` as response.
