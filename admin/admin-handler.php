<?php
$pdo = new PDO('mysql:host=localhost;dbname=analistacsc;charset=utf8mb4', 'root', 'afvm2611');

// Adicionar nova categoria
if (isset($_POST['novaCategoria'])) {
    $nome = $_POST['novaCategoria'];
    $cor = $_POST['cor'] ?? '#ffffff';

    $stmt = $pdo->prepare("INSERT INTO categorias (nome, cor) VALUES (?, ?)");
    $stmt->execute([$nome, $cor]);
    header('Location: admin.php');
    exit;
}

// Adicionar novo item (com possíveis subitens)
if (isset($_POST['novoItem'])) {
    $categoria_id = $_POST['categoria_id'];
    $nome = $_POST['novoItem'];
    $link = $_POST['link'] ?? null;

    $stmt = $pdo->prepare("INSERT INTO itens (categoria_id, nome, link) VALUES (?, ?, ?)");
    $stmt->execute([$categoria_id, $nome, $link]);

    $item_id = $pdo->lastInsertId();

    // Adicionar subitens (se existir)
    if (!empty($_POST['sub_nome'])) {
        foreach ($_POST['sub_nome'] as $index => $sub_nome) {
            $sub_link = $_POST['sub_link'][$index] ?? null;
            if (!empty($sub_nome)) {
                $stmtSub = $pdo->prepare("INSERT INTO sub_itens (item_id, nome, link) VALUES (?, ?, ?)");
                $stmtSub->execute([$item_id, $sub_nome, $sub_link]);
            }
        }
    }
    header('Location: admin.php');
    exit;
}

// Excluir categoria
if (isset($_GET['excluir_categoria'])) {
    $id = (int)$_GET['excluir_categoria'];

    // Deleta também os itens e subitens dessa categoria
    $stmtItens = $pdo->prepare("SELECT id FROM itens WHERE categoria_id = ?");
    $stmtItens->execute([$id]);
    $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

    foreach ($itens as $item) {
        $stmtSub = $pdo->prepare("DELETE FROM sub_itens WHERE item_id = ?");
        $stmtSub->execute([$item['id']]);
    }

    $stmt = $pdo->prepare("DELETE FROM itens WHERE categoria_id = ?");
    $stmt->execute([$id]);

    $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
    $stmt->execute([$id]);
    exit;
}

// Excluir item
if (isset($_GET['excluir_item'])) {
    $id = (int)$_GET['excluir_item'];

    $stmt = $pdo->prepare("DELETE FROM sub_itens WHERE item_id = ?");
    $stmt->execute([$id]);

    $stmt = $pdo->prepare("DELETE FROM itens WHERE id = ?");
    $stmt->execute([$id]);
    exit;
}

// Editar categoria
if (isset($_GET['editar_categoria'])) {
    $id = (int)$_GET['editar_categoria'];
    $data = json_decode(file_get_contents('php://input'), true);

    $nome = $data['nome'] ?? '';
    $cor = $data['cor'] ?? '#ffffff';

    $stmt = $pdo->prepare("UPDATE categorias SET nome = ?, cor = ? WHERE id = ?");
    $stmt->execute([$nome, $cor, $id]);

    echo json_encode(['status' => 'success']);
    exit;
}

// Editar item
if (isset($_GET['editar_item'])) {
    $id = (int)$_GET['editar_item'];
    $data = json_decode(file_get_contents('php://input'), true);

    $nome = $data['nome'] ?? '';
    $link = $data['link'] ?? null;

    $stmt = $pdo->prepare("UPDATE itens SET nome = ?, link = ? WHERE id = ?");
    $stmt->execute([$nome, $link, $id]);

    echo json_encode(['status' => 'success']);
    exit;
}

?>
