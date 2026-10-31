<?php
use App\Modules\Member4_Installation\View;

/** @var string $name Tên component, truyền từ View::component(). */
/** @var string $text Nội dung hiển thị, truyền từ View::component(). */
/** @var array $attrs Thuộc tính HTML, truyền từ View::component(). */
if (!isset($name, $text, $attrs)) {
    http_response_code(404);
    return;
}

// Dùng cùng tên class và biến CSS với các component base của dự án.
?>
<?php if ($name === 'button'): ?>
    <button <?= View::attributes($attrs) ?>><?= View::escape($text) ?></button>
<?php elseif ($name === 'badge'): ?>
    <span <?= View::attributes($attrs) ?>><?= View::escape($text) ?></span>
<?php elseif ($name === 'input'): ?>
    <input <?= View::attributes($attrs) ?>>
<?php endif; ?>
