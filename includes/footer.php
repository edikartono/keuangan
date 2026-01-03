<?php
// File: includes/footer.php
?>
                </div> <!-- Penutup border-bottom dari header -->
            </main>
        </div>
    </div>

    <!-- Modal Pengaturan Akun -->
    <div class="modal fade" id="pengaturanAkunModal" tabindex="-1" aria-labelledby="pengaturanAkunModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pengaturanAkunModalLabel">Pengaturan Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 class="mb-3">Ubah Password Anda</h6>
                    <!-- Form Ganti Password -->
                    <form action="/laporan-keuangan/pages/user/proses.php" method="POST">
                        <div class="mb-3">
                            <label for="old_password" class="form-label">Password Lama</label>
                            <input type="password" class="form-control" name="old_password" id="old_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" name="new_password" id="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                        </div>
                        <button type="submit" name="ganti_password" class="btn btn-primary">Simpan Password Baru</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap 5 JS Bundle (handles the sidebar toggle automatically) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Library untuk Export PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    
    <!-- Custom scripts from other pages will be loaded here if set -->
    <?php
        if (isset($_SESSION['flash_message'])) {
            $flash = $_SESSION['flash_message'];
            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const alertContainer = document.createElement('div');
                        alertContainer.innerHTML = `
                            <div class='alert alert-{$flash['type']} alert-dismissible fade show' role='alert' style='position: fixed; top: 80px; right: 20px; z-index: 2000;'>
                                {$flash['message']}
                                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                            </div>
                        `;
                        document.body.appendChild(alertContainer);
                        setTimeout(() => {
                            const alert = bootstrap.Alert.getOrCreateInstance(alertContainer.querySelector('.alert'));
                            alert.close();
                        }, 5000);
                    });
                  </script>";
            unset($_SESSION['flash_message']);
        }
        
        if (!empty($footer_scripts)) {
            echo $footer_scripts;
        }
    ?>
    <!-- DataTables CSS -->
<link rel="stylesheet" 
href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">

<!-- jQuery (wajib) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

<!--Pendapatan-->

<script>
$(document).ready(function() {
    // 1. Initialize DataTables
    $('#pendapatanTable, #pengeluaranTable').DataTable({
        "pageLength": 25,
        "order": [],   // bebas sorting
        "language": {
            "search": "Cari data:",
            "lengthMenu": "Tampilkan _MENU_ data",
            "zeroRecords": "Tidak ditemukan data",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            "infoEmpty": "Tidak ada data",
            "infoFiltered": "(difilter dari _MAX_ total data)"
        }
    });

    // 2. Global Modal Logic (Select2 & Shortcuts)
    $('#tambahModal').on('shown.bs.modal', function () {
        // Init Select2 if not exists
        if (!$('#kategori_id').hasClass("select2-hidden-accessible")) {
            $('#kategori_id').select2({
                placeholder: "Cari kategori...",
                allowClear: true,
                width: '100%',
                dropdownParent: $('#tambahModal')
            });
        }
        
        // Focus search on open
        $('#kategori_id').on('select2:open', function () {
            setTimeout(function () {
                document.querySelector('.select2-container--open .select2-search__field').focus();
            }, 0);
        });
    });

    // 3. Shortcuts (Alt+K, Alt+J)
    $(document).on('keydown', function (e) {
        if (!$('#tambahModal').hasClass('show')) return;

        // ALT + K → fokus kategori
        if (e.altKey && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            $('#kategori_id').select2('open');
        }

        // ALT + J → fokus jumlah
        if (e.altKey && e.key.toLowerCase() === 'j') {
            e.preventDefault();
            $('#jumlah').focus();
        }
    });

    // 4. Tambah Kategori (Unified Logic)
    $('#modalKategori').on('shown.bs.modal', function () {
        $('#nama_kategori').trigger('focus');
    });

    $('#nama_kategori').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#btnSimpanKategori').click();
        }
    });

    $('#btnSimpanKategori').off('click').click(function () {
        let btn = $(this);
        let nama = $('#nama_kategori').val().trim();

        if (nama === '') {
            alert('Nama kategori wajib diisi');
            $('#nama_kategori').focus();
            return;
        }

        // Tentukan tipe berdasarkan halaman
        let tipe = 'Pengeluaran'; // Default
        if ($('#pendapatanTable').length) {
            tipe = 'Pemasukan';
        }

        btn.prop('disabled', true).text('Menyimpan...');

        $.ajax({
            url: 'ajax/tambah_kategori2.php', // Menggunakan endpoint terbaru
            type: 'POST',
            dataType: 'json',
            data: { 
                nama_kategori: nama,
                tipe: tipe
            },
            success: function (res) {
                btn.prop('disabled', false).text('Simpan');

                if (res.status === 'exists') {
                    alert('Kategori sudah ada ❗');
                    $('#nama_kategori').focus();
                    return;
                }

                if (res.status === 'success') {
                    let option = new Option(res.nama, res.id, true, true);
                    $('#kategori_id').append(option).trigger('change');

                    $('#nama_kategori').val('');
                    $('#modalKategori').modal('hide');

                    setTimeout(function () {
                        $('#tambahModal').modal('show');
                    }, 300);

                } else {
                    alert('Gagal menyimpan kategori');
                    $('#nama_kategori').focus();
                }
            },
            error: function() {
                btn.prop('disabled', false).text('Simpan');
                alert('Terjadi kesalahan sistem');
            }
        });
    });

    // 5. Logic Edit Button (Generic)
    // Berfungsi untuk baik Pendapatan maupun Pengeluaran asalkan ID modalnya sama
    $(document).on('click', '.btn-edit', function () {
        $('#edit_id').val($(this).data('id'));
        $('#edit_tanggal').val($(this).data('tanggal'));
        $('#edit_sumber').val($(this).data('sumber'));
        // Casting float/number dilakukan di PHP, di sini tinggal assign
        $('#edit_jumlah').val($(this).data('jumlah')); 
        $('#edit_keterangan').val($(this).data('keterangan'));

        // Set select2 category if exists
        let katId = $(this).data('kategori');
        if($('#edit_kategori').length) {
            $('#edit_kategori').val(katId).trigger('change');
        }

        $('#editModal').modal('show');
    });

    // Init Select2 on Edit Modal
    $('#editModal').on('shown.bs.modal', function () {
        if ($('#edit_kategori').length && !$('#edit_kategori').hasClass('select2-hidden-accessible')) {
            $('#edit_kategori').select2({
                dropdownParent: $('#editModal'),
                width: '100%'
            });
        }
    });

    // 6. Form Submit Handlers (Distinct)
    
    // Edit Pendapatan
    $('#formEditPendapatan').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax/edit_pendapatan.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res === 'ok') {
                    location.reload();
                } else {
                    alert('Gagal update data');
                }
            }
        });
    });

    // Edit Pengeluaran
    $('#formEditPengeluaran').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax/edit_pengeluaran.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function (res) {
                if (res === 'ok') {
                    location.reload();
                } else {
                    alert('Gagal update data');
                }
            }
        });
    });
});
</script>



<!-- Select2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>




</body>
</html>
