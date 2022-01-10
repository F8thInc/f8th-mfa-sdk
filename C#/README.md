# F8th C# SDK Documenttation
[![Copyright](https://img.shields.io/badge/Copyright-F8th_Inc.-yellow.svg?style=plastic)](https://f8th.ai/doc/licenses/copyright/LICENSE)
![Version](https://img.shields.io/badge/Version-2.0.01-blue.svg?style=plastic) 

## How to Install?
1. Download the [DLL]()
2. Extract the `.zip` file
3. Reference the DLL in the source of your project

## How to Use the DLL
Create the `F8th` object by passing the user ID if the user is already authenticated as below. You should instantiate the F8th object as soon as possible, this allows the inputs of the user to be tracked so when you make a request for a prediction, it can check those inputs to see whether the current user is who they claim to be. When you are ready to make a prediction, use the AuthCheck function.
   ```C#
    //example without an user logged in
    F8th f8th = new F8th(bool isEnabled, string apiKey, string apiUrl, string branchId);

    //example with an user logged in
    F8th f8th = new F8th(bool isEnabled, string apiKey, string apiUrl, string branchId, string userId);
   ```

 ## A Word on the Data Collector
The data collector when running will track the mouse inputs and keyboard inputs of the user. However, we do not track what keys the user is pressing. In other words, passwords and other sensitive data will not be stored in our database. We keep track of the way the user uses their computer, but we make sure there's no way the data can be used to identify the data the user is actually inputting.

## F8th MFA Methods

### **`SessionId()`**
Return the Session ID requested when creating the `F8th` object.

**Output:** (`int`): Session ID requested

### **`SetUserId(string userId = null)`**
Sets the `userId`

**Input:**  

* **UserId** (`string`): Unique identification of the user

### **`AuthCheck(Dictionary<string, string> policy, string userId = null)`**
Detects multiple risks of fraud and account take over. A policy must be provided as a dictionary (see schema [AuthCheckScore](#auth-check-score) for the policy risk types options). The policy risk scores sent represent the maximum of acceptable risk. Which means, if the response's risk scores are equal of lower than all the policy risk scores provided, the value of `is_auth` of the response will be `1`.

**Input:**

* **policy**<strong style="color:red">*</strong> (`AuthCheckScore`): Checks the authenticity of the user  
* **userId** (`string`): Unique identification of the user

**Usage Example:**
```C#
        //instantiate F8th class
    F8th f8th = new F8th();

    //create a policy as dictionary for AuthCheck argument
    Dictionary<string, string> policy = new Dictionary<string, string>();
    policy.Add("risk", "43");
    policy.Add("authenticity", "77");
    policy.Add("web_boot", "50");
    policy.Add("insider_threat", "36");
    policy.Add("blacklist", "94");

    if(f8th.AuthCheck(policy)["is_auth"]) {
    // user is authenticated
} else {
    // user is did not pass the policy requirements
}
```

**Important:**  
You can create your own policies similar to shown below.
```C#
    Dictionary<string, string> policy = new Dictionary<string, string>();
    policy.Add("risk", "100");
```

**Output:**
 
* **is_auth<span style="color:red">*</span>** (`bool`): Checks if the response is valid  
* **score** (`AuthCheckScore`): Response Score   
* **request** (`GetAuthCheck`): Request received by the server   

**Response Example:**
```C#
object (3) {
    ["is_auth"] => int(1)
    ["score"] => object(5) {
        ["risk"] => int(12)
        ["authenticity"] => int(39)
        ["web_bot"] => int(14)
        ["insider_threat"] => int(36)
        ["blacklist"] => int(2)
    }
    ["request"] => object (3) {
        ["user_id"] => string(8) "jonathan"
        ["session_id"] => int(4088)
        ["policy"] => object (5) {
            ["risk"] => NULL
            ["authenticity"] => int(70)
            ["web_bot"] => int(90)
            ["insider_threat"] => int(95)
            ["blacklist"] => int(80)
        }
    }
}
``` 

### **`AuthConfirm(int sessionId = null, string userId = null)`**
Confirms the authentication of an user. If you initially created the F8th object without passing in a user ID, you can use this function to assign a user ID to the session ID after the fact.

**Input:**

* **sessionId** (`int`): Session ID of the confirmation
* **userId** (`string`): Unique identification of the user

**Usage Example:**
```C#
f8th.AuthConfirm();
```

**Output:**

* **affected_rows<span style="color:red">*</span>** (`int`): The number of row affected   
* **request** (`GetAuthCheck`): Request received by the server   

**Response Example**
```C#
object (2) {
    ["affected_rows"] => int(22)
    ["request"] => object (1) {
        "session_id": 294
        "user_id": "jonathan"
    }
}
```  

### **`AuthCancel( int sessionId = null, string userId = null)`**
Cancels the authentication of an user. This will remove the association of the user ID with the session ID.

**Input:**

* **sessionId** (`int`): Session ID of the cancellation
* **userId** (`string`): Unique identification of the user

**Usage Example:**
```C#
F8th.AuthCancel();
```

**Output:**

* **affected_rows<span style="color:red">*</span>** (`int`): The number of row affected   
* **request** (`GetAuthCheck`): Request received by the server   

**Response Example**
```C#
object (2) {
    ["affected_rows"] => int(13)
    ["request"] => object (1) {
        "session_id": 27
        "user_id": "jonathan"
    }
}
```  

### **`AuthReset(string userId = null)`**
Resets the behavioral biometric of the user    

**Input:**

* **userId** (`string`): Unique identification of the user

**Usage Example:**
```C#
F8th.AuthReset();
```

**Output:**

* **affected_rows<span style="color:red">*</span>** (`int`): The number of row affected   
* **request** (`GetAuthCheck`): Request received by the server   

**Response Example**
```C#
object (2) {
    ["affected_rows"] => int(356)
    ["request"] => object (1) {
        "user_id": "jonathan"
    }
}
``` 

## Schema

### AuthCheckScore

* **risk** (`integer`): Risk global based on every others scores
* **authenticity** (`integer`): Risk of account take over
* **web_bot** (`integer`): Risk of web bot actor
* **insider_threat** (`integer`): Risk of an insider threat
* **blacklist** (`integer`): Risk of blacklisted profile 

### AuthCheck

* **user_id** (`string`): Unique identification of the user
* **policy** (`AuthCheckScore`): Policy risk scores associative array

### Effects of isEnable to False

This property is often used when developers work in a development environment. When `isEnabled` is set to `false` the F8th MFA is disabled, and:

* the method `SessionId` return `0`, which disable the data collector
* the method `SetUserId` is disabled
* the method `AuthCheck` return `1` as below
```C#
  f8thRequest.Response["is_auth"] = f8thRequest.Response.ContainsKey("is_auth") ? Convert.ToBoolean(f8thRequest.Response["is_auth"]) : false;
        return f8thRequest.Response;
  ```
* the method `AuthReset` return as below
```C#
   f8thRequest.Response["affected_rows"] = f8thRequest.Response.ContainsKey("affected_rows") ? f8thRequest.Response["affected_rows"] : 0;
        return f8thRequest.Response;
  }
  ```

## To Keep in Mind 
* You can send API requests if: your IP has been added to the list of allowed IPs to use this API key, or your API key has no associated IPs and therefore allows any IP to use that key .

## Implementing F8th MFA Without Using SDK 
API Documentation:
* Docs: [https://demo-api.cluster-01.f8th.cloud/docs](https://demo-api.cluster-01.f8th.cloud/docs)
* Redoc: [https://demo-api.cluster-01.f8th.cloud/redoc](https://demo-api.cluster-01.f8th.cloud/redoc)