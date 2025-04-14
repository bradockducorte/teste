<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>COMEX TRACKING</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"/>
  <style>
    body {
      margin: 0;
      background-color: #f4f7fb;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #333;
      line-height: 1.6;
    }

    .container {
      max-width: 900px;
      margin: 0 auto;
      padding: 2rem 1rem;
    }

    .image-container {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 2rem 0;
    }

    .logo-image {
      max-width: 100%;
      width: 100%;
      max-width: 600px;
      height: auto;
    }

    .order-code-title {
      text-align: center;
      font-size: 1.6rem;
      font-weight: bold;
      color: #F90;
      margin-bottom: 2rem;
    }

    h2 {
      text-align: center;
      font-size: 1.7rem;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 20px;
      margin: 0 0 1rem;
    }

    h2 img {
      width: 30px;
      height: auto;
    }

    .tracking-info {
      background-color: #ffffff;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      margin-top: 2rem;
    }

    .tracking-info .tracking-item {
      display: flex;
      align-items: center;
      margin-bottom: 1rem;
    }

    .tracking-info .tracking-item i {
      font-size: 1.4rem;
      color: #F90;
      margin-right: 1rem;
    }

    .tracking-info .tracking-item span {
      font-size: 1.1rem;
      color: #555;
    }

    .tracking-info .tracking-item strong {
      font-weight: bold;
      color: #333;
    }

    .timeline {
      margin-top: 2rem;
    }

    .timeline-item {
      background-color: #ffffff;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 1.5rem;
      border-left: 5px solid #f90;
    }

    .timeline-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .timeline-row h4 {
      margin: 0;
      font-weight: bold;
      font-size: 1.1rem;
      color: #333;
    }

    .timeline-data {
      font-size: 0.9rem;
      color: #777;
    }

    .timeline-status {
      font-size: 1rem;
      margin-top: 0.5rem;
      color: #333;
    }

    .footer {
      text-align: center;
      margin-top: 3rem;
      font-size: 0.9rem;
      color: #000000;
    }

    /* Responsividade para dispositivos móveis */
    @media (max-width: 768px) {
      .container {
        padding: 1rem;
      }

      .logo-image {
        width: 100%;
      }

      .tracking-info, .timeline-item {
        padding: 1rem;
        border-radius: 8px;
      }

      .tracking-info .tracking-item {
        padding: 0.8rem;
      }

      .timeline-row h4 {
        font-size: 1.1rem;
      }

      .timeline-data {
        font-size: 0.85rem;
      }

      .timeline-status {
        font-size: 0.9rem;
      }

      .footer {
        font-size: 0.8rem;
      }
    }

    /* Responsividade para telas menores que 480px */
    @media (max-width: 480px) {
      h2 {
        font-size: 1.5rem;
      }

      .tracking-info, .timeline-item {
        padding: 0.8rem;
      }

      .timeline-row h4 {
        font-size: 1rem;
      }

      .timeline-data, .timeline-status {
        font-size: 0.8rem;
      }

      .footer {
        font-size: 0.7rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="image-container">
      <!-- Logo substituída pela nova imagem -->
      <img src="baner.jpg" alt="comexlogo.png" class="logo-image" />
    </div>

    <?php
    $conn = new mysqli("localhost", "u620302324_root", "Ab741852963$", "u620302324_painel");
    if ($conn->connect_error) {
        die("Erro de conexão: " . $conn->connect_error);
    }

    $codigo = $_POST['codigo_rastreio'] ?? '';
    $isInternational = (substr($codigo, -3) === "INT");

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['codigo_rastreio'])) {
        $sql_rastreio = "SELECT * FROM rastreio WHERE codigo = '$codigo'";
        $result_rastreio = $conn->query($sql_rastreio);

        if ($result_rastreio->num_rows > 0) {
           
            $rastreio = $result_rastreio->fetch_assoc();

            echo "<div class='tracking-info'>";
            echo "<div class='tracking-item'>";
            echo "<i class='fas fa-barcode'></i><span><strong>Código de Rastreamento:</strong> $codigo</span>";
            echo "</div>";

            if ($rastreio['origem'] != "Não especificado" && $rastreio['origem'] != "") {
                echo "<div class='tracking-item'>";
                echo "<i class='fas fa-map-marker-alt'></i><span><strong>Origem:</strong> {$rastreio['origem']}</span>";
                echo "</div>";
            }

            if ($rastreio['destino'] != "Não especificado" && $rastreio['destino'] != "") {
                echo "<div class='tracking-item'>";
                echo "<i class='fas fa-location-arrow'></i><span><strong>Destino:</strong> {$rastreio['destino']}</span>";
                echo "</div>";
            }
            echo "</div>"; // fim tracking-info

            $data_hoje = date('Y-m-d H:i:s');
            $sql_etapas = "SELECT * FROM rastreio_etapas WHERE codigo = '$codigo' AND data_hora <= '$data_hoje' ORDER BY data_hora ASC";
            $result_etapas = $conn->query($sql_etapas);

            if ($result_etapas->num_rows > 0) {
                echo "<div class='timeline'>";
                while ($etapa = $result_etapas->fetch_assoc()) {
                    $data_formatada = date('d/m H:i', strtotime($etapa['data_hora']));
                    echo "<div class='timeline-item'>";
                    echo "<div class='timeline-row'>";
                    echo "<h4><i class='fas fa-box'></i> " . ($etapa['localizacao'] ?? "Local não especificado") . "</h4>";
                    echo "<span class='timeline-data'>$data_formatada</span>";
                    echo "</div>";
                    echo "<p class='timeline-status'>" . ($etapa['descricao'] ?? "Sem descrição") . "</p>";
                    echo "</div>";
                }
                echo "</div>";
            } else {
                echo "<p>Sem etapas para exibir.</p>";
            }
        } else {
            echo "<p>Não encontramos o código de rastreamento.</p>";
        }
    }
    ?>

    <div class="footer">
      &copy; Copyright © 2022 Comex Tracking LTDA. Todos os direitos reservados.
    </div>
  </div>
</body>
</html>
