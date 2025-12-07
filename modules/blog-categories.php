<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /admin/index.php');  // DÜZELTİLDİ
    exit();
}

$page_title = "Blog Kategorileri";
ob_start();
?>

<div class="container-fluid">
    <!-- Başlık ve Butonlar -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Blog Kategorileri</h1>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-plus"></i> Yeni Kategori
            </button>
            <a href="/admin/modules/blog.php" class="btn btn-outline-secondary">  <!-- DÜZELTİLDİ -->
                <i class="fas fa-arrow-left"></i> Yazılara Dön
            </a>
        </div>
    </div>

    <!-- Kategoriler Tablosu -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="categoriesTable">
                    <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th>Kategori Adı</th>
                            <th>Slug</th>
                            <th>Açıklama</th>
                            <th>Yazı Sayısı</th>
                            <th>Durum</th>
                            <th width="120">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="categoriesTableBody">
                        <tr id="loadingRow">
                            <td colspan="7" class="text-center py-5">
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

<!-- Yeni Kategori Ekleme Modalı -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Yeni Kategori Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCategoryForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kategori Adı *</label>
                        <input type="text" name="kategori_adi" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Üst Kategori</label>
                        <select name="ust_kategori_id" class="form-select" id="parentCategorySelect">
                            <option value="">Ana Kategori</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <textarea name="aciklama" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Sıra No</label>
                            <input type="number" name="sira" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Durum</label>
                            <select name="aktif" class="form-select">
                                <option value="1">Aktif</option>
                                <option value="0">Pasif</option>
                            </select>
                        </div>
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

