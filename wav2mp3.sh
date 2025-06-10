#!/bin/bash

# Kayıt dizini
RECORDING_DIR="/var/spool/asterisk/monitor"

# Dönüştürme işlemi için fonksiyon
convert_wav_to_mp3() {
    # Bugünün tarihini al (yıl, ay, gün formatında)
    YEAR=$(date +"%Y")
    MONTH=$(date +"%m")
    DAY=$(date +"%d")

    # Bugünün klasör yolu
    TODAY_DIR="$RECORDING_DIR/$YEAR/$MONTH/$DAY"

    # Klasör var mı kontrol et
    if [ ! -d "$TODAY_DIR" ]; then
        echo "$(date): Bugün için klasör bulunamadı: $TODAY_DIR"
        return
    fi

    # Son 1 saat içinde oluşturulmuş WAV dosyalarını bul
    echo "$(date): Son 1 saat içinde oluşturulan WAV dosyaları aranıyor..."
    
    # find komutu ile son 60 dakika içinde değiştirilmiş dosyaları bul
    for wavfile in $(find "$TODAY_DIR" -type f -name "*.wav" -mmin -60)
    do
        filename=$(basename "$wavfile")
        dirname=$(dirname "$wavfile")
        basename="${filename%.*}"
        mp3file="$dirname/$basename.mp3"

        # MP3 versiyonu var mı kontrol et
        if [ ! -f "$mp3file" ]; then
            # MP3 dosyası yoksa, oluştur
            ffmpeg -i "$wavfile" -codec:a libmp3lame -qscale:a 2 "$mp3file" -y -loglevel quiet

            # İşlem başarılı ise log'a yaz
            if [ $? -eq 0 ]; then
                echo "$(date): Dönüştürüldü: $wavfile -> $mp3file"
                # Orijinal WAV dosyasını kaldırmak isterseniz yorum işaretini kaldırın
                # rm "$wavfile"
            else
                echo "$(date): Hata: $wavfile dosyası dönüştürülemedi!"
            fi
        else
            echo "$(date): MP3 zaten mevcut, atlanıyor: $mp3file"
        fi
    done
    
    # Kaç dosya işlendiğini bildiren özet
    PROCESSED_COUNT=$(find "$TODAY_DIR" -type f -name "*.wav" -mmin -60 | wc -l)
    echo "$(date): Son 1 saatte toplam $PROCESSED_COUNT WAV dosyası işlendi."
}

# Dönüştürme işlemini başlat
convert_wav_to_mp3
