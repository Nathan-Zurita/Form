<div class="row mb-4">
    <div class="col-12">
        <div class="card sindppenal-card">
            <div class="card-body sindppenal-header">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <div class="mb-3 mb-md-0">
                        <h1 class="h3 text-sindppenal mb-1">
                            <img src="../assets/icons/dashboard/graph.svg" alt="Dashboard" class="svg-icon size-lg">
                            Dashboard SINDPPENAL
                        </h1>
                        <p class="text-muted mb-0">Bem-vindo, <?php echo htmlspecialchars($_SESSION['admin_nome']); ?>!</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 sindppenal-btn-group">
                        <a href="dashboard.php" class="btn btn-secondary btn-sm">
                            <img src="../assets/icons/dashboard/casa.svg" alt="Dashboard" class="svg-icon-white size-sm">
                            Dashboard
                        </a>
                        <a href="contatos.php" class="btn btn-primary btn-sm">
                            <img src="../assets/icons/dashboard/users-2.svg" alt="Gerenciar" class="svg-icon-white size-sm">
                            Gerenciar Contatos
                        </a>
                        <a href="unidades.php" class="btn btn-primary btn-sm">
                            <img src="../assets/icons/dashboard/unidade.svg" alt="Unidades" class="svg-icon-white size-sm">
                            Gerenciar Unidades
                        </a>
                        <a href="combinacoes.php" class="btn btn-primary btn-sm">
                            <img src="../assets/icons/formulario/combination.svg" alt="Combinações" class="svg-icon-white size-sm">
                            Combinações
                        </a>
                        <a href="../index.php" class="btn btn-primary btn-sm" target="_blank">
                            <img src="../assets/icons/formulario/form.svg" alt="Site Público" class="svg-icon-white size-sm">
                            Formulário Público
                        </a>
                        <a href="contatos.php?action=add" class="btn btn-primary btn-sm">
                            <img src="../assets/icons/dashboard/add-user.svg" alt="Novo" class="svg-icon-white size-sm">
                            Novo Contato
                        </a>
                        <a href="criar_adm.php" class="btn btn-primary btn-sm">
                            <img src="../assets/icons/dashboard/add.svg" alt="Criar novo administrador" class="svg-icon-white size-sm">
                            Criar Administrador
                        </a>
                        <a href="logout.php" class="btn btn-danger btn-sm">
                            <img src="../assets/icons/dashboard/porta.svg" alt="Sair" class="svg-icon-white size-sm">
                            Sair
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>