@props([
    'activeTab' => 'home'
])

<nav class="sol-nav-wrapper">
    <div class="sol-container sol-nav-inner">
        <!-- 6 Tabs Bắt buộc của Đồ án -->
        <ul class="sol-nav-tabs">
            <li>
                <a href="index.php?page=home" class="sol-tab-item {{ $activeTab === 'home' ? 'active' : '' }}">
                    <span class="tab-icon">🏠</span>
                    <span>Home (Trang Chủ)</span>
                </a>
            </li>
            <li>
                <a href="index.php?page=products" class="sol-tab-item {{ $activeTab === 'products' ? 'active' : '' }}">
                    <span class="tab-icon">☀️</span>
                    <span>Giải pháp & Sản phẩm</span>
                </a>
            </li>
            <li>
                <a href="index.php?page=calculator" class="sol-tab-item {{ $activeTab === 'calculator' ? 'active' : '' }}">
                    <span class="tab-icon">🧮</span>
                    <span>Tư vấn & Demo Thiết kế</span>
                </a>
            </li>
            <li>
                <a href="index.php?page=installations" class="sol-tab-item {{ $activeTab === 'installations' ? 'active' : '' }}">
                    <span class="tab-icon">📋</span>
                    <span>Quản lý Lắp đặt</span>
                </a>
            </li>
            <li>
                <a href="index.php?page=exercises" class="sol-tab-item {{ $activeTab === 'exercises' ? 'active' : '' }}">
                    <span class="tab-icon">📚</span>
                    <span>Bài Tập (24 Bài)</span>
                </a>
            </li>
            <li>
                <a href="index.php?page=profile" class="sol-tab-item {{ $activeTab === 'profile' ? 'active' : '' }}">
                    <span class="tab-icon">👤</span>
                    <span>Thông Tin Cá Nhân</span>
                </a>
            </li>
        </ul>

        <!-- Badge trạng thái hệ thống -->
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span class="sol-badge sol-badge--corona">Solaris v2.0</span>
        </div>
    </div>
</nav>
