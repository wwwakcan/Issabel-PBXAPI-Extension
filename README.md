# Issabel PbxApi Documentation V2

## 🇹🇷 Türkçe

### Genel Bakış
IssabelPbxApi, Issabel PBX sistemleri için geliştirilmiş Laravel tabanlı bir API wrapper'dır. Standart PBX API'sinde bulunmayan çeşitli gelişmiş özellikleri içerir ve PBX sisteminizle kolay entegrasyon sağlar.

### Kurulum
```php
use App\Models\IssabelPbxApi;

$pbx = new IssabelPbxApi($server, $username, $password);
// veya
$pbx = IssabelPbxApi::connect($server, $username, $password);
```

### Özellikler

#### 1. Dahili Hat İşlemleri
- `extensions()`: Tüm dahili hatların listesini getirir
- `extension($extension)`: Belirli bir dahili hattın detaylı bilgilerini getirir

#### 2. Çağrı İzleme ve Yönetimi
- `channels()`: Aktif çağrı kanallarını listeler
- `originate()`: İki numara arasında çağrı başlatır
  ```php
  $pbx->originate(
      $channel = "SIP/XXXXXX", 
      $extension = "SIP/XXXXXXX",
      $callerID = "Başlık",
      $context = "from-internal",
      $timeout = "30000"
  );
  ```
- `spyCall()`: Aktif bir çağrıyı dinleme özelliği
  ```php
  $pbx->spyCall(
      $channel = "SIP/XXXXXX",
      $extension = "SIP/XXXXXXX",
      $listenMode = "q",
      $callerID = "Dinleme"
  );
  ```

#### 3. Çağrı Kayıtları (CDR)
- `cdr()`: Çağrı kayıtlarını sorgulama
  ```php
  $pbx->cdr(
      $startDate = "2024-01-01",
      $endDate = "2024-01-20",
      $filter = "all"
  );
  ```
- `cdrPlayer()`: Çağrı kayıtlarını dinleme
  ```php
  $pbx->cdrPlayer($cdrFile);
  ```

---

## 🇬🇧 English

### Overview
IssabelPbxApi is a Laravel-based API wrapper for Issabel PBX systems. It includes various advanced features not available in the standard PBX API and provides easy integration with your PBX system.

### Installation
```php
use App\Models\IssabelPbxApi;

$pbx = new IssabelPbxApi($server, $username, $password);
// or
$pbx = IssabelPbxApi::connect($server, $username, $password);
```

### Features

#### 1. Extension Management
- `extensions()`: Retrieves a list of all extensions
- `extension($extension)`: Gets detailed information about a specific extension

#### 2. Call Monitoring and Management
- `channels()`: Lists active call channels
- `originate()`: Initiates a call between two numbers
  ```php
  $pbx->originate(
      $channel = "SIP/XXXXXX", 
      $extension = "SIP/XXXXXXX",
      $callerID = "Title",
      $context = "from-internal",
      $timeout = "30000"
  );
  ```
- `spyCall()`: Enables call monitoring feature
  ```php
  $pbx->spyCall(
      $channel = "SIP/XXXXXX",
      $extension = "SIP/XXXXXXX",
      $listenMode = "q",
      $callerID = "Monitoring"
  );
  ```

#### 3. Call Detail Records (CDR)
- `cdr()`: Query call records
  ```php
  $pbx->cdr(
      $startDate = "2024-01-01",
      $endDate = "2024-01-20",
      $filter = "all"
  );
  ```
- `cdrPlayer()`: Listen to call recordings
  ```php
  $pbx->cdrPlayer($cdrFile);
  ```

### Error Handling
The library includes comprehensive error handling for API connections and requests. All methods may throw `ConnectionException` or general exceptions with detailed error messages.

### Authentication
Authentication is handled automatically through the `getToken()` method, which manages API tokens for secure communication with the PBX system.



------------


# IssabelPbxApi Documentation

## 🇹🇷 Türkçe

### Genel Bakış
IssabelPbxApi, Issabel PBX sistemleri için geliştirilmiş Laravel tabanlı bir API wrapper'dır. Standart PBX API'sinde bulunmayan çeşitli gelişmiş özellikleri içerir ve PBX sisteminizle kolay entegrasyon sağlar.

### Kurulum Adımları

1. Laravel projenize IssabelPbxApi sınıfını ekleyin.

2. Issabel sunucunuzda kurulum:
   - `/var/www/html/pbxapi/controllers` dizinine gidin
   - Bu dizine `v2apiservice.php` dosyasını aşağıdaki içerikle oluşturun:

### V2ApiService Özellikleri

1. Çağrı Kayıtları (CDR)
   - Tarih aralığına göre filtreleme
   - Dahili numaraya göre filtreleme
   - Detaylı çağrı bilgileri (süre, durum, kayıt dosyası vb.)

2. Aktif Kanal İzleme
   - Anlık kanal durumları
   - Kanal detayları (dahili no, konum, durum)
   - SIP/MMT-Out kanalları hariç tutma

3. Ses Kayıtları Oynatıcı
   - Kayıt dosyalarına erişim
   - Streaming desteği
   - İndirilebilir format

## 🇬🇧 English

### Overview
IssabelPbxApi is a Laravel-based API wrapper for Issabel PBX systems. It includes various advanced features not available in the standard PBX API and provides easy integration with your PBX system.

### Installation Steps

1. Add the IssabelPbxApi class to your Laravel project

2. On your Issabel server:
   - Navigate to `/var/www/html/pbxapi/controllers` directory
   - Create `v2apiservice.php` file with the content shown above

### V2ApiService Features

1. Call Detail Records (CDR)
   - Date range filtering
   - Extension-based filtering
   - Detailed call information (duration, disposition, recording file, etc.)

2. Active Channel Monitoring
   - Real-time channel status
   - Channel details (extension, location, status)
   - Excludes SIP/MMT-Out channels

3. Recording Player
   - Access to recording files
   - Streaming support
   - Downloadable format

[... rest of the English documentation remains the same ...]
