// pos.js - Arquivo principal de JavaSclet 
saleItems = [];
let selectedPaymentMethod = null;

// Função para adicionar item ao carrinho
function addToCart(product) {
    const existingItem = saleItems.find(item => item.id === product.id);
    
    if (existingItem) {
        existingItem.quantity += 1;
        existingItem.total = existingItem.quantity * existingItem.price;
    } else {
        saleItems.push({
            id: product.id,
            name: product.name,
            quantity: 1,
            price: parseFloat(product.price),
            total: parseFloat(product.price)
        });
    }
    
    updateCartDisplay();
    calculateTotals();
}
// Função para atualizar a exibição do carrinho
function updateCartDisplay() {
    const cartItems = document.getElementById('cartItems');
    cartItems.innerHTML = '';
    
    saleItems.forEach((item, index) => {
        const itemElement = document.createElement('div');
        itemElement.className = 'cart-item';
        itemElement.innerHTML = `
            <div class="cart-item-info">
                <h6 class="mb-0">${item.name}</h6>
                <small class="text-muted">MZN ${item.price.toFixed(2)} x ${item.quantity}</small>
            </div>
            <div class="cart-item-controls">
                <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, -1)">-</button>
                <span class="mx-2">${item.quantity}</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, 1)">+</button>
                <button class="btn btn-sm btn-outline-danger ms-2" onclick="removeItem(${index})">
                    <i class="mdi mdi-delete"></i>
                </button>
            </div>
        `;
        cartItems.appendChild(itemElement);
    });
}

// Função para atualizar quantidade
function updateQuantity(index, change) {
    const item = saleItems[index];
    const newQuantity = item.quantity + change;
    
    if (newQuantity > 0) {
        item.quantity = newQuantity;
        item.total = item.price * newQuantity;
        updateCartDisplay();
        calculateTotals();
    } else if (newQuantity === 0) {
        removeItem(index);
    }
}

// Função para remover item
function removeItem(index) {
    saleItems.splice(index, 1);
    updateCartDisplay();
    calculateTotals();
}
// Função para atualizar a exibição do carrinho
function updateCartDisplay() {
    const cartItems = document.getElementById('cartItems');
    cartItems.innerHTML = '';
    
    saleItems.forEach((item, index) => {
        const itemElement = document.createElement('div');
        itemElement.className = 'cart-item';
        itemElement.innerHTML = `
            <div class="cart-item-info">
                <h6 class="mb-0">${item.name}</h6>
                <small class="text-muted">MZN ${item.price.toFixed(2)} x ${item.quantity}</small>
            </div>
            <div class="cart-item-controls">
                <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, -1)">-</button>
                <span class="mx-2">${item.quantity}</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="updateQuantity(${index}, 1)">+</button>
                <button class="btn btn-sm btn-outline-danger ms-2" onclick="removeItem(${index})">
                    <i class="mdi mdi-delete"></i>
                </button>
            </div>
        `;
        cartItems.appendChild(itemElement);
    });
}

// Função para atualizar quantidade
function updateQuantity(index, change) {
    const item = saleItems[index];
    const newQuantity = item.quantity + change;
    
    if (newQuantity > 0) {
        item.quantity = newQuantity;
        item.total = item.price * newQuantity;
        updateCartDisplay();
        calculateTotals();
    } else if (newQuantity === 0) {
        removeItem(index);
    }
}

// Função para remover item
function removeItem(index) {
    saleItems.splice(index, 1);
    updateCartDisplay();
    calculateTotals();
}

// Função para calcular totais
function calculateTotals() {
    const subtotal = saleItems.reduce((sum, item) => sum + item.total, 0);
    document.getElementById('subtotal').textContent = `MZN ${subtotal.toFixed(2)}`;
    document.getElementById('total').textContent = `MZN ${subtotal.toFixed(2)}`;
    calculateChange();
}

// Função para selecionar método de pagamento
function selectPayment(method) {
    selectedPaymentMethod = method;
    document.querySelectorAll('.payment-card').forEach(card => {
        card.classList.remove('selected');
    });
    document.querySelector(`[onclick="selectPayment('${method}')"]`).classList.add('selected');
}

