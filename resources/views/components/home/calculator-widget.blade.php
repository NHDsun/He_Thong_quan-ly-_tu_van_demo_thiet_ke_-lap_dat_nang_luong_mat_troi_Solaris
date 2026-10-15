<section class="sol-section">
    <div class="sol-container">
        <div class="sol-calculator-widget">
            <div style="text-align: center; max-width: 600px; margin: 0 auto;">
                <span class="sol-badge sol-badge--amber">Mô Phỏng Nhanh</span>
                <h2 style="font-family: var(--font-heading); font-size: 1.85rem; color: #ffffff; margin: 0.65rem 0 0.4rem;">
                    Ước Tính Cấu Hình Theo Hóa Đơn Điện
                </h2>
                <p style="color: var(--text-secondary); font-size: 0.9rem;">
                    Kéo thanh trượt để xem công suất kWp, số tấm pin và diện tích mái cần thiết:
                </p>
            </div>

            <div style="max-width: 680px; margin: 2rem auto 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
                    <span style="font-size: 0.9rem; color: #cbd5e1; font-weight: 500;">Tiền điện trung bình / tháng:</span>
                    <span id="billDisplay" style="font-family: var(--font-heading); font-size: 1.4rem; font-weight: 700; color: var(--accent-gold);">
                        3.000.000 VNĐ
                    </span>
                </div>

                <input 
                    type="range" 
                    id="billSlider" 
                    class="sol-slider-track" 
                    min="1000000" 
                    max="20000000" 
                    step="500000" 
                    value="3000000" 
                    oninput="calculateSolar(this.value)"
                >

                <div style="display: flex; justify-content: space-between; font-size: 0.725rem; color: var(--text-muted); margin-top: 0.4rem;">
                    <span>1 Triệu</span>
                    <span>5 Triệu</span>
                    <span>10 Triệu</span>
                    <span>15 Triệu</span>
                    <span>20 Triệu VNĐ</span>
                </div>
            </div>

            <div class="sol-calc-stats" style="max-width: 820px; margin: 1.75rem auto 0;">
                <div style="text-align: center;">
                    <div class="sol-calc-stat-val" id="resKwp">7.4 kWp</div>
                    <div class="sol-calc-stat-lbl">Công Suất Khuyến Nghị</div>
                </div>
                <div style="text-align: center;">
                    <div class="sol-calc-stat-val" id="resPanels">14 Tấm</div>
                    <div class="sol-calc-stat-lbl">Số Tấm Pin 550W</div>
                </div>
                <div style="text-align: center;">
                    <div class="sol-calc-stat-val" id="resArea">35 m²</div>
                    <div class="sol-calc-stat-lbl">Diện Tích Mái Khả Dụng</div>
                </div>
                <div style="text-align: center;">
                    <div class="sol-calc-stat-val" id="resSavings">~2.7 Tr/tháng</div>
                    <div class="sol-calc-stat-lbl">Tiết Kiệm Dự Kiến</div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 1.75rem;">
                <a href="index.php?page=calculator" class="sol-btn sol-btn--solar">
                    Xem Chi Tiết Cấu Hình & Báo Giá Kỹ Thuật ➔
                </a>
            </div>
        </div>
    </div>
</section>

<script>
    function calculateSolar(val) {
        const bill = parseFloat(val);
        const billDisplay = document.getElementById('billDisplay');
        if (billDisplay) {
            billDisplay.innerText = new Intl.NumberFormat('vi-VN').format(bill) + ' VNĐ';
        }

        const monthlyKwh = bill / 3000;
        const kwp = (monthlyKwh / (4.5 * 30)).toFixed(1);
        const panels = Math.ceil((kwp * 1000) / 550);
        const area = Math.round(panels * 2.5);
        const savings = ((bill * 0.9) / 1000000).toFixed(1);

        const elKwp = document.getElementById('resKwp');
        const elPanels = document.getElementById('resPanels');
        const elArea = document.getElementById('resArea');
        const elSavings = document.getElementById('resSavings');

        if (elKwp) elKwp.innerText = kwp + ' kWp';
        if (elPanels) elPanels.innerText = panels + ' Tấm';
        if (elArea) elArea.innerText = area + ' m²';
        if (elSavings) elSavings.innerText = '~' + savings + ' Tr/tháng';
    }
</script>
