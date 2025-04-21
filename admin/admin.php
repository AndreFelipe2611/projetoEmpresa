<?php
$pdo = new PDO('mysql:host=localhost;dbname=analistacsc;charset=utf8mb4', 'root', 'afvm2611');

$stmt = $pdo->query("SELECT * FROM categorias ORDER BY id ASC");
$categoriasDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT * FROM itens ORDER BY id ASC");
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
        'cor' => $cat['cor'] ?? '#ffffff',
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
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="container">
    <h1>⚙️ Admin - ANALISTA CSC</h1>

    <form id="formCategoria" class="admin-form">
        <label>Nova Categoria:</label>
        <input type="text" name="novaCategoria" placeholder="Ex: 📂 NOVA CATEGORIA" required>

        <label>Cor da Categoria:</label>
        <input type="color" name="cor" value="#ffffff">

        <button type="submit">➕ Criar Categoria</button>
    </form>

    <hr><br>

    <form id="formItem" class="admin-form">
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
            <div class="section" style="border-left: 10px solid <?= htmlspecialchars($cat['cor']) ?>;">
                <h2>
                    <?= htmlspecialchars($cat['nome']) ?>
                    <a href="#" onclick="excluirCategoria(<?= $catId ?>, this)" style="float:right; font-size: 14px;">[X]</a>
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
                                <a href="#" onclick="excluirItem(<?= $item['id'] ?>, this)">🗑 Excluir</a>
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

<!-- MODAL DE LOGIN -->
<div id="loginModal" class="modal">
    <div class="modal-content">
        <h2>🔐 Acesso Restrito</h2>
        <input type="text" id="loginUser" placeholder="Usuário">
        <input type="password" id="loginPass" placeholder="Senha">
        <button onclick="validarLogin()">Entrar</button>
        <p id="loginError">Usuário ou senha incorretos.</p>
    </div>
</div>

<script>
    function validarLogin() {
        const user = document.getElementById('loginUser').value;
        const pass = document.getElementById('loginPass').value;
        const errorMsg = document.getElementById('loginError');

        if (user === 'admin' && pass === 'adm1234') {
            document.getElementById('loginModal').style.display = 'none';
        } else {
            errorMsg.style.display = 'block';
        }
    }

    window.onload = () => {
        document.getElementById('loginModal').style.display = 'flex';
    };

    document.getElementById('formCategoria').addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const response = await fetch('admin-handler.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.status === 'success') {
            const data = result.data;
            alert('Categoria adicionada!');
            this.reset();

            const novaHTML = `
                <div class="section" style="border-left: 10px solid ${data.cor};">
                    <h2>${data.nome} <a href="#" onclick="excluirCategoria(${data.id}, this)" style="float:right;">[X]</a></h2>
                    <ul></ul>
                </div>
            `;
            document.querySelector('.sections').insertAdjacentHTML('beforeend', novaHTML);
        } else {
            alert('Erro ao adicionar categoria.');
        }
    });

    document.getElementById('formItem').addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const response = await fetch('admin-handler.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.status === 'success') {
            const data = result.data;
            alert('Item adicionado!');
            this.reset();

            const sections = document.querySelectorAll('.sections .section');
            const targetSection = sections[data.categoria_id - 1]?.querySelector('ul');
            const novoItem = `
                <li>
                    ${data.link ? `<a href="${data.link}" target="_blank">${data.nome}</a>` : data.nome}
                    <div class="button-group">
                        <a href="#" onclick="excluirItem(${data.id}, this)">🗑 Excluir</a>
                    </div>
                </li>
            `;
            if (targetSection) {
                targetSection.insertAdjacentHTML('beforeend', novoItem);
            }
        } else {
            alert('Erro ao adicionar item.');
        }
    });

    // EXCLUSÕES SEM CONFIRM
    function excluirCategoria(id, linkRef) {
        fetch(`admin-handler.php?excluir_categoria=${id}`)
            .then(() => {
                linkRef.closest('.section').remove();
            });
    }

    function excluirItem(id, linkRef) {
        fetch(`admin-handler.php?excluir_item=${id}`)
            .then(() => {
                linkRef.closest('li').remove();
            });
    }
</script>
</body>
</html>
