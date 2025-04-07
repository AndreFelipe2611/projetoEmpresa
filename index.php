<?php
$pdo = new PDO('mysql:host=localhost;dbname=analistacsc;charset=utf8mb4', 'root', 'afvm2611');

// PEGAR TODOS OS ITENS
$stmt = $pdo->query("SELECT * FROM itens ORDER BY categoria, nome");
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// PEGAR TODOS OS SUBITENS
$stmtSub = $pdo->query("SELECT * FROM sub_itens ORDER BY item_id, nome");
$subitens = $stmtSub->fetchAll(PDO::FETCH_ASSOC);

// ORGANIZAR SUBITENS POR ID DO ITEM
$mapaSubitens = [];
foreach ($subitens as $sub) {
    $mapaSubitens[$sub['item_id']][] = $sub;
}

// ORGANIZAR ITENS POR CATEGORIA
$categorias = ['controles' => [], 'ferramentas' => [], 'acessos' => []];
foreach ($itens as $item) {
    $item['sub_itens'] = $mapaSubitens[$item['id']] ?? [];
    $categorias[$item['categoria']][] = $item;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Analista CSC</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>🚀 ANALISTA CSC 🚀</h1>
    <div class="sections">
        <?php foreach ($categorias as $categoria => $itens): ?>
            <div class="section <?= $categoria ?>">
                <h2>
                    <?= $categoria === 'controles' ? '🔥 CONTROLES 🔥' : ($categoria === 'ferramentas' ? '🛠️ FERRAMENTAS 🛠️' : '🔑 ACESSOS 🔑') ?>
                </h2>
                <ul>
                    <?php foreach ($itens as $item): ?>
                        <li>
                            <?php if (!empty($item['link'])): ?>
                                <a href="<?= htmlspecialchars($item['link']) ?>" target="_blank"><?= htmlspecialchars($item['nome']) ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($item['nome']) ?>
                            <?php endif; ?>

                            <?php if (!empty($item['sub_itens'])): ?>
                                <div class="button-group">
                                    <?php foreach ($item['sub_itens'] as $sub): ?>
                                        <a href="<?= htmlspecialchars($sub['link']) ?>" target="_blank">
                                            <button><?= htmlspecialchars($sub['nome']) ?></button>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
