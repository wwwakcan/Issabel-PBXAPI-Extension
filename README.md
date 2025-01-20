# Issabel PBXAPI Extension Documentation V2

English | Türkçe

Enhanced API extension for Issabel PBX systems, adding advanced features like call origination, monitoring, CDR access, and channel management.

POSTMAN 

https://documenter.getpostman.com/view/14352549/2sAYQcEVeB

---

## 🚀 Features

- **Extended API Capabilities**
  - Call origination between extensions
  - Real-time call monitoring (spy call)
  - Active channel monitoring
  - Detailed CDR (Call Detail Records) access
  - Call recording playback
  - Extension details and management

---

## 🗃️ Requirements

- Issabel PBX System
- PHP 7.4 or higher
- Laravel 8.x or higher (for Laravel integration)
- Asterisk with proper configurations

---

## ⚙️ Installation

### 1. Server-side Installation (`v2apiservice.php`)
- Add `v2apiservice.php` to your Issabel PBX system:
  ```
  /var/www/html/pbxapi/controllers/v2apiservice.php
  ```

### 2. Laravel Integration (`IssabelPbxApi.php`)
- Add `IssabelPbxApi.php` to your Laravel project:
  ```
  app/Models/IssabelPbxApi.php
  ```

---

## ⚖️ Configuration

```php
use App\Models\IssabelPbxApi;

// Initialize the API
$pbx = new IssabelPbxApi($server, $username, $password);

// Or use the static connection method
$pbx = IssabelPbxApi::connect($server, $username, $password);
```

---

## 📱 Usage Examples

### Extension Management

#### Get All Extensions
```php
$extensions = $pbx->extensions();
```

#### Get Specific Extension Details
```php
$extension = $pbx->extension('1001');
```

### Call Management

#### Initiate a Call
```php
$pbx->originate(
    $channel = "SIP/1001",
    $extension = "SIP/1002",
    $callerID = "Internal Call",
    $context = "from-internal",
    $timeout = "30000"
);
```

#### Monitor Active Channels
```php
$channels = $pbx->channels();
```

#### Spy on an Active Call
```php
$pbx->spyCall(
    $channel = "SIP/1001",
    $extension = "SIP/1002",
    $listenMode = "q",
    $callerID = "Monitor"
);
```

### CDR Access

#### Get Call Records
```php
$cdr = $pbx->cdr(
    $startDate = "2024-01-01",
    $endDate = "2024-01-31",
    $filter = "all"
);
```

#### Access Call Recording
```php
$recording = $pbx->cdrPlayer($cdrFile);
```

---

## 🗋 Postman Collection Documentation

### Authentication

**Endpoint:** `POST {{server}}/pbxapi/authenticate`

- **Description:** Authenticates user and returns access token
- **Required Parameters:**
  - `username`: PBX Admin Username
  - `password`: PBX Admin Password
- **Response:** Includes `access_token` used for subsequent requests

### Extension Management

#### Get All Extensions
- **Endpoint:** `GET {{server}}/pbxapi/extensions`
- **Authentication:** Bearer Token
- **Response:** List of all extensions

#### Get Extension Details
- **Endpoint:** `GET {{server}}/pbxapi/extensions/{extension_number}`
- **Authentication:** Bearer Token
- **Response:** Detailed information about the specific extension

### Call Management

#### Active Channels
- **Endpoint:** `GET {{server}}/pbxapi/v2apiservice`
- **Query Parameters:**
  - `action`: "channels"
- **Response:** List of active calls/channels

#### Call Origination
- **Endpoint:** `GET {{server}}/pbxapi/manager/originate`
- **Parameters:**
  - `channel`: Caller extension (e.g., "SIP/90002")
  - `extension`: Target number
  - `context`: Call context (default: "from-internal")
  - `timeout`: Answer timeout in ms
  - `callerid`: Caller ID display name
  - `priority`: Priority level (default: 1)

#### Call Monitoring (SpyCall)
- **Endpoint:** `GET {{server}}/pbxapi/manager/originate`
- **Parameters:**
  - `channel`: Listener extension
  - `application`: "ChanSpy"
  - `data`: Target extension + mode (e.g., "SIP/8003,q")
  - `callerid`: Display name for listener

### CDR (Call Detail Records)

#### Get CDR Records
- **Endpoint:** `GET {{server}}/pbxapi/v2apiservice`
- **Query Parameters:**
  - `action`: "cdr"
  - `start_date`: Start date (YYYY-MM-DD)
  - `end_date`: End date (YYYY-MM-DD)
  - `extension`: Extension number or "all"

#### CDR Recording Player
- **Endpoint:** `GET {{server}}/pbxapi/v2apiservice`
- **Query Parameters:**
  - `action`: "player"
  - `file`: Recording file path

---

## 🛠️ Important Notes

- Backup your system before installation
- Secure API access with a firewall
- Keep the system updated regularly