// Função para calcular troco
function calculateChange() {
    const total = parseFloat(document.getElementById('total').textContent.replace('MZN ', ''));
    const cashAmount = parseFloat(document.getElementById('cashAmount').value) || 0;
    const cardAmount = parseFloat(document.getElementById('cardAmount').value) || 0;
    const mpesaAmount = parseFloat(document.getElementById('mpesaAmount').value) || 0;
    const emolaAmount = parseFloat(document.getElementById('emolaAmount').value) || 0;
    
    const totalPaid = cashAmount + cardAmount + mpesaAmount + emolaAmount;
    const change = totalPaid - total;
    
    document.getElementById('changeAmount').value = change >= 0 ? `MZN ${change.toFixed(2)}` : 'Pagamento insuficiente';
    document.getElementById('btnFinalizeOrder').disabled = change < 0;
}

// Função para processar a venda
async function processSale() {
    if (saleItems.length === 0) {
        showNotification('Erro', 'Adicione itens ao carrinho antes de finalizar.', 'error');
        return;
    }

    const total = parseFloat(document.getElementById('total').textContent.replace('MZN ', ''));
    const saleData = {
        items: saleItems,
        cashPayment: parseFloat(document.getElementById('cashAmount').value) || 0,
        cardPayment: parseFloat(document.getElementById('cardAmount').value) || 0,
        mpesaPayment: parseFloat(document.getElementById('mpesaAmount').value) || 0,
        emolaPayment: parseFloat(document.getElementById('emolaAmount').value) || 0
    };

    try {
        const response = await fetch('gerir_vendas/process_sale.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(saleData)
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Sucesso', 'Venda realizada com sucesso!', 'success');
            printReceipt(result.saleId);
            resetSale();
        } else {
            showNotification('Erro', result.message, 'error');
        }
    } catch (error) {
        showNotification('Erro', 'Erro ao processar a venda.', 'error');
        console.error('Error:', error);
    }
}

// Função para limpar a venda
function resetSale() {
    saleItems = [];
    updateCartDisplay();
    calculateTotals();
    document.querySelectorAll('#cashAmount, #cardAmount, #mpesaAmount, #emolaAmount').forEach(input => {
        input.value = '';
    });
    document.getElementById('changeAmount').value = '';
    document.querySelectorAll('.payment-card').forEach(card => {
        card.classList.remove('selected');
    });
}

