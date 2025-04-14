<?php
$pdo = new PDO('mysql:host=localhost;dbname=analistacsc;charset=utf8mb4', 'root', 'afvm2611');

$stmt = $pdo->query("SELECT * FROM categorias ORDER BY nome");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM itens ORDER BY nome");
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM sub_itens ORDER BY item_id, nome");
$subitens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mapaSubitens = [];
foreach ($subitens as $sub) {
    $mapaSubitens[$sub['item_id']][] = $sub;
}

$mapaCategorias = [];
foreach ($categorias as $cat) {
    $mapaCategorias[$cat['id']] = [
        'nome' => $cat['nome'],
        'itens' => []
    ];
}

foreach ($itens as $item) {
    $item['sub_itens'] = $mapaSubitens[$item['id']] ?? [];
    $mapaCategorias[$item['categoria_id']]['itens'][] = $item;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>ANALISTA CSC</title>
    <link rel="stylesheet" href="./admin.css">
</head>
<body>
<div class="container">
    <h1>📊 ANALISTA CSC</h1>

    <div class="sections">
        <?php foreach ($mapaCategorias as $categoria): ?>
            <div class="section">
                <h2><?= htmlspecialchars($categoria['nome']) ?></h2>
                <ul>
                    <?php foreach ($categoria['itens'] as $item): ?>
                        <li>
                            <?php if ($item['link']): ?>
                                <a href="<?= htmlspecialchars($item['link']) ?>" target="_blank"><?= htmlspecialchars($item['nome']) ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($item['nome']) ?>
                            <?php endif; ?>

                            <?php if (!empty($item['sub_itens'])): ?>
                                <ul>
                                    <?php foreach ($item['sub_itens'] as $sub): ?>
                                        <li style="background: rgba(255,255,255,0.15); font-size: 14px;">
                                            <?php if ($sub['link']): ?>
                                                <a href="<?= htmlspecialchars($sub['link']) ?>" target="_blank">
                                                    - <?= htmlspecialchars($sub['nome']) ?>
                                                </a>
                                            <?php else: ?>
                                                - <?= htmlspecialchars($sub['nome']) ?>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
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
