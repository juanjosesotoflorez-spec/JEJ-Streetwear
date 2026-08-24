<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}
include("../config/db.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Métodos de Pago - JE&J Streetwear</title>
    <link rel="stylesheet" href="css/pagos.css">
    <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>
    <header style="padding:18px; text-align:center;">
        <div class="logo">JE&J <span>Streetwear</span></div>
        <nav style="margin-top:10px;">
            <a href="dashboard.php">Dashboard</a>
            <a href="perfil.php">Mi Perfil</a>
            <a href="../auth/logout.php">Cerrar Sesión</a>
        </nav>
    </header>

    <main style="padding: 28px;">
        <section class="payments-section">
            <h2 class="section-title">Métodos de Pago</h2>
            <p class="lead">Elige la forma de pago que prefieras. Todas las opciones son seguras y están disponibles para tus compras.</p>

            <div class="payments-grid">
                <article class="payment-card">
                    <div class="payment-icon">💳</div>
                    <h3>Tarjeta de Crédito / Débito</h3>
                    <p>Paga con Visa, MasterCard o American Express. Seguridad con 3D Secure y encriptación.</p>
                    <ul>
                        <li>Pago inmediato</li>
                        <li>Aceptamos principales bancos</li>
                    </ul>
                    <button class="btn-select" onclick="seleccionar('Tarjeta')">Seleccionar</button>
                </article>

                <article class="payment-card">
                    <div class="payment-icon">🅿️</div>
                    <h3>PayPal</h3>
                    <p>Usa tu cuenta PayPal para pagar de forma rápida y protegida sin compartir tus datos bancarios.</p>
                    <ul>
                        <li>Pago rápido</li>
                        <li>Protección al comprador</li>
                    </ul>
                    <button class="btn-select" onclick="seleccionar('PayPal')">Seleccionar</button>
                </article>

                <article class="payment-card">
                    <div class="payment-icon">🏦</div>
                    <h3>Transferencia Bancaria</h3>
                    <p>Realiza una transferencia desde tu banco. Envíanos el comprobante para procesar el pedido.</p>
                    <ul>
                        <li>Puede demorar 1-2 días hábiles</li>
                        <li>Ideal para pedidos grandes</li>
                    </ul>
                    <button class="btn-select" onclick="seleccionar('Transferencia')">Seleccionar</button>
                </article>

                <article class="payment-card">
                    <div class="payment-icon">💵</div>
                    <h3>Pago Contra Entrega</h3>
                    <p>Paga en efectivo cuando recibas tu pedido. Disponible en zonas seleccionadas.</p>
                    <ul>
                        <li>Pago al recibir</li>
                        <li>Suplemento de envío posible</li>
                    </ul>
                    <button class="btn-select" onclick="seleccionar('Contra Entrega')">Seleccionar</button>
                </article>

                <article class="payment-card">
                    <div class="payment-icon">🏪</div>
                    <h3>Pago en Efectivo (Convenios)</h3>
                    <p>Paga en puntos de pago autorizados (ej. OXXO, PagoFácil). Generamos un código de pago.</p>
                    <ul>
                        <li>Pago en efectivo en tiendas</li>
                        <li>Válido por 48 horas</li>
                    </ul>
                    <button class="btn-select" onclick="seleccionar('Pago en Efectivo')">Seleccionar</button>
                </article>

                <article class="payment-card">
                    <div class="payment-icon">🪙</div>
                    <h3>MercadoPago / Local</h3>
                    <p>Soporte para gateways locales populares (MercadoPago, etc.).</p>
                    <ul>
                        <li>Pagos con cuotas según disponibilidad</li>
                    </ul>
                    <button class="btn-select" onclick="seleccionar('MercadoPago')">Seleccionar</button>
                </article>
            </div>

        </section>
    </main>

    <footer>
        <p style="text-align:center; color:#aaa;">&copy; <?= date('Y') ?> JE&J Streetwear</p>
    </footer>

    <script>
        function seleccionar(name){
            alert('Has seleccionado: ' + name + "\nEn el siguiente paso podrás confirmar tu forma de pago al completar el pedido.");
        }
    </script>
</body>
</html>