// Função para mostrar notificações
function showNotification(title, message, type) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="mdi mdi-${type === 'success' ? 'check-circle' : 'alert-circle'}"></i>
        <div>
            <h6 class="mb-1">${title}</h6>
            <p class="mb-0">${message}</p>
        </div>
    `;
    
    document.body.appendChild(notification);
    setTimeout(() => notification.classList.add('show'), 100);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Função para filtrar produtos
function filterProducts() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categoryId = document.getElementById('categorySelect').value;
    
    document.querySelectorAll('.product-item').forEach(item => {
        const matchesSearch = item.textContent.toLowerCase().includes(searchTerm);
        const matchesCategory = !categoryId || item.dataset.category === categoryId;
        item.style.display = matchesSearch && matchesCategory ? '' : 'none';
    });
}

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar listeners para inputs de pagamento
    document.querySelectorAll('#cashAmount, #cardAmount, #mpesaAmount, #emolaAmount')
        .forEach(input => input.addEventListener('input', calculateChange));

    // Inicializar filtros de categoria
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('categorySelect').value = btn.dataset.category === 'all' ? '' : btn.dataset.category;
            filterProducts();
        });
    });
});

// Função para gerar o conteúdo do recibo
function generateReceiptContent(isPreview = false) {
    const date = new Date().toLocaleString('pt-BR');
    const total = saleItems.reduce((sum, item) => sum + item.total, 0);
    
    const cashAmount = parseFloat(document.getElementById('cashAmount').value) || 0;
    const cardAmount = parseFloat(document.getElementById('cardAmount').value) || 0;
    const mpesaAmount = parseFloat(document.getElementById('mpesaAmount').value) || 0;
    const emolaAmount = parseFloat(document.getElementById('emolaAmount').value) || 0;
    
    const totalPaid = cashAmount + cardAmount + mpesaAmount + emolaAmount;
    const change = totalPaid - total;

    let content = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>${isPreview ? 'Pré-visualização do Recibo' : 'Recibo'}</title>
            <style>
                body {
                    font-family: 'Arial', sans-serif;
                    margin: 0;
                    padding: 20px;
                    font-size: 12px;
                }
                .receipt {
                    max-width: 300px;
                    margin: 0 auto;
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .logo {
                    max-width: 150px;
                    margin-bottom: 10px;
                }
                .company-info {
                    margin-bottom: 20px;
                }
                .divider {
                    border-top: 1px dashed #000;
                    margin: 10px 0;
                }
                .items {
                    margin-bottom: 20px;
                }
                .item {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 5px;
                }
                .totals {
                    margin-top: 10px;
                    border-top: 1px solid #000;
                    padding-top: 10px;
                }
                .payment-methods {
                    margin-top: 15px;
                }
                .footer {
                    text-align: center;
                    margin-top: 20px;
                    font-size: 10px;
                }
                @media print {
                    @page {
                        margin: 0;
                        size: 80mm 297mm;
                    }
                    body {
                        margin: 10px;
                    }
                    .no-print {
                        display: none;
                    }
                }
            </style>
        </head>
        <body>
            <div class="receipt">
                <div class="header">
                    <h2>Lu & Yoshi Catering</h2>
                    <div class="company-info">
                        <p>Av. Eduardo Mondlane, 1234</p>
                        <p>Quelimane, Moçambique</p>
                        <p>Tel: +258 21 123 456</p>
                        <p>NUIT: 123456789</p>
                    </div>
                    <div>
                        <p>Data: ${date}</p>
                        ${isPreview ? '<h3 style="color: red;">PRÉ-VISUALIZAÇÃO</h3>' : ''}
                    </div>
                </div>
                
                <div class="divider"></div>
                
                <div class="items">
                    <table style="width: 100%;">
                        <tr>
                            <th style="text-align: left;">Item</th>
                            <th style="text-align: right;">Qtd</th>
                            <th style="text-align: right;">Preço</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                        ${saleItems.map(item => `
                            <tr>
                                <td style="text-align: left;">${item.name}</td>
                                <td style="text-align: right;">${item.quantity}</td>
                                <td style="text-align: right;">MZN ${item.price.toFixed(2)}</td>
                                <td style="text-align: right;">MZN ${item.total.toFixed(2)}</td>
                            </tr>
                        `).join('')}
                    </table>
                </div>
                
                <div class="totals">
                    <div class="item">
                        <strong>Subtotal:</strong>
                        <span>MZN ${total.toFixed(2)}</span>
                    </div>
                    <div class="item">
                        <strong>Total:</strong>
                        <span>MZN ${total.toFixed(2)}</span>
                    </div>
                </div>
                
                <div class="payment-methods">
                    <h4>Método de Pagamento:</h4>
                    ${cashAmount > 0 ? `<div class="item">
                        <span>Dinheiro:</span>
                        <span>MZN ${cashAmount.toFixed(2)}</span>
                    </div>` : ''}
                    ${cardAmount > 0 ? `<div class="item">
                        <span>Cartão:</span>
                        <span>MZN ${cardAmount.toFixed(2)}</span>
                    </div>` : ''}
                    ${mpesaAmount > 0 ? `<div class="item">
                        <span>M-Pesa:</span>
                        <span>MZN ${mpesaAmount.toFixed(2)}</span>
                    </div>` : ''}
                    ${emolaAmount > 0 ? `<div class="item">
                        <span>E-mola:</span>
                        <span>MZN ${emolaAmount.toFixed(2)}</span>
                    </div>` : ''}
                </div>
                
                <div class="divider"></div>
                
                <div class="footer">
                    <p>Obrigado pela preferência!</p>
                    <p>www.luyoshcatering.co.mz</p>
                    ${isPreview ? 
                        '<p style="color: red;">ESTE É APENAS UM EXEMPLO - NÃO É UM RECIBO VÁLIDO</p>' : 
                        '<p>Este documento não serve como fatura</p>'
                    }
                </div>
                
                ${isPreview ? `
                    <div class="no-print" style="margin-top: 20px; text-align: center;">
                        <button onclick="window.print()" style="padding: 10px 20px;">
                            Imprimir Pré-visualização
                        </button>
                    </div>
                ` : ''}
            </div>
        </body>
        </html>
    `;

    return content;
}

// Função para pré-visualizar o recibo
function previewReceipt() {
    if (saleItems.length === 0) {
        showNotification('Erro', 'Adicione itens ao carrinho antes de pré-visualizar o recibo.', 'error');
        return;
    }

    const receiptContent = generateReceiptContent(true);
    const previewWindow = window.open('', '_blank', 'width=400,height=600');
    previewWindow.document.write(receiptContent);
    previewWindow.document.close();
}

// Função para imprimir o recibo final
function printReceipt(saleId) {
    if (!saleId) {
        const receiptContent = generateReceiptContent(false);
        const printWindow = window.open('', '_blank', 'width=400,height=600');
        printWindow.document.write(receiptContent);
        printWindow.document.close();
        printWindow.print();
        printWindow.close();
    } else {
        // Se tiver um ID de venda, usa a URL do servidor
        window.open(`print_receipt.php?id=${saleId}`, '_blank');
    }
}