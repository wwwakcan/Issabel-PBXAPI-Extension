# Issabel PBXAPI Extension Documentation V2

English | Türkçe

Enhanced API extension for Issabel PBX systems, adding advanced features like call origination, monitoring, CDR access, and channel management.

## 🚀 Features

- **Extended API Capabilities**
  - Call origination between extensions
  - Real-time call monitoring (spy call)
  - Active channel monitoring
  - Detailed CDR (Call Detail Records) access
  - Call recording playback
  - Extension details and management

## 📋 Requirements

- Issabel PBX System
- PHP 7.4 or higher
- Laravel 8.x or higher (for Laravel integration)
- Asterisk with proper configurations

## ⚙️ Installation

### 1. Server-side Installation (v2apiservice.php)
- Add `v2apiservice.php` to your Issabel PBX system in:
```
/var/www/html/pbxapi/controllers/v2apiservice.php
```

### 2. Laravel Integration (IssabelPbxApi.php)
- Add `IssabelPbxApi.php` to your Laravel project in:
```
app/Models/IssabelPbxApi.php
```

## 🔧 Configuration

```php
use App\Models\IssabelPbxApi;

// Initialize the API
$pbx = new IssabelPbxApi($server, $username, $password);

// or use the static connection method
$pbx = IssabelPbxApi::connect($server, $username, $password);
```

## 📱 Usage Examples

### Extension Management
```php
// Get all extensions
$extensions = $pbx->extensions();

// Get specific extension details
$extension = $pbx->extension('1001');
```

### Call Management
```php
// Initiate a call
$pbx->originate(
    $channel = "SIP/1001",
    $extension = "SIP/1002",
    $callerID = "Internal Call",
    $context = "from-internal",
    $timeout = "30000"
);

// Monitor active channels
$channels = $pbx->channels();

// Spy on an active call
$pbx->spyCall(
    $channel = "SIP/1001",
    $extension = "SIP/1002",
    $listenMode = "q",
    $callerID = "Monitor"
);
```

### CDR Access
```php
// Get call records
$cdr = $pbx->cdr(
    $startDate = "2024-01-01",
    $endDate = "2024-01-31",
    $filter = "all"
);

// Access call recording
$recording = $pbx->cdrPlayer($cdrFile);
```

---

Issabel PBX sistemleri için geliştirilmiş, çağrı başlatma, izleme, CDR erişimi ve kanal yönetimi gibi gelişmiş özellikler sunan API uzantısı.

## 🚀 Özellikler

- **Gelişmiş API Özellikleri**
  - Dahili hatlar arası çağrı başlatma
  - Gerçek zamanlı çağrı izleme (spy call)
  - Aktif kanal izleme
  - Detaylı CDR (Çağrı Detay Kayıtları) erişimi
  - Çağrı kayıtlarını dinleme
  - Dahili hat detayları ve yönetimi

## 📋 Gereksinimler

- Issabel PBX Sistemi
- PHP 7.4 veya üzeri
- Laravel 8.x veya üzeri (Laravel entegrasyonu için)
- Asterisk (uygun konfigürasyonlarla)

## ⚙️ Kurulum

### 1. Sunucu Tarafı Kurulum (v2apiservice.php)
- `v2apiservice.php` dosyasını Issabel PBX sisteminizde şu konuma ekleyin:
```
/var/www/html/pbxapi/controllers/v2apiservice.php
```

### 2. Laravel Entegrasyonu (IssabelPbxApi.php)
- `IssabelPbxApi.php` dosyasını Laravel projenizde şu konuma ekleyin:
```
app/Models/IssabelPbxApi.php
```

## 🔧 Yapılandırma

```php
use App\Models\IssabelPbxApi;

// API'yi başlatma
$pbx = new IssabelPbxApi($server, $username, $password);

// veya statik bağlantı metodunu kullanma
$pbx = IssabelPbxApi::connect($server, $username, $password);
```

## 📱 Kullanım Örnekleri

### Dahili Hat Yönetimi
```php
// Tüm dahili hatları getir
$extensions = $pbx->extensions();

// Belirli bir dahili hattın detaylarını getir
$extension = $pbx->extension('1001');
```

### Çağrı Yönetimi
```php
// Çağrı başlatma
$pbx->originate(
    $channel = "SIP/1001",
    $extension = "SIP/1002",
    $callerID = "İç Hat Araması",
    $context = "from-internal",
    $timeout = "30000"
);

// Aktif kanalları izleme
$channels = $pbx->channels();

// Aktif çağrıyı dinleme
$pbx->spyCall(
    $channel = "SIP/1001",
    $extension = "SIP/1002",
    $listenMode = "q",
    $callerID = "İzleme"
);
```

### CDR Erişimi
```php
// Çağrı kayıtlarını getir
$cdr = $pbx->cdr(
    $startDate = "2024-01-01",
    $endDate = "2024-01-31",
    $filter = "all"
);

// Çağrı kaydını dinleme
$recording = $pbx->cdrPlayer($cdrFile);
```

## 📝 Önemli Notlar

- Kurulum öncesi sisteminizi yedekleyin
- API erişimlerini güvenlik duvarı ile koruyun
- Sistemi düzenli olarak güncelleyin


 #API Documentatio

