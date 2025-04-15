<?php
$pdo = new PDO('mysql:host=localhost;dbname=analistacsc;charset=utf8mb4', 'root', 'afvm2611');

// Buscar dados
$stmt = $pdo->query("SELECT * FROM categorias ORDER BY nome");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM itens ORDER BY nome");
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM sub_itens ORDER BY item_id, nome");
$subitens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organizar subitens
$mapaSubitens = [];
foreach ($subitens as $sub) {
    $mapaSubitens[$sub['item_id']][] = $sub;
}

// Mapear categorias
$mapaCategorias = [];
foreach ($categorias as $cat) {
    $mapaCategorias[$cat['id']] = [
        'nome' => $cat['nome'],
        'cor' => $cat['cor'] ?? '#ffffff',
        'itens' => []
    ];
}

// Agrupar itens por categoria
foreach ($itens as $item) {
    $item['sub_itens'] = $mapaSubitens[$item['id']] ?? [];
    if (isset($mapaCategorias[$item['categoria_id']])) {
        $mapaCategorias[$item['categoria_id']]['itens'][] = $item;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>ANALISTA CSC</title>
    <link rel="stylesheet" href="./style.css">
</head>
<body>
<div class="container">
    <h1>📊 ANALISTA CSC</h1>

    <div class="sections">
        <?php foreach ($mapaCategorias as $categoria): ?>
            <?php if (!isset($categoria['nome'])) continue; ?>
            <div class="section" style="background-color: <?= htmlspecialchars($categoria['cor']) ?>;">
                <h2><?= htmlspecialchars($categoria['nome']) ?></h2>
                <ul>
                    <?php foreach ($categoria['itens'] as $item): ?>
                        <li>
                            <?php if (!empty($item['link'])): ?>
                                <a href="<?= htmlspecialchars($item['link']) ?>" target="_blank"><?= htmlspecialchars($item['nome']) ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($item['nome']) ?>
                            <?php endif; ?>

                            <?php if (!empty($item['sub_itens'])): ?>
                                <div class="button-group">
                                    <?php foreach ($item['sub_itens'] as $sub): ?>
                                        <?php if (!empty($sub['link'])): ?>
                                            <a href="<?= htmlspecialchars($sub['link']) ?>" target="_blank">
                                                <button><?= htmlspecialchars($sub['nome']) ?></button>
                                            </a>
                                        <?php else: ?>
                                            <button><?= htmlspecialchars($sub['nome']) ?></button>
                                        <?php endif; ?>
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
