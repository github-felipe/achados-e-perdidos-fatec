<?php
    // Miniatura do item.
    $fotoId = (int) ($fotoId ?? 0);
?>
<?php if ($fotoId > 0): ?>
    <img src="<?= htmlspecialchars($rootPath) ?>imagem.php?id=<?= $fotoId ?>"
         alt="Foto do item"
         class="app-item-thumb">
<?php else: ?>
    <span class="w3-small w3-text-grey">—</span>
<?php endif; ?>
