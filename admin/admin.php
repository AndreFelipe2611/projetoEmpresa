<?php
$pdo = new PDO('mysql:host=localhost;dbname=analistacsc;charset=utf8mb4', 'root', 'afvm2611');

// ADICIONAR CATEGORIA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novaCategoria'])) {
    $novaCategoria = trim($_POST['novaCategoria']);
    if (!empty($novaCategoria)) {
        $stmt = $pdo->prepare("INSERT INTO categorias (nome) VALUES (?)");
        $stmt->execute([$novaCategoria]);
    }
    header("Location: admin.php");
    exit;
}

// ADICIONAR ITEM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novoItem'])) {
    $categoria_id = intval($_POST['categoria_id']);
    $nome = trim($_POST['novoItem']);
    $link = trim($_POST['link']);

    if (!empty($nome)) {
        $stmt = $pdo->prepare("INSERT INTO itens (categoria_id, nome, link) VALUES (?, ?, ?)");
        $stmt->execute([$categoria_id, $nome, $link]);
        $item_id = $pdo->lastInsertId();

        if (!empty($_POST['sub_nome'])) {
            foreach ($_POST['sub_nome'] as $i => $sub_nome) {
                $sub_nome = trim($sub_nome);
                $sub_link = trim($_POST['sub_link'][$i] ?? '');
                if (!empty($sub_nome)) {
                    $stmtSub = $pdo->prepare("INSERT INTO sub_itens (item_id, nome, link) VALUES (?, ?, ?)");
                    $stmtSub->execute([$item_id, $sub_nome, $sub_link]);
                }
            }
        }
    }

    header("Location: admin.php");
    exit;
}

// EXCLUIR ITEM
if (isset($_GET['excluir_item'])) {
    $id = intval($_GET['excluir_item']);
    $pdo->prepare("DELETE FROM sub_itens WHERE item_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM itens WHERE id = ?")->execute([$id]);
    header("Location: admin.php");
    exit;
}

// EXCLUIR CATEGORIA
if (isset($_GET['excluir_categoria'])) {
    $categoriaId = intval($_GET['excluir_categoria']);

    $stmt = $pdo->prepare("SELECT id FROM itens WHERE categoria_id = ?");
    $stmt->execute([$categoriaId]);
    $itemIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($itemIds)) {
        $in = str_repeat('?,', count($itemIds) - 1) . '?';
        $pdo->prepare("DELETE FROM sub_itens WHERE item_id IN ($in)")->execute($itemIds);
        $pdo->prepare("DELETE FROM itens WHERE id IN ($in)")->execute($itemIds);
    }

    $pdo->prepare("DELETE FROM categorias WHERE id = ?")->execute([$categoriaId]);
    header("Location: admin.php");
    exit;
}

// PEGAR CATEGORIAS, ITENS E SUBITENS
$stmt = $pdo->query("SELECT * FROM categorias ORDER BY nome");
$categoriasDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM itens ORDER BY nome");
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtSub = $pdo->query("SELECT * FROM sub_itens ORDER BY item_id, nome");
$subitens = $stmtSub->fetchAll(PDO::FETCH_ASSOC);

$mapaSubitens = [];
foreach ($subitens as $sub) {
    $mapaSubitens[$sub['item_id']][] = $sub;
}

$categorias = [];
foreach ($categoriasDb as $cat) {
    $categorias[$cat['id']] = [
        'nome' => $cat['nome'],
        'itens' => []
    ];
}

foreach ($itens as $item) {
    $item['sub_itens'] = $mapaSubitens[$item['id']] ?? [];
    if (isset($categorias[$item['categoria_id']])) {
        $categorias[$item['categoria_id']]['itens'][] = $item;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Admin - ANALISTA CSC</title>
    <link rel="stylesheet" href="./admin.css">
</head>
<body>
<div class="container">
    <h1>⚙️ Admin - ANALISTA CSC</h1>

    <form method="POST" class="admin-form">
        <label>Nova Categoria:</label>
        <input type="text" name="novaCategoria" placeholder="Ex: 📂 NOVA CATEGORIA" required>
        <button type="submit">➕ Criar Categoria</button>
    </form>

    <hr><br>

    <form method="POST" class="admin-form">
        <label>Categoria:</label>
        <select name="categoria_id" required>
            <?php foreach ($categoriasDb as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label>Nome do Item:</label>
        <input type="text" name="novoItem" placeholder="Ex: 📋 Novo Item" required>

        <label>Link do Item:</label>
        <input type="text" name="link" placeholder="https://...">

        <fieldset>
            <legend>Subitens</legend>
            <input type="text" name="sub_nome[]" placeholder="Subitem 1">
            <input type="text" name="sub_link[]" placeholder="Link 1"><br>
            <input type="text" name="sub_nome[]" placeholder="Subitem 2">
            <input type="text" name="sub_link[]" placeholder="Link 2"><br>
            <input type="text" name="sub_nome[]" placeholder="Subitem 3">
            <input type="text" name="sub_link[]" placeholder="Link 3">
        </fieldset>

        <button type="submit">➕ Adicionar Item</button>
    </form>

    <hr><br>

    <div class="sections">
        <?php foreach ($categorias as $catId => $cat): ?>
            <div class="section">
                <h2>
                    <?= htmlspecialchars($cat['nome']) ?>
                    <a href="?excluir_categoria=<?= $catId ?>" style="float:right; font-size: 14px;">[X]</a>
                </h2>
                <ul>
                    <?php foreach ($cat['itens'] as $item): ?>
                        <li>
                            <?php if ($item['link']): ?>
                                <a href="<?= htmlspecialchars($item['link']) ?>" target="_blank">
                                    <?= htmlspecialchars($item['nome']) ?>
                                </a>
                            <?php else: ?>
                                <?= htmlspecialchars($item['nome']) ?>
                            <?php endif; ?>

                            <div class="button-group">
                                <a href="?excluir_item=<?= $item['id'] ?>">🗑 Excluir</a>
                            </div>

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

    <div style="margin-top: 30px;">
        <a href="../index.php">⬅️ Voltar para Página Principal</a>
    </div>
</div>
</body>
</html>
