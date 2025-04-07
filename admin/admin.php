<?php
$pdo = new PDO('mysql:host=localhost;dbname=analistacsc;charset=utf8mb4', 'root', 'afvm2611');

// ADICIONAR ITEM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novoItem'])) {
    $categoria = $_POST['categoria'];
    $nome = trim($_POST['novoItem']);
    $link = trim($_POST['link']);

    if (!empty($nome)) {
        // Inserir o item principal
        $stmt = $pdo->prepare("INSERT INTO itens (categoria, nome, link) VALUES (?, ?, ?)");
        $stmt->execute([$categoria, $nome, $link]);
        $item_id = $pdo->lastInsertId();

        // Inserir os subitens na tabela separada
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
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $pdo->prepare("DELETE FROM itens WHERE id = ?")->execute([$id]);
    // subitens serão excluídos automaticamente se tiver ON DELETE
    header("Location: admin.php");
    exit;
}

// PEGAR ITENS
$stmt = $pdo->query("SELECT * FROM itens ORDER BY categoria, nome");
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// PEGAR SUBITENS
$stmtSub = $pdo->query("SELECT * FROM sub_itens ORDER BY item_id, nome");
$subitens = $stmtSub->fetchAll(PDO::FETCH_ASSOC);

// Agrupar subitens por item_id
$mapaSubitens = [];
foreach ($subitens as $sub) {
    $mapaSubitens[$sub['item_id']][] = $sub;
}

// Agrupar itens por categoria
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
    <title>Admin - ANALISTA CSC</title>
    <link rel="stylesheet" href="./admin.css">
</head>
<body>
<div class="container">
    <h1>⚙️ Admin - ANALISTA CSC</h1>

    <form method="POST" class="admin-form">
        <label>Categoria:</label>
        <select name="categoria">
            <option value="controles">🔥 CONTROLES</option>
            <option value="ferramentas">🛠️ FERRAMENTAS</option>
            <option value="acessos">🔑 ACESSOS</option>
        </select><br><br>

        <label>Nome do Item:</label>
        <input type="text" name="novoItem" placeholder="Ex: 📋 Novo Controle" required>

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

        <button type="submit">➕ Adicionar</button>
    </form>

    <hr><br>

    <?php foreach ($categorias as $cat => $lista): ?>
        <h2><?= strtoupper($cat) ?></h2>
        <ul>
            <?php foreach ($lista as $item): ?>
                <li>
                    <?php if ($item['link']): ?>
                        <a href="<?= htmlspecialchars($item['link']) ?>" target="_blank">
                            <?= htmlspecialchars($item['nome']) ?>
                        </a>
                    <?php else: ?>
                        <?= htmlspecialchars($item['nome']) ?>
                    <?php endif; ?>

                    <a href="?excluir=<?= $item['id'] ?>" style="color:red;">[Excluir]</a>

                    <?php if (!empty($item['sub_itens'])): ?>
                        <ul>
                            <?php foreach ($item['sub_itens'] as $sub): ?>
                                <li>
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
    <?php endforeach; ?>

    <div style="margin-top: 30px;">
        <a href="../index.php" style="color: yellow;">⬅️ Voltar para Página Principal</a>
    </div>
</div>
</body>
</html>
