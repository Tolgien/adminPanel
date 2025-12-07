// assets/js/main.js

// Global değişkenler
let API_BASE_URL = 'api/';

// Hata mesajı göster
function showError(message) {
    alert('Hata: ' + message);
}

// Başarı mesajı göster
function showSuccess(message) {
    alert('Başarılı: ' + message);
}

// Loading göster/gizle
function showLoading(element) {
    $(element).html('<div class="loading"><div class="loading-spinner"></div></div>');
}

// Tarih formatı
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('tr-TR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Para formatı
function formatCurrency(amount) {
    return '₺' + parseFloat(amount).toLocaleString('tr-TR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Modal aç/kapat
function openModal(modalId) {
    $('#' + modalId).removeClass('hidden');
}

function closeModal(modalId) {
    $('#' + modalId).addClass('hidden');
}

// Form verilerini serialize et
function serializeForm(formId) {
    const formData = {};
    $('#' + formId + ' :input').each(function() {
        if(this.name) {
            formData[this.name] = $(this).val();
        }
    });
    return formData;
}

// AJAX POST isteği
function ajaxPost(url, data, callback) {
    $.ajax({
        url: API_BASE_URL + url,
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                callback(response);
            } else {
                showError(response.message);
            }
        },
        error: function(xhr) {
            showError('Sunucu hatası: ' + xhr.status);
        }
    });
}

// AJAX GET isteği
function ajaxGet(url, data, callback) {
    $.ajax({
        url: API_BASE_URL + url,
        type: 'GET',
        data: data,
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                callback(response);
            } else {
                showError(response.message);
            }
        },
        error: function(xhr) {
            showError('Sunucu hatası: ' + xhr.status);
        }
    });
}