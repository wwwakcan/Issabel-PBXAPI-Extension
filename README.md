# Issabel PbxApi Documentation V2


# Issabel PBXAPI Extension

[English](#english) | [Türkçe](#turkish)

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
