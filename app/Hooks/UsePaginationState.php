<?php
/**
 * =========================================================================
 * HOOK: UsePaginationState.php
 * PHỤ TRÁCH: THÀNH VIÊN 3 (FULLSTACK / DATA HOOKS & SOLAR SIMULATOR)
 * =========================================================================
 * Hook dùng chung quản lý trạng thái phân trang dữ liệu khi danh sách > 20 dòng.
 * Tuân thủ Quy tắc kiến trúc bắt buộc số 2 trong PROJECT_CONTEXT.md.
 */

namespace App\Hooks;

class UsePaginationState
{
    private int $totalItems;
    private int $perPage;
    private int $currentPage;
    private int $totalPages;
    private int $offset;
    private string $pageParam;

    /**
     * Khởi tạo trạng thái phân trang
     *
     * @param int $totalItems Tổng số bản ghi trong tập dữ liệu
     * @param int $perPage Số bản ghi trên mỗi trang (mặc định: 20 theo quy chuẩn dự án)
     * @param int|null $currentPage Trang hiện tại (nếu null sẽ đọc từ $_GET)
     * @param string $pageParam Tên tham số URL đại diện cho trang (mặc định: 'p')
     */
    public function __construct(int $totalItems, int $perPage = 20, ?int $currentPage = null, string $pageParam = 'p')
    {
        $this->totalItems = max(0, $totalItems);
        $this->perPage = max(1, $perPage);
        $this->pageParam = $pageParam;

        $this->totalPages = $this->totalItems > 0 ? (int) ceil($this->totalItems / $this->perPage) : 1;

        if ($currentPage === null) {
            $reqPage = isset($_GET[$this->pageParam]) ? (int) $_GET[$this->pageParam] : 1;
            $this->currentPage = max(1, min($this->totalPages, $reqPage));
        } else {
            $this->currentPage = max(1, min($this->totalPages, $currentPage));
        }

        $this->offset = ($this->currentPage - 1) * $this->perPage;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getLimit(): int
    {
        return $this->perPage;
    }

    public function needsPagination(): bool
    {
        return $this->totalItems > $this->perPage;
    }

    public function getFromItem(): int
    {
        if ($this->totalItems === 0) return 0;
        return $this->offset + 1;
    }

    public function getToItem(): int
    {
        return min($this->totalItems, $this->offset + $this->perPage);
    }

    /**
     * Sinh URL cho trang cụ thể, giữ nguyên tất cả tham số query hiện có
     */
    public function getPageUrl(int $pageNumber): string
    {
        $queryParams = $_GET;
        $queryParams[$this->pageParam] = $pageNumber;
        return '?' . http_build_query($queryParams);
    }

    /**
     * Render HTML thanh điều hướng phân trang theo đúng Design System Solaris (Obsidian & Gold)
     */
    public function render(string $customClass = ''): string
    {
        if (!$this->needsPagination()) {
            return '';
        }

        $html = '<div class="sol-pagination ' . htmlspecialchars($customClass) . '" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-top: 1.5rem; padding: 0.85rem 1rem; background: rgba(14, 22, 38, 0.6); border: 1px solid var(--border-subtle); border-radius: var(--radius-md);">';
        
        // Thông tin hiển thị số lượng
        $html .= '<div style="font-size: 0.825rem; color: var(--text-secondary);">';
        $html .= 'Hiển thị <strong style="color: var(--text-primary);">' . $this->getFromItem() . ' - ' . $this->getToItem() . '</strong> trên tổng số <strong style="color: var(--accent-gold);">' . $this->totalItems . '</strong> bản ghi';
        $html .= ' (Trang <strong style="color: var(--accent-gold);">' . $this->currentPage . '</strong> / ' . $this->totalPages . ')';
        $html .= '</div>';

        // Các nút điều hướng
        $html .= '<div style="display: flex; align-items: center; gap: 0.35rem;">';

        // Nút Trước
        if ($this->currentPage > 1) {
            $prevUrl = $this->getPageUrl($this->currentPage - 1);
            $html .= '<a href="' . htmlspecialchars($prevUrl) . '" class="sol-btn sol-btn--secondary sol-btn--sm" style="padding: 0.35rem 0.75rem;">« Trước</a>';
        } else {
            $html .= '<span class="sol-btn sol-btn--secondary sol-btn--sm" style="opacity: 0.4; cursor: not-allowed; padding: 0.35rem 0.75rem;">« Trước</span>';
        }

        // Danh sách số trang (thuật toán hiển thị dải trang thông minh)
        $startPage = max(1, $this->currentPage - 2);
        $endPage = min($this->totalPages, $this->currentPage + 2);

        if ($startPage > 1) {
            $html .= '<a href="' . htmlspecialchars($this->getPageUrl(1)) . '" class="sol-btn sol-btn--secondary sol-btn--sm" style="padding: 0.35rem 0.65rem;">1</a>';
            if ($startPage > 2) {
                $html .= '<span style="color: var(--text-muted); padding: 0 0.25rem;">...</span>';
            }
        }

        for ($p = $startPage; $p <= $endPage; $p++) {
            if ($p === $this->currentPage) {
                $html .= '<span class="sol-btn sol-btn--solar sol-btn--sm" style="padding: 0.35rem 0.75rem; font-weight: 700;">' . $p . '</span>';
            } else {
                $html .= '<a href="' . htmlspecialchars($this->getPageUrl($p)) . '" class="sol-btn sol-btn--secondary sol-btn--sm" style="padding: 0.35rem 0.65rem;">' . $p . '</a>';
            }
        }

        if ($endPage < $this->totalPages) {
            if ($endPage < $this->totalPages - 1) {
                $html .= '<span style="color: var(--text-muted); padding: 0 0.25rem;">...</span>';
            }
            $html .= '<a href="' . htmlspecialchars($this->getPageUrl($this->totalPages)) . '" class="sol-btn sol-btn--secondary sol-btn--sm" style="padding: 0.35rem 0.65rem;">' . $this->totalPages . '</a>';
        }

        // Nút Sau
        if ($this->currentPage < $this->totalPages) {
            $nextUrl = $this->getPageUrl($this->currentPage + 1);
            $html .= '<a href="' . htmlspecialchars($nextUrl) . '" class="sol-btn sol-btn--secondary sol-btn--sm" style="padding: 0.35rem 0.75rem;">Sau »</a>';
        } else {
            $html .= '<span class="sol-btn sol-btn--secondary sol-btn--sm" style="opacity: 0.4; cursor: not-allowed; padding: 0.35rem 0.75rem;">Sau »</span>';
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