## Postman Collection Documentation

### Authentication
**Endpoint:** `POST {{server}}/pbxapi/authenticate`
- Authenticates user and returns access token
- Required Parameters:
  - `username`: PBX Admin Username
  - `password`: PBX Admin Password
- Response includes `access_token` used for subsequent requests

### Extension Management
1. **Get All Extensions**
   - Endpoint: `GET {{server}}/pbxapi/extensions`
   - Authentication: Bearer Token
   - Returns list of all extensions

2. **Get Extension Details**
   - Endpoint: `GET {{server}}/pbxapi/extensions/{extension_number}`
   - Authentication: Bearer Token
   - Returns detailed information about specific extension

### Call Management
1. **Active Channels**
   - Endpoint: `GET {{server}}/pbxapi/v2apiservice`
   - Query Parameters:
     - `action`: "channels"
   - Returns list of active calls/channels

2. **Call Origination**
   - Endpoint: `GET {{server}}/pbxapi/manager/originate`
   - Parameters:
     - `channel`: Caller extension (e.g., "SIP/90002")
     - `extension`: Target number
     - `context`: Call context (default: "from-internal")
     - `timeout`: Answer timeout in ms
     - `callerid`: Caller ID display name
     - `priority`: Priority level (default: 1)

3. **Call Monitoring (SpyCall)**
   - Endpoint: `GET {{server}}/pbxapi/manager/originate`
   - Parameters:
     - `channel`: Listener extension
     - `application`: "ChanSpy"
     - `data`: Target extension + mode (e.g., "SIP/8003,q")
     - `callerid`: Display name for listener

### CDR (Call Detail Records)
1. **Get CDR Records**
   - Endpoint: `GET {{server}}/pbxapi/v2apiservice`
   - Query Parameters:
     - `action`: "cdr"
     - `start_date`: Start date (YYYY-MM-DD)
     - `end_date`: End date (YYYY-MM-DD)
     - `extension`: Extension number or "all"

2. **CDR Recording Player**
   - Endpoint: `GET {{server}}/pbxapi/v2apiservice`
   - Query Parameters:
     - `action`: "player"
     - `file`: Recording file path

## Environment Variables
- `server`: PBX server URL
- `token`: Authentication token (auto-set after auth)

---
 
## Postman Koleksiyonu Dokümantasyonu

### Kimlik Doğrulama
**Endpoint:** `POST {{server}}/pbxapi/authenticate`
- Kullanıcıyı doğrular ve erişim tokeni döndürür
- Gerekli Parametreler:
  - `username`: PBX Admin Kullanıcı Adı
  - `password`: PBX Admin Şifresi
- Yanıt, sonraki isteklerde kullanılacak `access_token` içerir

### Dahili Hat Yönetimi
1. **Tüm Dahili Hatları Listele**
   - Endpoint: `GET {{server}}/pbxapi/extensions`
   - Kimlik Doğrulama: Bearer Token
   - Tüm dahili hatların listesini döndürür

2. **Dahili Hat Detayları**
   - Endpoint: `GET {{server}}/pbxapi/extensions/{dahili_no}`
   - Kimlik Doğrulama: Bearer Token
   - Belirli bir dahili hattın detaylı bilgilerini döndürür

### Çağrı Yönetimi
1. **Aktif Kanallar**
   - Endpoint: `GET {{server}}/pbxapi/v2apiservice`
   - Sorgu Parametreleri:
     - `action`: "channels"
   - Aktif çağrıları/kanalları listeler

2. **Çağrı Başlatma**
   - Endpoint: `GET {{server}}/pbxapi/manager/originate`
   - Parametreler:
     - `channel`: Arayan dahili (örn: "SIP/90002")
     - `extension`: Hedef numara
     - `context`: Çağrı bağlamı (varsayılan: "from-internal")
     - `timeout`: Cevaplama zaman aşımı (ms)
     - `callerid`: Arayan adı
     - `priority`: Öncelik seviyesi (varsayılan: 1)

3. **Çağrı İzleme (SpyCall)**
   - Endpoint: `GET {{server}}/pbxapi/manager/originate`
   - Parametreler:
     - `channel`: Dinleyici dahili
     - `application`: "ChanSpy"
     - `data`: Hedef dahili + mod (örn: "SIP/8003,q")
     - `callerid`: Dinleyici için görünen isim

### CDR (Çağrı Detay Kayıtları)
1. **CDR Kayıtları**
   - Endpoint: `GET {{server}}/pbxapi/v2apiservice`
   - Sorgu Parametreleri:
     - `action`: "cdr"
     - `start_date`: Başlangıç tarihi (YYYY-MM-DD)
     - `end_date`: Bitiş tarihi (YYYY-MM-DD)
     - `extension`: Dahili numara veya "all"

2. **CDR Kayıt Oynatıcı**
   - Endpoint: `GET {{server}}/pbxapi/v2apiservice`
   - Sorgu Parametreleri:
     - `action`: "player"
     - `file`: Kayıt dosyası yolu

## Ortam Değişkenleri
- `server`: PBX sunucu URL'si
- `token`: Kimlik doğrulama tokeni (auth sonrası otomatik ayarlanır)