<!-- Düzenleme Modalı -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kategori Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCategoryForm">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editCategoryId">
                    
                    <div class="mb-3">
                        <label class="form-label">Kategori Adı *</label>
                        <input type="text" name="kategori_adi" id="editCategoryName" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Üst Kategori</label>
                        <select name="ust_kategori_id" class="form-select" id="editParentCategorySelect">
                            <option value="">Ana Kategori</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <textarea name="aciklama" id="editCategoryDescription" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Sıra No</label>
                            <input type="number" name="sira" id="editCategoryOrder" class="form-control" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Durum</label>
                            <select name="aktif" id="editCategoryStatus" class="form-select">
                                <option value="1">Aktif</option>
                                <option value="0">Pasif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Güncelle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Silme Onay Modalı -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Kategori Sil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="deleteCategoryMessage">Bu kategoriyi silmek istediğinize emin misiniz? Bu işlem geri alınamaz.</p>
                <input type="hidden" id="deleteCategoryId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-danger" id="confirmCategoryDelete">Sil</button>
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
    const categoriesTableBody = document.getElementById('categoriesTableBody');
    const addCategoryForm = document.getElementById('addCategoryForm');
    const editCategoryForm = document.getElementById('editCategoryForm');
    const parentCategorySelect = document.getElementById('parentCategorySelect');
    const editParentCategorySelect = document.getElementById('editParentCategorySelect');
    
    // Modal instances
    const addCategoryModal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
    const editCategoryModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    const deleteCategoryModal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
    
    // İlk yükleme
    loadCategories();
    
    // Kategorileri yükle
    function loadCategories() {
        showLoading(true);
        
        fetch('/admin/api/blog/categories.php')  // DÜZELTİLDİ
            .then(response => response.json())
            .then(data => {
                showLoading(false);
                
                if (data.success) {
                    renderCategoriesTable(data.data);
                    populateParentCategorySelects(data.data);
                } else {
                    showError('Kategoriler yüklenirken bir hata oluştu.');
                }
            })
            .catch(error => {
                showLoading(false);
                showError('Sunucu hatası: ' + error.message);
            });
    }
    
    // Kategoriler tablosunu render et
    function renderCategoriesTable(categories) {
        categoriesTableBody.innerHTML = '';
        
        if (categories.length === 0) {
            categoriesTableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Henüz kategori bulunmamaktadır.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i class="fas fa-plus"></i> İlk Kategorinizi Ekleyin
                        </button>
                    </td>
                </tr>
            `;
            return;
        }
        
        categories.forEach(category => {
            const row = document.createElement('tr');
            
            const statusBadge = category.aktif == 1 
                ? '<span class="badge bg-success">Aktif</span>'
                : '<span class="badge bg-danger">Pasif</span>';
            
            let parentCategoryName = '-';
            if (category.ust_kategori_id) {
                const parent = categories.find(c => c.id == category.ust_kategori_id);
                if (parent) parentCategoryName = parent.kategori_adi;
            }
            
            row.innerHTML = `
                <td>${category.id}</td>
                <td>
                    <strong>${category.kategori_adi}</strong>
                    ${category.ust_kategori_id ? `<br><small class="text-muted">Üst: ${parentCategoryName}</small>` : ''}
                </td>
                <td><code>${category.slug}</code></td>
                <td>${category.aciklama ? category.aciklama.substring(0, 50) + '...' : '-'}</td>
                <td>${category.yazi_sayisi || 0}</td>
                <td>${statusBadge}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary edit-category-btn"
                                data-id="${category.id}"
                                data-name="${category.kategori_adi}"
                                data-description="${category.aciklama || ''}"
                                data-parent="${category.ust_kategori_id || ''}"
                                data-order="${category.sira}"
                                data-status="${category.aktif}"
                                title="Düzenle">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger delete-category-btn"
                                data-id="${category.id}"
                                data-name="${category.kategori_adi}"
                                data-count="${category.yazi_sayisi || 0}"
                                title="Sil">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            
            categoriesTableBody.appendChild(row);
        });
        
        // Düzenle butonlarına event listener ekle
        document.querySelectorAll('.edit-category-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-id');
                const categoryName = this.getAttribute('data-name');
                const description = this.getAttribute('data-description');
                const parentId = this.getAttribute('data-parent');
                const order = this.getAttribute('data-order');
                const status = this.getAttribute('data-status');
                
                document.getElementById('editCategoryId').value = categoryId;
                document.getElementById('editCategoryName').value = categoryName;
                document.getElementById('editCategoryDescription').value = description;
                document.getElementById('editParentCategorySelect').value = parentId;
                document.getElementById('editCategoryOrder').value = order;
                document.getElementById('editCategoryStatus').value = status;
                
                editCategoryModal.show();
            });
        });
        
        // Sil butonlarına event listener ekle
        document.querySelectorAll('.delete-category-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const categoryId = this.getAttribute('data-id');
                const categoryName = this.getAttribute('data-name');
                const postCount = this.getAttribute('data-count');
                
                document.getElementById('deleteCategoryId').value = categoryId;
                
                let message = `"${categoryName}" adlı kategoriyi silmek istediğinize emin misiniz?`;
                
                if (postCount > 0) {
                    message += `\n\nBu kategoriye ait ${postCount} yazı bulunmaktadır. Kategori silinirse bu yazılar kategorisiz olacaktır.`;
                }
                
                document.getElementById('deleteCategoryMessage').textContent = message;
                deleteCategoryModal.show();
            });
        });
    }
    
    // Üst kategori select'lerini doldur
    function populateParentCategorySelects(categories) {
        parentCategorySelect.innerHTML = '<option value="">Ana Kategori</option>';
        editParentCategorySelect.innerHTML = '<option value="">Ana Kategori</option>';
        
        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.kategori_adi;
            parentCategorySelect.appendChild(option);
            
            const editOption = option.cloneNode(true);
            editParentCategorySelect.appendChild(editOption);
        });
    }
    
    // Yeni kategori ekleme formu
    addCategoryForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        
        data.sira = parseInt(data.sira);
        data.aktif = parseInt(data.aktif);
        data.ust_kategori_id = data.ust_kategori_id ? parseInt(data.ust_kategori_id) : null;
        
        fetch('/admin/api/blog/categories.php', {  // DÜZELTİLDİ
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                addCategoryModal.hide();
                addCategoryForm.reset();
                showSuccess('Kategori başarıyla eklendi.');
                loadCategories();
            } else {
                showError('Kategori eklenemedi: ' + result.error);
            }
        })
        .catch(error => {
            showError('Sunucu hatası: ' + error.message);
        });
    });
    
    // Kategori düzenleme formu
    editCategoryForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        const categoryId = data.id;
        
        data.sira = parseInt(data.sira);
        data.aktif = parseInt(data.aktif);
        data.ust_kategori_id = data.ust_kategori_id ? parseInt(data.ust_kategori_id) : null;
        
        delete data.id;
        
        fetch(`/admin/api/blog/categories.php?id=${categoryId}`, {  // DÜZELTİLDİ
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                editCategoryModal.hide();
                showSuccess('Kategori başarıyla güncellendi.');
                loadCategories();
            } else {
                showError('Kategori güncellenemedi: ' + result.error);
            }
        })
        .catch(error => {
            showError('Sunucu hatası: ' + error.message);
        });
    });
    
    // Kategori silme işlemi
    document.getElementById('confirmCategoryDelete').addEventListener('click', function() {
        const categoryId = document.getElementById('deleteCategoryId').value;
        
        fetch(`/admin/api/blog/categories.php?id=${categoryId}`, {  // DÜZELTİLDİ
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                deleteCategoryModal.hide();
                showSuccess('Kategori başarıyla silindi.');
                loadCategories();
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
            categoriesTableBody.innerHTML = `
                <tr id="loadingRow">
                    <td colspan="7" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Yükleniyor...</span>
                        </div>
                    </td>
                </tr>
            `;
        }
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