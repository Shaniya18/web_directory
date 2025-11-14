<?php
function displayPagination($total, $per_page, $current_page, $base_url) {
    $total_pages = ceil($total / $per_page);
    
    if ($total_pages <= 1) return;
    ?>
    <div class="pagination">
        <?php if ($current_page > 1): ?>
            <a href="<?php echo $base_url . '&p=' . ($current_page - 1); ?>">&laquo; Previous</a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $current_page - 3); $i <= min($total_pages, $current_page + 3); $i++): ?>
            <?php if ($i == $current_page): ?>
                <span class="current"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="<?php echo $base_url . '&p=' . $i; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($current_page < $total_pages): ?>
            <a href="<?php echo $base_url . '&p=' . ($current_page + 1); ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
    <?php
}
?>