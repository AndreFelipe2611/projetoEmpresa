<?php
$pdo = new PDO('mysql:host=localhost;dbname=analistacsc;charset=utf8mb4', 'root', 'afvm2611');
header('Content-Type: application/json');

// Adicionar Categoria
if (isset($_POST['novaCategoria'])) {
    $nome = trim($_POST['novaCategoria']);
    $cor = $_POST['cor'] ?? '#ffffff';

    if (!empty($nome)) {
        $stmt = $pdo->prepare("INSERT INTO categorias (nome, cor) VALUES (?, ?)");
        $stmt->execute([$nome, $cor]);
        $id = $pdo->lastInsertId();
        echo json_encode(['status' => 'success', 'data' => ['id' => $id, 'nome' => $nome, 'cor' => $cor]]);
        exit;
    }
}

// Adicionar Item
if (isset($_POST['novoItem'])) {
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

        echo json_encode(['status' => 'success', 'data' => [
            'id' => $item_id,
            'categoria_id' => $categoria_id,
            'nome' => $nome,
            'link' => $link
        ]]);
        exit;
    }
}

// Excluir Categoria
if (isset($_GET['excluir_categoria'])) {
    $id = intval($_GET['excluir_categoria']);
    $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['status' => 'success']);
    exit;
}

// Excluir Item
if (isset($_GET['excluir_item'])) {
    $id = intval($_GET['excluir_item']);
    $pdo->prepare("DELETE FROM sub_itens WHERE item_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM itens WHERE id = ?")->execute([$id]);
    echo json_encode(['status' => 'success']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Requisição inválida']);
