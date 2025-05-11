<?php
// Conectar ao banco
$pdo = new PDO('mysql:host=localhost;dbname=analistacsc;charset=utf8mb4', 'root', 'afvm2611');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Adicionar nova categoria
if (isset($_POST['novaCategoria'])) {
    $nome = $_POST['novaCategoria'];
    $cor = $_POST['cor'] ?? '#ffffff';

    $stmt = $pdo->prepare("INSERT INTO categorias (nome, cor) VALUES (?, ?)");
    $stmt->execute([$nome, $cor]);

    header('Location: admin.php');
    exit;
}

// Adicionar novo item e subitens
if (isset($_POST['novoItem'])) {
    $categoria_id = $_POST['categoria_id'] ?? null;
    $nome = $_POST['novoItem'];
    $link = $_POST['link'] ?? '';

    // Verificação básica do categoria_id
    if (!$categoria_id || !is_numeric($categoria_id)) {
        die('Categoria inválida. Selecione uma categoria existente.');
    }

    // Verificação no banco se a categoria existe
    $stmt = $pdo->prepare("SELECT id FROM categorias WHERE id = ?");
    $stmt->execute([$categoria_id]);
    if (!$stmt->fetch()) {
        die('Categoria não encontrada no banco.');
    }

    // Inserir o item
    $stmt = $pdo->prepare("INSERT INTO itens (categoria_id, nome, link) VALUES (?, ?, ?)");
    $stmt->execute([$categoria_id, $nome, $link]);
    $item_id = $pdo->lastInsertId();

    // Inserir subitens se existirem
    if (!empty($_POST['sub_nome']) && is_array($_POST['sub_nome'])) {
        foreach ($_POST['sub_nome'] as $key => $sub_nome) {
            if (!empty($sub_nome)) {
                $sub_link = $_POST['sub_link'][$key] ?? '';
                $stmtSub = $pdo->prepare("INSERT INTO sub_itens (item_id, nome, link) VALUES (?, ?, ?)");
                $stmtSub->execute([$item_id, $sub_nome, $sub_link]);
            }
        }
    }

    header('Location: admin.php');
    exit;
}

// Editar categoria via fetch
if (isset($_GET['editar_categoria'])) {
    $id = $_GET['editar_categoria'];
    $dados = json_decode(file_get_contents('php://input'), true);

    $nome = $dados['nome'] ?? '';
    $cor = $dados['cor'] ?? '#ffffff';

    $stmt = $pdo->prepare("UPDATE categorias SET nome = ?, cor = ? WHERE id = ?");
    $stmt->execute([$nome, $cor, $id]);

    echo json_encode(['status' => 'success']);
    exit;
}

// Editar item via fetch
if (isset($_GET['editar_item'])) {
    $id = $_GET['editar_item'];
    $dados = json_decode(file_get_contents('php://input'), true);

    $nome = $dados['nome'] ?? '';
    $link = $dados['link'] ?? '';

    $stmt = $pdo->prepare("UPDATE itens SET nome = ?, link = ? WHERE id = ?");
    $stmt->execute([$nome, $link, $id]);

    echo json_encode(['status' => 'success']);
    exit;
}

// Excluir categoria
if (isset($_GET['excluir_categoria'])) {
    $id = $_GET['excluir_categoria'];

    // Primeiro, excluir todos os itens dessa categoria (subitens serão apagados pelo ON DELETE CASCADE)
    $stmt = $pdo->prepare("DELETE FROM itens WHERE categoria_id = ?");
    $stmt->execute([$id]);

    // Agora excluir a categoria
    $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['status' => 'success']);
    exit;
}

// Excluir item
if (isset($_GET['excluir_item'])) {
    $id = $_GET['excluir_item'];

    // Primeiro, excluir os subitens desse item
    $stmt = $pdo->prepare("DELETE FROM sub_itens WHERE item_id = ?");
    $stmt->execute([$id]);

    // Agora excluir o item
    $stmt = $pdo->prepare("DELETE FROM itens WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['status' => 'success']);
    exit;
}
?>
