<aside class="sidebar">
    <div class="sidebar-header">
        <i class='bx bxs-network-chart'></i>
        <h2>MAPS-NET</h2>
    </div>

    <div style="padding: 15px 12px;">
        <button onclick="toggleTheme()" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--hover-bg); color: var(--text-main); cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600; transition: 0.2s;">
            <i class='bx bx-moon' style="font-size: 18px;"></i> Ganti Tema
        </button>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                <i class='bx bx-map-alt'></i> Peta Jaringan
            </a>
        </li>
        <li>
            <a href="pelanggan.php" class="<?= basename($_SERVER['PHP_SELF']) == 'pelanggan.php' ? 'active' : '' ?>">
                <i class='bx bx-group'></i> Data Pelanggan
            </a>
        </li>
        
        <li>
            <a href="wilayah.php" class="<?= basename($_SERVER['PHP_SELF']) == 'wilayah.php' ? 'active' : '' ?>">
                <i class='bx bx-map'></i> Data Wilayah
            </a>
        </li>

        <li>
            <a href="perangkat.php" class="<?= basename($_SERVER['PHP_SELF']) == 'perangkat.php' ? 'active' : '' ?>">
                <i class='bx bx-devices'></i> Perangkat & ODP
            </a>
        </li>

        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <li>
            <a href="users.php" class="<?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
                <i class='bx bx-user-circle'></i> Manajemen User
            </a>
        </li>
        <?php endif; ?>

        <li>
            <a href="ganti_sandi.php" class="<?= basename($_SERVER['PHP_SELF']) == 'ganti_sandi.php' ? 'active' : '' ?>">
                <i class='bx bx-key'></i> Ganti Sandi
            </a>
        </li>

        <li style="margin-top: auto;">
            <a href="logout.php" onclick="return confirm('Yakin ingin keluar?')" style="color: var(--danger-color);">
                <i class='bx bx-log-out'></i> Keluar
            </a>
        </li>
    </ul>
</aside>