

<!-- Modal Add Product -->
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Adicionar Novo Produto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addProductForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="product_name">Nome do Produto</label>
                        <input type="text" class="form-control" id="product_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="product_description">Descrição</label>
                        <textarea class="form-control" id="product_description" name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="product_price">Preço</label>
                        <input type="number" class="form-control" id="product_price" name="price" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="product_stock_quantity">Quantidade em Estoque</label>
                        <input type="number" class="form-control" id="product_stock_quantity" name="stock_quantity"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="product_category_id">Categoria</label>
                        <select class="form-control" id="product_category_id" name="category_id" required>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="product_image">Imagem do Produto</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="product_image" name="product_image"
                                accept="image/*">
                            <label class="custom-file-label" for="product_image">Escolher arquivo...</label>
                        </div>
                        <small class="form-text text-muted">
                            Para produtos da categoria "Comida", a imagem será automaticamente obtida do menu se
                            disponível.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Edit Product -->
<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Editar Produto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editProductForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="edit_product_id" name="id">
                    <div class="form-group">
                        <label for="edit_product_name">Nome do Produto</label>
                        <input type="text" class="form-control" id="edit_product_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_product_description">Descrição</label>
                        <textarea class="form-control" id="edit_product_description" name="description"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_product_price">Preço</label>
                        <input type="number" class="form-control" id="edit_product_price" name="price" step="0.01"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="edit_product_stock_quantity">Quantidade em Estoque</label>
                        <input type="number" class="form-control" id="edit_product_stock_quantity" name="stock_quantity"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="edit_product_category_id">Categoria</label>
                        <select class="form-control" id="edit_product_category_id" name="category_id" required>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Imagem Atual</label>
                        <div id="current_image_container" class="mb-2">
                            <img id="current_product_image" src="" alt="Imagem atual do produto" class="img-thumbnail"
                                style="max-height: 150px; display: none;">
                            <p id="no_image_text" class="text-muted">Nenhuma imagem disponível</p>
                        </div>
                        <label for="edit_product_image">Nova Imagem</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="edit_product_image" name="product_image"
                                accept="image/*">
                            <label class="custom-file-label" for="edit_product_image">Escolher arquivo...</label>
                        </div>
                        <small class="form-text text-muted">
                            Para produtos da categoria "Comida", a imagem será automaticamente obtida do menu se
                            disponível.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /.modal-Categoria -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Adicionar Nova Categoria</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addCategoryForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="category_name">Nome da Categoria</label>
                        <input type="text" class="form-control" id="category_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>