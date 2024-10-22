<!-- Modal para criar mesa -->
<div class="modal fade" id="createTableModal" tabindex="-1" role="dialog" aria-labelledby="createTableModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createTableModalLabel">Criar Nova Mesa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="form-group">
                        <label for="number">Número da Mesa</label>
                        <input type="number" class="form-control" id="number" name="number" required min="1">
                    </div>
                    <div class="form-group">
                        <label for="capacity">Capacidade</label>
                        <input type="number" class="form-control" id="capacity" name="capacity" required min="1">
                    </div>
                    <!-- O status será inicializado como 'livre' -->
                    <input type="hidden" name="status" value="free">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Criar Mesa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ocupar mesa -->
<div class="modal fade" id="occupyTableModal" tabindex="-1" role="dialog" aria-labelledby="occupyTableModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="occupyTableModalLabel">Ocupar Mesa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="occupy">
                    <input type="hidden" name="table_id" id="occupyTableId">
                    <p>Tem certeza que deseja ocupar esta mesa?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Ocupar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para liberar mesa -->
<div class="modal fade" id="freeTableModal" tabindex="-1" role="dialog" aria-labelledby="freeTableModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="freeTableModalLabel">Liberar Mesa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="free">
                    <input type="hidden" name="table_id" id="freeTableId">
                    <p>Tem certeza que deseja liberar esta mesa?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Liberar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para unir mesas -->
<div class="modal fade" id="mergeTablesModal" tabindex="-1" aria-labelledby="mergeTablesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Unir Mesas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="mergeTablesForm">
                <div class="modal-body">
                    <p>Selecione as mesas que deseja unir:</p>
                    <div class="row">
                        <?php foreach ($tables as $table): ?>
                        <?php if ($table['real_status'] == 'livre' && !$table['group_id']): ?>
                        <div class="col-md-4 mb-2">
                            <div class="card table-card" data-table-id="<?php echo $table['id']; ?>" style="cursor: pointer;">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Mesa <?php echo $table['number']; ?></h5>
                                    <p class="card-text">Capacidade: <?php echo $table['capacity']; ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Unir Mesas</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para separar mesas -->
<div class="modal fade" id="splitTablesModal" tabindex="-1" aria-labelledby="splitTablesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Separar Mesa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="splitTableForm">
                <div class="modal-body">
                    <input type="hidden" name="table_id" id="splitTableId">
                    <p>Confirma a separação desta mesa do grupo?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Separar Mesa</button>
                </div>
            </form>
        </div>
    </div>
</div>
