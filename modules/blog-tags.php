<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$page_title = "Blog Etiketleri";
ob_start();
?>

<div class="container-fluid">
    <!-- Başlık ve Butonlar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Blog Etiketleri</h1>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTagModal">
                <i class="fas fa-plus"></i> Yeni Etiket
            </button>
            <a href="blog.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Yazılara Dön
            </a>
        </div>
    </div>

    <!-- Filtreler -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Etiket Ara</label>
                    <div class="input-group">
                        <input type="text" id="searchTagInput" class="form-control" placeholder="Etiket adı ara...">
                        <button class="btn btn-outline-secondary" type="button" id="searchTagBtn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-secondary w-100" id="resetTagSearch">
                        <i class="fas fa-redo"></i> Sıfırla
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Etiketler Tablosu -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="tagsTable">
                    <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th>Etiket Adı</th>
                            <th>Slug</th>
                            <th>Kullanım Sayısı</th>
                            <th>Oluşturulma Tarihi</th>
                            <th width="100">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="tagsTableBody">
                        <tr id="loadingRow">
                            <td colspan="6" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Yükleniyor...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Yeni Etiket Ekleme Modalı -->
<div class="modal fade" id="addTagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Etiket Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addTagForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Etiket Adı *</label>
                        <input type="text" name="etiket_adi" class="form-control" required>
                        <small class="form-text text-muted" id="tagSlugPreview"></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Silme Onay Modalı -->
<div class="modal fade" id="deleteTagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Etiket Sil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="deleteTagMessage">Bu etiketi silmek istediğinize emin misiniz? Bu işlem geri alınamaz.</p>
                <input type="hidden" id="deleteTagId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-danger" id="confirmTagDelete">Sil</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once '../includes/header.php';
?>

<div class="container-fluid">
    <?php 
    if (file_exists('../includes/sidebar.php')) {
        require_once '../includes/sidebar.php';
    }
    ?>
    
    <main class="main-content">
        <?php echo $content; ?>
    </main>
</div>

<?php 
require_once '../includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // DOM Elementleri
    const tagsTableBody = document.getElementById('tagsTableBody');
    const addTagForm = document.getElementById('addTagForm');
    const tagSlugPreview = document.getElementById('tagSlugPreview');
    const searchTagInput = document.getElementById('searchTagInput');
    const searchTagBtn = document.getElementById('searchTagBtn');
    const resetTagSearch = document.getElementById('resetTagSearch');
    
    // Modal instances
    const addTagModal = new bootstrap.Modal(document.getElementById('addTagModal'));
    const deleteTagModal = new bootstrap.Modal(document.getElementById('deleteTagModal'));
    
    // Arama değişkeni
    let currentSearch = '';
    
    // İlk yükleme
    loadTags();
    
    // Slug preview
    addTagForm.querySelector('[name="etiket_adi"]').addEventListener('input', function() {
        const tagName = this.value;
        const slug = createSlug(tagName);
        tagSlugPreview.textContent = 'Slug: ' + slug;
    });
    
    // Arama
    searchTagBtn.addEventListener('click', function() {
        currentSearch = searchTagInput.value;
        loadTags();
    });
    
    searchTagInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            currentSearch = this.value;
            loadTags();
        }
    });
    
    resetTagSearch.addEventListener('click', function() {
        searchTagInput.value = '';
        currentSearch = '';
        loadTags();
    });
    
    // Etiketleri yükle
    function loadTags() {
        showLoading(true);
        
        let url = '../api/blog/tags.php';
        if (currentSearch) {
            url += `?search=${encodeURIComponent(currentSearch)}`;
        }
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                showLoading(false);
                
                if (data.success) {
                    renderTagsTable(data.data);
                } else {
                    showError('Etiketler yüklenirken bir hata oluştu.');
                }
            })
            .catch(error => {
                showLoading(false);
                showError('Sunucu hatası: ' + error.message);
            });
    }
    
    // Etiketler tablosunu render et
    function renderTagsTable(tags) {
        tagsTableBody.innerHTML = '';
        
        if (tags.length === 0) {
            tagsTableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Henüz etiket bulunmamaktadır.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTagModal">
                            <i class="fas fa-plus"></i> İlk Etiketinizi Ekleyin
                        </button>
                    </td>
                </tr>
            `;
            return;
        }
        
        tags.forEach(tag => {
            const row = document.createElement('tr');
            
            const date = new Date(tag.created_at);
            const formattedDate = date.toLocaleDateString('tr-TR');
            
            row.innerHTML = `
                <td>${tag.id}</td>
                <td>
                    <span class="badge bg-light text-dark fs-6">${tag.etiket_adi}</span>
                </td>
                <td><code>${tag.slug}</code></td>
                <td>
                    <span class="badge bg-${tag.kullanım_sayisi > 0 ? 'info' : 'secondary'}">
                        ${tag.kullanım_sayisi} yazı
                    </span>
                </td>
                <td>${formattedDate}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-danger delete-tag-btn"
                                data-id="${tag.id}"
                                data-name="${tag.etiket_adi}"
                                data-count="${tag.kullanım_sayisi || 0}"
                                title="Sil">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            
            tagsTableBody.appendChild(row);
        });
        
        // Sil butonlarına event listener ekle
        document.querySelectorAll('.delete-tag-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tagId = this.getAttribute('data-id');
                const tagName = this.getAttribute('data-name');
                const usageCount = this.getAttribute('data-count');
                
                document.getElementById('deleteTagId').value = tagId;
                
                let message = `"${tagName}" etiketini silmek istediğinize emin misiniz?`;
                
                if (usageCount > 0) {
                    message += `\n\nBu etiket ${usageCount} yazıda kullanılıyor. Etiket silinirse bu yazılardan da kaldırılacak.`;
                }
                
                document.getElementById('deleteTagMessage').textContent = message;
                deleteTagModal.show();
            });
        });
    }
    
    // Yeni etiket ekleme formu
    addTagForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        
        fetch('../api/blog/tags.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                addTagModal.hide();
                addTagForm.reset();
                tagSlugPreview.textContent = '';
                showSuccess('Etiket başarıyla eklendi.');
                loadTags();
            } else {
                showError('Etiket eklenemedi: ' + result.error);
            }
        })
        .catch(error => {
            showError('Sunucu hatası: ' + error.message);
        });
    });
    
    // Etiket silme işlemi
    document.getElementById('confirmTagDelete').addEventListener('click', function() {
        const tagId = document.getElementById('deleteTagId').value;
        
        fetch(`../api/blog/tags.php?id=${tagId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                deleteTagModal.hide();
                showSuccess('Etiket başarıyla silindi.');
                loadTags();
            } else {
                showError('Silme işlemi başarısız: ' + data.error);
            }
        })
        .catch(error => {
            showError('Sunucu hatası: ' + error.message);
        });
    });
    
    // Yardımcı fonksiyonlar
    function showLoading(show) {
        if (show) {
            tagsTableBody.innerHTML = `
                <tr id="loadingRow">
                    <td colspan="6" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                    </td>
                </tr>
            `;
        }
    }
    
    function createSlug(text) {
        text = text.toLowerCase();
        text = text.replace(/[^a-z0-9]+/g, '-');
        text = text.replace(/^-+|-+$/g, '');
        return text;
    }
    
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Hata',
            text: message
        });
    }
    
    function showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Başarılı',
            text: message,
            timer: 2000
        });
    }
});
</script>