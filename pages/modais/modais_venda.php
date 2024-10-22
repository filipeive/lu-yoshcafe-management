    <!-- Modal para Nova Venda -->
    <div class="modal fade" id="newSaleModal" tabindex="-1" aria-labelledby="newSaleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newSaleModalLabel">Nova Venda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body mb-8">
                    <form id="newSaleForm">
                        <div class="row mb-8">
                            <div class="col-md-6">
                                <label for="product" class="form-label">Produto</label>
                                <select class="form-select" id="product" required>
                                    <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['id']; ?>"
                                        data-price="<?php echo $product['price']; ?>">
                                        <?php echo $product['name']; ?> - MZN
                                        <?php echo number_format($product['price'], 2); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="quantity" class="form-label">Quantidade</label>
                                <input type="number" class="form-control" id="quantity" min="1" value="1" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-success form-control" id="addItemButton"
                                    style="padding: 10px;">Adicionar</button>
                            </div>
                        </div>
                    </form>
                    <table class="table-responsive" id="saleItemsTable">
                        <thead>
                            <tr>
                                <style>
                                th {
                                    padding: 10px;
                                }
                                </style>
                                <th>Produto</th>
                                <th>Quantidade</th>
                                <th>Preço Unitário</th>
                                <th>Total</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">Total da Venda</th>
                                <th id="saleTotalAmount">MZN 0.00</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- Seção de Pagamento -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="cashPayment" class="form-label">Pagamento em Dinheiro</label>
                            <input type="number" class="form-control" id="cashPayment" min="0" value="0">
                        </div>
                        <div class="col-md-3">
                            <label for="cardPayment" class="form-label">Pagamento com Cartão</label>
                            <input type="number" class="form-control" id="cardPayment" min="0" value="0">
                        </div>
                        <div class="col-md-3">
                            <label for="mpesaPayment" class="form-label">Pagamento com M-Pesa</label>
                            <input type="number" class="form-control" id="mpesaPayment" min="0" value="0">
                        </div>
                        <div class="col-md-3">
                            <label for="emolaPayment" class="form-label">Pagamento com Emola</label>
                            <input type="number" class="form-control" id="emolaPayment" min="0" value="0">
                        </div>
                    </div>

                    <!-- Campo para Troco -->
                    <div class="mb-3">
                        <label for="change" class="form-label">Troco</label>
                        <input type="text" class="form-control" id="change" readonly value="MZN 0.00">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="finalizeSaleButton">Imprimir a Conta/Finalizar a
                        Venda</button>
                </div>
            </div>
        </div>
    </div>
</div>