<?php


function get_total_clients() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM clients");
    return $stmt->fetchColumn();
}

function get_table_by_id($id) {
    global $pdo;
    $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
    $stmt = $pdo->prepare("SELECT * FROM tables WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Função para obter vendas por categoria
function get_category_sales() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT c.name AS category, SUM(si.quantity * p.price) AS total
        FROM sale_items si
        JOIN products p ON si.product_id = p.id
        JOIN categories c ON p.category_id = c.id
        JOIN sales s ON si.sale_id = s.id
        WHERE s.status = 'completed'
        GROUP BY c.name
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    return $categories ?: []; // Retorna um array vazio se não houver vendas
}
/**
 * Funções para Relatórios do Restaurante
 */

/**
 * Obtém o total de vendas
 * @return float
 */

function get_total_sales() {
    global $pdo;
    try {
        $query = "SELECT COALESCE(SUM(total_amount), 0) as total 
                  FROM sales 
                  WHERE status = 'completed'";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return floatval($result['total']);
    } catch (PDOException $e) {
        error_log("Erro ao buscar total de vendas: " . $e->getMessage());
        return 0;
    }
}

/**
 * Obtém o total de pedidos
 * @return int
 */

function get_total_orders() {
    global $pdo;
    try {
        $query = "SELECT COUNT(*) as total 
                  FROM orders 
                  WHERE status IN ('completed', 'paid')";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($result['total']);
    } catch (PDOException $e) {
        error_log("Erro ao buscar total de pedidos: " . $e->getMessage());
        return 0;
    }
}

/**
 * Obtém as vendas mensais do último ano
 * @return array
 */

function get_monthly_sales() {
    global $pdo;
    try {
        $query = "SELECT 
                    DATE_FORMAT(sale_date, '%Y-%m') as month,
                    SUM(total_amount) as total
                  FROM sales
                  WHERE 
                    status = 'completed' 
                    AND sale_date >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)
                  GROUP BY DATE_FORMAT(sale_date, '%Y-%m')
                  ORDER BY month ASC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $monthly_sales = [];
        
        foreach ($results as $row) {
            // Formatar o nome do mês
            $date = DateTime::createFromFormat('Y-m', $row['month']);
            $month_name = strftime('%b/%Y', $date->getTimestamp());
            $monthly_sales[$month_name] = floatval($row['total']);
        }
        
        return $monthly_sales;
    } catch (PDOException $e) {
        error_log("Erro ao buscar vendas mensais: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtém as vendas semanais do último mês
 * @return array
 */
function get_weekly_sales() {
    global $pdo;
    try {
        $query = "SELECT 
                    DATE(sale_date) as sale_day,
                    SUM(total_amount) as total
                  FROM sales
                  WHERE 
                    status = 'completed'
                    AND sale_date >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
                  GROUP BY DATE(sale_date)
                  ORDER BY sale_day ASC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $weekly_sales = [];
        
        foreach ($results as $row) {
            // Formatar o dia da semana
            $date = DateTime::createFromFormat('Y-m-d', $row['sale_day']);
            $day_name = strftime('%a', $date->getTimestamp());
            $weekly_sales[$day_name] = floatval($row['total']);
        }
        
        return $weekly_sales;
    } catch (PDOException $e) {
        error_log("Erro ao buscar vendas semanais: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtém os pedidos recentes
 * @param int $limit Número de pedidos a retornar
 * @return array
 */
function get_recent_orders($limit = 10) {
    global $pdo;
    try {
        $query = "SELECT 
                    o.id,
                    o.table_id,
                    o.status,
                    o.total_amount,
                    o.created_at,
                    COUNT(oi.id) as items_count
                  FROM orders o
                  LEFT JOIN order_items oi ON o.id = oi.order_id
                  GROUP BY o.id
                  ORDER BY o.created_at DESC
                  LIMIT :limit";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao buscar pedidos recentes: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtém os produtos mais vendidos
 * @param int $limit Número de produtos a retornar
 * @return array
 */
function get_top_selling_products($limit = 5) {
    global $pdo;
    try {
        $query = "SELECT 
                    p.id,
                    p.name,
                    p.price,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.quantity * p.price) as total_revenue
                  FROM products p
                  INNER JOIN order_items oi ON p.id = oi.product_id
                  INNER JOIN orders o ON oi.order_id = o.id
                  WHERE o.status IN ('completed', 'paid')
                  GROUP BY p.id
                  ORDER BY total_quantity DESC
                  LIMIT :limit";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao buscar produtos mais vendidos: " . $e->getMessage());
        return [];
    }
}

/**
 * Obtém o total de vendas por método de pagamento
 * @return array
 */
/*
function get_sales_by_payment_method() {
    global $pdo;
    try {
        $query = "SELECT 
                    SUM(cash_amount) as cash_total,
                    SUM(card_amount) as card_total,
                    SUM(mpesa_amount) as mpesa_total,
                    SUM(emola_amount) as emola_total
                  FROM sales
                  WHERE status = 'completed'";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao buscar vendas por método de pagamento: " . $e->getMessage());
        return [
            'cash_total' => 0,
            'card_total' => 0,
            'mpesa_total' => 0,
            'emola_total' => 0
        ];
    }
}
*/
/**
 * Obtém as estatísticas de vendas do dia atual
 * @return array
 */
function get_today_sales_stats() {
    global $pdo;
    try {
        $query = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_amount,
                    AVG(total_amount) as average_order
                  FROM sales
                  WHERE 
                    status = 'completed'
                    AND DATE(sale_date) = CURRENT_DATE";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Adicionar formatação aos valores
        return [
            'total_orders' => intval($result['total_orders']),
            'total_amount' => floatval($result['total_amount']),
            'average_order' => floatval($result['average_order'])
        ];
    } catch (PDOException $e) {
        error_log("Erro ao buscar estatísticas do dia: " . $e->getMessage());
        return [
            'total_orders' => 0,
            'total_amount' => 0,
            'average_order' => 0
        ];
    }
}
/**
 * Obtém o número de pedidos feitos hoje
 * @return int
 */
function get_today_orders() {
    global $pdo;
    try {
        $query = "SELECT COUNT(*) as total 
                  FROM orders 
                  WHERE DATE(created_at) = CURRENT_DATE";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return intval($result['total']);
    } catch (PDOException $e) {
        error_log("Erro ao buscar pedidos de hoje: " . $e->getMessage());
        return 0;
    }
}

/**
 * Obtém estatísticas detalhadas dos pedidos de hoje
 * @return array
 */
function get_today_orders_stats() {
    global $pdo;
    try {
        $query = "SELECT 
                    COUNT(*) as total_orders,
                    COALESCE(SUM(total_amount), 0) as total_amount,
                    COALESCE(AVG(total_amount), 0) as average_amount,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_orders,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_orders
                  FROM orders 
                  WHERE DATE(created_at) = CURRENT_DATE";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro ao buscar estatísticas de pedidos de hoje: " . $e->getMessage());
        return [
            'total_orders' => 0,
            'total_amount' => 0,
            'average_amount' => 0,
            'completed_orders' => 0,
            'active_orders' => 0
        ];
    }
}

/**
 * Obtém o total de vendas de hoje
 * @return float
 */
function get_today_sales() {
    global $pdo;
    try {
        $query = "SELECT COALESCE(SUM(total_amount), 0) as total 
                  FROM sales 
                  WHERE DATE(sale_date) = CURRENT_DATE 
                  AND status = 'completed'";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return floatval($result['total']);
    } catch (PDOException $e) {
        error_log("Erro ao buscar vendas de hoje: " . $e->getMessage());
        return 0.0;
    }
}

/**
 * Obtém comparação de vendas com o dia anterior
 * @return array
 */
function get_sales_comparison() {
    global $pdo;
    try {
        $query = "SELECT 
                    (SELECT COALESCE(SUM(total_amount), 0)
                     FROM sales 
                     WHERE DATE(sale_date) = CURRENT_DATE
                     AND status = 'completed') as today_sales,
                    (SELECT COALESCE(SUM(total_amount), 0)
                     FROM sales 
                     WHERE DATE(sale_date) = DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)
                     AND status = 'completed') as yesterday_sales";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calcular a variação percentual
        $today = floatval($result['today_sales']);
        $yesterday = floatval($result['yesterday_sales']);
        $variation = $yesterday > 0 ? (($today - $yesterday) / $yesterday) * 100 : 0;
        
        return [
            'today' => $today,
            'yesterday' => $yesterday,
            'variation' => round($variation, 2)
        ];
    } catch (PDOException $e) {
        error_log("Erro ao buscar comparação de vendas: " . $e->getMessage());
        return [
            'today' => 0,
            'yesterday' => 0,
            'variation' => 0
        ];
    }
}