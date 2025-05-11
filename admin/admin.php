    <?php
    $pdo = new PDO('mysql:host=localhost;dbname=analistacsc;charset=utf8mb4', 'root', 'afvm2611');

    // Buscar categorias
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY id ASC");
    $categoriasDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar itens
    $stmt = $pdo->query("SELECT * FROM itens ORDER BY id ASC");
    $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar sub_itens 
    $stmtSub = $pdo->query("SELECT * FROM sub_itens ORDER BY item_id, nome");
    $subitens = $stmtSub->fetchAll(PDO::FETCH_ASSOC);

    // Mapear sub_itens por item_id
    $mapaSubitens = [];
    foreach ($subitens as $sub) {
        $mapaSubitens[$sub['item_id']][] = $sub;
    }

    // Organizar categorias
    $categorias = [];
    foreach ($categoriasDb as $cat) {
        $categorias[$cat['id']] = [
            'nome' => $cat['nome'],
            'cor' => $cat['cor'] ?? '#ffffff',
            'itens' => []
        ];
    }

    // Colocar itens nas categorias
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

        <!-- Formulário para Nova Categoria -->
        <form id="formCategoria" class="admin-form" method="post" action="admin-handler.php">
            <label>Nova Categoria:</label>
            <input type="text" name="novaCategoria" placeholder="Ex: 📂 NOVA CATEGORIA" required>

            <label>Cor da Categoria:</label>
            <input type="color" name="cor" value="#ffffff">

            <button type="submit">➕ Criar Categoria</button>
        </form>

        <hr><br>

        <!-- Formulário para Novo Item -->
        <form id="formItem" class="admin-form" method="post" action="admin-handler.php">
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

        <!-- Seções de Categorias e Itens -->
        <div class="sections">
            <?php foreach ($categorias as $catId => $cat): ?>
                <div class="section" style="border-left: 10px solid <?= htmlspecialchars($cat['cor']) ?>;">
                    <h2>
                        <?= htmlspecialchars($cat['nome']) ?>
                        <span style="float:right; font-size:14px;">
                            <a href="#" class="btn-editar" onclick="abrirModalCategoria(<?= $catId ?>, '<?= htmlspecialchars($cat['nome'], ENT_QUOTES) ?>', '<?= $cat['cor'] ?>')">✏</a>
                            <a href="#" class="btn-excluir" onclick="excluirCategoria(<?= $catId ?>, this)">🗑</a>
                        </span>
                    </h2>
                    <ul>
                        <?php foreach ($cat['itens'] as $item): ?>
                            <li>
                                <?php if ($item['link']): ?>
                                    <a href="<?= htmlspecialchars($item['link']) ?>" target="_blank">
                                        <?= htmlspecialchars($item['nome']) ?>
                                    </a>
                                <?php else: ?>
                                    <span><?= htmlspecialchars($item['nome']) ?></span>
                                <?php endif; ?>
                                <div class="button-group">
                                    <a href="#" class="btn-editar" onclick="abrirModalItem(<?= $item['id'] ?>, '<?= htmlspecialchars($item['nome'], ENT_QUOTES) ?>', '<?= htmlspecialchars($item['link']) ?>')">✏</a>
                                    <a href="#" class="btn-excluir" onclick="excluirItem(<?= $item['id'] ?>, this)">🗑</a>
                                </div>

                                <!-- Subitens -->
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

    <!-- MODAIS -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <h2>🔐 Acesso Restrito</h2>
            <input type="text" id="loginUser" placeholder="Usuário">
            <input type="password" id="loginPass" placeholder="Senha">
            <button onclick="validarLogin()">Entrar</button>
            <p id="loginError">Usuário ou senha incorretos.</p>
        </div>
    </div>

    <div id="modalEditarCategoria" class="modal">
        <div class="modal-content">
            <h2>✏ Editar Categoria</h2>
            <input type="hidden" id="editCatId">
            <input type="text" id="editCatNome" placeholder="Nome da Categoria">
            <input type="color" id="editCatCor">
            <button onclick="salvarCategoria()">Salvar</button>
        </div>
    </div>

    <div id="modalEditarItem" class="modal">
        <div class="modal-content">
            <h2>✏ Editar Item</h2>
            <input type="hidden" id="editItemId">
            <input type="text" id="editItemNome" placeholder="Nome do Item">
            <input type="text" id="editItemLink" placeholder="Link do Item">
            <button onclick="salvarItem()">Salvar</button>
        </div>
    </div>

    <script>
        function validarLogin() {
            const user = document.getElementById('loginUser').value;
            const pass = document.getElementById('loginPass').value;
            const errorMsg = document.getElementById('loginError');

            if (user === 'admin' && pass === 'adm1234') {
                localStorage.setItem('logado', 'true'); // salva no navegador que está logado
                document.getElementById('loginModal').style.display = 'none';
            } else {
                errorMsg.style.display = 'block';
            }
        }

        window.onload = () => {
            if (!localStorage.getItem('logado')) {
                document.getElementById('loginModal').style.display = 'flex';
            }
        };

        function abrirModalCategoria(id, nome, cor) {
            document.getElementById('editCatId').value = id;
            document.getElementById('editCatNome').value = nome;
            document.getElementById('editCatCor').value = cor;
            document.getElementById('modalEditarCategoria').style.display = 'flex';
        }

        function abrirModalItem(id, nome, link) {
            document.getElementById('editItemId').value = id;
            document.getElementById('editItemNome').value = nome;
            document.getElementById('editItemLink').value = link;
            document.getElementById('modalEditarItem').style.display = 'flex';
        }

        function salvarCategoria() {
            const id = document.getElementById('editCatId').value;
            const nome = document.getElementById('editCatNome').value;
            const cor = document.getElementById('editCatCor').value;

            fetch(`admin-handler.php?editar_categoria=${id}`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({nome, cor})
            }).then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload(); // Recarrega a página
                }
            });
        }

        function salvarItem() {
            const id = document.getElementById('editItemId').value;
            const nome = document.getElementById('editItemNome').value;
            const link = document.getElementById('editItemLink').value;

            fetch(`admin-handler.php?editar_item=${id}`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({nome, link})
            }).then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                }
            });
        }

        function excluirCategoria(id, linkRef) {
            if (confirm('Tem certeza que quer excluir esta categoria?')) {
                fetch(`admin-handler.php?excluir_categoria=${id}`).then(() => {
                    linkRef.closest('.section').remove();
                });
            }
        }

        function excluirItem(id, linkRef) {
            if (confirm('Tem certeza que quer excluir este item?')) {
                fetch(`admin-handler.php?excluir_item=${id}`).then(() => {
                    linkRef.closest('li').remove();
                });
            }
        }
    </script>

    </body>
    </html>
