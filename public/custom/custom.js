let saleItems = [];
        document.getElementById('addItemButton').addEventListener('click', function() {
            const productSelect = document.getElementById('product');
            const product = productSelect.options[productSelect.selectedIndex];
            const quantity = document.getElementById('quantity').value;
            const price = parseFloat(product.dataset.price);

            if (quantity <= 0) {
                alert('Por favor, insira uma quantidade válida.');
                return;
            }

            const item = {
                id: product.value,
                name: product.text.split(' - ')[0], // Pega apenas o nome do produto
                quantity: parseInt(quantity),
                price: price,
                total: price * parseInt(quantity)
            };

            saleItems.push(item);
            updateSaleItemsTable();

            // Resetar campos
            document.getElementById('quantity').value = 1;
            productSelect.selectedIndex = 0;
        });

        function updateSaleItemsTable() {
            const tableBody = document.querySelector('#saleItemsTable tbody');
            const totalAmountElement = document.getElementById('saleTotalAmount');
            let totalAmount = 0;

            tableBody.innerHTML = '';

            saleItems.forEach((item, index) => {
                const row = tableBody.insertRow();
                row.innerHTML = `
            <td>${item.name}</td>
            <td>${item.quantity}</td>
            <td>MZN ${item.price.toFixed(2)}</td>
            <td>MZN ${item.total.toFixed(2)}</td>
            <td><button class="btn btn-danger btn-sm" onclick="removeItem(${index})">Remover</button></td>
        `;
                totalAmount += item.total;
            });

            totalAmountElement.textContent = `MZN ${totalAmount.toFixed(2)}`;
        }

        function removeItem(index) {
            saleItems.splice(index, 1);
            updateSaleItemsTable();
        }

        document.getElementById('finalizeSaleButton').addEventListener('click', function() {
            if (saleItems.length === 0) {
                alert('Por favor, adicione itens à venda antes de finalizar.');
                return;
            }

            const paymentMethod = document.getElementById('paymentMethod').value;

            fetch('gerir_vendas/process_sale.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        items: saleItems,
                        paymentMethod: paymentMethod
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Venda realizada com sucesso!');
                        // Abrir o recibo em uma nova janela
                        window.open(data.receiptUrl, '_blank');
                        // Fechar o modal e limpar os itens
                        $('#newSaleModal').modal('hide');
                        saleItems = [];
                        updateSaleItemsTable();
                        // Recarregar a página para atualizar o histórico de vendas
                        location.reload();
                    } else {
                        alert('Erro ao realizar a venda: ' + data.message);
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('Erro ao processar a venda.');
                });
        });

        function viewSaleDetails(saleId) {
            window.location.href = 'sale_details.php?id=' + saleId;
        }

        function printReceipt(saleId) {
            window.open('print_receipt.php?id=' + saleId, /*'_blank'*/ );
        }
